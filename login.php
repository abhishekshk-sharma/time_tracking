<?php
require_once "includes/config.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle AJAX location detection requests FIRST - before any HTML output
if (isset($_POST['action']) && $_POST['action'] == 'store_location') {
    if (isset($_POST['latitude']) && isset($_POST['longitude']) && isset($_POST['method'])) {
        $_SESSION['stored_latitude'] = floatval($_POST['latitude']);
        $_SESSION['stored_longitude'] = floatval($_POST['longitude']);
        $_SESSION['stored_location_method'] = $_POST['method'];
        
        // Clear any previous output and send JSON response
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } else {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Missing location data']);
        exit;
    }
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
    ['lat' =>23.0260736, 'lng' => 72.5352448],  // Office location
    // ['lat' =>23.052288, 'lng' => 72.58112],
    // Add more locations as needed
];

$getredius = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'office_radius'");
$fetchredius = $getredius->fetch(PDO::FETCH_ASSOC);
$allowedRadius = $fetchredius['setting_value']; // meters

// Check if user has already passed geolocation verification
$geolocationVerified = isset($_SESSION['geolocation_verified']) && $_SESSION['geolocation_verified'] === true;

// Initialize error variable
$errorCode = 0;

// Store location attempt state in session
$showLocationPrompt = false;
if (isset($_SESSION['location_attempt_failed']) && $_SESSION['location_attempt_failed']) {
    $showLocationPrompt = true;
    unset($_SESSION['location_attempt_failed']); // Clear after use
}

// Store location data in session if we have valid coordinates
$storedLocation = [
    'latitude' => $_SESSION['stored_latitude'] ?? 0,
    'longitude' => $_SESSION['stored_longitude'] ?? 0,
    'method' => $_SESSION['stored_location_method'] ?? ''
];

// Only consider it valid if we have actual GPS coordinates (not IP method)
$hasValidStoredLocation = ($storedLocation['latitude'] != 0 && $storedLocation['longitude'] != 0 && $storedLocation['method'] == 'gps');

// Function to get location by IP (Fallback method)
function getLocationByIP($ip = null) {
    if (!$ip) {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Handle localhost/private IPs
        if ($ip == '::1' || $ip == '127.0.0.1' || substr($ip, 0, 3) == '10.' || 
            substr($ip, 0, 8) == '192.168.' || substr($ip, 0, 7) == '172.16.') {
            // For local development, return office coordinates
            return [
                'lat' => 23.0260736,
                'lng' => 72.5352448,
                'method' => 'ip',
                'city' => 'Office Network',
                'region' => 'Gujarat',
                'country' => 'India',
                'accuracy' => 'high'
            ];
        }
    }
    
    // Try multiple free IP geolocation services
    $services = [
        "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query",
        "https://ipapi.co/{$ip}/json/",
        "http://www.geoplugin.net/json.gp?ip={$ip}"
    ];
    
    foreach ($services as $url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        try {
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                
                // ip-api.com format
                if (isset($data['status']) && $data['status'] == 'success') {
                    return [
                        'lat' => $data['lat'],
                        'lng' => $data['lon'],
                        'method' => 'ip',
                        'city' => $data['city'] ?? 'Unknown',
                        'region' => $data['regionName'] ?? 'Unknown',
                        'country' => $data['country'] ?? 'Unknown',
                        'accuracy' => 'medium'
                    ];
                }
                // ipapi.co format
                elseif (isset($data['latitude']) && isset($data['longitude'])) {
                    return [
                        'lat' => $data['latitude'],
                        'lng' => $data['longitude'],
                        'method' => 'ip',
                        'city' => $data['city'] ?? 'Unknown',
                        'region' => $data['region'] ?? 'Unknown',
                        'country' => $data['country_name'] ?? 'Unknown',
                        'accuracy' => 'medium'
                    ];
                }
                // geoplugin.net format
                elseif (isset($data['geoplugin_latitude']) && isset($data['geoplugin_longitude'])) {
                    return [
                        'lat' => $data['geoplugin_latitude'],
                        'lng' => $data['geoplugin_longitude'],
                        'method' => 'ip',
                        'city' => $data['geoplugin_city'] ?? 'Unknown',
                        'region' => $data['geoplugin_region'] ?? 'Unknown',
                        'country' => $data['geoplugin_countryName'] ?? 'Unknown',
                        'accuracy' => 'low'
                    ];
                }
            }
        } catch (Exception $e) {
            // Continue to next service
            error_log("IP geolocation service failed: " . $e->getMessage());
            continue;
        }
    }
    
    // If all services fail, return null
    return null;
}

