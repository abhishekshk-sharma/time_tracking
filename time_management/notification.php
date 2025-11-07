<?php
require_once "../includes/config.php";


$userid = isset($_SESSION['id'])?$_SESSION['id']:null;
if($userid == null){
    header("location: ../login.php");
}

$error = '';
$output = '';

$click = isset($_POST['click'])?$_POST['click']:null;
$error = "error";
if($click == null){
    echo $error;
}

if($click == "notification"){

    $stmt = $pdo->prepare("SELECT n.*, a.* FROM notification n LEFT JOIN applications a ON n.App_id = a.id WHERE notify_to = ? ORDER BY a.id DESC" );
    $stmt->execute([$userid]);
    $noti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
    $stmt->execute([$userid, "pending"]);
    $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($countnoti);
    $output = "";
    
    foreach($noti as $row){

        $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ? AND notify_to = ?");
        $stmt->execute([$row['App_id'], $userid]);
        $checkread = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $time = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));
        $time = $time->format("Y-m-d");

        $class = "";
        if($row['status'] == "approved"){
            $color = "green";
     
        }
        else{
            $color = "red";
           
        }
        
        if(isset($checkread['status']) && $checkread['status'] == "checked"){
            $class = " ";
     
        }
        else{
            
            $class = "notification-item unread";    
        }


        $output .= "
        
            <div class='notification-content-head-div $class' data-appid='".$row['App_id']."'>
                ";

            if($row['req_type'] == "Birthday"){

                $output .= "
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>🎂 Happy Birthday! 🎉 </div>
                <div class=\"notification-desc\" style='margin-left: 30px; font-weight: 700;'> 
                

                On this special day, may you feel the quiet joy of being deeply appreciated, the strength of every challenge you've overcome, and the light of every moment you've made brighter for others. You are not just growing older—you’re growing wiser, kinder, and more extraordinary with each passing year.

                May your heart be full, your dreams feel closer, and your laughter echo louder than ever. Here's to the journey ahead—one filled with purpose, peace, and people who truly see you.

                <p style='color:red; font-weight: 700;'>You matter. You inspire. You belong.</p>
                

                💫✨🌸
                ".$row['subject']."! </p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
            else if($row['req_type'] !== "Birthday"){

                $output .= "<div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
    }


    $response = [
        "output"=>$output,
        "count"=>$count
    ];

    echo json_encode($response);
     
}




