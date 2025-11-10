<?php

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
    $stmt->execute([$userID, "pending"]);
    $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ?");
    $stmt->execute([$userID]);
    $countnoti1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $countnoty = COUNT($countnoti);
    $count = COUNT($countnoti1);
    
    $color = "";
    
    if($countnoty <= 0){
        $color = "white";
        $_SESSION['notification_session_count'] = 0;
    }
    else if($countnoty > 0){
        $color = "red";
        $_SESSION['notification_session_count'] = $countnoty;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrack - Employee Time Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-clock"></i>
                    <h1>ST ZK Digital Media</h1>
                </div>
                <div class="user-info">
                    <!-- <img src="https://ui-avatars.com/api/?name=John+Doe&background=0D8ABC&color=fff" alt="User"> -->
                    <span><?php echo $details['full_name']; ?></span>

                    <div class="notification-bell notybell" id="notificationBell">
                        <i class="fas fa-bell notybell" style="color: <?php echo "$color"; ?>;"></i>
                        <span class="notification-badge" id="notificationBadge" ><?php echo $countnoty;  ?></span>
                        <audio id="notifSound" src="noti-sound/notificationsound.mp3" preload="auto"></audio>
                    </div>
                </div>
            </div>
        </div>
    </header>