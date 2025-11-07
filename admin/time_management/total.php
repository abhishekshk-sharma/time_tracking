<?php
require_once "../../includes/config.php";

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

$info = $_POST['info'];


// <<<<<<<<<<<<============ this is for creating a new employee ============>>>>>>>>>>>>>>

if($info == "createUser"){


    $employeeId = isset($_POST['employeeId'])?trim($_POST['employeeId']):'';
    $fullname = isset($_POST['fullname'])?trim($_POST['fullname']):'';
    $username = isset($_POST['username'])?trim($_POST['username']):'';
    $email = isset($_POST['email'])?trim($_POST['email']):'';
    $department = isset($_POST['department'])?trim($_POST['department']):'';
    $position = isset($_POST['position'])?trim($_POST['position']):'';
    $phone = isset($_POST['phone'])?trim($_POST['phone']):'';
    $hireDate = isset($_POST['hireDate'])?trim($_POST['hireDate']):'';
    $password = isset($_POST['password'])?trim($_POST['password']):'';
    $status = isset($_POST['status'])?trim($_POST['status']):'';
    $role = isset($_POST['role'])?trim($_POST['role']):'';


    $error = 0;

    $checkmobile = "/^[6-9]\d{9}$/";
    $form = "";
                

    if($employeeId ==  ""){
        $error = 1;
        $from = 'employeeId';
    }
    if($role ==  ""){
        $error = 1;
        $from = 'role';
    }
    if($status ==  ""){
        $error = 1;
        $from = 'status';
    }
    if($password ==  ""){
        $error = 1;
        $from = 'password';
    }else{
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
    }
    if($hireDate ==  ""){
        $error = 1;
        $from = 'hireDate';
    }
    else{
        $hireDate = new DateTime($hireDate, new DateTimeZone("asia/kolkata"));
    }
    if(!preg_match($checkmobile,$phone)){
        $error = 1;
        $from = 'phone';
    }
    if($position ==  ""){
        $error = 1;
        $from = 'position';
    }
    if($department ==  ""){
        $error = 1;
        $from = 'department';
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = 1;
        $from = 'email';
    }
    if($username ==  ""){
        $error = 1;
        $from = 'username';
    }
    if($fullname ==  ""){
        $error = 1;
        $from = 'fullname';
    }
    if($error ==  1){
        $send = json_encode([
            "error" => $error,
            "details" => "Please Check For Credentials: ".$from."."
        ]);
        echo $send;
        exit;
        // echo "Please Check For Credentials: ".$from.".";
    }
    else{
        
        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO employees(emp_id, full_name, username, email, phone, department, position, hire_date, status, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


            if($success = $stmt->execute([$employeeId, $fullname, $username, $email, $phone, $department, $position, $hireDate->format("Y-m-d"), $status,   $hash_password, $role])){

                 

                // if ($success) {
                //     $employeeId = $pdo->lastInsertId();
                //     if (!$employeeId) {
                //         throw new Exception("Failed to retrieve employee ID");
                //     }
                //     else{
                //         $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE id = ?");
                //         $stmt->execute([$employeeId]);
                //         if ($stmt->fetchColumn() == 0) {
                //         throw new Exception("Employee ID not found");
                //     }
                //     }
                // }

                $stmt = $pdo->query("SELECT * FROM system_settings");
                $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach($sys as $row){
                    if($row['setting_key'] == "casual_leave" ){
                        $casual = intval($row['setting_value']);
                    }
                    if($row['setting_key'] == "sick_leave"){
                        $sick = intval($row['setting_value']);
                    }
                }
                
              

                

                $stmt = $pdo->prepare("INSERT INTO `leavecount`( `employee_id`, `casual_leave`, `sick_leave`) VALUES (?, ?, ?)");
                $stmt->execute([$employeeId, $casual, $sick]);

                $send = json_encode([
                    "error" => 0,
                    "details" => "success from total"
                ]);
                echo $send;
                
            }else{
                $send = json_encode([
                    "error" => $error,
                    "details" => "Please Check For Credentials: ".$from."."
                ]);
                echo $send;
                exit;
            }
            
            $pdo->commit();
            
          
        }
        catch(exception $e){
            $pdo->rollback();
             echo json_encode(["error" => 1, "details" => "Exception: " . $e->getMessage()]);
        }


    }



}



/* <<<<<<<============ this is for admin index page getting all the intial 

                    // initial details of employees. ==============>>>>>>>>>>>>   

                    */



