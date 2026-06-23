<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// 🆔 Fetch Student ID
$stmt_stu = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt_stu->execute([$_SESSION['user_id']]);
$student = $stmt_stu->fetch();
if (!$student) { header("Location: ../login.php"); exit(); }
$student_id = $student['id'];

// 🚫 Handle Cancel/Withdraw Application
$success_msg = '';
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_app_id'])) {
    $app_id = (int)$_POST['cancel_app_id'];
    // Sirf 'applied' status wali application cancel kar sakte hain
    $check = $pdo->prepare("SELECT status FROM applications WHERE id = ? AND student_id = ?");
    $check->execute([$app_id, $student_id]);
    $app = $check->fetch();

    if ($app && $app['status'] === 'applied') {
        $del = $pdo->prepare("DELETE FROM applications WHERE id = ? AND student_id = ?");
        $del->execute([$app_id, $student_id]);
        $success_msg = "✅ Application withdrawn successfully.";
    } else {
        $error_msg = "⚠️ Application already processed. Cannot withdraw.";
    }
}

// 📥 Fetch All Applications for this Student
$stmt_apps = $pdo->prepare("
    SELECT a.id, a.status, a.applied_at, j.title as job_title, j.city, c.company_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
");
$stmt_apps->execute([$student_id]);
$applications = $stmt_apps->fetchAll();

// 🎨 Status Badge Mapping
function getStatusBadge($status) {
    $classes = [
        'applied' => 'bg-primary',
        'shortlisted' => 'bg-warning text-dark',
        'interview' => 'bg-info text-dark',
        'hired' => 'bg-success',
        'rejected' => 'bg-danger'
    ];
    $class = $classes[$status] ?? 'bg-secondary';
    return "<span class='badge $class px-3 py-2'>$status</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications | Student Portal</title>
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
        .btn-danger-outline {
            color: #dc3545; border: 1px solid #dc3545; font-size: 0.8rem; padding: 4px 10px;
        }
        .btn-danger-outline:hover { background: #dc3545; color: white; }
        .table th { background: rgba(243, 232, 255, 0.5); font-weight: 600; }
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
        <h3 class="fw-bold mb-4">📋 My Applications</h3>

        <!-- Alerts -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-warning alert-dismissible fade show"><?= $error_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (count($applications) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Location</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($app['job_title']) ?></td>
                            <td><?= htmlspecialchars($app['company_name']) ?></td>
                            <td><i class="bi bi-geo-alt text-muted me-1"></i><?= htmlspecialchars($app['city']) ?></td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td><?= getStatusBadge($app['status']) ?></td>
                            <td>
                                <?php if ($app['status'] === 'applied'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to withdraw this application?');" style="display:inline;">
                                        <input type="hidden" name="cancel_app_id" value="<?= $app['id'] ?>">
                                        <button type="submit" class="btn btn-danger-outline">Withdraw</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                <h5>No applications yet</h5>
                <p>Browse jobs and apply to start tracking your progress.</p>
                <a href="view_jobs.php" class="btn btn-primary mt-2">🔍 Browse Jobs</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>