// Function to check if within allowed area with different tolerances
function isWithinAllowedArea($userLat, $userLng, $method = 'gps') {
    global $allowedLocations, $allowedRadius;
    
    // If using IP method, allow larger radius (for less accurate IP geolocation)
    $radius = $method === 'ip' ? $allowedRadius * 2 : $allowedRadius;
    
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
            
            // Try stored location data first (GPS method)
            if (isset($_SESSION['stored_latitude']) && $_SESSION['stored_latitude'] != 0 && $_SESSION['stored_location_method'] == 'gps') {
                $userLat = floatval($_SESSION['stored_latitude']);
                $userLng = floatval($_SESSION['stored_longitude']);
                $locationMethod = 'gps';
            }
            // Try GPS coordinates from form
            elseif (isset($_POST['latitude']) && $_POST['latitude'] != 0 && isset($_POST['longitude']) && $_POST['longitude'] != 0) {
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
                    
                    // Log IP location details
                    error_log("IP Location for {$_SERVER['REMOTE_ADDR']}: {$userLat}, {$userLng} - {$ipLocation['city']}, {$ipLocation['region']}");
                } else {
                    $errorCode = 5; // IP location failed
                    $geolocationVerified = false;
                    $_SESSION['location_attempt_failed'] = true;
                }
            }
            
            // Verify location if we have coordinates
            if ($userLat != 0 && $userLng != 0) {
                if (isWithinAllowedArea($userLat, $userLng, $locationMethod)) {
                    $_SESSION['geolocation_verified'] = true;
                    $_SESSION['location_method'] = $locationMethod;
                    $geolocationVerified = true;
                    
                    // Clear stored location after successful verification
                    unset($_SESSION['stored_latitude']);
                    unset($_SESSION['stored_longitude']);
                    unset($_SESSION['stored_location_method']);
                } else {
                    $errorCode = 1; // Location not allowed
                    $geolocationVerified = false;
                    $_SESSION['location_attempt_failed'] = true;
                    $_SESSION['location_debug'] = [
                        'method' => $locationMethod,
                        'user_lat' => $userLat,
                        'user_lng' => $userLng,
                        'allowed_locations' => $allowedLocations,
                        'distance' => calculateDistance($userLat, $userLng, $allowedLocations[0]['lat'], $allowedLocations[0]['lng'])
                    ];
                }
            } elseif (!isset($errorCode)) {
                $errorCode = 3; // Location not provided
                $geolocationVerified = false;
                $_SESSION['location_attempt_failed'] = true;
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
                
                // Clear location attempt flags on successful login
                unset($_SESSION['location_attempt_failed']);
                
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
        $_SESSION['location_attempt_failed'] = true;
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
            margin: 5px;
            width: 100%;
            max-width: 200px;
        }
        
        .fallback-option {
            margin-top: 15px;
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
            width: 100%;
            max-width: 200px;
        }
        
        .location-status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
            font-size: 14px;
        }
        
        .location-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .location-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .location-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .method-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 15px 0;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 20px;
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
            
            <?php if(isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] == true): ?>

            <?php else: ?>
            
            <!-- Location Status Indicator -->
            <div id="location-status" class="location-status" style="display: none;">
                <i class="fas fa-map-marker-alt"></i>
                <span id="location-status-text">Location not verified</span>
            </div>
            
            <div id="location-message" class="location-permission" style="<?php echo ($showLocationPrompt || !$hasValidStoredLocation) ? 'display: block;' : 'display: none;'; ?>">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Location Verification Required</h3>
                <p>For security, we need to verify you're at the office location.</p>
                
                <div id="location-buttons" class="method-buttons">
                    <button id="get-location" class="location-btn">
                        <i class="fas fa-satellite"></i> Use GPS Location (Mobile)
                    </button>
                    
                    <div class="fallback-option">
                        <p><strong>On Desktop?</strong> Use IP-based detection:</p>
                        <button id="use-ip-location" class="fallback-btn">
                            <i class="fas fa-wifi"></i> Use IP Location
                        </button>
                    </div>
                </div>
                
                <div id="location-success" style="display: none; margin-top: 15px;">
                    <div class="location-status location-success">
                        <i class="fas fa-check-circle"></i>
                        <span id="success-message">Location verified! You can now login.</span>
                    </div>
                </div>

                <div id="location-error" style="display: none; margin-top: 15px;">
                    <div class="location-status location-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="error-message-text">Location detection failed.</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <form id="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" id="latitude" name="latitude" value="<?php echo $storedLocation['latitude']; ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?php echo $storedLocation['longitude']; ?>">
                <input type="hidden" id="fallback_location" name="fallback_location" value="<?php echo $storedLocation['method'] === 'ip' ? 'ip' : ''; ?>">
                
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
        const showLocationPrompt = <?php echo $showLocationPrompt ? 'true' : 'false'; ?>;
        const hasValidStoredLocation = <?php echo $hasValidStoredLocation ? 'true' : 'false'; ?>;

        // Show appropriate error message based on error code
        document.addEventListener('DOMContentLoaded', function() {
            // Show location status if we have valid stored GPS location
            if (hasValidStoredLocation) {
                showLocationSuccessUI("Location pre-verified! You can login now.");
            }
            
            switch(errorCode) {
                case 1:
                    showLocationError("You are not in an allowed location to access this system. Please try again.");
                    document.getElementById('location-message').style.display = 'block';
                    break;
                case 2:
                    showCredentialsError("Invalid username or password.");
                    document.getElementById('location-message').style.display = 'block';
                    break;
                case 3:
                    showLocationError("Location access is required to login. Please verify your location first.");
                    document.getElementById('location-message').style.display = 'block';
                    break;
                case 4:
                    showCredentialsError("Your account is inactive. Please contact administrator.");
                    break;
                case 5:
                    showLocationError("IP location detection failed. Please try GPS location or contact support.");
                    document.getElementById('location-message').style.display = 'block';
                    break;
            }

            // Auto-show location prompt if it's a fresh page load
            if (errorCode === 0 && !hasValidStoredLocation && !<?php echo isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] ? 'true' : 'false'; ?>) {
                if (!document.getElementById('username').value) {
                    setTimeout(showInitialLocationPrompt, 1000);
                }
            }
        });
        
        // Prevent all kind of functions by user.
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.onkeydown = function (e) {
            if (e.keyCode === 123) return false;
            if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;
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
            document.getElementById('get-location').disabled = true;
            document.getElementById('get-location').innerHTML = '<i class="fas fa-times-circle"></i> GPS Not Supported';
        }
        
        function showInitialLocationPrompt() {
            Swal.fire({
                title: "Office Location Verification",
                text: "For security, we need to verify you're at the office location. Mobile users should use GPS. Desktop users should use IP detection.",
                icon: "info",
                confirmButtonText: "OK"
            });
        }

        // Enhanced GPS location detection with retry logic
        function requestLocationWithRetry(retries = 3) {
            document.getElementById('get-location').innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Detecting...';
            document.getElementById('get-location').disabled = true;

            Swal.fire({
                title: 'Detecting GPS Location',
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
                        <p>Using device GPS for precise location...</p>
                        <p><small>Please allow location access when prompted</small></p>
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
                        // Success: got precise GPS coordinates
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;


                        ///////////////////////////////////////////////


                    alert("lat: "+lat+" lng: "+lng);


                    ///////////////////////////////////////////////
                        
                        // Store coordinates in session via AJAX
                        storeLocationInSession(lat, lng, 'gps').then(() => {
                            // Update form fields
                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                            document.getElementById('fallback_location').value = '';
                            
                            Swal.close();
                            showLocationSuccessUI(`GPS location verified! Accuracy: ${Math.round(accuracy)} meters. You can now login.`);
                            
                            // Re-enable location button
                            resetGPSButton();
                        });
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
                                            <p><small>Retrying GPS detection</small></p>
                                        </div>
                                    </div>
                                `;
                                attempt(attemptCount + 1);
                            }, 2000);
                        } else {
                            // All retries failed
                            Swal.close();
                            resetGPSButton();
                            
                            let errorMessage;
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage = "Location access denied. Please use IP-based detection instead.";
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = "GPS location unavailable. Please use IP-based detection.";
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = "GPS request timed out. Please use IP-based detection.";
                                    break;
                                default:
                                    errorMessage = "GPS detection failed. Please use IP-based detection.";
                                    break;
                            }
                            
                            showLocationError(errorMessage);
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            };

            attempt(1);
        }

        function resetGPSButton() {
            document.getElementById('get-location').innerHTML = '<i class="fas fa-satellite"></i> Use GPS Location (Mobile)';
            document.getElementById('get-location').disabled = false;
        }

        // Store location in session via AJAX
        function storeLocationInSession(lat, lng, method) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('action', 'store_location');
                formData.append('latitude', lat);
                formData.append('longitude', lng);
                formData.append('method', method);

                fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text().then(text => {
                    try {
                        const data = JSON.parse(text);
                        return data;
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        return {success: true};
                    }
                }))
                .then(data => {
                    if (data && data.success) {
                        resolve(data);
                    } else {
                        reject(new Error(data.error || 'Failed to store location'));
                    }
                })
                .catch(error => {
                    console.error('Location storage error:', error);
                    resolve({success: true});
                });
            });
        }

        // IP-based location detection using external service
        function useIPLocation() {
            document.getElementById('use-ip-location').innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Detecting...';
            document.getElementById('use-ip-location').disabled = true;

            Swal.fire({
                title: 'Detecting IP Location',
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
                        <p><small>Using network-based location detection</small></p>
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

            // Use free IP geolocation service
            fetch('https://ipapi.co/json/')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data && data.latitude && data.longitude) {
                        // Success! We got actual coordinates from IP
                        const lat = data.latitude;
                        const lng = data.longitude;
                        const city = data.city || 'Unknown';
                        const region = data.region || 'Unknown';
                        
                        // Update form fields - IP detection happens on server side
                        document.getElementById('latitude').value = 0;
                        document.getElementById('longitude').value = 0;
                        document.getElementById('fallback_location').value = 'ip';
                        
                        Swal.close();
                        showLocationSuccessUI(`IP location detected! You appear to be in ${city}, ${region}. Location will be verified on login.`);
                        
                    } else {
                        throw new Error('Could not determine location from IP');
                    }
                })
                .catch(error => {
                    console.error('IP geolocation failed:', error);
                    
                    // Fallback: Let server handle IP detection
                    document.getElementById('latitude').value = 0;
                    document.getElementById('longitude').value = 0;
                    document.getElementById('fallback_location').value = 'ip';
                    
                    Swal.close();
                    showLocationSuccessUI("IP location detection complete! Location will be verified on server.");
                })
                .finally(() => {
                    // Re-enable button
                    document.getElementById('use-ip-location').innerHTML = '<i class="fas fa-wifi"></i> Use IP Location';
                    document.getElementById('use-ip-location').disabled = false;
                });
        }

        // Show location success in UI
        function showLocationSuccessUI(message) {
            document.getElementById('location-buttons').style.display = 'none';
            document.getElementById('location-success').style.display = 'block';
            document.getElementById('location-status').style.display = 'block';
            document.getElementById('location-status').className = 'location-status location-success';
            document.getElementById('success-message').textContent = message;
            document.getElementById('location-status-text').textContent = message;
        }

        function showLocationError(message) {
            document.getElementById('location-error').style.display = 'block';
            document.getElementById('error-message-text').textContent = message;
        }

        // Add event listeners
        document.getElementById('get-location').addEventListener('click', requestLocationWithRetry);
        document.getElementById('use-ip-location').addEventListener('click', useIPLocation);

        // Form submission validation
        document.getElementById('login-form').addEventListener('submit', function(e) {
            const lat = document.getElementById('latitude').value;
            const lng = document.getElementById('longitude').value;
            const fallback = document.getElementById('fallback_location').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            // Check if credentials are filled
            if (!username || !password) {
                e.preventDefault();
                showCredentialsError("Please enter both username and password.");
                return;
            }
            
            // Check if location is verified for non-WFH users
            if ((!lat || !lng || lat == 0 || lng == 0) && !fallback && !<?php echo isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] ? 'true' : 'false'; ?>) {
                e.preventDefault();
                showLocationError("Please verify your location before logging in.");
                document.getElementById('location-message').style.display = 'block';
                return;
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