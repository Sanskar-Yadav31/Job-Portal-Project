<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// 📥 Fetch Current Profile
$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();
if (!$student) { header("Location: ../login.php"); exit(); }

$success = '';
$error = '';

// 🚀 Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $field = trim($_POST['field']);
    $education = trim($_POST['education']);

    if (empty($full_name) || empty($phone) || empty($city) || empty($field)) {
        $error = "⚠️ Name, Phone, City aur Field are required!";
    } else {
        try {
            $update = $pdo->prepare("UPDATE students SET full_name=?, phone=?, city=?, field_of_interest=?, education=? WHERE user_id=?");
            $update->execute([$full_name, $phone, $city, $field, $education, $_SESSION['user_id']]);
            $success = "✅ Profile updated successfully!";
            
            // Refresh data
            $stmt->execute([$_SESSION['user_id']]);
            $student = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "❌ Failed to update profile. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Student Portal</title>
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
            max-width: 600px;
            margin: 2rem auto;
        }
        .btn-primary { background: linear-gradient(90deg, #2563eb, #7c3aed); border: none; font-weight: 600; }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: #fff; }
        .form-control:focus, .form-select:focus { border-color: #7c3aed; box-shadow: 0 0 0 0.2rem rgba(124,58,237,0.25); }
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

<div class="container">
    <div class="glass-card">
        <h3 class="fw-bold mb-4 text-center">👤 Edit Profile</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">City</label>
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($student['city']) ?>" required>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Field of Interest</label>
                    <select name="field" class="form-select" required>
                        <option value="">Select Field</option>
                        <option value="IT" <?= $student['field_of_interest']=='IT'?'selected':'' ?>>IT / Software</option>
                        <option value="Marketing" <?= $student['field_of_interest']=='Marketing'?'selected':'' ?>>Marketing</option>
                        <option value="Finance" <?= $student['field_of_interest']=='Finance'?'selected':'' ?>>Finance</option>
                        <option value="HR" <?= $student['field_of_interest']=='HR'?'selected':'' ?>>HR / Admin</option>
                        <option value="Design" <?= $student['field_of_interest']=='Design'?'selected':'' ?>>Design</option>
                        <option value="Sales" <?= $student['field_of_interest']=='Sales'?'selected':'' ?>>Sales</option>
                        <option value="Other" <?= $student['field_of_interest']=='Other'?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Education</label>
                    <input type="text" name="education" class="form-control" value="<?= htmlspecialchars($student['education']) ?>" placeholder="e.g., B.Tech 3rd Year">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">💾 Save Changes</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>