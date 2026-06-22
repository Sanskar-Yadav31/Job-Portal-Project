<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check: Logged in? Role company hai?
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 📥 Fetch Company Profile Data
$stmt = $pdo->prepare("SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();

if (!$company) {
    header("Location: ../login.php");
    exit();
}

// 📊 Calculate Stats
// 1. Total Jobs Posted by this company
$stmt_jobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ?");
$stmt_jobs->execute([$company['id']]);
$jobs_count = $stmt_jobs->fetchColumn();

// 2. Active Jobs Count
$stmt_active = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'active'");
$stmt_active->execute([$company['id']]);
$active_count = $stmt_active->fetchColumn();

// 3. Total Applications Received (across all jobs)
$stmt_apps = $pdo->prepare("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?");
$stmt_apps->execute([$company['id']]);
$apps_count = $stmt_apps->fetchColumn();

// 📋 Fetch Recent 5 Applications
$stmt_recent = $pdo->prepare("
    SELECT a.id, a.status, a.applied_at, s.full_name as student_name, j.title as job_title
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN students s ON a.student_id = s.id
    WHERE j.company_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt_recent->execute([$company['id']]);
$recent_apps = $stmt_recent->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard | Job Portal</title>
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
        .status-badge { font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 20px; }
        .status-applied { background: #e3f2fd; color: #1565c0; }
        .status-shortlisted { background: #fff3e0; color: #e65100; }
        .status-interview { background: #f3e5f5; color: #6a1b9a; }
        .status-hired { background: #e8f5e9; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">🏢 Company Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
            <ul class="navbar-nav align-items-center gap-3">
                <li class="nav-item"><span class="text-muted">Welcome,</span> <span class="fw-semibold text-dark"><?= htmlspecialchars($company['company_name']) ?></span></li>
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
                <h2 class="fw-bold mb-2">Company Dashboard 🏢</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($company['city']) ?> | 
                    <i class="bi bi-buildings text-primary"></i> <?= htmlspecialchars($company['industry']) ?> | 
                    <i class="bi bi-person-badge text-primary"></i> Contact: <?= htmlspecialchars($company['contact_person']) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- 📊 Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1">Jobs Posted</p>
                    <h3 class="fw-bold mb-0"><?= $jobs_count ?></h3>
                </div>
                <i class="bi bi-briefcase stat-icon text-primary"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1">Total Applications</p>
                    <h3 class="fw-bold mb-0"><?= $apps_count ?></h3>
                </div>
                <i class="bi bi-inbox stat-icon text-success"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1">Active Jobs</p>
                    <h3 class="fw-bold mb-0"><?= $active_count ?></h3>
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
                    <a href="post_job.php" class="btn btn-gradient"><i class="bi bi-plus-circle me-2"></i>Post New Job</a>
                    <a href="manage_jobs.php" class="btn btn-outline-primary"><i class="bi bi-list-check me-2"></i>Manage Jobs</a>
                    <a href="browse_students.php" class="btn btn-outline-primary"><i class="bi bi-people me-2"></i>Browse Students</a>
                    <a href="view_applications.php" class="btn btn-outline-primary"><i class="bi bi-eye me-2"></i>View Applications</a>
                    <!-- Is line ko dhundh aur iske baad add kar -->
                    <a href="profile.php" class="btn btn-outline-primary"><i class="bi bi-building-gear me-2"></i>Edit Profile</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 Recent Applications -->
    <div class="row">
        <div class="col-12">
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4">📥 Recent Applications</h5>
                <?php if (count($recent_apps) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Applied For</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_apps as $app): 
                                    $status_class = 'status-' . $app['status'];
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($app['student_name']) ?></td>
                                    <td><?= htmlspecialchars($app['job_title']) ?></td>
                                    <td><span class="badge <?= $status_class ?>"><?= ucfirst($app['status']) ?></span></td>
                                    <td class="text-muted small"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">View Profile</a>
                                        <a href="#" class="btn btn-sm btn-outline-success">Shortlist</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No applications received yet. Post a job to attract students!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>