<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Form submitted!<br>"; // Debug line
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "Email: " . htmlspecialchars($email) . "<br>"; // Debug
    
    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "User found!<br>"; // Debug
                if (password_verify($password, $user['password'])) {
                    echo "Password correct! Redirecting...<br>"; // Debug
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    
                    $redirect = ($user['role'] == 'student') ? 'student/dashboard.php' : 'company/dashboard.php';
                    header("Location: $redirect");
                    exit();
                } else {
                    $error = "❌ Wrong password!";
                }
            } else {
                $error = "❌ User not found!";
            }
        } catch (PDOException $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Please fill all fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 420px;
            width: 100%;
        }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            border: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <h3 class="text-center mb-4 fw-bold">Login</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3 mb-0">
            <a href="register.php">Create Account</a>
        </p>
    </div>
</body>
</html>