<?php
// logout.php
session_start();

// 1. Unset all active session variables in global state memory
$_SESSION = array();

// 2. If the application is tracking cookies, completely delete the session cookie tracking token
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the actual backend server-side session allocation container 
session_destroy();

// 4. Smooth redirection back to the newly created login form screen
header("Location: login.php");
exit;