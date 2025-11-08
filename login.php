<?php
require_once "includes/config.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_SESSION['id'])){
    header("location: index.php");
    exit;
}

if (!isset($_SESSION['redirected'])) {
    $_SESSION['redirected'] = true;
    header("location: login.php");
    exit;
}

// Define allowed coordinates (latitude, longitude)
$allowedLocations = [   
    // ['lat' =>23.031006, 'lng' => 72.570951],    // Example: New York City
    ['lat' =>23.052288, 'lng' => 72.58112],  // for localhost
    // Add more locations as needed
];

$getredius = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'office_radius'");
$fetchredius = $getredius->fetch(PDO::FETCH_ASSOC);
$allowedRadius = $fetchredius['setting_value']; // meters

// Check if user has already passed geolocation verification
$geolocationVerified = isset($_SESSION['geolocation_verified']) && $_SESSION['geolocation_verified'] === true;

// Initialize error variable
$errorCode = 0;

// Function to get location by IP (Fallback method)
function getLocationByIP($ip = null) {
    if (!$ip) {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Handle localhost/private IPs
        if ($ip == '::1' || $ip == '127.0.0.1' || substr($ip, 0, 3) == '10.' || 
            substr($ip, 0, 8) == '192.168.' || substr($ip, 0, 7) == '172.16.') {
            // For local development, return a default location
            return [
                'lat' => 23.0260736,
                'lng' => 72.5352448,
                'method' => 'ip',
                'city' => 'Local Development'
            ];
        }
    }
    
    // Use IP-based geolocation service
    $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]
    ]);
    
    try {
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            
            if ($data && $data['status'] == 'success') {
                return [
                    'lat' => $data['lat'],
                    'lng' => $data['lon'],
                    'method' => 'ip',
                    'city' => $data['city'] ?? 'Unknown',
                    'region' => $data['regionName'] ?? 'Unknown',
                    'country' => $data['country'] ?? 'Unknown'
                ];
            }
        }
    } catch (Exception $e) {
        // Log error if needed
        error_log("IP geolocation failed: " . $e->getMessage());
    }
    
    return null;
}

// Function to check if within allowed area with different tolerances
function isWithinAllowedArea($userLat, $userLng, $method = 'gps') {
    global $allowedLocations, $allowedRadius;
    
    // If using IP method, allow slightly larger radius (for office WiFi variations)
    $radius = $method === 'ip' ? $allowedRadius * 1.5 : $allowedRadius;
    
    foreach ($allowedLocations as $location) {
        $distance = calculateDistance($userLat, $userLng, $location['lat'], $location['lng']);
        if ($distance <= $radius) {
            return true;
        }
    }
    return false;
}

