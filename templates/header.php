<?php
// This header assumes a session has been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Marketing System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 48px 0 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        background-color: #2c3e50;
    }

    .sidebar-sticky {
        position: relative;
        top: 0;
        height: calc(100vh - 48px);
        padding-top: .5rem;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .nav-link {
        font-weight: 500;
        color: #c7d0d9;
    }

    .nav-link .bi {
        margin-right: 8px;
    }

    .nav-link:hover {
        color: #fff;
    }

    .nav-link.active {
        color: #fff;
    }

    .navbar-brand {
        padding-top: .75rem;
        padding-bottom: .75rem;
        font-size: 1rem;
        background-color: rgba(0, 0, 0, .25);
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
    }

    .navbar .navbar-toggler {
        top: .25rem;
        right: 1rem;
    }
    </style>
</head>

<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">My System</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <?php if ($is_admin) : ?>
                        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i
                                    class="bi bi-person-badge"></i> Admin Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="refferels.php"><i class="bi bi-people"></i> View
                                Network</a></li>
                        <li class="nav-item"><a class="nav-link" href="sales_settings.php"><i
                                    class="bi bi-cart-check"></i> Sales Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="commission_settings.php"><i
                                    class="bi bi-sliders"></i> Commission Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="hierarchy_payments.php"><i
                                    class="bi bi-diagram-3"></i> Hierarchy Payments</a></li>
                        <?php else : ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door"></i>
                                Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="refferels.php"><i class="bi bi-people"></i> My
                                Network</a></li>
                        <?php endif; ?>
                    </ul>
                    <hr class="text-white">
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i
                                    class="bi bi-door-open"></i> Logout</a></li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h1>
                </div>