if($click == "markAllRead"){

    $stmt = $pdo->prepare("UPDATE notification SET status = ? WHERE notify_to = ?");
    
    

    if(!$stmt->execute(["checked", $userid])){
        echo json_encode(["error"=> "Status Not Changed!"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT n.*, a.* FROM notification n LEFT JOIN applications a ON n.App_id = a.id WHERE notify_to = ?");
    $stmt->execute([$userid]);
    $noti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
    $stmt->execute([$userid, "pending"]);
    $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($countnoti);
    $output = "";
    
    foreach($noti as $row){

        $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ? AND notify_to = ?");
        $stmt->execute([$row['App_id'], $userid]);
        $checkread = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $time = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));
        $time = $time->format("Y-m-d");

        $class = "";
        if($row['status'] == "approved"){
            $color = "green";
     
        }
        else{
            $color = "red";
           
        }
        
        if(isset($checkread['status']) && $checkread['status'] == "checked"){
            $class = " ";
     
        }
        else{
            
            $class = "notification-item unread";    
        }


        $output .= "
        
            <div class='notification-content-head-div $class' data-appid='".$row['App_id']."'>
                ";

            if($row['req_type'] == "Birthday"){

                $output .= "
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>🎂 Happy Birthday! 🎉 </div>
                <div class=\"notification-desc\" style='margin-left: 30px; font-weight: 700;'> 
                

                On this special day, may you feel the quiet joy of being deeply appreciated, the strength of every challenge you've overcome, and the light of every moment you've made brighter for others. You are not just growing older—you’re growing wiser, kinder, and more extraordinary with each passing year.

                May your heart be full, your dreams feel closer, and your laughter echo louder than ever. Here's to the journey ahead—one filled with purpose, peace, and people who truly see you.

                <p style='color:red; font-weight: 700;'>You matter. You inspire. You belong.</p>
                

                💫✨🌸
                ".$row['subject']."! </p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
            else if($row['req_type'] !== "Birthday"){

                $output .= "<div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
    }


    $response = [
        "output"=>$output,
        "count"=>$count
    ];

    echo json_encode($response);
     
}




if($click == "changeNoteStatus"){

    $appid = isset($_POST['appid'])?intval($_POST['appid']):null;

    $stmt = $pdo->prepare("UPDATE notification SET status = ? WHERE App_id = ?");
    
    

    if(!$stmt->execute(["checked", $appid])){
        echo json_encode(["error"=> "Status Not Changed! $appid"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT n.*, a.* FROM notification n LEFT JOIN applications a ON n.App_id = a.id WHERE notify_to = ? ORDER BY a.id DESC");
    $stmt->execute([$userid]);
    $noti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
    $stmt->execute([$userid, "pending"]);
    $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($countnoti);
    $output = "";
    
    foreach($noti as $row){

       $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ? AND notify_to = ?");
        $stmt->execute([$row['App_id'], $userid]);
        $checkread = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $time = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));
        $time = $time->format("Y-m-d");

        $class = "";
        if($row['status'] == "approved"){
            $color = "green";
     
        }
        else{
            $color = "red";
           
        }
        
        if(isset($checkread['status']) && $checkread['status'] == "checked"){
            $class = " ";
     
        }
        else{
            
            $class = "notification-item unread";    
        }


        $output .= "
        
            <div class='notification-content-head-div $class' data-appid='".$row['App_id']."'>
                ";

            if($row['req_type'] == "Birthday"){

                $output .= "
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>🎂 Happy Birthday! 🎉 </div>
                <div class=\"notification-desc\" style='margin-left: 30px; font-weight: 700;'> 
                

                On this special day, may you feel the quiet joy of being deeply appreciated, the strength of every challenge you've overcome, and the light of every moment you've made brighter for others. You are not just growing older—you’re growing wiser, kinder, and more extraordinary with each passing year.

                May your heart be full, your dreams feel closer, and your laughter echo louder than ever. Here's to the journey ahead—one filled with purpose, peace, and people who truly see you.

                <p style='color:red; font-weight: 700;'>You matter. You inspire. You belong.</p>
                

                💫✨🌸
                ".$row['subject']."! </p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
            else if($row['req_type'] !== "Birthday"){

                $output .= "<div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
    }


    $response = [
        "output"=>$output,
        "count"=>$count
    ];

    echo json_encode($response);
     
}


if($click == "deletenoty"){

    $appid = isset($_POST['appid'])?intval($_POST['appid']):null;

    $stmt = $pdo->prepare("DELETE FROM notification WHERE App_id = ? AND notify_to = ? ");
 

    if(!$stmt->execute([$appid, $userid])){
        echo json_encode(["error"=> "Status Not Changed! $appid"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT n.*, a.* FROM notification n LEFT JOIN applications a ON n.App_id = a.id WHERE notify_to = ?");
    $stmt->execute([$userid]);
    $noti = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
    $stmt->execute([$userid, "pending"]);
    $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($countnoti);
    $output = "";
    
    foreach($noti as $row){

        $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ? AND notify_to = ?");
        $stmt->execute([$row['App_id'], $userid]);
        $checkread = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $time = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));
        $time = $time->format("Y-m-d");

        $class = "";
        if($row['status'] == "approved"){
            $color = "green";
     
        }
        else{
            $color = "red";
           
        }
        
        if(isset($checkread['status']) && $checkread['status'] == "checked"){
            $class = " ";
     
        }
        else{
            
            $class = "notification-item unread";    
        }


        $output .= "
        
            <div class='notification-content-head-div $class' data-appid='".$row['App_id']."'>
                ";

            if($row['req_type'] == "Birthday"){

                $output .= "
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>🎂 Happy Birthday! 🎉 </div>
                <div class=\"notification-desc\" style='margin-left: 30px; font-weight: 700;'> 
                

                On this special day, may you feel the quiet joy of being deeply appreciated, the strength of every challenge you've overcome, and the light of every moment you've made brighter for others. You are not just growing older—you’re growing wiser, kinder, and more extraordinary with each passing year.

                May your heart be full, your dreams feel closer, and your laughter echo louder than ever. Here's to the journey ahead—one filled with purpose, peace, and people who truly see you.

                <p style='color:red; font-weight: 700;'>You matter. You inspire. You belong.</p>
                

                💫✨🌸
                ".$row['subject']."! </p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
            else if($row['req_type'] !== "Birthday"){

                $output .= "<div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
            ";
            }
    }


    $response = [
        "output"=>$output,
        "count"=>$count
    ];

    echo json_encode($response);
     
}




?>