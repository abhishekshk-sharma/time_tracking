<?php

require_once "../includes/config.php";



$userid = isset($_SESSION['id'])?$_SESSION['id']:null;
if($userid == null){
    header("location: ../login.php");
    exit;
}

    $info = isset($_POST['info'])?$_POST['info']:'';


if($info == "checkReq"){
    $id = isset($_POST['id'])?($_POST['id']): null;
    $output = "";
    
    $stmt = $pdo->prepare("SELECT * FROM `applications` WHERE employee_id = ? AND req_type NOT IN ('Birthday') AND MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE) ORDER BY id DESC");
    $stmt->execute([$id]);
    $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

   if(count($fetch) >= 1){
            

            $output = "";
            foreach($fetch as $row){
                $create = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));           
                $time = $create->format("Y-m-d H:i:s");

                $start = new DateTime($row['start_date'], new DateTimeZone("asia/kolkata"));
                $end = ($row['end_date'] != null)?(new DateTime($row['end_date'], new DateTimeZone("asia/kolkata"))): null;
                $output .= "<tr>
                                <td>".$row['created_at']."</td>
                                <td>".$row['req_type']."</td>
                                <td>".$row['subject']."</td>
                                <td>".$start->format("M d")." - ".(($end != null)?($end->format("M d")):"")."</td>
                                <td><span class='status-badge status-".$row['status']."'>".$row['status']."</span></td>
                                <td>
                                    <button class='action-btn view-btn view_request' data-id='".$row['employee_id']."' data-type='".$row['req_type']."' data-time='".$time."''>
                                        <i class='fas fa-eye'></i> View
                                    </button>
                                </td>
                            </tr>";
            }

            echo $output;
            exit;
        }
        else{
            echo "<p style='color:red;'>Not Found!</p>";
        }

}



if($info == "casualLeave"){
    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $end_date = isset($_POST['end_date'])?new DateTime($_POST['end_date'], new DateTimeZone("asia/kolkata")): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
    $rcl = isset($_POST['rcl'])?intval($_POST['rcl']): null;
  
    $checkavailableLeave = 0;
    
    for($date = clone $start_date; $date <= $end_date; $date->modify("+1 day")){
        $checkavailableLeave += 1;

    }

    if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
    // Reject the request
    die("Start and end dates are required.");
    }

    if ($start_date > $end_date) {
    die("Start date must be before end date.");
    }

    if($rcl < $checkavailableLeave){
        die( "Can't apply more then remaining days! ".$rcl);
        
    }


    ///////////////////////////////////////////////////////////////////////////////////////

    $time = ($start_date)->format('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
    $stmt->execute([$userid, "$time%"]);
    $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: Check if punchOut is pending
    
    if ($firstEntry && isset($firstEntry['entry_type'])) {
        if (
            $firstEntry['entry_type'] == "regularization" ||
            $firstEntry['entry_type'] == "casual_leave" || // ← typo here!
            $firstEntry['entry_type'] == "sick_leave" ||
            $firstEntry['entry_type'] == "holiday"
        ) {
            die("Can't Apply on already declared(Casual Leave, Sick Leave, Holiday, or Regularization).");
        }
    }
    else if ($firstEntry && $firstEntry['entry_type'] === 'punch_in') {
    
        die("Already Punched In.");
        
    }
    //////////////////////////////////////////////////////////////////////




    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";

    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            
           die("Invalid file type.");
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            die("File too large.");
            
           
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
           
            die("Error uploading image.");
        }
    }


    
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `end_date`, `file`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $end_date->format("Y-m-d H:i:s"), $filePath])){

        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }

    }
    else{
        echo "Not uploaded";
        exit;
    }

}





