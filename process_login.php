<?php
// process_login.php
session_start();
require_once 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matric_no = trim($_POST['matric_no']);
    $password = trim($_POST['password']);

    if (empty($matric_no) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter both matric number and password.';
        header('Location: login.php');
        exit;
    }

    try {
        // Make sure face_token is included in the SELECT query strings
        $stmt = $pdo->prepare("SELECT id, full_name, password_hash, face_token FROM students WHERE matric_no = ? LIMIT 1");
        $stmt->execute([$matric_no]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($password, $student['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['authenticated'] = true;
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['matric_no'] = $matric_no;
            
            // Save the captured image data string into the active session block
            $_SESSION['face_token'] = $student['face_token'];

            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_error'] = 'Invalid matric number or password combination.';
            header('Location: login.php');
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'System Error. Please try again shortly.';
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>