<?php
session_start();
include 'config/db.php';

// Check if user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get all users with their parent names
$sql = "SELECT u1.username, u1.email, u1.referral_code, u2.username AS referrer 
        FROM users u1 
        LEFT JOIN users u2 ON u1.parent_id = u2.id";
$all_users = $conn->query($sql);
?>

<h2>Admin Panel - Marketing System</h2>

<table border="1">
    <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Referral Code</th>
        <th>Referred By (Parent)</th>
    </tr>
    <?php while ($row = $all_users->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['referral_code']; ?></td>
            <td><?php echo $row['referrer'] ? $row['referrer'] : 'N/A (Super User)'; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<a href="logout.php">Logout</a>