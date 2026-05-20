<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EspolyBot - Student Authentication</title>
    <link rel="stylesheet" href="login-style.css">
</head>
<body>
    <div class="login-container">
        <!-- Brand/Identity Header -->
        <header class="login-header">
            <div class="bot-icon"><img src="espoly_icon.jpg" alt="Espoly Logo" width="50px" height="50px" ></div>
            <h1>EspolyBot Portal</h1>
            <p>Sign in with your institutional credentials to access your academic AI advisor.</p>
        </header>

        <!-- Dynamic PHP Error Alert Box -->
        <?php 
        session_start();
        if (isset($_SESSION['login_error'])): 
        ?>
            <div class="error-alert">
                <span>⚠️</span> <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Native Login Form Processing Pipeline -->
        <form action="process_login.php" method="POST" autocomplete="off">
            <div class="input-group">
                <label for="matricNo">Matric / Registration Number</label>
                <input type="text" id="matricNo" name="matric_no" required placeholder="e.g., EU/2026/0482" autofocus>
            </div>

            <div class="input-group">
                <label for="password">Portal Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="login-submit-btn">Authorize Session</button>
        </form>

        <footer class="login-footer">
            <p>New student? <a href="register.php">Enroll biometric profile here</a></p>
        </footer>
    </div>
</body>
</html>