if($info == "sick_leave"){


    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $end_date = isset($_POST['end_date'])?new DateTime($_POST['end_date'], new DateTimeZone("asia/kolkata")): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
    $rsl = isset($_POST['rsl'])?intval($_POST['rsl']): null;
  
    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";


    $checkavailableLeave = 0;
    
    for($date = clone $start_date; $date <= $end_date; $date->modify("+1 day")){
        $checkavailableLeave += 1;

    }



    //////////////////////////////////////////////////




    if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
    // Reject the request
    die("Start and end dates are required.");
    }

    if ($start_date > $end_date) {
    die("Start date must be before end date.");
    }

    if($rsl < $checkavailableLeave){
        die( "Can't apply more then remaining days! ".$rsl);
        
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    $time = ($start_date)->format('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
    $stmt->execute([$userid, "$time%"]);
    $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: Check if punchOut is pending
    
    if ($firstEntry && isset($firstEntry['entry_type'])) {
        if (
            $firstEntry['entry_type'] == "regularization" ||
            $firstEntry['entry_type'] == "casual_leave" || // ← typo here!
            $firstEntry['entry_type'] == "sick_leave" ||
            $firstEntry['entry_type'] == "holiday"
        ) {
            die("Can't Apply on already declared(Casual Leave, Sick Leave, Holiday, or Regularization).");
        }
    }
    else if ($firstEntry && $firstEntry['entry_type'] === 'punch_in') {
    
        die("Already Punched In.");
        
    }
    //////////////////////////////////////////////////////////////////////



    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            echo "File too large.";
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
            echo "Error uploading image.";
            exit;
        }
    }
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `end_date`, `file`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $end_date->format("Y-m-d H:i:s"), $filePath])){
        
        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }
    }
    else{
        echo "Not uploaded";
        exit;
    }

}


if($info == "work_from_home"){


    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $end_date = isset($_POST['end_date'])?new DateTime($_POST['end_date'], new DateTimeZone("asia/kolkata")): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
    // $rsl = isset($_POST['rsl'])?intval($_POST['rsl']): null;
  
    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";


    // $checkavailableLeave = 0;
    
    // for($date = clone $start_date; $date <= $end_date; $date->modify("+1 day")){
    //     $checkavailableLeave += 1;

    // }



    //////////////////////////////////////////////////




    if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
    // Reject the request
    die("Start and end dates are required.");
    }

    if ($start_date > $end_date) {
    die("Start date must be before end date.");
    }

    // if($rsl < $checkavailableLeave){
    //     die( "Can't apply more then remaining days! ".$rsl);
        
    // }

    ///////////////////////////////////////////////////////////////////////////////////////

    $time = ($start_date)->format('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
    $stmt->execute([$userid, "$time%"]);
    $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: Check if punchOut is pending
    
    if ($firstEntry && isset($firstEntry['entry_type'])) {
        if (
            $firstEntry['entry_type'] == "regularization" ||
            $firstEntry['entry_type'] == "casual_leave" || // ← typo here!
            $firstEntry['entry_type'] == "sick_leave" ||
            $firstEntry['entry_type'] == "holiday"
        ) {
            die("Can't Apply on already declared(Casual Leave, Sick Leave, Holiday, or Regularization).");
        }
    }
    else if ($firstEntry && $firstEntry['entry_type'] === 'punch_in') {
    
        die("Already Punched In.");
        
    }
    //////////////////////////////////////////////////////////////////////



    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            echo "File too large.";
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
            echo "Error uploading image.";
            exit;
        }
    }
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `end_date`, `file`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $end_date->format("Y-m-d H:i:s"), $filePath])){
        
        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }
    }
    else{
        echo "Not uploaded";
        exit;
    }

}





if($info == "half_day"){
    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $half_D_type = isset($_POST['half_D_type'])?($_POST['half_D_type']): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
  
    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";



    ///////////////////////////////////////////////////////////////////////////////////////

    $time = ($start_date)->format('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
    $stmt->execute([$userid, "$time%"]);
    $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: Check if punchOut is pending
    
    if ($firstEntry && isset($firstEntry['entry_type'])) {
        if (
            $firstEntry['entry_type'] == "regularization" ||
            $firstEntry['entry_type'] == "casual_leave" || // ← typo here!
            $firstEntry['entry_type'] == "sick_leave" ||
            $firstEntry['entry_type'] == "holiday"
        ) {
            die("Can't Apply on already declared(Casual Leave, Sick Leave, Holiday, or Regularization).");
        }
    }

    //////////////////////////////////////////////////////////////////////




    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            echo "File too large.";
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
            echo "Error uploading image.";
            exit;
        }
    }
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `half_day`, `file`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $half_D_type, $filePath])){
        
        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }
    }
    else{
        echo "Not uploaded";
        exit;
    }

}




