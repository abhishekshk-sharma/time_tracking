
<?php
    require_once "config.php";

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
    }
    else if($countnoty > 0){
        $color = "red";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ST ZK DM Solutions - Employee Time Tracking</title>
    <link rel="icon" type="image/x-icon" href="includes/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
    
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="includes/logo.png" alt="" width="80" height="50" >
                    <!--<h1 style="margin-left: 10px;">ST ZK Digital Media</h1>-->
                </div>
                <div class="user-info">
                    <span style="cursor: pointer;" onclick="window.location.href='profile.php'"><?php echo $details['full_name'] ?></span>
                    <div class="notification-bell" id="notificationBell">
                        <i class="fas fa-bell notybell" style="color: <?php echo "$color"; ?>;"></i>
                        <span class="notification-badge" id="notificationBadge"><?php echo $countnoty; ?></span>
                    </div>
                    <!-- <img src="https://ui-avatars.com/api/?name=John+Doe&background=0D8ABC&color=fff" alt="User"> -->
                </div>
            </div>
        </div>
    </header>


