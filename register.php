<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EspolyBot - Biometric Enrollment</title>
    <link rel="stylesheet" href="login-style.css">
    <style>
        .register-container {
            max-width: 750px !important; /* Wider layout to accommodate camera view side-by-side */
            display: flex;
            gap: 30px;
        }
        .form-section { flex: 1; }
        .camera-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #334155;
        }
        #webcam {
            width: 100%;
            max-width: 280px;
            height: auto;
            border-radius: 6px;
            transform: scaleX(-1); /* Mirrors the camera feed for natural viewing */
            background: #1e293b;
        }
        #captured-preview {
            width: 100%;
            max-width: 280px;
            display: none;
            border-radius: 6px;
            border: 2px solid #10b981;
        }
        .cam-btn {
            margin-top: 15px;
            padding: 10px 18px;
            background: #475569;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }
        .cam-btn:hover { background: #64748b; }
        .capture-success { color: #10b981; font-size: 12px; margin-top: 8px; display: none; }
        @media (max-width: 680px) {
            .register-container { flex-direction: column-reverse; }
        }
    </style>
</head>
<body>
    <div class="login-container register-container">
        
        <!-- Right Side: Biometric Camera Verification View -->
        <div class="camera-section">
            <h3 style="font-size:14px; color:#cbd5e1; margin-bottom:12px;">Biometric Face Capture</h3>
            <video id="webcam" autoplay playsinline></video>
            <canvas id="canvas" style="display:none;" width="320" height="240"></canvas>
            <img id="captured-preview" alt="Snapshot preview">
            
            <button type="button" id="capture-btn" class="cam-btn">📸 Capture Photo</button>
            <button type="button" id="retake-btn" class="cam-btn" style="display:none; background:#b91c1c;">🔄 Retake Photo</button>
            <span id="success-status" class="capture-success">✓ Facial profile locked in</span>
        </div>

        <!-- Left Side: Registration Input Fields -->
        <div class="form-section">
            <header class="login-header" style="text-align: left;">
                <h1>Student Enrollment</h1>
                <p>Register your profile to activate biometric access keys.</p>
            </header>

            <?php session_start(); if (isset($_SESSION['register_error'])): ?>
                <div class="error-alert">
                    <span>⚠️</span> <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                </div>
            <?php endif; ?>

            <form action="process_register.php" method="POST" id="regForm">
                <!-- Hidden inputs hold the base64 picture data captured via Javascript -->
                <input type="hidden" id="biometric_image" name="biometric_image" required>

                <div class="input-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="full_name" required placeholder="e.g., Benjamin Chidi">
                </div>

                <div class="input-group">
                    <label for="matricNo">Matric Number</label>
                    <input type="text" id="matricNo" name="matric_no" required placeholder="e.g., EU/2026/0482">
                </div>

                <div class="input-group">
                    <label for="email">Institutional Email</label>
                    <input type="email" id="email" name="email" required placeholder="e.g., ben@espoly.edu.ng">
                </div>

                <div class="input-group">
                    <label for="password">Portal Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create portal password">
                </div>

                <button type="submit" class="login-submit-btn" style="background:#10b981;">Complete Enrollment</button>
            </form>

            <footer class="login-footer" style="text-align: left;">
                <p>Already registered? <a href="login.php">Return to Sign In</a></p>
            </footer>
        </div>

    </div>

    <!-- Inject the camera management operations script -->
    <script src="register-biometrics.js"></script>
</body>
</html>