// Function to calculate distance between two coordinates (Haversine formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // meters
    
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    return $angle * $earthRadius;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['username'];
    $password = $_POST['password'];

    // First, verify user credentials
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE `username` = ?");
    $stmt->execute([$name]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Check WFH status for this user
        $stmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = DATE(CURRENT_DATE)");
        $stmt->execute([$user['emp_id']]);
        $wfhRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($wfhRequest){
            $_SESSION['WFHsuccess'] = true;
            $_SESSION['WFHsuccessId'] = $user['emp_id'];
            $isWFHUser = true;
        } else {
            $_SESSION['WFHsuccess'] = false;
            $_SESSION['WFHsuccessId'] = false;
            $isWFHUser = false;
        }

        // Enhanced location verification for non-WFH users
        if (!$isWFHUser) {
            $locationMethod = 'unknown';
            $userLat = 0;
            $userLng = 0;
            
            // Try GPS coordinates first (most accurate)
            if (isset($_POST['latitude']) && $_POST['latitude'] != 0 && isset($_POST['longitude']) && $_POST['longitude'] != 0) {
                $userLat = floatval($_POST['latitude']);
                $userLng = floatval($_POST['longitude']);
                $locationMethod = 'gps';
            } 
            // Fallback to IP-based geolocation
            elseif (isset($_POST['fallback_location']) && $_POST['fallback_location'] == 'ip') {
                $ipLocation = getLocationByIP();
                if ($ipLocation) {
                    $userLat = $ipLocation['lat'];
                    $userLng = $ipLocation['lng'];
                    $locationMethod = 'ip';
                    $_SESSION['location_info'] = $ipLocation; // Store for debugging
                }
            }
            
            // Verify location if we have coordinates
            if ($userLat != 0 && $userLng != 0) {
                if (isWithinAllowedArea($userLat, $userLng, $locationMethod)) {
                    $_SESSION['geolocation_verified'] = true;
                    $_SESSION['location_method'] = $locationMethod;
                    $geolocationVerified = true;
                } else {
                    $errorCode = 1; // Location not allowed
                    $geolocationVerified = false;
                    $_SESSION['location_debug'] = [
                        'method' => $locationMethod,
                        'user_lat' => $userLat,
                        'user_lng' => $userLng,
                        'allowed_locations' => $allowedLocations
                    ];
                }
            } else {
                $errorCode = 3; // Location not provided
                $geolocationVerified = false;
            }
        } else {
            // WFH users bypass location check
            $geolocationVerified = true;
        }
        
        // If geolocation is verified or user is WFH, process login
        if ($geolocationVerified || $isWFHUser) {
            if($user['status'] == "inactive"){
                $errorCode = 4; // Inactive user
            } else {
                $_SESSION['id'] = $user['emp_id'];
                $_SESSION['at_office'] = true;
                
                if($user['role'] == "admin"){
                    $_SESSION['redirected'] = false;
                    header("Location: admin/index.php");
                    exit;
                } else {
                    $_SESSION['redirected'] = false;
                    header("Location: index.php");
                    exit;
                }
            }
        }
    } else {
        $errorCode = 2; // Invalid credentials
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
        /* Your existing CSS styles remain the same */
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
        
        .fallback-option {
            margin-top: 10px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .fallback-btn {
            background: #f39c12;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
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
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="includes/logo.png" alt="" width="80" height="50" >
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
                
                <!-- <div id="fallback-option" class="fallback-option" style="display: none; margin-top: 15px;">
                    <p><strong>GPS not working?</strong> Try IP-based location detection:</p>
                    <button id="use-ip-location" class="fallback-btn">Use IP Location</button>
                </div> -->
            </div>
            <?php endif; ?>
            
            <form id="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" id="latitude" name="latitude" value="0">
                <input type="hidden" id="longitude" name="longitude" value="0">
                <input type="hidden" id="fallback_location" name="fallback_location" value="">
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="input-field" placeholder="Enter your username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password" required>
                        <i class="fas fa-eye-slash" style="position:relative; float:right;transform: translate( -150%,-190%); cursor: pointer;" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-button" id="submit-button">Sign In</button>
                <span>Not In Office? go to <a href="checkin.php">General Access Login</a>!</span><br>
                <span>Forget Password? <a href="forgot_pass.php">Click Here</a>!</span>
            </form>
        </div>
    </div>
    
    <script src="js/jQuery.min.js"></script>
    <script src="js/sweetAlert.js"></script>
    
    <script>
        // Store error code in JavaScript variable
        const errorCode = <?php echo $errorCode; ?>;
        
        // Show appropriate error message based on error code
        document.addEventListener('DOMContentLoaded', function() {
            switch(errorCode) {
                case 1:
                    showLocationError("You are not in an allowed location to access this system.");
                    break;
                case 2:
                    showCredentialsError("Invalid username or password.");
                    break;
                case 3:
                    showLocationError("Location access is required to login. Please allow location access.");
                    document.getElementById('location-message').style.display = 'block';
                    break;
                case 4:
                    showCredentialsError("Your account is inactive. Please contact administrator.");
                    break;
            }
            
            // Show fallback option after a delay if no errors
            if (errorCode === 0) {
                setTimeout(() => {
                    document.getElementById('fallback-option').style.display = 'block';
                }, 5000);
            }
        });
        
        // Prevent all kind of functions by user.
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
    
        // Toggle password visibility
        $("#togglePassword").click(function(){
            let passwordField = $("#password");
            let type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            this.classList.toggle('fa-eye');
        });
    
        // Check if geolocation is supported
        if (!navigator.geolocation) {
            showLocationError("Geolocation is not supported by your browser");
            document.getElementById('fallback-option').style.display = 'block';
        }
        
        // Initial geolocation check
        document.addEventListener('DOMContentLoaded', function() {
            if (errorCode === 0 && !<?php echo isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] ? 'true' : 'false'; ?>) {
                initialGeolocationCheck();
            }
        });
        
        function initialGeolocationCheck() {
            // Only show location request if no errors and not WFH user
            if (errorCode === 0 && !<?php echo isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] ? 'true' : 'false'; ?>) {
                Swal.fire({
                    title: "Location Access Required",
                    text: "We need to verify your location for attendance tracking. This works best on mobile devices. Desktop users may need to use IP-based detection.",
                    icon: "info",
                    confirmButtonText: "OK",
                    showCancelButton: true,
                    cancelButtonText: "cancel",
                    showDenyButton: true,
                    denyButtonText: "Try GPS First"
                }).then((result) => {
                    if (result.isConfirmed || result.isDenied) {
                        requestLocationWithRetry();
                    } 
                    // else if (result.dismiss === Swal.DismissReason.cancel) {
                    //     useIPLocation();
                    // }
                });
            }
        }

        // Enhanced location detection with retry logic
        function requestLocationWithRetry(retries = 3) {
            document.getElementById('get-location').textContent = 'Detecting location...';
            document.getElementById('get-location').disabled = true;
            document.getElementById('location-message').style.display = 'block';

            Swal.fire({
                title: 'Detecting Location...',
                html: `
                  <div style="display: flex; flex-direction: column; align-items: center;">
                    <div class="spinner" style="
                      width: 40px;
                      height: 40px;
                      border: 4px solid #ccc;
                      border-top: 4px solid #3085d6;
                      border-radius: 50%;
                      animation: spin 1s linear infinite;
                      margin-bottom: 10px;
                    "></div>
                    <div style="font-size: 16px; text-align: center;">
                        <p>Attempting GPS location detection...</p>
                        <p><small>This works best on mobile devices</small></p>
                    </div>
                  </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                backdrop: true,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const attempt = (attemptCount) => {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        // Success: got location
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude; 


                        ///////////////////////////////////////////////


                    // alert("lat: "+lat+" lng: "+lng);


                    ///////////////////////////////////////////////
                        
                        // Store coordinates in hidden form fields
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        document.getElementById('fallback_location').value = '';
                        
                        // Hide location message
                        document.getElementById('location-message').style.display = 'none';
                        
                        Swal.close();
                        showLocationSuccess("GPS location verified! You can now login.");
                        
                        // Re-enable location button
                        document.getElementById('get-location').textContent = 'Allow Location Access';
                        document.getElementById('get-location').disabled = false;
                    },
                    (error) => {
                        if (attemptCount < retries) {
                            // Retry after delay
                            setTimeout(() => {
                                Swal.getHtmlContainer().querySelector('div').innerHTML = `
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="spinner" style="
                                            width: 40px;
                                            height: 40px;
                                            border: 4px solid #ccc;
                                            border-top: 4px solid #3085d6;
                                            border-radius: 50%;
                                            animation: spin 1s linear infinite;
                                            margin-bottom: 10px;
                                        "></div>
                                        <div style="font-size: 16px; text-align: center;">
                                            <p>Attempt ${attemptCount + 1} of ${retries}...</p>
                                            <p><small>Retrying location detection</small></p>
                                        </div>
                                    </div>
                                `;
                                attempt(attemptCount + 1);
                            }, 2000);
                        } else {
                            // All retries failed
                            Swal.close();
                            document.getElementById('get-location').textContent = 'Allow Location Access';
                            document.getElementById('get-location').disabled = false;
                            
                            let errorMessage;
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage = "Location access denied. Please enable location services or use IP-based detection.";
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = "GPS location unavailable. Please try IP-based detection.";
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = "Location request timed out. Please try IP-based detection.";
                                    break;
                                default:
                                    errorMessage = "GPS detection failed. Please try IP-based detection.";
                                    break;
                            }
                            
                            showLocationError(errorMessage);
                            document.getElementById('fallback-option').style.display = 'block';
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000 // Cache for 1 minute
                    }
                );
            };

            attempt(1);
        }

        // IP-based location fallback
        function useIPLocation() {
            Swal.fire({
                title: 'Using IP Location',
                html: `
                  <div style="display: flex; flex-direction: column; align-items: center;">
                    <div class="spinner" style="
                      width: 40px;
                      height: 40px;
                      border: 4px solid #ccc;
                      border-top: 4px solid #f39c12;
                      border-radius: 50%;
                      animation: spin 1s linear infinite;
                      margin-bottom: 10px;
                    "></div>
                    <div style="font-size: 16px; text-align: center;">
                        <p>Detecting your location via IP address...</p>
                        <p><small>This method uses your network location</small></p>
                    </div>
                  </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                backdrop: true,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Set fallback flag and submit form
            document.getElementById('fallback_location').value = 'ip';
            document.getElementById('latitude').value = 0;
            document.getElementById('longitude').value = 0;
            document.getElementById('location-message').style.display = 'none';
            
            // Small delay to show the loading message, then submit
            setTimeout(() => {
                document.getElementById('login-form').submit();
            }, 2000);
        }

        // Add event listeners
        document.getElementById('get-location').addEventListener('click', requestLocationWithRetry);
        document.getElementById('use-ip-location').addEventListener('click', useIPLocation);

        // Form submission validation
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            const fallback = document.getElementById('fallback_location').value;
            
            // If location hasn't been verified yet and not WFH user, prevent form submission
            if ((!lat || !lng || lat == 0 || lng == 0) && !fallback && !<?php echo isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] ? 'true' : 'false'; ?>) {
                e.preventDefault();
                showLocationError("Please allow location access or use IP-based detection before submitting.");
                document.getElementById('location-message').style.display = 'block';
            }
        });
        
        function showLocationError(message) {
            Swal.fire({
                title: 'Location Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
        
        function showCredentialsError(message) {
            Swal.fire({
                title: 'Login Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
        
        function showLocationSuccess(message) {
            Swal.fire({
                title: 'Success',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
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