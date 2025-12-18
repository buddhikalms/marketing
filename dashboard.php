<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current user details
$user_query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user = $user_query->fetch_assoc();

// Get downline count
$downline_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE parent_id = '$user_id'");
$downline_count = $downline_query->fetch_assoc()['total'];

// Get downline members list
$members = $conn->query("SELECT username, email, created_at FROM users WHERE parent_id = '$user_id' ORDER BY created_at DESC");

// Referral Link generation
$ref_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/register.php?ref=" . $user['referral_code'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            height: 100vh;
            background: #2c3e50;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
        }

        .sidebar a:hover {
            background: #34495e;
        }

        .stat-card {
            border-radius: 10px;
            border: none;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar d-none d-md-block">
                <h4 class="text-center">My System</h4>
                <hr>
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="#">💰 Commissions</a>
                <a href="#">👥 My Team</a>
                <a href="logout.php" class="text-danger">🚪 Logout</a>
            </div>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <span class="badge bg-primary">Account Type: <?php echo ucfirst($user['role']); ?></span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white p-3">
                            <h5>Referral Code</h5>
                            <h3><?php echo $user['referral_code']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white p-3">
                            <h5>Direct Referrals</h5>
                            <h3><?php echo $downline_count; ?> Members</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-warning text-dark p-3">
                            <h5>Total Commission</h5>
                            <h3>$0.00</h3>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Share Your Referral Link</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo $ref_link; ?>" id="refLink"
                                readonly>
                            <button class="btn btn-outline-primary" onclick="copyLink()">Copy Link</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">My Recent Referrals</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Joined Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($members->num_rows > 0): ?>
                                    <?php while ($row = $members->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['username']; ?></td>
                                            <td><?php echo $row['email']; ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No referrals yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyLink() {
            var copyText = document.getElementById("refLink");
            copyText.select();
            document.execCommand("copy");
            alert("Referral link copied!");
        }
    </script>

</body>

</html>