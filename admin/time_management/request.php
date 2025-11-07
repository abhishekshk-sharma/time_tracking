<?php
require_once "../includes/config.php";


    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: ../login.php");
    }


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

$info = isset($_POST['info'])?$_POST['info']: "";




if($info == "getRequests"){

    $pages = isset($_POST['pages'])?intval($_POST['pages']): 1;
    $limit = isset($_POST['limit'])?intval($_POST['limit']): 10;
    $statusFilter = isset($_POST['statusFilter'])?$_POST['statusFilter']: "";
    $typeFilter = isset($_POST['typeFilter'])?$_POST['typeFilter']: "";
    $dateFilter = isset($_POST['dateFilter'])?$_POST['dateFilter']: "";
    $searchInput = isset($_POST['searchInput'])?$_POST['searchInput']: null;

    $offset = ($pages - 1) * $limit;


    $conditions = [];
    $params = [];
    $countlimit = 0;

    if($statusFilter !== ""){
        $conditions[] = "a.status = ?";
        $params[] = $statusFilter;
    }
    if($typeFilter !== ""){
        $conditions[] = "req_type = ?";
        $params[] = $typeFilter;
    }
    if($dateFilter !== ""){

        if($dateFilter == "week"){
            
            $conditions[] = "WEEK(a.created_at) = WEEK(CURRENT_DATE)";
            
        }
        else if($dateFilter == "month"){
            
            $conditions[] = "MONTH(a.created_at) = MONTH(CURRENT_DATE)";
        }
        else if($dateFilter == "quarter"){
            $conditions[] = "QUARTER(a.created_at) = QUARTER(CURRENT_DATE)";

        }
    }
    $index = false;
    if($searchInput !== null){
        $conditions[] = "employee_id LIKE ?";
       

        $params[] = "%$searchInput%";
    }

    if(COUNT($conditions) < 1){
        
        
        $stmt = $pdo->query("SELECT a.*, e.emp_id AS EmpId FROM applications a LEFT JOIN employees e ON e.emp_id = a.employee_id  WHERE YEAR(a.created_at) = YEAR(CURRENT_DATE) ORDER BY a.id desc LIMIT $limit OFFSET $offset");
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $stmt = $pdo->query("SELECT * FROM applications WHERE YEAR(created_at) = YEAR(CURRENT_DATE) ORDER BY id");
        $forcount = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countlimit = COUNT($forcount);
    }
    else{

        
        $finalCondition = implode(" OR ", $conditions);
        $stmt = $pdo->prepare("SELECT a.*, e.emp_id AS EmpId FROM applications a LEFT JOIN employees e ON e.emp_id = a.employee_id  WHERE $finalCondition AND YEAR(a.created_at) = YEAR(CURRENT_DATE) ORDER BY id desc LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT a.* FROM applications a WHERE $finalCondition AND YEAR(a.created_at) = YEAR(CURRENT_DATE) ORDER BY a.id ");
        $stmt->execute($params);
        $forcount = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countlimit = COUNT($forcount);
    }

    if(COUNT($fetch) > 0){

        $output = "";

        foreach($fetch as $row){
            $create = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));
            $create1 = $create->format("d M, Y");
            $time = $create->format("Y-m-d H:i:s");

            $start = new DateTime($row['start_date'], new DateTimeZone("asia/kolkata"));
            $start = $start->format("d M");
            $end = isset($row['end_date'])? new DateTime($row['end_date'], new DateTimeZone("asia/kolkata")): "";
            if($end != ""){
                $end = $end->format("d M, Y");
            }

            $stmt = $pdo->prepare("SELECT username FROM employees WHERE emp_id = ?");
            $stmt->execute([$row['EmpId']]);
            $name = $stmt->fetch(PDO::FETCH_ASSOC);

            $output .= "<tr>
                            
                            <td>".$name['username']." (".$row['EmpId'].")</td>
                            <td>".$row['req_type']."</td>
                            <td>".$row['subject']."</td>
                            <td>".$start." - ".$end ."</td>
                            <td>".$create1 ."</td>
                            <td><span class='status-badge status-".$row['status']."'>".$row['status']."</span></td>
                            <td>
                                <button class='action-btn view-btn view_request' data-id='".$row['employee_id']."' data-type='".$row['req_type']."' data-time='".$time."'>
                                    <i class='fas fa-eye'></i> View
                                </button>
                            </td>
                        </tr>";
        }

        $response = [
            "output"=>$output,
            "row"=>$countlimit
        ];
        echo json_encode($response);
        exit;
    }
    else{

        $response = [
            "error"=>"<h3 style='color:red;'>No Requests!</h3>"
            
        ];
        echo json_encode($response);
        
    }
}


