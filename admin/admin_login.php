

<?php
require_once "includes/config.php";



if(isset($_SESSION['id'])){
        header("location: index.php");
}




// Define allowed coordinates (latitude, longitude)
$allowedLocations = [   
    // ['lat' =>23.031006, 'lng' => 72.570951],    // Example: New York City
    ['lat' =>23.03097131001558, 'lng' => 72.57092427981297],  // for localhost
    // Add more locations as needed
];

$getredius = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'office_radius'");
$fetchredius = $getredius->fetch(PDO::FETCH_ASSOC);
$allowedRadius = $fetchredius['setting_value']; // meters

// Check if user has already passed geolocation verification
$geolocationVerified = isset($_SESSION['geolocation_verified']) && $_SESSION['geolocation_verified'] === true;

// Handle form submission
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['username'];
    $password = $_POST['password'];

    // First, verify user credentials
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE `username` = ?");
    $stmt->execute([$name]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        if($user['role'] !== "admin"){
            header("location: ../login.php");
        }
        else{
            $_SESSION['id'] = $user['emp_id'];
            header("location:index.php");
        }
        
    } else {
        $d = 2; // Invalid credentials
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrack - Login</title>
    <link rel="icon" type="image/x-icon" href="includes/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    <style>
        /* Your existing CSS styles */
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
        
        .login-container {
            margin-top: 100px;
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-card {
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
        
        .logo i {
            font-size: 32px;
            color: var(--secondary);
        }
        
        .logo h1 {
            font-weight: 700;
            font-size: 28px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .login-header {
            margin-bottom: 25px;
        }
        
        .login-header h2 {
            font-size: 22px;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .login-header p {
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
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember input {
            width: 16px;
            height: 16px;
        }
        
        .forgot-password {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-button {
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
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider span {
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider p {
            padding: 0 15px;
            color: var(--gray);
            font-size: 14px;
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .social-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .social-btn:hover {
            background: #f9f9f9;
        }
        
        .social-btn i {
            font-size: 18px;
        }
        
        .google-btn {
            color: #DB4437;
        }
        
        .microsoft-btn {
            color: #0078D7;
        }
        
        .signup-link {
            font-size: 14px;
            color: var(--gray);
        }
        
        .signup-link a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
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
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2ecc71;
            border: 1px solid #c8e6c9;
        }
        
        .location-permission {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .location-permission i {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .location-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 20px;
            }
            
            .social-login {
                flex-direction: column;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="../includes/logo.png" alt="" width="80" height="50" >
                <h1 style="margin-left: 10px;">ST ZK DM</h1>
            </div>
            
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your account to continue </p>

                <?php if(isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] == true): ?>
                    <h3 style="color: blue;">Work From Home</h3>
                    <span style="color: green; font-weight: bold;">Work From Home Access Granted!</span>
                <?php endif; ?>
            </div>
            
            <div id="error-message" class="notification error">
                <i class="fas fa-exclamation-circle"></i> Invalid username or password
            </div>

            <?php if(isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] == true): ?>

            <?php else: ?>
            
            <div id="location-message" class="location-permission" style="display: none;">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Location Access Required</h3>
                <p>We need to verify your location before you can login.</p>
                <button id="get-location" class="location-btn">Allow Location Access</button>
            </div>
            <?php endif; ?>
            <form id="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="input-field" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password" required showpassword>
                        <!-- <span id="togglePassword" style="cursor:pointer;">👁️</span> -->
                        <i class="fas fa-eye-slash" style="position:relative; float:right;transform: translate( -150%,-190%); cursor: pointer;" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-button" id="submit-button">Sign In</button>
                <!-- <span>For Work From Home <span id="work_from_home" style="color:blue; cursor:pointer;">Click Here</span>!</span><br> -->
           
                <span>Forget Password? <a href="../forgot_pass.php">Click Here</a>!</span>
            </form>
        </div>
    </div>
    
    <script src="../js/jQuery.min.js"></script>
    <script src="../js/sweetAlert.js"></script>
    
    <script>
        
     
       
        
            // // prevent all kind of functions by user.
        
        // document.addEventListener('contextmenu', e => e.preventDefault());
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
    
    
        // Toggle password visibility
        $("#togglePassword").click(function(){
            let passwordField = $("#password");
            let type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            this.classList.toggle('fa-eye');
        });

        
    
    

        
        // Handle PHP response
        let d = <?php echo isset($d) ? $d : 0; ?>;
        // alert(typeof(d))
        if (d == 1) {
            Swal.fire({
                title: "Access Denied!",
                text: "You are not in an allowed location to access this system.",
                icon: "error"
            }).then(() => {
                // Clear location data
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                document.getElementById('location-message').style.display = 'block';
                // window.location.href="login.php";
            });
        }
        if (d == 2) {
            Swal.fire({
                title: "Invalid Credentials!",
                icon: "error",
                showConfirmButton: true
            }).then(res => {
                if (res.isConfirmed) {
                    // window.location.href = "login.php";
                }
            });
        }
        if (d == 3) {
            Swal.fire({
                title: "Location Not Provided!",
                icon: "error",
                showConfirmButton: true
            }).then(res => {
                if (res.isConfirmed) {
                    $("#location-message").show();
                }
            });
        }

        
        // Add focus effects to inputs
        const inputs = document.querySelectorAll('.input-field');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>