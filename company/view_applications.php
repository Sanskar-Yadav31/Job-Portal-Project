<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

// 🆔 Fetch Company ID
$stmt_com = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt_com->execute([$_SESSION['user_id']]);
$company = $stmt_com->fetch();
if (!$company) { header("Location: ../login.php"); exit(); }
$company_id = $company['id'];

// 📥 Fetch Company's Jobs
$stmt_jobs = $pdo->prepare("SELECT id, title, status FROM jobs WHERE company_id = ? ORDER BY posted_at DESC");
$stmt_jobs->execute([$company_id]);
$jobs = $stmt_jobs->fetchAll();

// 🎯 Determine Selected Job
$selected_job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : ($jobs[0]['id'] ?? null);
$current_job_title = '';
if ($selected_job_id) {
    $stmt_title = $pdo->prepare("SELECT title FROM jobs WHERE id = ? AND company_id = ?");
    $stmt_title->execute([$selected_job_id, $company_id]);
    $current_job_title = $stmt_title->fetchColumn() ?: '';
}

// 🚀 Handle Status Update
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = (int)$_POST['app_id'];
    $new_status = $_POST['new_status'];
    $valid_statuses = ['applied', 'shortlisted', 'interview', 'hired', 'rejected'];

    if (in_array($new_status, $valid_statuses)) {
        try {
            $stmt_update = $pdo->prepare("
                UPDATE applications a 
                JOIN jobs j ON a.job_id = j.id 
                SET a.status = ? 
                WHERE a.id = ? AND j.company_id = ?
            ");
            $stmt_update->execute([$new_status, $app_id, $company_id]);
            header("Location: view_applications.php?job_id=" . $selected_job_id . "&updated=1");
            exit();
        } catch (PDOException $e) {
            $error_msg = "❌ Failed to update status. Try again.";
        }
    } else {
        $error_msg = "⚠️ Invalid status.";
    }
}

if (isset($_GET['updated'])) {
    $success_msg = "✅ Application status updated successfully!";
}

// 📋 Fetch Applications for Selected Job
$applications = [];
if ($selected_job_id && $current_job_title) {
    $stmt_apps = $pdo->prepare("
        SELECT a.id, a.status, a.applied_at, s.full_name, s.city, s.phone, s.field_of_interest, u.email
        FROM applications a
        JOIN students s ON a.student_id = s.id
        JOIN users u ON s.user_id = u.id
        WHERE a.job_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt_apps->execute([$selected_job_id]);
    $applications = $stmt_apps->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications | Company Portal</title>
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
        .btn-primary { background: linear-gradient(90deg, #2563eb, #7c3aed); border: none; font-weight: 600; }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: #fff; }
        .badge-status { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-applied { background: #dbeafe; color: #1e40af; }
        .status-shortlisted { background: #fef3c7; color: #92400e; }
        .status-interview { background: #e0e7ff; color: #3730a3; }
        .status-hired { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .table th { background: rgba(243, 232, 255, 0.5); font-weight: 600; }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">🏢 Company Portal</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-grid me-1"></i>Dashboard</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h3 class="fw-bold mb-4">📊 Manage Applications</h3>

    <!-- Alerts -->
    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $error_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Job Selector -->
    <div class="glass-card p-4 mb-4">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-10">
                <label class="form-label fw-semibold">Select Job to View Applications</label>
                <select name="job_id" class="form-select" required>
                    <?php if (empty($jobs)): ?>
                        <option value="">No jobs posted yet</option>
                    <?php else: ?>
                        <?php foreach ($jobs as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= ($j['id'] == $selected_job_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($j['title']) ?> (<?= ucfirst($j['status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Load</button>
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <?php if ($selected_job_id && $current_job_title): ?>
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2">
                📄 Applications for: <span class="text-primary"><?= htmlspecialchars($current_job_title) ?></span>
            </h5>

            <?php if (count($applications) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student Details</th>
                                <th>Contact</th>
                                <th>Applied On</th>
                                <th>Current Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): 
                                $status_class = 'status-' . $app['status'];
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($app['full_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['field_of_interest']) ?> | <?= htmlspecialchars($app['city']) ?></small>
                                </td>
                                <td>
                                    <div class="small"><?= htmlspecialchars($app['email']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['phone']) ?></small>
                                </td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                <td><span class="badge-status <?= $status_class ?>"><?= ucfirst($app['status']) ?></span></td>
                                <td>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                        <select name="new_status" class="form-select form-select-sm" style="width: 120px;">
                                            <option value="applied" <?= $app['status']=='applied'?'selected':'' ?>>Applied</option>
                                            <option value="shortlisted" <?= $app['status']=='shortlisted'?'selected':'' ?>>Shortlisted</option>
                                            <option value="interview" <?= $app['status']=='interview'?'selected':'' ?>>Interview</option>
                                            <option value="hired" <?= $app['status']=='hired'?'selected':'' ?>>Hired</option>
                                            <option value="rejected" <?= $app['status']=='rejected'?'selected':'' ?>>Rejected</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    <p class="mb-0">No applications received for this job yet.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (empty($jobs)): ?>
        <div class="glass-card p-4 text-center mt-3">
            <p class="text-muted mb-0">You haven't posted any jobs yet. <a href="post_job.php" class="text-primary">Post a Job</a> to start receiving applications.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>