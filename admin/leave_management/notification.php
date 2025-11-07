<?php
require_once "../includes/config.php";

if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 10800) {
    // Session expired
    session_unset();
    session_destroy();
    header("Location: ../../login.php"); // Or redirect as needed
    // echo 1;
    exit;
}

$userid = isset($_SESSION['id'])?$_SESSION['id']:null;
if($userid == null){
    header("location: ../../login.php");
}

$error = '';
$output = '';

$click = isset($_POST['click'])?$_POST['click']:null;
$error = "error";
if($click == null){
    echo json_encode(["error"=>$error]);
}

if($click == "notification"){

    $notificcationstatus = "false from top";

    $time =( new DateTime("now", new DateTimeZone("Asia/Kolkata")))->format("m-d");

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE dob LIKE ?");
    $stmt->execute(["%$time%"]);
    $dob = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($dob){

        try{
            $pdo->beginTransaction();

            foreach($dob as $row){
            $stmt = $pdo->prepare("SELECT * FROM applications WHERE subject LIKE ? AND description LIKE ? AND employee_id = ? And DATE(created_at) = DATE(CURRENT_DATE)");
            $stmt->execute(["%BirthDay%", "%".$row['emp_id']."%", $row['emp_id']]);
            $fetchdob = $stmt->fetch(PDO::FETCH_ASSOC);

                if(empty($fetchdob)){
                    $stmt = $pdo->prepare("INSERT INTO applications (`employee_id`, `req_type`,`subject`, `description`, `start_date`) VALUES (?, ?, ?, ?, ?)");

                    if($stmt->execute([$row['emp_id'], "Birthday", "BirthDay", "Today Is ".$row['username']." -- ".$row['emp_id']."'s   Birthday", $time])){

                        $lastInsertId = $pdo->lastInsertId();
                    
                        $createnotification = $pdo->prepare("INSERT INTO notification (`App_id`, `created_by`, `notify_to`) VALUES (?,?,?)  ");

                        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
                    
                        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach($admins as $adrow){

                            if($createnotification->execute([$lastInsertId, "system", $adrow['emp_id']])){
                                $notificcationstatus = true;
                            }
                            else{
                                $notificcationstatus = "false from else";

                            }
                        }


                    }



                }


            }

            $pdo->commit();
            
        }
        catch(Exception $e){
            $pdo->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
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

        $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ?");
        $stmt->execute([$row['App_id']]);
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
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>".$row['req_type']." </div>
                    <div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
        ";
    }


    $response = [
        "output"=>$output,
        "count"=>$count,
        "status"=>$notificcationstatus,
        
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

        $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ?");
        $stmt->execute([$row['App_id']]);
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
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>".$row['req_type']." </div>
                    <div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
        ";
    }


    $response = [
        "output"=>$output,
        "count"=>$count
    ];

    echo json_encode($response);
     
}




if($click == "changeNoteStatus"){

    $appid = isset($_POST['appid'])?($_POST['appid']):null;


    $stmt = $pdo->prepare("SELECT a.*, e.username FROM applications a LEFT JOIN employees e on e.emp_id = a.employee_id WHERE a.id = ?");
    $stmt->execute([$appid]);
    $noti = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
    $stmt->execute([$userid, "pending"]);
    $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = COUNT($countnoti);
  
    $output = "";
    
    $creby = "";
    
    if($noti['req_type'] == "Birthday"){
            $creby = "System";
    }
    else{
        $creby = $noti['username'];
    }

        $output .= "
        
            <div class=\"detail-field\">
                <span class=\"detail-label\">Application ID</span>
                <div class=\"detail-value\">".$noti['id']."</div>
            </div>
            
            <div class=\"detail-field\">  
                <span class=\"detail-label\">Created By</span>
                <div class=\"detail-value\">".$creby."</div>
            </div>

            <div class=\"detail-field\">  
                <span class=\"detail-label\">Subject</span>
                <div class=\"detail-value\">".$noti['subject']."</div>
            </div>
            <div class=\"detail-field\">  
                <span class=\"detail-label\">Description</span>
                <div class=\"detail-value\">".$noti['description']."</div>
            </div>
            
            
            <div class=\"detail-field\">
                <span class=\"detail-label\">Status</span>
                <div class=\"detail-value\">
                    <span class=\"status-badge status-".$noti['status']." \">".$noti['status']."</span>
                </div>
            </div>
            <div class=\"detail-field\">
                <span class=\"detail-label\">Created At</span>
                <div class=\"detail-value\">".$noti['created_at']."</div>
            </div>
        ";
    


    $response = [
        "output"=>$output,
        "count"=>$count,
        "appid"=>$appid
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

        $stmt = $pdo->prepare("SELECT * FROM notification WHERE App_id = ?");
        $stmt->execute([$row['App_id']]);
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
                <div class=\"notification-content \">
                    <div class=\"notification-title\" style='margin: 30px;' data-appId='".$row['App_id']."'>".$row['req_type']." </div>
                    <div class=\"notification-desc\" style='margin-left: 30px;'> ".$row['subject']." --- <p style='display:inline;color:".$color.";'>".$row['status']."</p></div>
                    <div class=\"notification-time\" style='margin: 30px;'>".$time."</div>
                    <div class=\"notification-time deletenoty \" style='cursor:pointer;font-weight: 600; color:Red;background-color: rgba(251, 9, 9, 0.26);  padding: 10px; border-radius: 10px;' data-appid='".$row['App_id']."'>Delete</div>
                </div>
            </div>
        ";
    }


    $response = [
        "output"=>$output,
        "count"=>$count
    ];

    echo json_encode($response);
     
}


if($click == "markAsReadBtn"){

    $appid = isset($_POST['appid'])?intval($_POST['appid']):null;


    $stmt = $pdo->prepare("UPDATE notification SET status = ? WHERE App_id = ? AND notify_to = ?");
    
    

    if(!$stmt->execute(["checked", $appid, $userid])){
        echo json_encode(["error"=> "Status Not Changed!"]);
        exit;
    }
    else{
            $stmt = $pdo->prepare("SELECT * FROM notification WHERE notify_to = ? AND status = ?");
            $stmt->execute([$userid, "pending"]);
            $countnoti = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = COUNT($countnoti);

        echo json_encode(["success"=> "success", "count"=>$count]);
    }


}




?>