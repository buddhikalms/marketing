<?php
session_start();
include 'config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Authentication required.']);
    exit();
}

$viewing_user_id = $_SESSION['user_id'];
$user_to_view_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_to_view_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID.']);
    exit();
}

// Security Check: Function to verify if a user is in the current user's downline
function is_in_downline($conn, $viewer_id, $target_id)
{
    if ($viewer_id == $target_id) return true; // User can view their own details

    $stmt = $conn->prepare("SELECT parent_id FROM users WHERE id = ?");
    $current_id = $target_id;

    while ($current_id != 0) {
        $stmt->bind_param("i", $current_id); // Bind the current ID in the hierarchy
        $stmt->execute(); // Execute for the current ID
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['parent_id'] == $viewer_id) {
                $stmt->close();
                return true;
            }
            $current_id = $row['parent_id'];
        } else {
            break; // User not found or no parent
        }
    }
    $stmt->close();
    return false;
}

if (!is_in_downline($conn, $viewing_user_id, $user_to_view_id)) {
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to view this user.']);
    exit();
}

$stmt = $conn->prepare("
    SELECT 
        u.username, u.full_name, u.email, u.nic_number, u.gender, u.district, u.city, u.address, u.contact_number, u.created_at,
        COALESCE((SELECT SUM(amount) FROM sales WHERE user_id = u.id), 0) as total_sales,
        COALESCE((SELECT COUNT(*) FROM sales WHERE user_id = u.id), 0) as sales_count,
        COALESCE((SELECT SUM(amount) FROM commissions WHERE user_id = u.id), 0) as total_commissions
    FROM users u 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_to_view_id);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found.']);
}
$stmt->close();