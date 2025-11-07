<?php

require_once "includes/config.php";

// Redirect if OTP not sent
if (!isset($_SESSION['password_reset']) || !isset($_SESSION['otp_sent'])) {
    header("Location: forgot_pass.php");
    exit;
}

// Check if OTP has expired
if (time() > $_SESSION['password_reset']['expires']) {
    unset($_SESSION['password_reset']);
    unset($_SESSION['otp_sent']);
    header("Location: forgot_pass.php?error=expired");
    exit;
}

// Handle OTP verification and password reset
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['otp'])) {
        // Verify OTP
        $entered_otp = trim($_POST['otp']);
        $stored_otp = $_SESSION['password_reset']['otp'];
        
        if ($entered_otp === $stored_otp) {
            $_SESSION['otp_verified'] = true;
            $success = "OTP verified successfully. You can now set your new password.";
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } elseif (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        // Set new password
        if ($_SESSION['otp_verified'] !== true) {
            header("Location: reset_password.php");
            exit;
        }
        
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            // Update password in database
            $user_id = $_SESSION['password_reset']['user_id'];
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE employees SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$password_hash, $user_id])) {
                // Clear all sessions
                unset($_SESSION['password_reset']);
                unset($_SESSION['otp_sent']);
                unset($_SESSION['otp_verified']);
                
                $_SESSION['reset_success'] = true;

                $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
                $stmt->execute([$user_id]);
                $fetchUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if($fetchUser['role'] === 'admin'){

                    header("Location: admin/admin_login.php");
                }
                else{

                    header("Location: login.php");
                }
                exit;
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TimeTrack</title>
    <link rel="icon" type="image/x-icon" href="includes/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            line-height: 1.6;
        }
        
        .reset-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .reset-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .logo img {
            width: 80px;
            height: 50px;
        }
        
        .logo h1 {
            font-weight: 700;
            font-size: 28px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .reset-header {
            margin-bottom: 25px;
        }
        
        .reset-header h2 {
            font-size: 22px;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .reset-header p {
            color: var(--gray);
            font-size: 15px;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .input-field {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .input-with-icon .input-field {
            padding-left: 45px;
        }
        
        .submit-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .back-to-login {
            font-size: 14px;
            color: var(--gray);
        }
        
        .back-to-login a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        .notification {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .error {
            background-color: #ffebee;
            color: #e74c3c;
            border: 1px solid #ffcdd2;
            display: block;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2ecc71;
            border: 1px solid #c8e6c9;
            display: block;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #2ecc71; }
        
        @media (max-width: 480px) {
            .reset-container {
                padding: 15px;
            }
            
            .reset-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="logo">
                <img src="includes/logo.png" alt="TimeTrack Logo">
                <h1>ST ZK DM</h1>
            </div>
            
            <div class="reset-header">
                <h2>Reset Password</h2>
                <p>
                    <?php if (!isset($_SESSION['otp_verified'])): ?>
                    Enter the OTP sent to your email
                    <?php else: ?>
                    Set your new password
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['otp_verified'])): ?>
            <!-- OTP Verification Form -->
            <form id="otp-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="input-group">
                    <label for="otp">Enter OTP</label>
                    <div class="input-with-icon">
                        <i class="fas fa-shield-alt"></i>
                        <input type="text" id="otp" name="otp" class="input-field" placeholder="Enter 6-digit OTP" maxlength="6" required>
                    </div>
                    <small style="color: var(--gray);">OTP will expire in 10 minutes</small>
                </div>
                
                <button type="submit" class="submit-button">Verify OTP</button>
                
                <div class="back-to-login">
                    <a href="forgot_pass.php"><i class="fas fa-arrow-left"></i> Back to Email Entry</a>
                </div>
            </form>
            <?php else: ?>
            <!-- Password Reset Form -->
            <form id="password-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="input-group">
                    <label for="new_password">New Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="new_password" name="new_password" class="input-field" placeholder="Enter new password" required>
                        <i class="fas fa-eye-slash" style="position:absolute; left:89%; top:50%; transform:translateY(-50%); cursor: pointer;" id="toggleNewPassword"></i>
                    </div>
                    <div id="password-strength" class="password-strength"></div>
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="input-field" placeholder="Confirm new password" required>
                        <i class="fas fa-eye-slash" style="position:absolute; left:89%; top:50%; transform:translateY(-50%); cursor: pointer;" id="toggleConfirmPassword"></i>
                    </div>
                    <div id="password-match" class="password-strength"></div>
                </div>
                
                <button type="submit" class="submit-button" id="reset-button">Reset Password</button>
                
                <div class="back-to-login">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/jQuery.min.js"></script>
    <script src="js/sweetAlert.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('toggleNewPassword')?.addEventListener('click', function() {
            const passwordField = document.getElementById('new_password');
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
            const passwordField = document.getElementById('confirm_password');
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Password strength indicator
        document.getElementById('new_password')?.addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthText.textContent = '';
                strengthText.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            if (strength <= 1) {
                strengthText.textContent = 'Weak';
                strengthText.className = 'password-strength strength-weak';
            } else if (strength <= 3) {
                strengthText.textContent = 'Medium';
                strengthText.className = 'password-strength strength-medium';
            } else {
                strengthText.textContent = 'Strong';
                strengthText.className = 'password-strength strength-strong';
            }
        });
        
        // Password match indicator
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                return;
            }
            
            if (newPassword === confirmPassword) {
                matchText.textContent = 'Passwords match';
                matchText.className = 'password-strength strength-strong';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'password-strength strength-weak';
            }
        });
        
        // Form validation
        document.getElementById('password-form')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 6) {
                e.preventDefault();
                Swal.fire({
                    title: "Weak Password",
                    text: "Password must be at least 6 characters long.",
                    icon: "error"
                });
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    title: "Password Mismatch",
                    text: "Passwords do not match. Please confirm your password.",
                    icon: "error"
                });
            }
        });
        
        // OTP input formatting
        document.getElementById('otp')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });
    </script>
        <script>
        $(document).ready(function(){
                // // prevent all kind of functions by user.
                //  document.addEventListener('contextmenu', e => e.preventDefault());
                // document.onkeydown = function (e) {
                // // F12
                // if (e.keyCode === 123) return false;
            
                // // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
                // if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;
            
                // // Ctrl+U (View Source)
                // if (e.ctrlKey && e.key.toUpperCase() === 'U') return false;
                // };
                
                // document.addEventListener('selectstart', e => e.preventDefault());
                // document.addEventListener('dragstart', e => e.preventDefault());
                // document.addEventListener('copy', e => e.preventDefault());    
   
        });
    </script>
</body>
</html>