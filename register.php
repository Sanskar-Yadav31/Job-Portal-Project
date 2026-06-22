<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);

    // 1. Basic Validation
    if (empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($city)) {
        $error = "All required fields must be filled!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits!";
    } else {
        try {
            $pdo->beginTransaction(); // Safe insert shuru

            // 2. Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("Email already registered!");
            }

            // 3. Insert into users table
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashed_password, $role]);
            $user_id = $pdo->lastInsertId();

            // 4. Insert into role-specific table
            if ($role === 'student') {
                $full_name = trim($_POST['full_name']);
                $field = trim($_POST['field_of_interest']);
                $education = trim($_POST['education']);

                if (empty($full_name) || empty($field)) {
                    throw new Exception("Student profile fields are required!");
                }

                $stmt = $pdo->prepare("INSERT INTO students (user_id, full_name, phone, city, field_of_interest, education) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $full_name, $phone, $city, $field, $education]);
                $success = "✅ Student registration successful! Please login.";
            } else {
                $company_name = trim($_POST['company_name']);
                $contact_person = trim($_POST['contact_person']);
                $industry = trim($_POST['industry']);
                $website = trim($_POST['website']);

                if (empty($company_name) || empty($contact_person) || empty($industry)) {
                    throw new Exception("Company profile fields are required!");
                }

                $stmt = $pdo->prepare("INSERT INTO companies (user_id, company_name, contact_person, phone, city, industry, website) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $company_name, $contact_person, $phone, $city, $industry, $website]);
                $success = "✅ Company registration successful! Please login.";
            }

            $pdo->commit(); // Sab sahi hua toh save
        } catch (Exception $e) {
            $pdo->rollBack(); // Kuch galat hua toh wapas undo
            $error = "❌ " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
        }
        .toggle-btn { border-radius: 20px; font-weight: 600; transition: all 0.3s ease; }
        .toggle-btn.active { background-color: #2563eb; color: white; border-color: #2563eb; }
        .form-control:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
        }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            border: none; font-weight: 600;
        }
        .btn-primary:hover { background: linear-gradient(90deg, #1d4ed8, #6d28d9); }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="glass-card">
        <h3 class="text-center mb-4 fw-bold" style="color: #1e293b;">Create Account</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Role Toggle -->
            <div class="btn-group w-100 mb-4" role="group">
                <input type="radio" class="btn-check" name="role" id="roleStudent" value="student" checked>
                <label class="btn btn-outline-primary toggle-btn active" for="roleStudent" onclick="toggleRole('student')">🎓 Student</label>

                <input type="radio" class="btn-check" name="role" id="roleCompany" value="company">
                <label class="btn btn-outline-primary toggle-btn" for="roleCompany" onclick="toggleRole('company')">🏢 Company</label>
            </div>

            <!-- Common Fields -->
            <div class="mb-3"><label class="form-label">Email Address</label><input type="email" class="form-control" name="email" required placeholder="you@example.com"></div>
            <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required minlength="6" placeholder="Min 6 characters"></div>
            <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="confirm_password" required minlength="6" placeholder="Re-enter password"></div>
            
            <div class="row g-3 mb-3">
                <div class="col-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" required pattern="\d{10}" placeholder="10 digits"></div>
                <div class="col-6"><label class="form-label">City</label><input type="text" class="form-control" name="city" required placeholder="e.g., Delhi"></div>
            </div>

            <!-- Student Fields -->
            <div id="studentFields">
                <div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" name="full_name" placeholder="Your Name"></div>
                <div class="mb-3"><label class="form-label">Field of Interest</label>
                    <select class="form-select" name="field_of_interest"><option value="">Select Field</option><option value="IT">IT / Software</option><option value="Marketing">Marketing</option><option value="Finance">Finance</option><option value="HR">HR</option><option value="Design">Design</option><option value="Sales">Sales</option></select>
                </div>
                <div class="mb-3"><label class="form-label">Education (Optional)</label><input type="text" class="form-control" name="education" placeholder="e.g., B.Tech 3rd Year"></div>
            </div>

            <!-- Company Fields -->
            <div id="companyFields" class="hidden">
                <div class="mb-3"><label class="form-label">Company Name</label><input type="text" class="form-control" name="company_name" placeholder="Company Name"></div>
                <div class="mb-3"><label class="form-label">Contact Person</label><input type="text" class="form-control" name="contact_person" placeholder="HR / Manager Name"></div>
                <div class="mb-3"><label class="form-label">Industry</label>
                    <select class="form-select" name="industry"><option value="">Select Industry</option><option value="IT">IT / Technology</option><option value="Healthcare">Healthcare</option><option value="Education">Education</option><option value="Retail">Retail</option><option value="Manufacturing">Manufacturing</option><option value="Other">Other</option></select>
                </div>
                <div class="mb-3"><label class="form-label">Website (Optional)</label><input type="url" class="form-control" name="website" placeholder="https://example.com"></div>
            </div>

            <button type="submit" name="register" class="btn btn-primary w-100 py-2 mt-3">Register Now</button>
        </form>

        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold" style="color: #2563eb;">Login here</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleRole(role) {
            document.getElementById('studentFields').classList.toggle('hidden', role !== 'student');
            document.getElementById('companyFields').classList.toggle('hidden', role !== 'company');
            document.querySelectorAll('.toggle-btn').forEach((btn, i) => {
                btn.classList.toggle('active', (i === 0 && role === 'student') || (i === 1 && role === 'company'));
            });
        }
    </script>
</body>
</html>