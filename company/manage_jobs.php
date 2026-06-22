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

$success_msg = $error_msg = '';

// 🚀 Handle Actions (Toggle Status or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $job_id = (int)$_POST['job_id'];
        $current_status = $_POST['current_status'];
        $new_status = ($current_status === 'active') ? 'closed' : 'active';

        try {
            $stmt = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ? AND company_id = ?");
            $stmt->execute([$new_status, $job_id, $company_id]);
            $success_msg = "✅ Job status updated to " . ucfirst($new_status) . "!";
        } catch (PDOException $e) {
            $error_msg = "❌ Failed to update status.";
        }
    } 
    elseif (isset($_POST['delete_job'])) {
        $job_id = (int)$_POST['job_id'];
        try {
            // Deleting job will automatically delete related applications (ON DELETE CASCADE)
            $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
            $stmt->execute([$job_id, $company_id]);
            $success_msg = "🗑️ Job deleted successfully!";
        } catch (PDOException $e) {
            $error_msg = "❌ Failed to delete job.";
        }
    }
}

// 📥 Fetch Company's Jobs + Application Counts
$stmt_jobs = $pdo->prepare("
    SELECT j.id, j.title, j.job_field, j.city, j.status, j.posted_at, 
           COUNT(a.id) as app_count 
    FROM jobs j 
    LEFT JOIN applications a ON j.id = a.job_id 
    WHERE j.company_id = ? 
    GROUP BY j.id 
    ORDER BY j.posted_at DESC
");
$stmt_jobs->execute([$company_id]);
$jobs = $stmt_jobs->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs | Company Portal</title>
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
        .badge-active { background: #dcfce7; color: #166534; padding: 5px 10px; border-radius: 20px; }
        .badge-closed { background: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 20px; }
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
    <h3 class="fw-bold mb-4">📋 Manage Jobs</h3>

    <!-- Alerts -->
    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $error_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Jobs Table -->
    <div class="glass-card p-4">
        <?php if (count($jobs) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Field / Location</th>
                            <th>Posted On</th>
                            <th>Apps Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): 
                            $status_class = ($job['status'] == 'active') ? 'badge-active' : 'badge-closed';
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($job['title']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($job['job_field']) ?></span>
                                <small class="text-muted ms-1"><?= htmlspecialchars($job['city']) ?></small>
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($job['posted_at'])) ?></td>
                            <td>
                                <span class="badge bg-primary rounded-pill"><?= $job['app_count'] ?></span>
                            </td>
                            <td><span class="<?= $status_class ?>"><?= ucfirst($job['status']) ?></span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <!-- Toggle Status Button -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $job['status'] ?>">
                                        <?php if ($job['status'] == 'active'): ?>
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-warning" title="Close Job">
                                                <i class="bi bi-lock"></i> Close
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-outline-success" title="Reopen Job">
                                                <i class="bi bi-unlock"></i> Open
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <!-- Delete Button -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <button type="submit" name="delete_job" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this job? Applications will also be deleted.');" title="Delete Job">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-briefcase fs-1 text-muted d-block mb-3"></i>
                <h5>No jobs posted yet</h5>
                <p class="text-muted">Start by posting a new job to receive applications.</p>
                <a href="post_job.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Post New Job</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>