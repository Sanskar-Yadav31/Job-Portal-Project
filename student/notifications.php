<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 📥 Fetch Notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// 🏷️ Handle Mark as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
    $notif_id = (int)$_POST['mark_read_id'];
    $update = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $update->execute([$notif_id, $user_id]);
    header("Location: notifications.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.5); }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }
        .notif-unread { border-left: 4px solid #2563eb; background: rgba(37, 99, 235, 0.05); }
        .notif-read { border-left: 4px solid #cbd5e1; opacity: 0.85; }
        .btn-mark { background: #f3e8ff; color: #5b21b6; border: none; font-size: 0.8rem; }
        .btn-mark:hover { background: #ddd6fe; }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">🎓 Student Portal</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-grid me-1"></i>Dashboard</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="glass-card p-4">
        <h3 class="fw-bold mb-4">🔔 Your Notifications</h3>

        <?php if (count($notifications) > 0): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $notif): 
                    $is_unread = ($notif['is_read'] == 0);
                ?>
                <div class="list-group-item p-4 mb-3 rounded-3 shadow-sm <?= $is_unread ? 'notif-unread' : 'notif-read' ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1 <?= $is_unread ? 'text-primary' : 'text-muted' ?>">
                                <?= htmlspecialchars($notif['title']) ?>
                            </h6>
                            <p class="mb-1 small text-dark"><?= $notif['message'] ?></p>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?></small>
                        </div>
                        <?php if ($is_unread): ?>
                            <form method="POST" class="ms-3">
                                <input type="hidden" name="mark_read_id" value="<?= $notif['id'] ?>">
                                <button type="submit" class="btn btn-mark px-3 py-1">Mark as Read</button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-light text-muted border">Read</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                <h5>No notifications yet</h5>
                <p>Companies will notify you when they shortlist your profile.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>