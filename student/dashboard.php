<?php 
session_start();
require_once '../includes/db.php';

// 🔒 Security Check: Logged in? Role student hai?
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 📥 Fetch Student Profile Data
$stmt = $pdo->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: ../login.php");
    exit();
}

// 📊 Calculate Stats
$total_jobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();

$stmt_apps = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$stmt_apps->execute([$student['id']]);
$apps_count = $stmt_apps->fetchColumn();

$stmt_short = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ? AND status = 'shortlisted'");
$stmt_short->execute([$student['id']]);
$short_count = $stmt_short->fetchColumn();

// 📋 Fetch Latest 5 Active Jobs
$stmt_jobs = $pdo->query("
    SELECT j.*, COALESCE(c.company_name, 'Unknown Company') as company_name 
    FROM jobs j 
    LEFT JOIN companies c ON j.company_id = c.id 
    WHERE j.status = 'active' 
    ORDER BY j.posted_at DESC 
    LIMIT 5
");
$latest_jobs = $stmt_jobs->fetchAll();
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Job Portal</title>
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
            transition: transform 0.2s;
        }
        .glass-card:hover { transform: translateY(-3px); }
        .stat-icon { font-size: 2rem; opacity: 0.8; }
        .btn-gradient {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            color: white; border: none; font-weight: 600;
        }
        .btn-gradient:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: white; }
        .job-card { border-left: 4px solid #7c3aed; }
        .badge-status { font-size: 0.8rem; }
    </style>
</head>
<body> 

🧭 Navbar
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">🎓 JobPortal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <ul class="navbar-nav align-items-center gap-3">
                <li class="nav-item"><span class="text-muted">Welcome,</span> <span class="fw-semibold text-dark"><?= htmlspecialchars($student['full_name']) ?></span></li>
                <li class="nav-item"><a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout <i class="bi bi-box-arrow-right"></i></a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <!-- 👋 Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4">
                <h2 class="fw-bold mb-2">Dashboard 👋</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($student['city']) ?> | 
                    <i class="bi bi-briefcase-fill text-primary"></i> <?= htmlspecialchars($student['field_of_interest']) ?> | 
                    <i class="bi bi-envelope-fill text-primary"></i> <?= htmlspecialchars($student['email']) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- 📊 Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1">Total Active Jobs</p>
                    <h3 class="fw-bold mb-0"><?= $total_jobs ?></h3>
                </div>
                <i class="bi bi-globe stat-icon text-primary"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1">My Applications</p>
                    <h3 class="fw-bold mb-0"><?= $apps_count ?></h3>
                </div>
                <i class="bi bi-file-earmark-text stat-icon text-success"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1">Shortlisted</p>
                    <h3 class="fw-bold mb-0"><?= $short_count ?></h3>
                </div>
                <i class="bi bi-check-circle stat-icon text-warning"></i>
            </div>
        </div>
    </div>

    <!-- ⚡ Quick Actions -->
    <div class="row g-3 mb-5">
        <div class="col-12">
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-3">Quick Actions</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="view_jobs.php" class="btn btn-gradient"><i class="bi bi-search me-2"></i>Browse Jobs</a>
                    <!-- My Applications -->
                    <a href="my_applications.php" class="btn btn-outline-primary"><i class="bi bi-journal-check me-2"></i>My Applications</a>

                    <!-- Edit Profile -->
                    <a href="profile.php" class="btn btn-outline-primary"><i class="bi bi-person-gear me-2"></i>Edit Profile</a>

                    <!-- Manage Skills -->
                    <a href="skills.php" class="btn btn-outline-primary"><i class="bi bi-stars me-2"></i>Manage Skills</a>
                    
                    <!-- Notification -->
                    <a href="notifications.php" class="btn btn-outline-primary"><i class="bi bi-bell me-2"></i>Notifications</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 Latest Jobs -->
    <div class="row">
        <div class="col-12">
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4">🔥 Latest Job Openings</h5>
                <?php if (count($latest_jobs) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Location</th>
                                    <th>Field</th>
                                    <th>Posted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latest_jobs as $job): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($job['title']) ?></td>
                                    <td><?= htmlspecialchars($job['company_name']) ?></td>
                                    <td><i class="bi bi-geo-alt text-muted me-1"></i><?= htmlspecialchars($job['city']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($job['job_field']) ?></span></td>
                                    <td class="text-muted small"><?= date('M d, Y', strtotime($job['posted_at'])) ?></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No active jobs available right now. Check back later!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>