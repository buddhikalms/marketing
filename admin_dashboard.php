<?php
session_start();
include 'config/db.php';
$page_title = "Admin Dashboard";

// Check if user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get all users with their parent names
$sql = "SELECT u1.username, u1.email, u1.referral_code, u2.username AS referrer 
        FROM users u1 
        LEFT JOIN users u2 ON u1.parent_id = u2.id";
$all_users_result = $conn->query($sql);

include 'templates/header.php';
?>

<div class="card">
    <div class="card-header">
        All Users
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Referral Code</th>
                    <th>Referred By (Parent)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $all_users_result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['referral_code']); ?></td>
                    <td><?php echo $row['referrer'] ? htmlspecialchars($row['referrer']) : '<span class="text-muted">N/A</span>'; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>