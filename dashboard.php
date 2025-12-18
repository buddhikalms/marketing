<?php
session_start();
$page_title = "User Dashboard";
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Use prepared statements for security
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get downline count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE parent_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$downline_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get downline members list
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE parent_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$members = $stmt->get_result();

// Referral Link generation
$ref_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?ref=" . $user['referral_code'];

include 'templates/header.php';
?>

<div class="alert alert-info">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary p-3">
            <h5>Referral Code</h5>
            <h3><?php echo $user['referral_code']; ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success p-3">
            <h5>Direct Referrals</h5>
            <h3><?php echo $downline_count; ?> Members</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-dark bg-warning p-3">
            <h5>Total Commission</h5>
            <h3>$0.00</h3>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Share Your Referral Link</h5>
        <div class="input-group">
            <input type="text" class="form-control" value="<?php echo $ref_link; ?>" id="refLink" readonly>
            <button class="btn btn-outline-primary" onclick="copyLink()">Copy Link</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">My Recent Referrals</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Joined Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($members->num_rows > 0) : ?>
                    <?php while ($row = $members->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="3" class="text-center">No referrals yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function copyLink() {
        var copyText = document.getElementById("refLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value).then(function() {
            alert("Referral link copied!");
        }, function(err) {
            alert("Failed to copy link.");
        });
    }
</script>

<?php include 'templates/footer.php'; ?>