<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// 🔍 Filter Inputs (GET method)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_city = isset($_GET['city']) ? trim($_GET['city']) : '';
$filter_field = isset($_GET['field']) ? trim($_GET['field']) : '';

// 🧠 Build Dynamic Query
$sql = "SELECT j.*, c.company_name FROM jobs j LEFT JOIN companies c ON j.company_id = c.id WHERE j.status = 'active'";
$params = [];

if ($search !== '') {
    $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.required_skills LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($filter_city !== '') {
    $sql .= " AND j.city = ?";
    $params[] = $filter_city;
}
if ($filter_field !== '') {
    $sql .= " AND j.job_field = ?";
    $params[] = $filter_field;
}

$sql .= " ORDER BY j.posted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// 📥 Fetch unique cities & fields for filter dropdowns
$cities = $pdo->query("SELECT DISTINCT city FROM jobs WHERE status='active' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
$fields = $pdo->query("SELECT DISTINCT job_field FROM jobs WHERE status='active' ORDER BY job_field")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs | Student Portal</title>
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
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .glass-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
        .filter-bar {
            background: rgba(255,255,255,0.7);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .form-control, .form-select { border-radius: 8px; }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            border: none; font-weight: 600;
        }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); }
        .job-tag { background: #f3e8ff; color: #5b21b6; font-size: 0.75rem; padding: 2px 8px; border-radius: 20px; }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">🎓 Student Portal</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <!-- 🔍 Filter Bar -->
    <div class="filter-bar shadow-sm">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">🔍 Search Jobs</label>
                <input type="text" name="search" class="form-control" placeholder="Title, skills or company..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">📍 Location</label>
                <select name="city" class="form-select">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= ($filter_city === $c) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">💼 Field</label>
                <select name="field" class="form-select">
                    <option value="">All Fields</option>
                    <?php foreach ($fields as $f): ?>
                        <option value="<?= htmlspecialchars($f) ?>" <?= ($filter_field === $f) ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Go</button>
            </div>
        </form>
    </div>

    <!-- 📋 Jobs Grid -->
    <?php if (count($jobs) > 0): ?>
        <div class="row g-4">
            <?php foreach ($jobs as $job): ?>
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100 d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($job['title']) ?></h5>
                        <p class="text-muted mb-2"><i class="bi bi-building me-1"></i><?= htmlspecialchars($job['company_name'] ?? 'Unknown Company') ?></p>
                        <div class="d-flex gap-2 mb-3">
                            <span class="job-tag"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($job['city']) ?></span>
                            <span class="job-tag"><i class="bi bi-briefcase me-1"></i><?= htmlspecialchars($job['job_field']) ?></span>
                            <?php if ($job['salary_range']): ?>
                                <span class="job-tag"><i class="bi bi-currency-rupee me-1"></i><?= htmlspecialchars($job['salary_range']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="small text-secondary mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($job['description']) ?>
                        </p>
                    </div>
                    <div class="mt-auto">
                        <a href="job_details.php?id=<?= $job['id'] ?>" class="btn btn-primary w-100">View Details & Apply</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: 
    // Check karo ki filters applied hain ya nahi
    $has_filters = !empty($search) || !empty($filter_city) || !empty($filter_field);
?>
    <div class="text-center py-5">
        <i class="bi <?= $has_filters ? 'bi-funnel' : 'bi-briefcase' ?> fs-1 text-muted d-block mb-3"></i>
        <h5 class="fw-semibold">
            <?= $has_filters ? 'No jobs match your filters' : 'No active jobs available' ?>
        </h5>
        <p class="text-muted">
            <?php if ($has_filters): ?>
                <?php if (!empty($filter_city)): ?>
                    Currently no jobs available in <strong><?= htmlspecialchars($filter_city) ?></strong>.
                <?php elseif (!empty($filter_field)): ?>
                    No jobs found in <strong><?= htmlspecialchars($filter_field) ?></strong> field.
                <?php else: ?>
                    Try adjusting your search criteria.
                <?php endif; ?>
            <?php else: ?>
                Companies will post jobs soon. Check back later!
            <?php endif; ?>
        </p>
        <?php if ($has_filters): ?>
            <a href="view_jobs.php" class="btn btn-outline-primary mt-2">
                <i class="bi bi-x-circle me-1"></i>Clear Filters
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>