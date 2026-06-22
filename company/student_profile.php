<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

// 🆔 Fetch Company Info for Notification
$stmt_com = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt_com->execute([$_SESSION['user_id']]);
$company = $stmt_com->fetch();
if (!$company) { header("Location: ../login.php"); exit(); }

// 📥 Get Student ID from URL
$target_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($target_user_id <= 0) { header("Location: browse_students.php"); exit(); }

// 📋 Fetch Student Profile
$stmt_stu = $pdo->prepare("SELECT s.*, u.email, u.created_at FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt_stu->execute([$target_user_id]);
$student = $stmt_stu->fetch();
if (!$student) { header("Location: browse_students.php"); exit(); }

// 💼 Fetch Skills
$stmt_skills = $pdo->prepare("SELECT sk.skill_name FROM student_skills ss JOIN skills sk ON ss.skill_id = sk.id WHERE ss.student_id = ? ORDER BY sk.skill_name ASC");
$stmt_skills->execute([$student['id']]);
$skills = $stmt_skills->fetchAll(PDO::FETCH_COLUMN);

// 📊 Fetch Activity Stats
$stmt_apps = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$stmt_apps->execute([$student['id']]);
$app_count = $stmt_apps->fetchColumn();

// 🚀 Handle Notification
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify'])) {
    try {
        $msg = "Congratulations! Your profile has been shortlisted by <b>" . htmlspecialchars($company['company_name']) . "</b>.";
        $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Shortlisted', ?)");
        $stmt_notif->execute([$target_user_id, $msg]);
        $success_msg = "✅ Shortlist notification sent successfully!";
    } catch (PDOException $e) {
        $error_msg = "❌ Failed to send notification. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | Company Portal</title>
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
            padding: 2.5rem;
            max-width: 750px;
            margin: 2rem auto;
        }
        .profile-avatar {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; font-weight: bold;
        }
        .info-item { margin-bottom: 0.75rem; }
        .info-label { font-weight: 600; color: #64748b; font-size: 0.85rem; display: block; }
        .info-value { color: #0f172a; font-size: 1rem; }
        .skill-tag {
            display: inline-block; background: #e0e7ff; color: #1e40af;
            padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;
            margin: 3px 4px 3px 0;
        }
        .btn-primary { background: linear-gradient(90deg, #2563eb, #7c3aed); border: none; font-weight: 600; }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: #fff; }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">🏢 Company Portal</a>
        <div class="ms-auto">
            <a href="browse_students.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Browse</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="glass-card">
        <!-- Profile Header -->
        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
            <div class="profile-avatar me-3"><?= strtoupper(substr($student['full_name'], 0, 1)) ?></div>
            <div>
                <h3 class="fw-bold mb-1"><?= htmlspecialchars($student['full_name']) ?></h3>
                <p class="text-muted mb-0"><?= htmlspecialchars($student['field_of_interest']) ?> Aspirant | 📍 <?= htmlspecialchars($student['city']) ?></p>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $error_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Details Grid -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-item"><span class="info-label">📧 Email</span><span class="info-value"><?= htmlspecialchars($student['email']) ?></span></div>
                <div class="info-item"><span class="info-label">📞 Phone</span><span class="info-value"><?= htmlspecialchars($student['phone']) ?></span></div>
            </div>
            <div class="col-md-6">
                <div class="info-item"><span class="info-label">🎓 Education</span><span class="info-value"><?= htmlspecialchars($student['education'] ?: 'Not specified') ?></span></div>
                <div class="info-item"><span class="info-label">📅 Member Since</span><span class="info-value"><?= date('M d, Y', strtotime($student['created_at'])) ?></span></div>
            </div>
        </div>

        <!-- Skills Section -->
        <div class="mb-4">
            <h6 class="fw-semibold text-muted mb-2"><i class="bi bi-stars me-1"></i>Skills</h6>
            <?php if (count($skills) > 0): ?>
                <div class="d-flex flex-wrap">
                    <?php foreach ($skills as $sk): ?>
                        <span class="skill-tag"><?= htmlspecialchars($sk) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted fst-italic mb-0">No skills added yet.</p>
            <?php endif; ?>
        </div>

        <!-- Activity Stats -->
        <div class="bg-light rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">📊 Platform Activity</h6>
                <small class="text-muted">Total jobs applied by this student</small>
            </div>
            <h3 class="fw-bold text-primary mb-0"><?= $app_count ?></h3>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-3">
            <form method="POST" class="flex-grow-1" onsubmit="return confirm('Send shortlist notification to this student?');">
                <button type="submit" name="notify" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-send-fill me-2"></i>Send Shortlist Notification
                </button>
            </form>
            <a href="browse_students.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>