if($info == "GetAllDetails"){

    $today = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');

    $page = isset($_POST['page'])?intval($_POST['page']):null;
    $limit = isset($_POST['limit'])?intval($_POST['limit']):null;
    $searchInput = isset($_POST['searchInput'])?($_POST['searchInput']):"";
    $searchdate = isset($_POST['searchdate'])?($_POST['searchdate']):"";


    if (!empty($searchdate) && $searchdate !== "") {
        $dateinput = new DateTime($searchdate, new DateTimeZone("Asia/Kolkata"));
        $searchdate = $dateinput->format("Y-m-d");
        $today = $dateinput->format("Y-m-d");
        $_SESSION['searchdate'] = $searchdate;  
    } else { 
        $searchdate = ""; // prevent invalid date usage
        $_SESSION['searchdate'] = "";
    }


    $offset = ($page - 1) * $limit;

    $countrow = 0;

    if($searchInput !== "" && $searchdate !== ""){

        $stmt = $pdo->prepare("SELECT e.*, t.* FROM employees e JOIN time_entries t ON e.emp_id = t.employee_id WHERE e.username LIKE ? AND DATE(t.entry_time) = ? GROUP BY t.employee_id LIMIT $limit OFFSET $offset");
        $stmt->execute(["%$searchInput%", $searchdate]);
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM employees e JOIN time_entries t ON e.emp_id = t.employee_id WHERE e.username LIKE ? AND DATE(t.entry_time) = ? GROUP BY t.employee_id");
        $stmt->execute(["%$searchInput%", $searchdate]);
        $forcountrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countrow = COUNT($forcountrow);
    }elseif($searchdate !== ""){

        $stmt = $pdo->prepare("SELECT e.*, t.* FROM employees e JOIN time_entries t ON e.emp_id = t.employee_id WHERE DATE(t.entry_time) = ? GROUP BY t.employee_id LIMIT $limit OFFSET $offset");
        $stmt->execute([$searchdate]);
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT e.*, t.* FROM employees e JOIN time_entries t ON e.emp_id = t.employee_id WHERE DATE(t.entry_time) = ? GROUP BY t.employee_id");
        $stmt->execute([$searchdate]);
        $forcountrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countrow = COUNT($forcountrow);
    }elseif($searchInput !== ""){

        $stmt = $pdo->prepare("SELECT * FROM employees WHERE username LIKE ? LIMIT $limit OFFSET $offset");
        $stmt->execute(["%$searchInput%"]);
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE username LIKE ?");
        $stmt->execute(["%$searchInput%"]);
        $forcountrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countrow = COUNT($forcountrow);
    }
    else{
        
        $stmt = $pdo->query("SELECT * FROM employees ORDER BY emp_id ASC LIMIT $limit OFFSET $offset");
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->query("SELECT * FROM employees");
        $forcountrow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countrow = COUNT($forcountrow);
    }
    



    $output = '';
    $total = null;
    foreach($getuserid as $row){

            $stmt = $pdo->prepare("SELECT a.emp_id, b.* FROM employees a LEFT JOIN time_entries b ON a.emp_id = b.employee_id WHERE b.employee_id LIKE ? AND b.entry_time like ? ORDER BY b.entry_time ASC");
            $stmt->execute([$row['emp_id'], "$today%"]);
            $getdetail = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $wfhstmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
            $wfhstmt->execute([$row['emp_id'], $today]);

            $punchin = $lunchstart = $lunchend = $punchout = null;

            foreach($getdetail as $data){
                if($data['entry_type'] == "punch_in"){
                    $punchin = $data['entry_time'];
                }
                if($data['entry_type'] == "lunch_start"){
                    $lunchstart = $data['entry_time'];
                }
                if($data['entry_type'] == "lunch_end"){
                    $lunchend = $data['entry_time'];
                }
                if($data['entry_type'] == "punch_out"){
                    $punchout = $data['entry_time'];
                }
            }
            $output .= '<tr >
                            <td data-id="'.$row['emp_id'].'" style="color: blue; cursor:pointer;" class="touser ">'.$row['username'].' ('.$row['emp_id'].')</td>
                            <td>'.$today.'</td>
                            <td>'.$punchin.'</td>
                            <td>'.$lunchstart.'</td>
                            <td>'.$lunchend.'</td>
                            <td>'.$punchout.'</td>';

        $time = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');

        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC LIMIT 1");
        $stmt->execute([$row['emp_id'], "$today%"]);
        $lastEntry = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastAction = isset($lastEntry['entry_type'])?$lastEntry['entry_type']: null;


        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
        $stmt->execute([$row['emp_id'], "$today%"]);
        $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        // Step 2: Check if punchOut is pending
        $ForOutTime = (new DateTime('now', new DateTimeZone("Asia/Kolkata")));
        $outtime = isset($lastEntry['entry_time'])? new DateTime($lastEntry['entry_time'], new DateTimeZone("asia/kolkata")): $today;
        if($firstEntry){

            $first = new DateTime($firstEntry['entry_time'], new DateTimeZone("asia/kolkata"));
            $workedDuration = $first->diff($outtime);
            $total = $workedDuration->format('%H:%I:%S');

        }else{
            $first = null;
            $total = null;
        }


        // Getting the total work time defined by admin
        $stmt = $pdo->query("SELECT * FROM system_settings");
        $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($sys as $row){
            if($row['setting_key'] == "work_start_time"){
                $startTime = new DateTime($row['setting_value'], new DateTimeZone("asia/kolkata"));
                $checkstart = strtotime($row['setting_value']);
            }
            if($row['setting_key'] == "work_end_time"){
                $endTime = new DateTime($row['setting_value'], new DateTimeZone("asia/kolkata"));
            }
            if($row['setting_key'] == "lunch_duration"){
                $lunchduration = $row['setting_value'];
            }
            if($row['setting_key'] == "late_threshold"){
                $late = intval($row['setting_value']);
            }
        }



        // Checking If employee is late or not
        // $fe = new datetime($firstEntry['entry_time']);
        if($first !== null){
            $firstpunch = strtotime(isset($firstEntry['entry_time'])?$first->format("H:i:s"):null);
            $totalgap  =  $firstpunch - $checkstart;
            $gaphour = floor(($totalgap/3600)*60);


            if($firstEntry['entry_type'] == "regularization" || $firstEntry['entry_type'] == "casual_leave" || $firstEntry['entry_type'] == "sick_leave" || $firstEntry['entry_type'] == "holiday"){
                
                    $state = $firstEntry['entry_type'];
                    
            
            }
            else{
            
                if($gaphour <= $late){
                    $state = "Present";
                }
                else if($gaphour >= $late){
                    $state = "Late";
                }
            }
        }
        else{
            $state = "Absent";
        }

        if($wfhstmt->rowCount() > 0){
            $output .= '<td>'.$total.'</td>';
            $output .= '<td><span class="status-badge status-Present" style="background-color: rgba(237, 225, 247, 1); color: purple;">WFH</span></td>';
            
        }
        else if($state != "Present" && $state != "Late" && $state != "Absent"){

            $output .= '<td>'.$total.'</td>';
           
            $output .= '<td><span class="status-badge status-Late" style="color: orange;">'.$state.'</span></td>
            </tr>';
        }           
        else{

            $output .= '<td>'.$total.'</td>';
            $present = ($first == null)? "absent":"present";
            $output .= '<td><span class="status-badge status-'.$state.'">'.$state.'</span></td>
            </tr>';
        }
    
    }

    $response = [
        "output"=>$output,
        "row"=> $countrow
    ];


    function isSundayOrSecondOrFourthSaturday($dateStr) {
    $date = new DateTime($dateStr);
    $dayOfWeek = $date->format('w'); // 0 = Sunday, 6 = Saturday

    // If it's Sunday, return true
    if ($dayOfWeek == 0) {
        return true;
    }

    // If it's Saturday, check if it's the 2nd or 4th Saturday
    if ($dayOfWeek == 6) {
        $monthStart = new DateTime($date->format('Y-m-01'));
        $monthEnd = new DateTime($date->format('Y-m-t'));

        $interval = new DateInterval('P1D');
        $period = new DatePeriod($monthStart, $interval, $monthEnd->modify('+1 day'));

        $saturdays = [];
        foreach ($period as $day) {
            if ($day->format('w') == 6) {
                $saturdays[] = $day->format('Y-m-d');
            }
        }

        // Check if the date matches the 2nd or 4th Saturday
        return in_array($date->format('Y-m-d'), [$saturdays[1] ?? '', $saturdays[3] ?? '']);
    }

    return false;
}


    if($response['output'] == ""){
        $dateinput = new DateTime($searchdate, new DateTimeZone("Asia/Kolkata"));
        $searchdate = $dateinput->format("Y-m-d");
        if(isSundayOrSecondOrFourthSaturday($searchdate)){
            $response['output'] = '<tr><td colspan="9" style="text-align:center; color: purple;">Holiday (Sunday or 2nd/4th Saturday)</td></tr>';
        }
        else{
            $response['output'] = '<tr><td colspan="9" style="text-align:center; color: red;">No Records Found</td></tr>';
        }
        
    }
    echo json_encode($response);
}



