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

$success = '';
$error = '';

// 🚫 Handle Remove Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_skill_id'])) {
    $skill_id = (int)$_POST['remove_skill_id'];
    $del = $pdo->prepare("DELETE FROM student_skills WHERE student_id = ? AND skill_id = ?");
    $del->execute([$student_id, $skill_id]);
    header("Location: skills.php");
    exit();
}

// ➕ Handle Add Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    $input_skill = trim($_POST['skill_input']);
    
    if (empty($input_skill)) {
        $error = "⚠️ Skill name cannot be empty.";
    } else {
        try {
            // Check if skill exists (case-insensitive)
            $check = $pdo->prepare("SELECT id FROM skills WHERE LOWER(skill_name) = LOWER(?)");
            $check->execute([$input_skill]);
            $existing = $check->fetch();

            if ($existing) {
                $skill_id = $existing['id'];
            } else {
                // Create new skill
                $insert_skill = $pdo->prepare("INSERT INTO skills (skill_name) VALUES (?)");
                $insert_skill->execute([ucwords($input_skill)]);
                $skill_id = $pdo->lastInsertId();
            }

            // Check if already linked to this student
            $check_link = $pdo->prepare("SELECT id FROM student_skills WHERE student_id = ? AND skill_id = ?");
            $check_link->execute([$student_id, $skill_id]);
            
            if ($check_link->fetch()) {
                $error = "⚠️ This skill is already added to your profile.";
            } else {
                // Link skill to student
                $link = $pdo->prepare("INSERT INTO student_skills (student_id, skill_id) VALUES (?, ?)");
                $link->execute([$student_id, $skill_id]);
                $success = "✅ Skill added successfully!";
            }
        } catch (PDOException $e) {
            $error = "❌ Failed to add skill. Please try again.";
        }
    }
}

// 📥 Fetch Current Skills
$stmt_skills = $pdo->prepare("
    SELECT sk.id, sk.skill_name 
    FROM skills sk 
    JOIN student_skills ss ON sk.id = ss.skill_id 
    WHERE ss.student_id = ? 
    ORDER BY sk.skill_name ASC
");
$stmt_skills->execute([$student_id]);
$current_skills = $stmt_skills->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills | Student Portal</title>
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
        .btn-primary { background: linear-gradient(90deg, #2563eb, #7c3aed); border: none; font-weight: 600; }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); color: #fff; }
        
        /* Skill Tags */
        .skill-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: #e0e7ff; color: #1e40af;
            padding: 6px 14px; border-radius: 20px;
            font-size: 0.9rem; font-weight: 500;
            margin: 4px; transition: all 0.2s;
        }
        .skill-tag:hover { background: #c7d2fe; }
        .skill-tag button {
            background: none; border: none; color: #ef4444;
            font-size: 1.2rem; cursor: pointer; padding: 0; line-height: 1;
        }
        .skill-tag button:hover { color: #b91c1c; transform: scale(1.1); }
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
        <h3 class="fw-bold mb-4 text-center">💼 Manage Skills</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Current Skills Display -->
        <div class="mb-4">
            <h6 class="fw-semibold text-muted mb-3"><i class="bi bi-stars me-1"></i>Your Added Skills</h6>
            <div class="d-flex flex-wrap">
                <?php if (count($current_skills) > 0): 
                    foreach ($current_skills as $sk): ?>
                    <div class="skill-tag">
                        <?= htmlspecialchars($sk['skill_name']) ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="remove_skill_id" value="<?= $sk['id'] ?>">
                            <button type="submit" onclick="return confirm('Remove this skill?');" title="Remove Skill">×</button>
                        </form>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted fst-italic mb-0">No skills added yet. Add your first skill below!</p>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-4">

        <!-- Add Skill Form -->
        <form method="POST" class="row g-2 align-items-end">
            <div class="col-md-9">
                <label class="form-label fw-semibold">Add New Skill</label>
                <input type="text" name="skill_input" class="form-control" placeholder="Type skill name (e.g., Python, React, SEO...)" required autocomplete="off">
            </div>
            <div class="col-md-3">
                <button type="submit" name="add_skill" class="btn btn-primary w-100 py-2">➕ Add Skill</button>
            </div>
        </form>
        <small class="text-muted mt-2 d-block">💡 Tip: If the skill already exists in the system, it will be linked to your profile. Otherwise, it will be created automatically.</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>