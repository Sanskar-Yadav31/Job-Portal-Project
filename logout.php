<?php
session_start();

// 1. Saare session variables clear karo
$_SESSION = array();

// 2. Session cookie delete karo (security best practice)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Session destroy karo
session_destroy();

// 4. Login page pe redirect karo
header("Location: login.php");
exit();
?>