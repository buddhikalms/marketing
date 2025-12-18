<?php
session_start();
$page_title = "Commission Settings";
include 'config/db.php';

// Admin-only access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle form submission for adding/updating levels
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_level'])) {
        $level = (int)$_POST['level'];
        $rate = (float)$_POST['rate'];

        if ($level > 0 && $rate >= 0) {
            // Use INSERT ... ON DUPLICATE KEY UPDATE to either add a new level or update an existing one
            $stmt = $conn->prepare("INSERT INTO commission_levels (level, rate) VALUES (?, ?) ON DUPLICATE KEY UPDATE rate = ?");
            $stmt->bind_param("idd", $level, $rate, $rate);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Commission level saved successfully.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error saving commission level.</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>Invalid level or rate.</div>";
        }
    } elseif (isset($_POST['delete_level'])) {
        $level_id = (int)$_POST['level_id'];
        $stmt = $conn->prepare("DELETE FROM commission_levels WHERE id = ?");
        $stmt->bind_param("i", $level_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Commission level deleted.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error deleting level.</div>";
        }
        $stmt->close();
    }
}

// Fetch all commission levels
$levels_result = $conn->query("SELECT * FROM commission_levels ORDER BY level ASC");

include 'templates/header.php';
?>

<?php echo $message; ?>

<div class="row">
    <!-- Form to add/update a level -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Add / Edit Commission Level</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="level" class="form-label">Hierarchy Level</label>
                        <input type="number" class="form-control" id="level" name="level" min="1" required>
                        <div class="form-text">e.g., 1 for direct referrals, 2 for their referrals, etc.</div>
                    </div>
                    <div class="mb-3">
                        <label for="rate" class="form-label">Commission Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" id="rate" name="rate" min="0" required>
                    </div>
                    <button type="submit" name="add_level" class="btn btn-primary">Save Level</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table of existing levels -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Existing Commission Levels</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Rate (%)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($levels_result->num_rows > 0) : ?>
                            <?php while ($row = $levels_result->fetch_assoc()) : ?>
                                <tr>
                                    <td>Level <?php echo htmlspecialchars($row['level']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rate']); ?>%</td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this level?');" style="display:inline;">
                                            <input type="hidden" name="level_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_level" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="text-center">No commission levels defined yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>