if($info == "complaint" || $info == "other"){
    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
  
    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";

    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            echo "File too large.";
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
            echo "Error uploading image.";
            exit;
        }
    }
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `file`) VALUES (?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $filePath])){
        
        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }
    }
    else{
        echo "Not uploaded";
        exit;
    }

}



if($info == "regularization"){
    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $end_date = isset($_POST['end_date'])?new DateTime($_POST['end_date'], new DateTimeZone("asia/kolkata")): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
    $rcl = isset($_POST['rcl'])?intval($_POST['rcl']): null;
  
    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";




    if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
    // Reject the request
    die("Start and end dates are required.");
    }

    if ($start_date > $end_date) {
    die("Start date must be before end date.");
    }



    ///////////////////////////////////////////////////////////////////////////////////////

    $time = ($start_date)->format('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
    $stmt->execute([$userid, "$time%"]);
    $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: Check if punchOut is pending
    
    if ($firstEntry && isset($firstEntry['entry_type'])) {
        if (
            $firstEntry['entry_type'] == "regularization" ||
            $firstEntry['entry_type'] == "casual_leave" || // ← typo here!
            $firstEntry['entry_type'] == "sick_leave" ||
            $firstEntry['entry_type'] == "holiday"
        ) {
            die("Can't Apply on already declared(Casual Leave, Sick Leave, Holiday, or Regularization).");
        }
    }
    else if ($firstEntry && $firstEntry['entry_type'] === 'punch_in') {
    
        die("Already Punched In.");
        
    }
    //////////////////////////////////////////////////////////////////////





    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            echo "File too large.";
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
            echo "Error uploading image.";
            exit;
        }
    }


    
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `end_date`, `file`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $end_date->format("Y-m-d H:i:s"), $filePath])){
        
        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }
    }
    else{
        echo "Not uploaded";
        exit;
    }

}


if($info == "punch_Out_regularization"){
    $id = isset($_POST['id'])?($_POST['id']): null;
    $type = isset($_POST['type'])?$_POST['type']: "";
    $start_date = isset($_POST['start_date'])?new DateTime($_POST['start_date'], new DateTimeZone("asia/kolkata")): "";
    $end_date = isset($_POST['end_date'])?new DateTime($_POST['end_date'], new DateTimeZone("asia/kolkata")): "";
    $subject = isset($_POST['subject'])?$_POST['subject']: "";
    $description = isset($_POST['description'])?$_POST['description']: "";
    $rcl = isset($_POST['rcl'])?intval($_POST['rcl']): null;
  
    $fileName = isset($_FILES['image']['name'])?basename($_FILES['image']['name']): "";
    $uploadDir = "../UPLOAD/";
    $targetPath = $uploadDir . $fileName;
    $filePath = "";


    $outTime = ($start_date->format('Y-m-d'))." ".$end_date->format('H:i');

    if (empty($_POST['start_date']) || empty($_POST['end_date'])) {
    // Reject the request
    die("Start and end dates are required.");
    }

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time like ? AND entry_type = 'punch_out' ORDER BY entry_time DESC LIMIT 1 ");
    $stmt->execute([$id, "%".$start_date->format('Y-m-d')."%"]);
    $checkentryfortheday = $stmt->fetch(PDO::FETCH_ASSOC);

    if($checkentryfortheday){
        die("Select The Date Witout Punch Out");
        
    }



    ///////////////////////////////////////////////////////////////////////////////////////

    $time = ($start_date)->format('Y-m-d');

    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
    $stmt->execute([$userid, "$time%"]);
    $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Step 2: Check if punchOut is pending
    
    if ($firstEntry && isset($firstEntry['entry_type'])) {
        if (
            $firstEntry['entry_type'] == "regularization" ||
            $firstEntry['entry_type'] == "casual_leave" || // ← typo here!
            $firstEntry['entry_type'] == "sick_leave" ||
            $firstEntry['entry_type'] == "holiday"
        ) {
            die("Can't Apply on already declared(Casual Leave, Sick Leave, Holiday, or Regularization).");
        }
    }
    else if ($firstEntry && $firstEntry['entry_type'] === 'punch_in') {
    
        die("Already Punched In.");
        
    }
    //////////////////////////////////////////////////////////////////////





    if($fileName != ""){
        // Optional: Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            echo "Invalid file type.";
            exit;
        }

        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            echo "File too large.";
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $filePath = 'UPLOAD/' . basename($_FILES['image']['name']);

        } else {
            echo "Error uploading image.";
            exit;
        }
    }


    
  

    $stmt = $pdo->prepare("INSERT INTO `applications`(`employee_id`, `req_type`, `subject`, `description`, `start_date`, `end_date`, `file`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    

    if($stmt->execute([$id, $type, $subject, $description, $start_date->format("Y-m-d H:i:s"), $outTime, $filePath])){
        
        $lastId = $pdo->lastInsertId();
        $noterror = true;

        $stmt = $pdo->query("SELECT * FROM employees WHERE role = 'admin'");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($fetch as $row){

            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
            
    
            if($stmt2->execute([$lastId, $id, $row['emp_id']])){
                
                $noterror = true;
            
    
            }
            else{
                $noterror = false;
                
            }
        }

        if($noterror){
            echo 100;
            exit;
        }
    }
    else{
        echo "Not uploaded";
        exit;
    }

}