// <<<<<<<======== this is for admin can get idea about actions by employee for the day ========>>>>>>>>>>


if($info == "timeWorked"){
    $userid = isset($_POST['id']) ? ($_POST['id']) : "";
    $date = isset($_POST['date']) ? $_POST['date'] : "";
    

    if($_SESSION['searchdate'] != "" && isset($_SESSION['searchdate']) && $date == ""){
        $date = $_SESSION['searchdate'];
    }
    
    if($userid == ""){
        echo json_encode(['error' => 'Invalid employee ID']);
        exit;
    }

    // Set timezone and format date
    $timezone = new DateTimeZone("Asia/Kolkata");
    if(!empty($date)) {
        $dateObj = new DateTime($date, $timezone);
        $time = $dateObj->format("Y-m-d");
    } else {
        $time = (new DateTime('now', $timezone))->format('Y-m-d');
    }

    $wfhstmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
    $wfhstmt->execute([$userid, $time]);
    // Get system settings
    $stmt = $pdo->query("SELECT * FROM system_settings");
    $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $work_start_time_str = '10:30:00'; // default
    $lunchduration = '60'; // default
    $late_threshold = 15; // default in minutes
    
    foreach($sys as $row){
        switch($row['setting_key']) {
            case "work_start_time":
                $work_start_time_str = $row['setting_value'];
                $startTime = new DateTime($row['setting_value'], $timezone);
                
                    $dateObj = new DateTime($date, $timezone);
                    $date = $dateObj->format("Y-m-d");
                    $dateObj = $date."".$startTime->format(" H:i");
                    $checkstart = (new DateTime($dateObj, $timezone))->getTimestamp();
                
                break;
            case "work_end_time":
                $work_end_time_str = $row['setting_value'];
                break;
            case "lunch_duration":
                $lunchduration = $row['setting_value'];
                break;
            case "late_threshold":
                $late_threshold = intval($row['setting_value']);
                break;
        }
    }

    // Create expected start time for the specific date
    $expected_start_time = strtotime($time . ' ' . $work_start_time_str);
    $expected_end_time = strtotime($time . ' ' . $work_end_time_str);
    $workTime = date('H:i:s A', $expected_start_time) . "-" . date('H:i:s A', $expected_end_time); // Assuming 6 PM end time

    // Get all time entries for the selected date
    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND DATE(entry_time) = DATE(?) ORDER BY entry_time ASC");
    $stmt->execute([$userid, $time]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate lunch time
    $total_lunch_seconds = 0;
    $lunch_start = null;

    foreach ($entries as $entry) {
        if ($entry['entry_type'] == 'lunch_start') {
            $lunch_start = strtotime($entry['entry_time']);
        } elseif ($entry['entry_type'] == 'lunch_end' && $lunch_start !== null) {
            $lunch_end = strtotime($entry['entry_time']);
            
            if ($lunch_end > $lunch_start) {
                $total_lunch_seconds += ($lunch_end - $lunch_start);
            }
            $lunch_start = null;
        }
    }

    // Handle unmatched lunch_start
    if ($lunch_start !== null) {
        // Use last entry time or current time if no entries after lunch start
        $fallback_end = !empty($entries) ? strtotime(end($entries)['entry_time']) : time();
        if ($fallback_end > $lunch_start) {
            $total_lunch_seconds += ($fallback_end - $lunch_start);
        }
    }

    $lunch_hours = floor($total_lunch_seconds / 3600);
    $lunch_minutes = floor(($total_lunch_seconds % 3600) / 60);
    $totallunchByemp = $lunch_hours."H : ".$lunch_minutes."M";

    // Get first and last entries
    $firstEntry = !empty($entries) ? $entries[0] : null;
    $lastEntry = !empty($entries) ? end($entries) : null;

    // Check for leave applications for the specific date
    $stmt = $pdo->prepare("SELECT * FROM applications 
                          WHERE employee_id = ? AND status = 'approved'
                          AND DATE(?)  BETWEEN DATE(start_date) AND DATE(end_date) 
                          ORDER BY created_at ASC LIMIT 1");
    $stmt->execute([$userid, $time]);
    $leaveApplication = $stmt->fetch(PDO::FETCH_ASSOC);

    // Determine attendance state
    $state = "Absent";
    
    if (!empty($entries)) {
        // Check if first entry is a special type
        if ($firstEntry && in_array($firstEntry['entry_type'], ["regularization", "casual_leave", "sick_leave", "holiday"])) {
            $state = $firstEntry['entry_type'];
        }
        // Check if there's an approved leave record for this date
        elseif ($leaveApplication && in_array($leaveApplication['req_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
            $state = $leaveApplication['req_type'];
        }
        else if($wfhstmt->rowCount() > 0){
                $state = "WFH";
        }
        // Check if employee punched in
        elseif ($firstEntry && $firstEntry['entry_type'] == "punch_in") {
            // Check if employee is late - FIXED: Correct time calculation
            $firstPunchTime = new DateTime($firstEntry['entry_time'], $timezone);
            $firstPunchTimestamp = $firstPunchTime->getTimestamp();
            
            // Calculate minutes late (positive value means late).

          
                $minutesLate = ($firstPunchTimestamp - $checkstart) / 60;

            
             
            
            if ($minutesLate <= $late_threshold) {
                $state = "Present";
            } else {
                $state = "Late";
            }
        }
        // Check for half day
        elseif ($firstEntry && $firstEntry['entry_type'] == "half_day") {
            $state = "half_day";
        }
    }
    
    // Additional check for holidays if still absent
    if ($state == "Absent") {
        // Check if any entry indicates a holiday
        foreach ($entries as $entry) {
            if ($entry['entry_type'] === 'holiday') {
                $state = "holiday";
                break;
            }
        }
    }

    // Format state display
    switch($state) {
        case "Present":
            $lateresult = "<label style='color:green;'>$state</label>";
            break;
        case "Late":
            $lateresult = "<label style='color:orange;'>$state</label>";
            break;
        case "Absent":
            $lateresult = "<label style='color:red;'>$state</label>";
            break;
        case "half_day":
            $lateresult = "<label style='color:blue;'>Half Day</label>";
            break;
        case "casual_leave":
            $lateresult = "<label style='color:purple;'>Casual Leave</label>";
            break;
        case "sick_leave":
            $lateresult = "<label style='color:purple;'>Sick Leave</label>";
            break;
        case "regularization":
            $lateresult = "<label style='color:orange;'>Regularization</label>";
            break;
        case "holiday":
            $lateresult = "<label style='color:gray;'>Holiday</label>";
            break;
        case "WFH":
            $lateresult = "<label style='color:purple;'>WFH</label>";
            break;
        default:    
            $lateresult = "<label style='color:orange;'>$state</label>";
    }

    // Calculate total worked time (only from punch_in to punch_out sessions)
    $total_worked_seconds = 0;
    $punch_in_time = null;
    
    foreach ($entries as $entry) {
        if ($entry['entry_type'] == 'punch_in') {
            $punch_in_time = strtotime($entry['entry_time']);
        } elseif ($entry['entry_type'] == 'punch_out' && $punch_in_time !== null) {
            $punch_out_time = strtotime($entry['entry_time']);
            
            if ($punch_out_time > $punch_in_time) {
                $total_worked_seconds += ($punch_out_time - $punch_in_time);
            }
            $punch_in_time = null;
        }
    }
    
    // Handle incomplete punch session (punch_in without punch_out)
    if ($punch_in_time !== null) {
        // Option: Count ongoing sessions if you want to show current work time
        // $current_time = time();
        // if ($current_time > $punch_in_time) {
        //     $total_worked_seconds += ($current_time - $punch_in_time);
        // }
    }
    
    // Convert to hours:minutes:seconds
    $worked_hours = floor($total_worked_seconds / 3600);
    $worked_minutes = floor(($total_worked_seconds % 3600) / 60);
    $worked_seconds = $total_worked_seconds % 60;
    $total_worked_time = sprintf('%02d:%02d:%02d', $worked_hours, $worked_minutes, $worked_seconds);
    
    // Calculate net work time (subtract lunch time)
    $net_work_seconds = $total_worked_seconds - $total_lunch_seconds;
    if ($net_work_seconds < 0) $net_work_seconds = 0;
    
    $net_hours = floor($net_work_seconds / 3600);
    $net_minutes = floor(($net_work_seconds % 3600) / 60);
    $net_seconds = $net_work_seconds % 60;
    $net_work_time = sprintf('%02d:%02d:%02d', $net_hours, $net_minutes, $net_seconds);
    
    // Get first punch time for display
    $first_punch_display = "N/A";
    if ($firstEntry && $firstEntry['entry_type'] == 'punch_in') {
        $firstPunch = new DateTime($firstEntry['entry_time'], $timezone);
        $first_punch_display = $firstPunch->format('H:i A');
    }


    
    // Prepare response
    $response = [
        'worktime' => $workTime,
        'punchTime' => date('H:i:s A', $expected_start_time),
        'lunchDuation' => $lunchduration."M",
        'punch_in' => $first_punch_display,
        'total_hours' => $total_worked_time,
        'network' => $net_work_time,
        'totalLunchByemp' => $totallunchByemp,
        'late' => $lateresult,
        'debug' => [ // Added for debugging
            'expected_start' => date('Y-m-d H:i:s', $expected_start_time),
            'first_punch' => $firstEntry ? $firstEntry['entry_time'] : 'None',
            'minutes_late' => isset($minutesLate) ? round($minutesLate, 2) : 'N/A',
            'late_threshold' => $late_threshold
        ]
    ];

    echo json_encode($response);
    exit;
}



// <<<<<<<+============ This is for admin when want to see perticular date using id ============>>>>>>>>>>>>>>>>>

if($info == "todaysActivity"){
    $userid = isset($_POST['id'])?($_POST['id']): null;
    $output = "";
    if($userid !== null){

    if(!isset($_SESSION['searchdate']) || $_SESSION['searchdate'] == ""){
        $time = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');
    } else {
        $time = $_SESSION['searchdate'];
    }

    $stmt = $pdo->prepare("SELECT * FROM `time_entries` WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC");
    $stmt->execute([$userid, "$time%"]);
    $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($_SESSION['searchdate'] !== ""){
        $output .= '<h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Activity on '.$time.'
                </h3>';
    } else {
        $output .= '<h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Today\'s Activity
                </h3>';
    }

    // $icon = "bullseye";
    foreach($fetch as $row){   
        $icon = "bullseye";
        if($row['entry_type'] == 'punch_in'){
            $icon = "fingerprint";
        }   
        if($row['entry_type'] == 'lunch_start'){
            $icon = "utensils";
        }   
        if($row['entry_type'] == 'lunch_end'){
            $icon = "utensils";
        }   
        if($row['entry_type'] == 'punch_out'){
            $icon = "door-open";
        }   

        $output .= '<ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-'.$icon.'"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-name">'.$row['entry_type'].'</div>
                            <div class="activity-time">'.$row['entry_time'].'</div>
                        </div>
                    </li>
                    </ul>';        
    }

    echo $output;
    }
}


// <<<<<<<<<<========== This is when admin want to see perticular data of perticular date
if($info == "activityByDay"){
    $userid = isset($_POST['id'])?($_POST['id']): null;
    $date = isset($_POST['date'])?($_POST['date']): "";
    $output = "";
    if($userid !== null){
        $rowtime = new DateTime($date, new DateTimeZone("asia/kolkata"));
        $time = $rowtime->format("Y-m-d");
        $stmt = $pdo->prepare("SELECT * FROM `time_entries` WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC");
        $stmt->execute([$userid, "$time%"]);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output .= '<h3 class="section-title">
                        <i class="fas fa-history"></i>
                        '.$time.'\'s Activity
                    </h3>';

        foreach($fetch as $row){   
            $icon = "fingerprint";
            if($row['entry_type'] == 'punch_in'){
                $icon = "fingerprint";
            }   
            if($row['entry_type'] == 'lunch_start'){
                $icon = "utensils";
            }   
            if($row['entry_type'] == 'lunch_end'){
                $icon = "utensils";
            }   
            if($row['entry_type'] == 'punch_out'){
                $icon = "door-open";
            }   

            $output .= '<ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-'.$icon.'"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-name" data-id="'.$row['id'].'" style="cursor:pointer;" >'.$row['entry_type'].'</div>
                            <div class="activity-time">'.$row['entry_time'].'</div>
                        </div>
                    </li>
                    </ul>';        
        }

            echo $output;
    }
}


// <<<<<<<<<<<<<<<<========== This is When admin click on the employee entry type of perticular date. then fetch the details of that entry for modal ================>>>>>>>>>>>>


if($info == "activityDetailByIdforeditmodal"){
    $entryid = isset($_POST['id'])?$_POST['id']: null;

    $response = [
            "status"=> "notFetched"
        ];
    if($entryid !== null){
        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE id = ?");
        $stmt->execute([$entryid]);
        $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($fetch){
            $response['status'] = "fetched";
            $response['data'] = $fetch;
        }

        echo json_encode($response);
    }
}


// <<<<<<<<<<<<<<<<<=================  This is for admin to update the entry of perticular employee ===============>>>>>>>>>>>>>>>

if($info == "updateEntryById"){
    $entryid = isset($_POST['id'])?$_POST['id']: null;
    $entrytype = isset($_POST['entry_type'])?trim($_POST['entry_type']):'';
    $entrytime = isset($_POST['entry_time'])?trim($_POST['entry_time']):'';

    $error = 0;
    $from = "";

    if($entrytime ==  ""){
        $error = 1;
        $from = 'entrytime';
    }
    if($entrytype ==  ""){
        $error = 1;
        $from = 'entrytype';
    }
    if($entryid ==  ""){
        $error = 1;
        $from = 'entryid';
    }
    if($error ==  1){
        $send = json_encode([
            "error" => $error,
            "details" => "Please Check For Credentials: ".$from."."
        ]);
        echo $send;
        exit;
        // echo "Please Check For Credentials: ".$from.".";
    }
    else{
        
        try{
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE time_entries SET entry_type = ?, entry_time = ? WHERE id = ?");
            $stmt->execute([$entrytype, $entrytime, $entryid]);
            $pdo->commit();

            $response = [
                "status" => "updated"
            ];
            echo json_encode($response);
        } catch (Exception $e) {
            $pdo->rollBack();
            $response = [
                "status" => "error",
                "message" => $e->getMessage()
            ];
            echo json_encode($response);
        }
    }
}



//  <<<<<<<<<<<<<============= This is when the admin want to see the history of that perticular employee ============>>>>>>>>>>>>>>

if($info == "detailsById"){
    $userid = isset($_POST['id'])?$_POST['id']:null;

    if($userid !== null){

        $today = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');

        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND MONTH(entry_time) = MONTH(CURRENT_DATE) 
      AND YEAR(entry_time) = YEAR(CURRENT_DATE) GROUP BY DATE(entry_time) ");
        $stmt->execute([$userid]);
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = '';
        $total = null;  
        foreach($getuserid as $row){

                $entry_date = new DateTime($row['entry_time'], new DateTimeZone("asia/kolkata"));
                $formatted_date = $entry_date->format('Y-m-d'); 


                $stmt = $pdo->prepare("SELECT a.id, b.* FROM employees a LEFT JOIN time_entries b ON a.emp_id = b.employee_id WHERE b.employee_id = ? AND DAY(entry_time) = DAY(?) AND MONTH(entry_time) = MONTH(?) 
                AND YEAR(entry_time) = YEAR(?)  ORDER BY b.entry_time ASC");
    
                $stmt->execute([$row['employee_id'],  $formatted_date, $formatted_date, $formatted_date]);
                $getdetail = $stmt->fetchAll(PDO::FETCH_ASSOC);


                $wfhstmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
                $wfhstmt->execute([$row['employee_id'], $formatted_date]);


                $punchin = $lunchstart = $lunchend = $punchout = null;

                foreach($getdetail as $data){
                    if($data['entry_type'] == "punch_in"){
                        $punchin = $data['entry_time'];
                    }
                    if($data['entry_type'] == "lunch_start"){
                        $lunchstart = $data['entry_time'];
                    }
                    if($data['entry_type'] == "lunch_end"){
                        $lunchend = $data['entry_time'];
                    }
                    if($data['entry_type'] == "punch_out"){
                        $punchout = $data['entry_time'];
                    }
                }
                $output .= '<tr>
                                
                                <td style="color:blue;cursor:pointer;"  data-date="'.$row['entry_time'].'" class="searchDate">'.$entry_date->format("M d, Y").'</td>
                                <td>'.$punchin.'</td>
                                <td>'.$lunchstart.'</td>
                                <td>'.$lunchend.'</td>
                                <td>'.$punchout.'</td>';

            $time = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');

            $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC LIMIT 1");
            $stmt->execute([$row['employee_id'], "$formatted_date%"]);
            $lastEntry = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastAction = isset($lastEntry['entry_type'])?$lastEntry['entry_type']: null;


            $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 2");
            $stmt->execute([$row['employee_id'], "$formatted_date%"]);
            $firstEntrydata = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $firstEntry = $firstEntrydata[0];
            $firstEntryIsHalf['entry_type'] = "";

            if($firstEntry['entry_type'] == "half_day"){
                $firstEntry = $firstEntrydata[1];
                $firstEntryIsHalf = $firstEntrydata[0];
            }
            // Step 2: Check if punchOut is pending
            $ForOutTime = (new DateTime('now', new DateTimeZone("Asia/Kolkata")));
            $outtime = isset($lastEntry['entry_time'])? new DateTime($lastEntry['entry_time'], new DateTimeZone("asia/kolkata")): $ForOutTime;
            if($firstEntry){
            
                $first = new DateTime($firstEntry['entry_time'], new DateTimeZone("asia/kolkata"));
                $workedDuration = $first->diff($outtime);
                $total = $workedDuration->format('%H:%I:%S');
            
            }else{
                $first = null;
                $total = null;
            }

            // Getting the total work time defined by admin
            $stmt = $pdo->query("SELECT * FROM system_settings");
            $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($sys as $row){
                if($row['setting_key'] == "work_start_time"){
                    $startTime = new DateTime($row['setting_value'], new DateTimeZone("asia/kolkata"));
                    $checkstart = strtotime($row['setting_value']);
                }
                if($row['setting_key'] == "work_end_time"){
                    $endTime = new DateTime($row['setting_value'], new DateTimeZone("asia/kolkata"));
                }
                if($row['setting_key'] == "lunch_duration"){
                    $lunchduration = $row['setting_value'];
                }
                if($row['setting_key'] == "late_threshold"){
                    $late = intval($row['setting_value']);
                }
            }



            // Checking If employee is late or not
            // $fe = new datetime($firstEntry['entry_time']);
            if($first !== null){

                if($lastEntry['entry_type'] == "regularization" || $lastEntry['entry_type'] == "casual_leave" || $lastEntry['entry_type'] == "sick_leave" || $lastEntry['entry_type'] == "holiday" || $firstEntryIsHalf['entry_type'] == "half_day"){

                    if( $firstEntryIsHalf['entry_type'] == "half_day"){

                        $state = $firstEntryIsHalf['entry_type'];
                        
                    }
                    else{
                        $state = $lastEntry['entry_type'];

                    }
                }
                else{
                    $firstpunch = strtotime(isset($firstEntry['entry_time'])?$first->format("H:i:s"):null);
                    $totalgap  =  $firstpunch - $checkstart;
                    $gaphour = floor(($totalgap/3600)*60);
                    if($gaphour <= $late){
                        $state = "Present";
                    }
                    else if($gaphour >= $late){
                        $state = "Late";
                    }
                }
            }
            else{
                $state = "Absent";
            }
         
            if($wfhstmt->rowCount() > 0){
                $output .= '<td>'.$total.'</td>';
                $output .= '<td><span class="status-badge status-Present" style="background-color: rgba(237, 225, 247, 1); color: purple;">WFH</span></td>';
            }
            else if($lastEntry['entry_type'] == "regularization" || $lastEntry['entry_type'] == "casual_leave" || $lastEntry['entry_type'] == "sick_leave" || $lastEntry['entry_type'] == "holiday" || $firstEntryIsHalf['entry_type'] == "half_day"){
                
                  
                    $output .= '<td>'.$total.'</td>';
                    $present = ($first == null)? "absent":"present";
                    $output .= '<td><span class="status-badge status-Late" style="color: orange;">'.$state.'</span></td>
                    </tr>';
            }
            else{
            
                $output .= '<td>'.$total.'</td>';
                $present = ($first == null)? "absent":"present";
                $output .= '<td><span class="status-badge status-'.$state.'">'.$state.'</span></td>
                </tr>';
            }
        
        }

        if(!empty($output)){

            echo $output;
        }
        else{
            echo "<h2 style='position:absolute; color:red; margin-left: 20%;'>No Data Found!</h2>";
        }

    }
}


    // <<<<<<<<<<<<============= This is for when admin edits the user details along with password change =============>>>>>>>>>>>>>



if($info == "editUser"){
    $id = isset($_POST['id'])?($_POST['id']):null;
    $empId = isset($_POST['empId'])?($_POST['empId']):"";
    $empName = isset($_POST['fullname'])?$_POST['fullname']:'';
    $empusername = isset($_POST['empusername'])?$_POST['empusername']:'';
    $empDept = isset($_POST['empDept'])?$_POST['empDept']:'';
    $empPosition = isset($_POST['empPosition'])?$_POST['empPosition']:'';
    $empEmail = isset($_POST['empEmail'])?$_POST['empEmail']:'';
    $empPhone = isset($_POST['empPhone'])?$_POST['empPhone']:'';
    $empJoiningDate = isset($_POST['empJoiningDate'])?$_POST['empJoiningDate']:'';
    $empEndingDate = isset($_POST['empEndingDate'])?$_POST['empEndingDate']:null;
    $empCasualLeave = isset($_POST['empCasualLeave'])?intval($_POST['empCasualLeave']):'';
    $empSickLeave = isset($_POST['empSickLeave'])?intval($_POST['empSickLeave']):'';
    $empStatus = isset($_POST['empStatus'])?$_POST['empStatus']:'';
    $email_pattern = "/^[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+$/";
    $phone_pattern = "/^[6-9]\d{9}$/";

    $errorCode = 0;
    $error_where = "";
    
    $currentTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $date = $currentTime;
    $datetime = $date->format("Y-m-d H:i:s");
    $output = "";
    $status = "";
  

            if(!preg_match($email_pattern, $empEmail)){

                $errorCode = 1;
                $error_where ="email";
            }
            
            if($empName == ""){
                $errorCode = 1;
                $error_where ="fullname";
            }
            if($empusername == ""){
                $errorCode = 1;
                $error_where ="username";
            }
            if($empId == ""){
                $errorCode = 1;
                $error_where ="Empid";
                
            }
            if($id == null){
                $errorCode = 1;
                $error_where ="id";
                
            }
            if($empDept == ""){
                $errorCode = 1;
                $error_where ="department";

            }
            if($empPosition == ""){
                $errorCode = 1;
                $error_where ="position";

            }
            if(!preg_match($phone_pattern, $empPhone)){
                $errorCode = 1;
                $error_where ="phone";

            }
            
            if($empCasualLeave == ''){
                $errorCode = 1;
                $error_where ="casual leave";

            }
            if($empSickLeave == ''){
                $errorCode = 1;
                $error_where ="sick leave ($empCasualLeave)";

            }
            if($empStatus == ''){
                $errorCode = 1;
                $error_where ="status";

            }
            if($errorCode == 1){
                echo 'something went wrong! from '.$error_where;
            }
            else{
                              
                try{
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("UPDATE `employees` SET `emp_id`=?, `full_name`=?,`username`=?,`email`=?,`phone`=?,`department`=?,`position`=?, `hire_date` = ?, `end_date` = ?, `status`=?,`updated_at`=? WHERE `id` = ?");

                    if($stmt->execute([$empId, $empName, $empusername, $empEmail, $empPhone, $empDept,  $empPosition, $empJoiningDate, $empEndingDate, $empStatus, $datetime,  $id])){
                        $status = "Edited";
                        
                    }
                    else{
                        echo "NotEdited";
                        exit;
                    }

                    if($empCasualLeave !== "" || $empSickLeave !== null){

                        $stmt = $pdo->prepare("UPDATE `leavecount` SET `casual_leave`=?,`sick_leave`=? WHERE `employee_id` = ?");

                        if($stmt->execute([$empCasualLeave, $empSickLeave, $empId])){

                            if(($empSickLeave!== null) && ($empStatus !== "inactive")){
                                $stmt = $pdo->prepare("UPDATE `employees` SET `status`=? WHERE `emp_id` = ?");
                                $stmt->execute(["inactive", $empId]);
                            }
                            $status = "Edited";
                            
                        }
                        else{
                            echo "NotEdited";
                            exit;
                        }
                    }

                    echo $status;

                    $pdo->commit();

                    exit;
                }catch (Exception $e) {
                    $pdo->rollBack();
                    echo "Error: " . $e->getMessage();
                    exit;
                }
            }

}

if($info == "ChangePassword"){

    // $oldpass = isset($_POST['oldPass'])?trim($_POST['oldPass']):'';
    $newpass = isset($_POST['newPass'])?$_POST['newPass']:'';
    $id = ($_SESSION['getUserById']);

    $currentTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $date = $currentTime;
    $datetime = $date->format("Y-m-d H:i:s");
    $error = 0;

 
 
    if($newpass == ''){
        $error = 1;
    }
    else{
        $newNewPass = password_hash($newpass, PASSWORD_DEFAULT);
    }
    if($id == null){
        $error = 1;
    }
    if($error == 1){
        echo $error;
        exit;
    }


        $stmt = $pdo->prepare("UPDATE `employees` SET `password_hash`=?,`updated_at`=? WHERE emp_id = ?");
        if($stmt->execute([$newNewPass, $datetime, $id])){
            $error = 0;
            echo "changed";
            exit;
        }   
        else{
            $error= 1;
            echo $error;
            exit;
        }
    

}



if($info == "deleteEmployee"){
    $id = isset($_POST['empId'])?$_POST['empId']:null;

    if($id !== null){

        $stmt = $pdo->prepare("DELETE FROM employees WHERE emp_id = ?");
        if($stmt->execute([$id])){
            echo "Deleted";
            exit;
        }
        else{
            echo "notdeleted";
            exit;
        }
    }
}


if($info == "update_seettings"){

    $workStartTime = isset($_POST['workStartTime'])?($_POST['workStartTime']):"";
    $workEndTime = isset($_POST['workEndTime'])?($_POST['workEndTime']):"";
    $lunchDuration = isset($_POST['lunchDuration'])?($_POST['lunchDuration']):"";
    $late = isset($_POST['late'])?($_POST['late']):"";
    $cl_leave = isset($_POST['cl_leave'])?($_POST['cl_leave']):"";
    $sl_casual = isset($_POST['sl_casual'])?($_POST['sl_casual']):"";
    $min_working_hours = isset($_POST['min_working_hours'])?($_POST['min_working_hours']):"";
    
    $hHour = isset($_POST['hHour'])?($_POST['hHour']):"";
    $office_radius = isset($_POST['office_radius'])?($_POST['office_radius']):"";


        $updates = [
        
        ['work_start_time', $workStartTime],
        ['work_end_time', $workEndTime],
        ['lunch_duration', $lunchDuration],
        ['late_threshold', $late],
        ['casual_leave', $cl_leave],
        ['sick_leave', $sl_casual],
        ['half_day_time', $hHour],
        ['office_radius', $office_radius],
        ['min_working_hours', $min_working_hours]
    ];

        $success = true;

    foreach($updates as $update){
        $key = $update[0];
        $value = $update[1];

        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = :value WHERE setting_key = :key");
        
        if (!$stmt->execute([':value' => $value, ':key' => $key])) {
            $success = false;
            $errorInfo = $stmt->errorInfo();
            echo "Error updating $key: " . $errorInfo[2];
        }

    }

    if (!$success) {
        $res = [
            "status" => "error"
        ];
        echo json_encode($res);
    }
    else if($success){

        $stmt = $pdo->query("SELECT * FROM system_settings");
        $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [];
        $response ["status"] = "success";

        foreach ($sys as $row) {
            $response[$row['setting_key']] = ($row['setting_value']);
        }


        
        echo json_encode($response);
    }



}



if($info == "check_setting"){
            $stmt = $pdo->query("SELECT * FROM system_settings");
        $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [];
        

        foreach ($sys as $row) {
            $response[$row['setting_key']] = $row['setting_value'];
        }        
        echo json_encode($response);
}

?>

