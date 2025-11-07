<?php

require_once "includes/config.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Store OTP in session with expiration (10 minutes)
        $_SESSION['password_reset'] = [
            'user_id' => $user['id'],
            'otp' => $otp,
            'email' => $email,
            'expires' => time() + (10 * 60) // 10 minutes from now
        ];
        
        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stzkdigitalmedia@gmail.com'; // Your Gmail
            $mail->Password   = 'ytsl icti dcvn nkhq'; // Your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Recipients
            $mail->setFrom('noreply@timetrack.com', 'TimeTrack System');
            $mail->addAddress($email, $user['full_name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP - STZK Digital Media';
            $mail->Body    = "
            <html>
            <head>
                <title>Password Reset OTP</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .otp-box { background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0; border-radius: 5px; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Password Reset Request</h2>
                    <p>Hello <strong>" . htmlspecialchars($user['full_name']) . "</strong>,</p>
                    <p>You have requested to reset your password. Please use the following OTP to verify your identity:</p>
                    <div class='otp-box'><strong>" . $otp . "</strong></div>
                    <p>This OTP will expire in 10 minutes.</p>
                    <p>If you didn't request this reset, please ignore this email.</p>
                    <div class='footer'>
                        <p>Best regards,<br><strong>TimeTrack System</strong></p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->AltBody = "Password Reset OTP\n\nHello " . $user['full_name'] . ",\n\nYou have requested to reset your password. Please use the following OTP to verify your identity:\n\nOTP: " . $otp . "\n\nThis OTP will expire in 10 minutes.\n\nIf you didn't request this reset, please ignore this email.\n\nBest regards,\nSTZK Digital Media";
            
            $mail->send();
            $_SESSION['otp_sent'] = true;
            header("Location: reset_password.php");
            exit;
            
        } catch (Exception $e) {
            $error = "Failed to send OTP. Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "Email not found in our system.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - TimeTrack</title>
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
        
        .forgot-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .forgot-card {
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
        
        .forgot-header {
            margin-bottom: 25px;
        }
        
        .forgot-header h2 {
            font-size: 22px;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .forgot-header p {
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
        
        @media (max-width: 480px) {
            .forgot-container {
                padding: 15px;
            }
            
            .forgot-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="logo">
                <img src="includes/logo.png" alt="TimeTrack Logo">
                <h1>ST ZK DM</h1>
            </div>
            
            <div class="forgot-header">
                <h2>Forgot Password</h2>
                <p>Enter your email to reset your password</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form id="forgot-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="input-field" placeholder="Enter your registered email" required>
                    </div>
                </div>
                
                <button type="submit" class="submit-button">Send OTP</button>
                
                <div class="back-to-login">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="js/jQuery.min.js"></script>
    <script src="js/sweetAlert.js"></script>
    
    <script>
        // Form validation
        document.getElementById('forgot-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                e.preventDefault();
                Swal.fire({
                    title: "Invalid Email",
                    text: "Please enter a valid email address.",
                    icon: "error"
                });
            }
        });
    </script>
    
    <script>
        $(document).ready(function(){
                // // prevent all kind of functions by user.
                 document.addEventListener('contextmenu', e => e.preventDefault());
                document.onkeydown = function (e) {
                // F12
                if (e.keyCode === 123) return false;
            
                // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
                if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;
            
                // Ctrl+U (View Source)
                if (e.ctrlKey && e.key.toUpperCase() === 'U') return false;
                };
                
                document.addEventListener('selectstart', e => e.preventDefault());
                document.addEventListener('dragstart', e => e.preventDefault());
                document.addEventListener('copy', e => e.preventDefault());    
   
        });
    </script>
</body>
</html>