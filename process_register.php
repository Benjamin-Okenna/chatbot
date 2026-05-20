<?php
// process_register.php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Clean and capture text inputs
    $full_name = trim($_POST['full_name']);
    $matric_no = trim($_POST['matric_no']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $biometric_image = $_POST['biometric_image'] ?? '';

    // Validation Guard: Ensure all essential fields have data
    if (empty($full_name) || empty($matric_no) || empty($email) || empty($password) || empty($biometric_image)) {
        $_SESSION['register_error'] = 'All registration fields and a valid face snapshot are required.';
        header('Location: register.php');
        exit;
    }

    try {
        // 2. Security Check: Make sure this Matric Number or Email isn't already taken
        $check_stmt = $pdo->prepare("SELECT id FROM students WHERE matric_no = ? OR email = ? LIMIT 1");
        $check_stmt->execute([$matric_no, $email]);
        
        if ($check_stmt->fetch()) {
            $_SESSION['register_error'] = 'This Matric Number or Email is already registered.';
            header('Location: register.php');
            exit;
        }

        // 3. Cryptographic Hash: Secure the password using your server's native BCRYPT engine
        // This ensures password_verify() works perfectly when logging in later
        $secure_password_hash = password_hash($password, PASSWORD_BCRYPT);

        // 4. Database Query: Insert the new student and their biometric face token string
        $insert_sql = "INSERT INTO students (full_name, matric_no, email, password_hash, face_token) 
                       VALUES (:name, :matric, :email, :hash, :face)";
        
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            ':name'   => $full_name,
            ':matric' => $matric_no,
            ':email'  => $email,
            ':hash'   => $secure_password_hash,
            ':face'   => $biometric_image // Long Base64 string from camera canvas
        ]);

        // 5. Success: Redirect back to login with a clean slate
        header('Location: login.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['register_error'] = 'Database Error: ' . $e->getMessage();
        header('Location: register.php');
        exit;
    }
} else {
    header('Location: register.php');
    exit;
}
?>