if($info == "req_modal"){

    $id = isset($_POST['id'])?($_POST['id']):null;
    
    if($id !== null){
        $type = isset($_POST['type'])?$_POST['type']:"";
        $time = isset($_POST['time'])?new DateTime($_POST['time'], new DateTimeZone("asia/kolkata")):"";
        $time = $time->format("Y-m-d H:i:s");
        $stmt = $pdo->prepare("SELECT a.*, b.username as name, b.emp_id as EmpId FROM applications a LEFT JOIN employees b ON b.emp_id = a.employee_id WHERE a.employee_id = ? AND req_type = ? AND a.created_at = ?");
        $stmt->execute([$id, $type, $time]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = [
            "id"=>$result['id'],
            "emp_id"=> $result['EmpId'],
            "name"=> $result['name'],
            "req_type"=> $result['req_type'],
            "subject"=>$result['subject'],
            "description"=>$result['description'],
            "halfday"=>isset($result['half_day'])?$result['half_day']:"-",
            "startdate"=>isset($result['start_date'])?$result['start_date']:"-",
            "enddate"=>isset($result['end_date'])?$result['end_date']:"-",
            "file"=>isset($result['file'])?$result['file']:"-",
            "status"=>isset($result['status'])?$result['status']:"-",
            "actionby"=>isset($result['action_by'])?$result['action_by']:"",
            "createdat"=>isset($result['created_at'])?(new DateTime($result['created_at'], new DateTimeZone("asia/kolkata")))->format("Y-m-d H:i:s"): "-"
        ];
        
        if($result['req_type'] === "punch_Out_regularization"){
            $response['enddate'] = (isset($result['end_date'])?(new DateTime($result['end_date'], new DateTimeZone("Asia/Kolkata")))->format("H:i"):"-");
        }
        
        echo $send = json_encode($response);
        
        exit;
    }
}

if($info == "reject"){
    $appId = isset($_POST['val'])?intval($_POST['val']): null;
    $status = isset($_POST['status'])?$_POST['status']: "";

    $user_id = isset($_POST['user_id'])?($_POST['user_id']): null;
    $created_by = isset($_POST['created_by'])?($_POST['created_by']): null;
    $app_id = isset($_POST['app_id'])?intval($_POST['app_id']): null;
   

    if($appId !==null){
        
        $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`, `created_by`, `notify_to`) VALUES (?, ?, ?)");
        

        if($stmt2->execute([$app_id,$created_by, $user_id])){

            $stmt = $pdo->prepare("UPDATE applications SET status = ?, action_by = ? WHERE id = ?");
            if($stmt->execute([$status, $userID, $appId])){
                echo 0;
                exit;
            }
            else{
                echo 1;
            }
        }

    }
}

if($info == "approve"){
    $appId = isset($_POST['val'])?intval($_POST['val']): null;
    $status = isset($_POST['status'])?$_POST['status']: "";


    $user_id = ($_POST['user_id'])?($_POST['user_id']): null;
    $created_by = ($_POST['created_by'])?($_POST['created_by']): null;

    $date_range = isset($_POST['date_range'])?$_POST['date_range']: "";
    $detail_type = isset($_POST['detail_type'])?$_POST['detail_type']: "";
    $app_id = isset($_POST['app_id'])?intval($_POST['app_id']): null;


    if($date_range !== ""){
        list($start, $end) = explode("/", $date_range);

        $startDate = (new DateTime($start, new DateTimeZone("asia/kolkata")));
        $startDateforhalf = $start;

        if($end != " -"){

            $endDate = (new DateTime($end, new DateTimeZone("asia/kolkata")));
        }
        else{
            $endDate = $startDate;
        }
    }



    try {
        $pdo->beginTransaction(); // Step 1


        
        if($appId !==null){
        
        
            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`,`created_by`, `notify_to`) VALUES (?, ?, ?)");
        

            if($stmt2->execute([$app_id, $created_by, $user_id])){

                $stmt = $pdo->prepare("UPDATE applications SET status = ? , action_by = ? WHERE id = ?");
                $stmt->execute([$status, $userID, $appId]);
                   
            }

            // Here we checking for if the rquest is in these scenarioes then create the entry for employee.

            if($detail_type == "sick_leave" || $detail_type == "casual_leave" || $detail_type == "regularization"){

                
                
                $stmt = $pdo->prepare("INSERT INTO `time_entries`(`employee_id`, `entry_type`, `entry_time`, `notes`) VALUES (?, ?, ?, ?)");
    
                $minus_days = 0;
                for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {

                    $stmt->execute([
                        $user_id, $detail_type, $date->format("Y-m-d H:i:s"), $detail_type
                    ]);
                    $minus_days += 1;
                }

                if($detail_type == "sick_leave"){
                    $stmt = $pdo->prepare("UPDATE leavecount set sick_leave = sick_leave - ? WHERE employee_id = ?");
                    $stmt->execute([$minus_days, $user_id]);
                }
                if($detail_type == "casual_leave"){
                    $stmt = $pdo->prepare("UPDATE leavecount set casual_leave = casual_leave - ? WHERE employee_id = ?");
                    $stmt->execute([$minus_days, $user_id]);
                }


            }




        }
    
        $pdo->commit(); // Step 3
        echo 0;
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack(); // Step 4
        echo "Transaction failed: " . $e->getMessage();
    }


    
}



