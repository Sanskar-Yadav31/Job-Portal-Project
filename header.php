<?php
session_start();
require_once 'db.php';
$page_title = isset($page_title) ? $page_title : "Student Job Portal";
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.5); }
        .glass-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 16px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08); transition: transform 0.2s; }
        .glass-card:hover { transform: translateY(-3px); }
        .btn-gradient { background: linear-gradient(90deg, #2563eb, #7c3aed); color: white; border: none; font-weight: 600; }
        .btn-gradient:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: white; }
        /* Page-specific CSS yahan add kar sakte ho */
        <?= isset($custom_css) ? $custom_css : '' ?>
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= $role === 'company' ? 'dashboard.php' : 'dashboard.php' ?>">
            <?= $role === 'company' ? '🏢 Company Portal' : '🎓 Student Portal' ?>
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="text-muted me-2 d-none d-md-inline">Welcome, User</span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout <i class="bi bi-box-arrow-right"></i></a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-4">