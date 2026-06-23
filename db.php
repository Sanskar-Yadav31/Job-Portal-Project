<!-- 
// // 🛡️ Production Security Settings
// error_reporting(0);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);

// // 🔒 Session Security
// ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 0); // Live pe HTTPS hoga toh isko 1 kar dena
// ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_samesite', 'Lax');
// $host = 'localhost';
// $dbname = 'student_job_portal';
// $username = 'root';
// $password = '';

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
//     // Error mode: Exception throw karega agar koi query fail ho
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
//     // Default fetch mode: Array return karega (associative)
//     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
// } catch (PDOException $e) {
//     // Production mein ye line hata denge, abhi debugging ke liye rakhi hai
//     die("Database Connection Failed: " . $e->getMessage());
// } -->

<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Production mein error log karega, screen pe nahi dikhayega
    error_log("DB Connection Failed: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}
?>