if($info == "doapprove_wfh"){
    $appId = isset($_POST['val'])?intval($_POST['val']): null;
    $status = isset($_POST['status'])?$_POST['status']: "";


    $user_id = ($_POST['user_id'])?($_POST['user_id']): null;
    $created_by = ($_POST['created_by'])?($_POST['created_by']): null;

    $date_range = isset($_POST['date_range'])?$_POST['date_range']: "";
    $detail_type = isset($_POST['detail_type'])?$_POST['detail_type']: "";
    $app_id = isset($_POST['app_id'])?intval($_POST['app_id']): null;

    $title = isset($_POST['title'])?$_POST['title']: "";
    $desc = isset($_POST['desc'])?$_POST['desc']: "";




    if($date_range !== ""){
        list($start, $end) = explode("/", $date_range);

        $startDate = (new DateTime($start, new DateTimeZone("asia/kolkata")));
        $startDateforhalf = $start;

        if($end != " -"){

            $endDate = (new DateTime($end, new DateTimeZone("asia/kolkata")));
        }
        else{
            $endDate = $startDate;
        }
    }



    try {
        $pdo->beginTransaction(); // Step 1


        
        if($appId !==null){
        
        
            $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`,`created_by`, `notify_to`) VALUES (?, ?, ?)");
        

            if($stmt2->execute([$app_id, $created_by, $user_id])){

                $stmt = $pdo->prepare("UPDATE applications SET status = ?, action_by = ? WHERE id = ?");
                $stmt->execute([$status, $userID, $appId]);

            }

            // Here we checking for if the rquest is in these scenarioes then create the entry for employee.

            if($detail_type == "work_from_home"){

                
                
                $stmt = $pdo->prepare("INSERT INTO `wfh`(`employee_id`, `title`, `description`, `date`) VALUES (?, ?, ?, ?)");
    
                for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {

                    $stmt->execute([
                        $user_id, $title, $desc, $date->format("Y-m-d H:i:s")
                    ]);
                }

               


            }




        }
    
        $pdo->commit(); // Step 3
        echo 0;
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack(); // Step 4
        echo "Transaction failed: " . $e->getMessage();
    }


    
}


if($info == "approve_punchouttime"){
    $appId = isset($_POST['val'])?intval($_POST['val']): null;
    $status = isset($_POST['status'])?$_POST['status']: "";


    $user_id = ($_POST['user_id'])?($_POST['user_id']): null;
    $created_by = ($_POST['created_by'])?($_POST['created_by']): null;

    $date_range = isset($_POST['date_range'])?$_POST['date_range']: "";
    $detail_type = isset($_POST['detail_type'])?$_POST['detail_type']: "";
    $app_id = isset($_POST['app_id'])?intval($_POST['app_id']): null;
    $time_out = isset($_POST['time_out'])?$_POST['time_out']: "";


    if($date_range !== ""){
        list($start, $end) = explode("/", $date_range);

        $startDate = (new DateTime($start, new DateTimeZone("asia/kolkata")));
        $startDateforhalf = $start;

        if($end != " -"){

            $endDate = (new DateTime($end, new DateTimeZone("asia/kolkata")));
        }
        else{
            $endDate = $startDate;
        }
    }
    $startDate = $startDate->format("Y-m-d");

    $dopunchout = $startDate." ".$time_out;

    $error = "";
    try{
        $pdo->beginTransaction();

        
        $stmt2 = $pdo->prepare("INSERT INTO `notification`(`App_id`,`created_by`, `notify_to`) VALUES (?, ?, ?)");


        if($stmt2->execute([$app_id, $created_by, $user_id])){

            $stmt = $pdo->prepare("UPDATE applications SET status = ?, action_by = ? WHERE id = ?");
            $stmt->execute([$status, $userID, $appId]);

        }

        $stmt = $pdo->prepare("INSERT INTO time_entries (employee_id, entry_type, entry_time) VALUES (?, ?, ?)");
        if($stmt->execute([$user_id, "punch_out", $dopunchout])){
            $error =  "success";
         
        } else {
            $error  = "error";
      
        }

        $pdo->commit();
        echo $error;
    } catch (Exception $e) {
        $pdo->rollBack(); // Step 4
        echo "Transaction failed: " . $e->getMessage();
    }

    
}



if($info == "applicationsreport"){

    $stmt = $pdo->query("SELECT * FROM applications");

    $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    $pending = 0;
    $approved = 0;
    $rejected = 0;

    foreach($fetch as $row){
        $total++;

        if($row['status'] == "pending"){
            $pending++;
            
        }
        if($row['status'] == "approved"){
            $approved++;

        }
        if($row['status'] == "rejected"){
            $rejected++;

        }

    }
    echo json_encode(["total"=>$total, "pending"=>$pending, "approved"=>$approved, "rejected"=>$rejected]);
}


?>