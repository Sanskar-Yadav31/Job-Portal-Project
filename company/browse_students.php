<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

// 🆔 Fetch Company ID
$stmt_com = $pdo->prepare("SELECT id, company_name FROM companies WHERE user_id = ?");
$stmt_com->execute([$_SESSION['user_id']]);
$company = $stmt_com->fetch();
if (!$company) { header("Location: ../login.php"); exit(); }
$company_id = $company['id'];

// 🚀 Handle Shortlist Action (Notification Logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shortlist_id'])) {
    $target_student_user_id = (int)$_POST['shortlist_id'];
    
    // Notification insert karo
    $msg = "Congratulations! The company <b>" . htmlspecialchars($company['company_name']) . "</b> has shortlisted your profile.";
    $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Shortlisted', ?)");
    $stmt_notif->execute([$target_student_user_id, $msg]);
    
    $success_msg = "✅ Shortlisted notification sent!";
}

// 🔍 Filtering Logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_city = isset($_GET['city']) ? trim($_GET['city']) : '';
$filter_field = isset($_GET['field']) ? trim($_GET['field']) : '';

// Base Query
$sql = "SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (s.full_name LIKE ? OR s.field_of_interest LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}
if ($filter_city) {
    $sql .= " AND s.city = ?";
    $params[] = $filter_city;
}
if ($filter_field) {
    $sql .= " AND s.field_of_interest = ?";
    $params[] = $filter_field;
}

$sql .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// 📥 Options for Dropdowns
$cities = $pdo->query("SELECT DISTINCT city FROM students ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
$fields = $pdo->query("SELECT DISTINCT field_of_interest FROM students ORDER BY field_of_interest")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Students | Company Portal</title>
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
        .filter-bar { background: rgba(255,255,255,0.7); border-radius: 12px; padding: 1rem; margin-bottom: 2rem; }
        .skill-tag { background: #e0e7ff; color: #1e40af; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; margin-right: 4px; display: inline-block; margin-bottom: 4px;}
        .btn-shortlist { background: linear-gradient(90deg, #10b981, #059669); color: white; border: none; font-weight: 600; }
        .btn-shortlist:hover { background: linear-gradient(90deg, #059669, #047857); color: white; }
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
    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- 🔍 Filter Bar -->
    <div class="filter-bar shadow-sm">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">🔍 Search by Name/Field</label>
                <input type="text" name="search" class="form-control" placeholder="e.g. John, IT..." value="<?= htmlspecialchars($search) ?>">
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
    </div>

    <!-- 🎓 Students Grid -->
    <?php if (count($students) > 0): ?>
        <div class="row g-4">
            <?php foreach ($students as $stu): 
                // Fetch Skills for this student
                $stmt_sk = $pdo->prepare("SELECT sk.skill_name FROM student_skills ss JOIN skills sk ON ss.skill_id = sk.id WHERE ss.student_id = ?");
                $stmt_sk->execute([$stu['id']]);
                $skills = $stmt_sk->fetchAll(PDO::FETCH_COLUMN);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100 d-flex flex-column">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded-circle p-2 me-3 text-center" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center;">
                            <i class="bi bi-person-fill fs-4 text-secondary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($stu['full_name']) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($stu['field_of_interest']) ?> | <?= htmlspecialchars($stu['city']) ?></small>
                        </div>
                    </div>

                    <div class="mb-3 flex-grow-1">
                        <p class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($stu['email']) ?></p>
                        <p class="small text-muted mb-2"><i class="bi bi-phone me-1"></i><?= htmlspecialchars($stu['phone']) ?></p>
                        <p class="small text-muted mb-1"><i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($stu['education']) ?></p>
                        
                        <div class="mt-2">
                            <strong class="small d-block mb-1">Skills:</strong>
                            <?php if (count($skills) > 0): ?>
                                <?php foreach ($skills as $sk): ?>
                                    <span class="skill-tag"><?= htmlspecialchars($sk) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic small">No skills added</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top">
                        <a href="student_profile.php?id=<?= $stu['user_id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-2"><i class="bi bi-eye me-1"></i>View Full Profile</a>
                        <form method="POST" onsubmit="return confirm('Send shortlist notification to this student?');">
                            <input type="hidden" name="shortlist_id" value="<?= $stu['user_id'] ?>">
                            <button type="submit" class="btn btn-shortlist btn-sm w-100"><i class="bi bi-star me-1"></i>Shortlist Student</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
            <h5>No students found</h5>
            <p class="text-muted">Try adjusting your filters.</p>
            <a href="browse_students.php" class="btn btn-outline-primary mt-2">Clear Filters</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>