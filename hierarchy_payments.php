<?php
session_start();
$page_title = "Hierarchy Payments";
include 'config/db.php';

// Admin-only access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include 'templates/header.php';
?>

<div class="alert alert-info">This page is under construction. A report or overview of hierarchy-based payments will be displayed here.</div>

<?php include 'templates/footer.php'; ?>