if($info == "req_modal"){

    $id = isset($_POST['id'])?($_POST['id']):null;
    
    if($id !== null){
        $type = isset($_POST['type'])?$_POST['type']:"";
        $time = isset($_POST['time'])?new DateTime($_POST['time'], new DateTimeZone("asia/kolkata")):"";
        $time = $time->format("Y-m-d H:i:s");
        $stmt = $pdo->prepare("SELECT a.*, b.username as name FROM applications a LEFT JOIN employees b ON b.emp_id = a.employee_id WHERE employee_id = ? AND req_type = ? AND a.created_at = ?");
        $stmt->execute([$id, $type, $time]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = [
            "id"=>$result['id'],
            "emp_id"=> $result['employee_id'],
            "name"=> $result['name'],
            "req_type"=> $result['req_type'],
            "subject"=>$result['subject'],
            "description"=>$result['description'],
            "halfday"=>isset($result['half_day'])?$result['half_day']:"-",
            "startdate"=>isset($result['start_date'])?$result['start_date']:"-",
            "enddate"=>isset($result['end_date'])?$result['end_date']:"-",
            "file"=>isset($result['file'])?$result['file']:"-",
            "status"=>isset($result['status'])?$result['status']:"-",
            "createdat"=>isset($result['created_at'])?(new DateTime($result['created_at'], new DateTimeZone("asia/kolkata")))->format("Y-m-d H:i:s"): "-"
        ];

    
        if($result['req_type'] === "punch_Out_regularization"){
            $response['enddate'] = (isset($result['end_date'])?(new DateTime($result['end_date'], new DateTimeZone("Asia/Kolkata")))->format("H:i"):"-");
        }
        
        echo $send = json_encode($response);
        
        exit;
    }
}


if($info == "leaveDayCheck"){
 
    $stmt = $pdo->query("SELECT * FROM system_settings");
    $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($sys as $row){
        if($row['setting_key'] == "sick_leave"){
            $sickLeave = $row['setting_value'];
        }
        if($row['setting_key'] == "casual_leave"){
            $casualLeave = $row['setting_value'];
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM leavecount WHERE employee_id = ?");
    $stmt->execute([$userid]);

    $get = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        "tsl"=> "<lable style='color:green;font-size: 20px;'>$sickLeave</lable>",
        "rsl"=> "<lable style='color:green;font-size: 20px;'>".$get['sick_leave']."</lable>",
        "tcl"=> "<lable style='color:orange;font-size: 20px;'>$casualLeave</lable>",
        "rcl"=> "<lable style='color:orange;font-size: 20px;'>".$get['casual_leave']."</lable>",
        "valrsl"=> $get['casual_leave'],
        "valrcl"=>$get['sick_leave']
    ];

    echo $send = json_encode($response);

}

?>