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
$levels = [];
if ($levels_result) {
    while ($row = $levels_result->fetch_assoc()) {
        $levels[] = $row;
    }
}

include 'templates/header.php';
include 'views/commission_settings_view.php';
include 'templates/footer.php';
