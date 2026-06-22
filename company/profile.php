<?php
session_start();
require_once '../includes/db.php';

// 🔒 Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

// 📥 Fetch Current Profile
$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$company = $stmt->fetch();
if (!$company) { header("Location: ../login.php"); exit(); }

$success = '';
$error = '';

// 🚀 Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['company_name']);
    $contact = trim($_POST['contact_person']);
    $industry = trim($_POST['industry']);
    $city = trim($_POST['city']);
    $website = trim($_POST['website']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($contact) || empty($industry) || empty($city)) {
        $error = "⚠️ Company Name, Contact Person, Industry, and City are required!";
    } else {
        try {
            $update = $pdo->prepare("UPDATE companies SET company_name=?, contact_person=?, industry=?, city=?, website=?, phone=? WHERE user_id=?");
            $update->execute([$name, $contact, $industry, $city, $website, $phone, $_SESSION['user_id']]);
            $success = "✅ Profile updated successfully!";
            
            // Refresh data to show new values
            $stmt->execute([$_SESSION['user_id']]);
            $company = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "❌ Failed to update profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Company Portal</title>
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
            max-width: 800px;
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
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-grid me-1"></i>Dashboard</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="glass-card">
        <h3 class="fw-bold mb-4 text-center">🏢 Edit Company Profile</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($company['company_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($company['contact_person']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Industry <span class="text-danger">*</span></label>
                    <select class="form-select" name="industry" required>
                        <option value="">Select Industry</option>
                        <option value="IT" <?= $company['industry']=='IT'?'selected':'' ?>>IT / Software</option>
                        <option value="Marketing" <?= $company['industry']=='Marketing'?'selected':'' ?>>Marketing</option>
                        <option value="Finance" <?= $company['industry']=='Finance'?'selected':'' ?>>Finance</option>
                        <option value="HR" <?= $company['industry']=='HR'?'selected':'' ?>>HR / Administration</option>
                        <option value="Design" <?= $company['industry']=='Design'?'selected':'' ?>>Design / Creative</option>
                        <option value="Sales" <?= $company['industry']=='Sales'?'selected':'' ?>>Sales / Business Dev</option>
                        <option value="Other" <?= $company['industry']=='Other'?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">City / Location <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($company['city']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Website URL</label>
                    <input type="url" class="form-control" name="website" value="<?= htmlspecialchars($company['website']) ?>" placeholder="https://example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($company['phone']) ?>" placeholder="+91 98765 43210">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-gradient w-100 py-2">💾 Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>