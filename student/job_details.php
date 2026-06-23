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

// 📥 Get Job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($job_id <= 0) { header("Location: view_jobs.php"); exit(); }

// 📋 Fetch Job + Company Details
$stmt_job = $pdo->prepare("
    SELECT j.*, c.company_name, c.industry, c.website 
    FROM jobs j 
    LEFT JOIN companies c ON j.company_id = c.id 
    WHERE j.id = ? AND j.status = 'active'
");
$stmt_job->execute([$job_id]);
$job = $stmt_job->fetch();

if (!$job) { header("Location: view_jobs.php"); exit(); }

// 🔍 Check if already applied
$stmt_app = $pdo->prepare("SELECT status, applied_at FROM applications WHERE job_id = ? AND student_id = ?");
$stmt_app->execute([$job_id, $student_id]);
$application = $stmt_app->fetch();

// 🚀 Handle Apply Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if (!$application) {
        try {
            $stmt_insert = $pdo->prepare("INSERT INTO applications (job_id, student_id, status) VALUES (?, ?, 'applied')");
            $stmt_insert->execute([$job_id, $student_id]);
            header("Location: job_details.php?id=$job_id&success=1");
            exit();
        } catch (PDOException $e) {
            $error_msg = "❌ Failed to submit application. Please try again.";
        }
    } else {
        $error_msg = "⚠️ You have already applied for this job.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> | Job Details</title>
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
        .btn-primary {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            border: none; font-weight: 600;
        }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); }
        .skill-tag { background: #f3e8ff; color: #5b21b6; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; }
        .status-applied { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .apply-card { position: sticky; top: 20px; }
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
    <!-- Back Button -->
    <a href="view_jobs.php" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left me-1"></i>Back to Jobs
    </a>

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Application submitted successfully! You can track the status in <a href="my_applications.php" class="alert-link">My Applications</a>.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($error_msg)): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <?= $error_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- 📝 Left: Job Details -->
        <div class="col-lg-8">
            <div class="glass-card p-4">
                <h2 class="fw-bold mb-3"><?= htmlspecialchars($job['title']) ?></h2>
                <div class="d-flex flex-wrap gap-3 text-muted mb-4">
                    <span><i class="bi bi-building me-1"></i><?= htmlspecialchars($job['company_name'] ?? 'Unknown Company') ?></span>
                    <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['city']) ?></span>
                    <span><i class="bi bi-calendar me-1"></i>Posted: <?= date('M d, Y', strtotime($job['posted_at'])) ?></span>
                </div>

                <h5 class="fw-semibold mt-4 border-bottom pb-2">Job Description</h5>
                <div class="text-secondary lh-lg mt-3">
                    <?= nl2br(htmlspecialchars($job['description'])) ?>
                </div>

                <h5 class="fw-semibold mt-4 border-bottom pb-2">Required Skills</h5>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php 
                    $skills = explode(',', $job['required_skills']);
                    foreach ($skills as $skill):
                        $skill = trim($skill);
                        if ($skill): ?>
                        <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                    <?php endif; endforeach; ?>
                </div>

                <?php if ($job['salary_range']): ?>
                    <h5 class="fw-semibold mt-4 border-bottom pb-2">Salary Range</h5>
                    <p class="fs-5 text-primary fw-bold mt-2 mb-0">💰 <?= htmlspecialchars($job['salary_range']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 🚀 Right: Apply & Company Info Card -->
        <div class="col-lg-4">
            <div class="glass-card p-4 apply-card">
                <h5 class="fw-bold mb-3 text-center">Application Status</h5>
                
                <?php if ($application): ?>
                    <div class="alert status-applied text-center p-3">
                        <i class="bi bi-check-circle-fill fs-3 d-block mb-2"></i>
                        <strong class="d-block fs-5">Applied ✅</strong>
                        <small class="text-muted">Submitted on <?= date('M d, Y', strtotime($application['applied_at'])) ?></small>
                    </div>
                <?php else: ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to apply for this job?');">
                        <button type="submit" name="apply" class="btn btn-primary w-100 py-3 fs-5 shadow-sm">
                            <i class="bi bi-send-fill me-2"></i>Apply Now
                        </button>
                    </form>
                <?php endif; ?>

                <hr class="my-4">
                <h6 class="fw-semibold text-muted mb-3"><i class="bi bi-info-circle me-1"></i>Company Info</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-building me-2 text-primary"></i><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></li>
                    <li class="mb-2"><i class="bi bi-tag me-2 text-primary"></i><?= htmlspecialchars($job['industry'] ?? 'N/A') ?></li>
                    <?php if ($job['website']): ?>
                        <li><i class="bi bi-globe me-2 text-primary"></i><a href="https://<?= htmlspecialchars($job['website']) ?>" target="_blank" class="text-decoration-none">Visit Website ↗</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>