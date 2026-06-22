<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check: Company logged in?
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

// 🆔 Fetch Company ID from DB
$stmt = $pdo->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
if (!$company) { header("Location: ../login.php"); exit(); }
$company_id = $company['id'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $skills = trim($_POST['skills']);
    $city = trim($_POST['city']);
    $field = trim($_POST['field']);
    $salary = trim($_POST['salary']);

    // Validation
    if (empty($title) || empty($desc) || empty($skills) || empty($city) || empty($field)) {
        $error = "⚠️ Please fill all required fields!";
    } else {
        try {
            // Insert Job
            $stmt = $pdo->prepare("INSERT INTO jobs (company_id, title, description, required_skills, city, job_field, salary_range, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$company_id, $title, $desc, $skills, $city, $field, $salary]);
            
            $success = "✅ Job posted successfully! Redirecting to dashboard...";
            header("Refresh: 2; url=dashboard.php");
        } catch (PDOException $e) {
            $error = "❌ Failed to post job. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job | Company Portal</title>
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
            max-width: 700px;
            margin: 2rem auto;
        }
        .btn-gradient {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            color: white; border: none; font-weight: 600;
        }
        .btn-gradient:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: white; }
        .form-control:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
        }
    </style>
</head>
<body>

<!-- 🧭 Navbar -->
<nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="dashboard.php">🏢 Company Portal</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="glass-card">
        <h3 class="fw-bold mb-4 text-center">📝 Post a New Job</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title" required placeholder="e.g., Frontend Developer Intern">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Job Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="4" required placeholder="Roles, responsibilities, requirements..."></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Required Skills <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="skills" required placeholder="e.g., HTML, CSS, JavaScript, React">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Salary Range</label>
                    <input type="text" class="form-control" name="salary" placeholder="e.g., ₹15,000 - ₹25,000 / month">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">City / Location <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="city" required placeholder="e.g., Bangalore, Remote">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Job Field <span class="text-danger">*</span></label>
                    <select class="form-select" name="field" required>
                        <option value="">Select Field</option>
                        <option value="IT">IT / Software</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Finance">Finance</option>
                        <option value="HR">HR / Administration</option>
                        <option value="Design">Design / Creative</option>
                        <option value="Sales">Sales / Business Dev</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-gradient w-100 py-2">🚀 Publish Job</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>