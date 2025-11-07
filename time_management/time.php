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



// $time = (new DateTime('now', new DateTImeZone('Asia/Kolkata')))->format('H:i:s');

if($click == "punch_in" || $click == "lunch_start" || $click == "lunch_end" || $click == "punch_out"){
    $time = new DateTime("now", new DateTimeZone("asia/kolkata"));
    $time = $time->format("Y-m-d H:i:s");

    $checklast = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND DATE(entry_time) = DATE(CURRENT_DATE) ORDER BY entry_time DESC LIMIT 1 OFFSET 0 ");
    $checklast->execute([$userid]);

    $lastentry = $checklast->fetch(PDO::FETCH_ASSOC);


    if(empty($lastentry) || $lastentry['entry_type'] !== $click){

        $stmt = $pdo->prepare("INSERT INTO `time_entries`( `employee_id`, `entry_type`,`entry_time`,  `notes`) VALUES (?,?,?,?)");
        if($stmt->execute([$userid, $click, $time, $click])){
            $error = "nothing";
            echo $error;
            exit;
        }
    }
    else{
        echo "error";
        exit;
    }
    
    // echo "hello";
}


if($click == "getDetails" ){
    $time = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM `time_entries` WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC");
    $stmt->execute([$userid, "$time%"]);
    $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output .= '<h3 class="section-title">
                    <i class="fas fa-history"></i>
                    Today\'s Activity
                </h3>';
    $icon = "bullseye";    
    foreach($fetch as $row){   
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



if($click == "timeWorked") {
    // Set timezone and get current date
    $timezone = new DateTimeZone("Asia/Kolkata");
    $currentDate = (new DateTime('now', $timezone))->format('Y-m-d');
    
    // Get system settings
    $stmt = $pdo->query("SELECT * FROM system_settings");
    $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $startTime = $endTime = $lunchduration = $late = null;
    foreach($sys as $row) {
        switch($row['setting_key']) {
            case "work_start_time":
                $startTime = new DateTime($row['setting_value'], $timezone);
                
                $checkstart = $row['setting_value'];
                $combinedDateTime  = $currentDate.' '.$checkstart;
                $checkstart11 = new DateTime($combinedDateTime, $timezone);
                $checkstartTimestamp = $checkstart11->getTimestamp();
                // $checkstart11 = new DateTime($combinedDateTime, $timezone);
                // $checkstart = $checkstart11->getTimestamp();
                
                break;
            case "work_end_time":
                $endTime = new DateTime($row['setting_value'], $timezone);
                $checkend = $row['setting_value'];
                break;
            case "lunch_duration":
                $lunchduration = $row['setting_value'];
                break;
            case "late_threshold":
                $late = intval($row['setting_value']);
                break;
        }
    }
    
    $workTime = $startTime->format('H:i:s A') . "-" . $endTime->format('H:i:s A');
    
    // Get all time entries for the employee on current date
    $stmt = $pdo->prepare("SELECT * FROM time_entries 
                          WHERE employee_id = ? AND DATE(entry_time) = ? 
                          ORDER BY entry_time ASC");
    $stmt->execute([$userid, $currentDate]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate lunch time
    $total_seconds = 0;
    $lunch_start = null;
    
    foreach ($entries as $entry) {
        if ($entry['entry_type'] == 'lunch_start') {
            $lunch_start = strtotime($entry['entry_time']);
        } elseif ($entry['entry_type'] == 'lunch_end' && $lunch_start !== null) {
            $lunch_end = strtotime($entry['entry_time']);
            
            if ($lunch_end > $lunch_start) {
                $total_seconds += ($lunch_end - $lunch_start);
            }
            $lunch_start = null;
        }
    }
    
    // Handle unmatched lunch_start
    if ($lunch_start !== null) {
        $fallback_end = time();
        if ($fallback_end > $lunch_start) {
            $total_seconds += ($fallback_end - $lunch_start);
        }
    }
    
    $lunchHours = floor($total_seconds / 3600);
    $lunchMinutes = floor(($total_seconds % 3600) / 60);
    $totallunchByemp = $lunchHours . "H : " . $lunchMinutes . "M";
    
    // Check for leave/holiday/regularization
    $stmt = $pdo->prepare("SELECT * FROM applications 
                          WHERE employee_id = ?  AND DATE(created_at) = ? AND status = 'approved'
                          ORDER BY created_at ASC LIMIT 1");
    $stmt->execute([$userid, $currentDate]);
    $leaveRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determine attendance state
    $state = "Absent";
$firstEntry = null;
$lastEntry = null;

if (!empty($entries)) {
    $firstEntry = $entries[0];
    $lastEntry = end($entries);
    
    // Check if employee is on leave/holiday (check the first entry type)
    if ($firstEntry && in_array($firstEntry['entry_type'], ["regularization", "casual_leave", "sick_leave", "holiday"])) {
        $state = $firstEntry['entry_type'];
    }
    // Check if there's an approved leave record for this date
    elseif ($leaveRecord && in_array($leaveRecord['req_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
        $state = $leaveRecord['req_type'];
    }
    // Check if employee punched in
    elseif ($firstEntry && $firstEntry['entry_type'] == "punch_in") {
        // Check if employee is late
        $firstPunchTime = new DateTime($firstEntry['entry_time'], $timezone);
        $firstPunchTimestamp = $firstPunchTime->getTimestamp();
        
        // Calculate minutes late (positive value means late, negative means early)
        $minutesLate = ($firstPunchTimestamp - $checkstartTimestamp) / 60;
        
        if ($minutesLate <= $late) {
            $state = "Present";
        } else {
            $state = "Late";
        }
    }
    // Check for half day without punch in (approved half day leave)
    elseif ($leaveRecord && $leaveRecord['req_type'] == "half_day") {
        $state = "half_day";
    }
}

// Additional check for holidays from system settings or other sources
if ($state == "Absent") {
    // You might want to add a check here for holidays from a holidays table
    // $holidayCheck = checkIfHoliday($date, $pdo);
    // if ($holidayCheck) {
    //     $state = "holiday";
    // }
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
        default:
            $lateresult = "<label style='color:orange;'>$state</label>";
    }
    
    // Calculate total worked time (across all punch sessions)
    $totalWorkedSeconds = 0;
    $punchInTime = null;
    
    foreach ($entries as $entry) {
        if ($entry['entry_type'] == 'punch_in') {
            $punchInTime = strtotime($entry['entry_time']);
        } elseif ($entry['entry_type'] == 'punch_out' && $punchInTime !== null) {
            $punchOutTime = strtotime($entry['entry_time']);
            if ($punchOutTime > $punchInTime) {
                $totalWorkedSeconds += ($punchOutTime - $punchInTime);
            }
            $punchInTime = null;
        }
    }
    
    // Handle incomplete punch session (punch_in without punch_out)
    if ($punchInTime !== null) {
        $currentTime = time();
        if ($currentTime > $punchInTime) {
            $totalWorkedSeconds += ($currentTime - $punchInTime);
        }
    }
    
    // Convert to hours:minutes:seconds
    $workedHours = floor($totalWorkedSeconds / 3600);
    $workedMinutes = floor(($totalWorkedSeconds % 3600) / 60);
    $workedSeconds = $totalWorkedSeconds % 60;
    $totalWorkedTime = sprintf('%02d:%02d:%02d', $workedHours, $workedMinutes, $workedSeconds);
    
    // Calculate net work time (subtract lunch time)
    $netWorkSeconds = $totalWorkedSeconds - $total_seconds;
    $netHours = floor($netWorkSeconds / 3600);
    $netMinutes = floor(($netWorkSeconds % 3600) / 60);
    $netSeconds = $netWorkSeconds % 60;
    $netWorkTime = sprintf('%02d:%02d:%02d', $netHours, $netMinutes, $netSeconds);
    
        $starttime1 = DateTime::createFromFormat('H:i', $checkstart);
        $endtime1 = DateTime::createFromFormat('H:i', $checkend);

        $interval1 = $starttime1->diff($endtime1);


        $totaldefinedhours = $interval1->h + ($interval1->i / 60);
    
    // Check for half-day
    $is_half = false;
    $com_half = false;
    
    if ($leaveRecord && $leaveRecord['req_type'] == "half_day" && $leaveRecord['status'] == "approved") {
        $is_half = true;
        
        // Get half day time requirement
        $stmt = $pdo->query("SELECT setting_value AS value FROM system_settings WHERE setting_key = 'half_day_time'");
        $getHalftime = $stmt->fetch(PDO::FETCH_ASSOC);
        
        list($halfHours, $halfMinutes) = explode(':', $getHalftime['value']);
        $halfDayMinutes = ((int)$halfHours * 60) + (int)$halfMinutes;
        
        // Calculate total worked minutes
        $workedMinutesTotal = ($workedHours * 60) + $workedMinutes;
        
        if ($workedMinutesTotal >= $halfDayMinutes) {
            $com_half = true;
        }
    }
    $action = "run";
    if($lastEntry && $lastEntry['entry_type'] === 'punch_out'){
        $totaltime = new DateTime($totalWorkedTime, new DateTimeZone("Asia/Kolkata"));
        $tot = (int)$totaltime->format("H");
        $workedSeconds = ($netHours * 3600) + ($netMinutes * 60) + $netSeconds;
        $actualSeconds = ($totaltime->h * 3600) + ($totaltime->i * 60) + $totaltime->s;
        
        if ($tot >= $totaldefinedhours) {
            $action = "block";
        } else {
            $action = "run";
        }
    }

    $elsefornetwork = "";
    if($totallunchByemp && $lastEntry['entry_type'] !== 'punch_out'){

        if($netMinutes < 0 && $netSeconds <= 0){

            $elsefornetwork = $totalWorkedTime;
        }
        else{
            
            $elsefornetwork = $netWorkTime;
        }
    }
    
    // Prepare response
    $firstPunchFormatted = $firstEntry ? (new DateTime($firstEntry['entry_time'], $timezone))->format('H:i A') : 'N/A';
    
    

    $response = [
        'worktime' => $workTime,
        'punchTime' => $startTime->format('H:i:s A'),
        'action' => $action,
        'lunchDuation' => $lunchduration . "M",
        'punch_in' => $firstPunchFormatted,
        'total_hours' => $totalWorkedTime,
        'network' => ($lastEntry && isset($lastEntry['entry_type']) && $lastEntry['entry_type'] === 'punch_out')? $netWorkTime:$elsefornetwork,
        'totalLunchByemp' => $totallunchByemp,
        'late' => $lateresult,
        'isHalf' => $is_half,
        'com_half' => $com_half
    ];
    
    echo json_encode($response);
    exit;
}




if($click == "checkfirstpunchin"){
    // Step 1: Get the latest entry for the employee
    $time = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
$stmt->execute([$userid, "$time%"]);
$firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);

// Step 2: Check if punchOut is pending
// echo 1;
// exit;


if ( is_array($firstEntry) && (
    $firstEntry['entry_type'] == "regularization" || 
    $firstEntry['entry_type'] == "casual_leave" || 
    $firstEntry['entry_type'] == "sick_leave" || 
    $firstEntry['entry_type'] == "holiday")) {
    echo $firstEntry['entry_type'];
    // echo 1;
    exit;
}
else if ($firstEntry && $firstEntry['entry_type'] === 'punch_in') {    

        $time = (new DateTime('now', new DateTimeZone("Asia/Kolkata")))->format('Y-m-d');

        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC LIMIT 1");
        $stmt->execute([$userid, "$time%"]);
        $lastEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        if($lastEntry['entry_type'] == "punch_out"){
            echo 4;
            exit;
        }
        else if($lastEntry['entry_type'] == "lunch_start"){
            echo 2;
            exit;
        }
        
        else if($lastEntry['entry_type'] == "lunch_end"){
            echo 3;
            exit;
        }

        else if($lastEntry['entry_type'] == "regularization" 
        || $lastEntry['entry_type'] == "casual_leave"
        || $lastEntry['entry_type'] == "sick_leave"
        || $lastEntry['entry_type'] == "holiday"){

            echo $lastEntry['entry_type'];
        }
        
        else{
            echo 1;
            exit;
        }
    //  punchOut recorded 
    
} else {

    if( $firstEntry && $firstEntry['entry_type'] == "half_day" ){

        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time DESC LIMIT 1");
        $stmt->execute([$userid, "$time%"]);
        $lastEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        if($lastEntry['entry_type'] == "punch_in"){
            echo 1;
            exit;
        }
        if($lastEntry['entry_type'] == "punch_out"){
            echo 4;
            exit;
        }
        else if($lastEntry['entry_type'] == "lunch_start"){
            echo 2;
            exit;
        }
        
        else if($lastEntry['entry_type'] == "lunch_end"){
            echo 3;
            exit;
        }

        else if($lastEntry['entry_type'] == "regularization" 
        || $lastEntry['entry_type'] == "casual_leave"
        || $lastEntry['entry_type'] == "sick_leave"
        || $lastEntry['entry_type'] == "holiday"){

            echo $lastEntry['entry_type'];
        }
        
        else{
            echo 1;
            exit;
        }

    }
    
    else{
        echo 5;
        exit;

    }
}
}




//  <<<<<<<<<<<<<============= This is when the admin want to see the history of that perticular employee ============>>>>>>>>>>>>>>


if($click == "detailsById"){
    $userid = isset($_POST['id']) ? ($_POST['id']) : null;

    if($userid !== null){
        $timezone = new DateTimeZone("Asia/Kolkata");
        $today = (new DateTime('now', $timezone))->format('Y-m-d');

        // Get all unique dates for the current month
        $stmt = $pdo->prepare("SELECT DISTINCT DATE(entry_time) as entry_date 
                              FROM time_entries 
                              WHERE employee_id = ? 
                              AND MONTH(entry_time) = MONTH(CURRENT_DATE) 
                              AND YEAR(entry_time) = YEAR(CURRENT_DATE) 
                              ORDER BY entry_date ASC");
        $stmt->execute([$userid]);
        $uniqueDates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = '';
        
        // Get system settings for attendance calculation
        $stmt = $pdo->query("SELECT * FROM system_settings");
        $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $work_start_time_str = '09:00:00'; // default
        $late_threshold = 15; // default in minutes
        
        foreach($sys as $row){
            if($row['setting_key'] == "work_start_time"){
                $work_start_time_str = $row['setting_value'];
                $work_start_time= new DateTime($row['setting_value'], new DateTimeZone("Asia/Kolkata"));
                $checkstart = $work_start_time->getTimestamp();
            }
            if($row['setting_key'] == "late_threshold"){
                $late_threshold = intval($row['setting_value']);
            }
        }
        
        foreach($uniqueDates as $dateRow){
            $formatted_date = $dateRow['entry_date'];
            $entry_date = new DateTime($formatted_date, $timezone);

            // Get all entries for this specific date
            $stmt = $pdo->prepare("SELECT * FROM time_entries 
                                  WHERE employee_id = ? 
                                  AND DATE(entry_time) = ? 
                                  ORDER BY entry_time ASC");
            $stmt->execute([$userid, $formatted_date]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize variables
            $punchin = $lunchstart = $lunchend = $punchout = null;
            $total_worked_seconds = 0;
            $total_lunch_seconds = 0;
            $punch_in_time = null;
            $lunch_start_time = null;


            // checking for the work from home
            $checkwfh = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
            $checkwfh->execute([$userid, $formatted_date]);

            // Process all entries to calculate actual work time
            foreach($entries as $entry){
                switch($entry['entry_type']){
                    case "punch_in":
                        $punchin = $entry['entry_time'];
                        $punch_in_time = strtotime($entry['entry_time']);
                        break;
                    case "punch_out":
                        $punchout = $entry['entry_time'];
                        if ($punch_in_time !== null) {
                            $punch_out_time = strtotime($entry['entry_time']);
                            if ($punch_out_time > $punch_in_time) {
                                $total_worked_seconds += ($punch_out_time - $punch_in_time);
                            }
                            $punch_in_time = null;
                        }
                        break;
                    case "lunch_start":
                        $lunchstart = $entry['entry_time'];
                        $lunch_start_time = strtotime($entry['entry_time']);
                        break;
                    case "lunch_end":
                        $lunchend = $entry['entry_time'];
                        if ($lunch_start_time !== null) {
                            $lunch_end_time = strtotime($entry['entry_time']);
                            if ($lunch_end_time > $lunch_start_time) {
                                $total_lunch_seconds += ($lunch_end_time - $lunch_start_time);
                            }
                            $lunch_start_time = null;
                        }
                        break;
                }
            }

            // Handle incomplete sessions
            if ($lunch_start_time !== null) {
                // Handle incomplete lunch session
                $current_time = time();
                if ($current_time > $lunch_start_time) {
                    $total_lunch_seconds += ($current_time - $lunch_start_time);
                }
            }

            // Calculate net work time (work time minus lunch time)
            $net_work_seconds = $total_worked_seconds - $total_lunch_seconds;
            if ($net_work_seconds < 0) $net_work_seconds = 0;
            
            // Format the total time
            $worked_hours = floor($total_worked_seconds / 3600);
            $worked_minutes = floor(($total_worked_seconds % 3600) / 60);
            $worked_seconds = $total_worked_seconds % 60;
            $total_time = sprintf('%02d:%02d:%02d', $worked_hours, $worked_minutes, $worked_seconds);

            // Check for leave applications for this specific date
            $stmt = $pdo->prepare("SELECT * FROM applications 
                WHERE employee_id = ? AND status = 'approved'
                AND ? BETWEEN DATE(start_date) AND DATE(end_date) 
                ORDER BY created_at ASC LIMIT 1");
            $stmt->execute([$userid, $formatted_date]);
            $leaveRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // Determine attendance state
            $state = "Absent";
            $firstEntry = null;
            $lastEntry = null;

            if (!empty($entries)) {
                $firstEntry = $entries[0];
                $lastEntry = end($entries);

                // Check if employee is on leave/holiday (check the first entry type)
                if ($firstEntry && in_array($firstEntry['entry_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $firstEntry['entry_type'];
                }
                // Check if there's an approved leave record for this date
                elseif ($leaveRecord && in_array($leaveRecord['req_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $leaveRecord['req_type'];
                }
                // Check if employee punched in
                elseif ($firstEntry && $firstEntry['entry_type'] == "punch_in") {
                    // Check if employee is late - FIXED: Use the work start time for the specific date
                    $firstPunchTime = new DateTime($firstEntry['entry_time'], $timezone);
                    $firstPunchTimestamp = $firstPunchTime->getTimestamp();
                    
                    // Create expected start time for THIS specific date
                    $expectedStartTime = new DateTime(($formatted_date . ' ' . $work_start_time_str), new DateTimeZone("Asia/Kolkata"));
                    $checkstart = $expectedStartTime->getTimestamp();
                    // Calculate minutes late (positive value means late)
                    $minutesLate = ($firstPunchTimestamp - $checkstart) / 60;

                    if ($minutesLate <= $late_threshold) {
                        $state = "Present";
                    } else {
                        $state = "Late";
                    }
                }
                // Check for half day without punch in (approved half day leave)
                elseif ($leaveRecord && $leaveRecord['req_type'] == "half_day") {
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

            // Format timestamps for display
            $punchin_display = $punchin ? (new DateTime($punchin, $timezone))->format('H:i:s') : '-';
            $lunchstart_display = $lunchstart ? (new DateTime($lunchstart, $timezone))->format('H:i:s') : '-';
            $lunchend_display = $lunchend ? (new DateTime($lunchend, $timezone))->format('H:i:s') : '-';
            $punchout_display = $punchout ? (new DateTime($punchout, $timezone))->format('H:i:s') : '-';

            // Generate table row
            $output .= '<tr>
                <td style="color:blue;cursor:pointer;" data-date="'.$formatted_date.'" class="searchDate">'.$entry_date->format("M d, Y").'</td>
                <td>'.$punchin_display.'</td>
                <td>'.$lunchstart_display.'</td>
                <td>'.$lunchend_display.'</td>
                <td>'.$punchout_display.'</td>
                <td>'.$total_time.'</td>';

            if($checkwfh->rowCount() > 0){
                $output .= '<td><span class="status-badge status-Present" style="background-color: rgba(237, 225, 247, 1); color: purple;">WFH</span></td>';
            }
            else{
                if($state != "Present" && $state != "Late" && $state != "Absent"){
                    $output .= '<td><span class="status-badge status-Late" style="color: orange;">'.$state.'</span></td></tr>';
                } else {
                    $output .= '<td><span class="status-badge status-'.$state.'">'.$state.'</span></td></tr>';
                }

            }
        }

        if(!empty($output)){
            echo $output;
        } else {
            echo "<h2 style='position:absolute; color:red; margin-left: 20%;'>No Data Found!</h2>";
        }
    }
}




// This is for employees history last month details
if($click == "filterLastMonth"){
    $userid = isset($_POST['id']) ? ($_POST['id']) : null;

    $lastMonth = date('m', strtotime('-1 month'));
    $lastYear = date('Y', strtotime('-1 month'));

    if($userid !== null){
        $timezone = new DateTimeZone("Asia/Kolkata");
        $today = (new DateTime('now', $timezone))->format('Y-m-d');

        // Get all unique dates for the current month
        $stmt = $pdo->prepare("SELECT DISTINCT DATE(entry_time) as entry_date 
                              FROM time_entries 
                              WHERE employee_id = ? 
                              AND entry_time LIKE ?
                              ORDER BY entry_date ASC");
        $stmt->execute([$userid, "%$lastYear-$lastMonth%"]);
        $uniqueDates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = '';
        
        // Get system settings for attendance calculation
        $stmt = $pdo->query("SELECT * FROM system_settings");
        $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $work_start_time_str = '09:00:00'; // default
        $late_threshold = 15; // default in minutes
        
        foreach($sys as $row){
            if($row['setting_key'] == "work_start_time"){
                $work_start_time_str = $row['setting_value'];
                $work_start_time= new DateTime($row['setting_value'], new DateTimeZone("Asia/Kolkata"));
                $checkstart = $work_start_time->getTimestamp();
            }
            if($row['setting_key'] == "late_threshold"){
                $late_threshold = intval($row['setting_value']);
            }
        }
        
        foreach($uniqueDates as $dateRow){
            $formatted_date = $dateRow['entry_date'];
            $entry_date = new DateTime($formatted_date, $timezone);

            // Get all entries for this specific date
            $stmt = $pdo->prepare("SELECT * FROM time_entries 
                                  WHERE employee_id = ? 
                                  AND DATE(entry_time) = ? 
                                  ORDER BY entry_time ASC");
            $stmt->execute([$userid, $formatted_date]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize variables
            $punchin = $lunchstart = $lunchend = $punchout = null;
            $total_worked_seconds = 0;
            $total_lunch_seconds = 0;
            $punch_in_time = null;
            $lunch_start_time = null;

            // Process all entries to calculate actual work time
            foreach($entries as $entry){
                switch($entry['entry_type']){
                    case "punch_in":
                        $punchin = $entry['entry_time'];
                        $punch_in_time = strtotime($entry['entry_time']);
                        break;
                    case "punch_out":
                        $punchout = $entry['entry_time'];
                        if ($punch_in_time !== null) {
                            $punch_out_time = strtotime($entry['entry_time']);
                            if ($punch_out_time > $punch_in_time) {
                                $total_worked_seconds += ($punch_out_time - $punch_in_time);
                            }
                            $punch_in_time = null;
                        }
                        break;
                    case "lunch_start":
                        $lunchstart = $entry['entry_time'];
                        $lunch_start_time = strtotime($entry['entry_time']);
                        break;
                    case "lunch_end":
                        $lunchend = $entry['entry_time'];
                        if ($lunch_start_time !== null) {
                            $lunch_end_time = strtotime($entry['entry_time']);
                            if ($lunch_end_time > $lunch_start_time) {
                                $total_lunch_seconds += ($lunch_end_time - $lunch_start_time);
                            }
                            $lunch_start_time = null;
                        }
                        break;
                }
            }

            // Handle incomplete sessions
            if ($lunch_start_time !== null) {
                // Handle incomplete lunch session
                $current_time = time();
                if ($current_time > $lunch_start_time) {
                    $total_lunch_seconds += ($current_time - $lunch_start_time);
                }
            }

            // Calculate net work time (work time minus lunch time)
            $net_work_seconds = $total_worked_seconds - $total_lunch_seconds;
            if ($net_work_seconds < 0) $net_work_seconds = 0;
            
            // Format the total time
            $worked_hours = floor($total_worked_seconds / 3600);
            $worked_minutes = floor(($total_worked_seconds % 3600) / 60);
            $worked_seconds = $total_worked_seconds % 60;
            $total_time = sprintf('%02d:%02d:%02d', $worked_hours, $worked_minutes, $worked_seconds);

            // Check for leave applications for this specific date
            $stmt = $pdo->prepare("SELECT * FROM applications 
                WHERE employee_id = ? AND status = 'approved'
                AND ? BETWEEN DATE(start_date) AND DATE(end_date) 
                ORDER BY created_at ASC LIMIT 1");
            $stmt->execute([$userid, $formatted_date]);
            $leaveRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // Determine attendance state
            $state = "Absent";
            $firstEntry = null;
            $lastEntry = null;

            if (!empty($entries)) {
                $firstEntry = $entries[0];
                $lastEntry = end($entries);

                // Check if employee is on leave/holiday (check the first entry type)
                if ($firstEntry && in_array($firstEntry['entry_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $firstEntry['entry_type'];
                }
                // Check if there's an approved leave record for this date
                elseif ($leaveRecord && in_array($leaveRecord['req_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $leaveRecord['req_type'];
                }
                // Check if employee punched in
                elseif ($firstEntry && $firstEntry['entry_type'] == "punch_in") {
                    // Check if employee is late - FIXED: Use the work start time for the specific date
                    $firstPunchTime = new DateTime($firstEntry['entry_time'], $timezone);
                    $firstPunchTimestamp = $firstPunchTime->getTimestamp();
                    
                    // Create expected start time for THIS specific date
                    $expectedStartTime = new DateTime(($formatted_date . ' ' . $work_start_time_str), new DateTimeZone("Asia/Kolkata"));
                    $checkstart = $expectedStartTime->getTimestamp();
                    // Calculate minutes late (positive value means late)
                    $minutesLate = ($firstPunchTimestamp - $checkstart) / 60;

                    if ($minutesLate <= $late_threshold) {
                        $state = "Present";
                    } else {
                        $state = "Late";
                    }
                }
                // Check for half day without punch in (approved half day leave)
                elseif ($leaveRecord && $leaveRecord['req_type'] == "half_day") {
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

            // Format timestamps for display
            $punchin_display = $punchin ? (new DateTime($punchin, $timezone))->format('H:i:s') : '-';
            $lunchstart_display = $lunchstart ? (new DateTime($lunchstart, $timezone))->format('H:i:s') : '-';
            $lunchend_display = $lunchend ? (new DateTime($lunchend, $timezone))->format('H:i:s') : '-';
            $punchout_display = $punchout ? (new DateTime($punchout, $timezone))->format('H:i:s') : '-';

            // Generate table row
            $output .= '<tr>
                <td style="color:blue;cursor:pointer;" data-date="'.$formatted_date.'" class="searchDate">'.$entry_date->format("M d, Y").'</td>
                <td>'.$punchin_display.'</td>
                <td>'.$lunchstart_display.'</td>
                <td>'.$lunchend_display.'</td>
                <td>'.$punchout_display.'</td>
                <td>'.$total_time.'</td>';

            if($state != "Present" && $state != "Late" && $state != "Absent"){
                $output .= '<td><span class="status-badge status-Late" style="color: orange;">'.$state.'</span></td></tr>';
            } else {
                $output .= '<td><span class="status-badge status-'.$state.'">'.$state.'</span></td></tr>';
            }
        }

        if(!empty($output)){
            echo $output;
        } else {
            echo "<h2 style='position:absolute; color:red; margin-left: 20%;'>No Data Found! </h2>";
        }
    }
}


// This is for employees history custom month details
if($click == "filterCustom"){
    $userid = isset($_POST['id']) ? ($_POST['id']) : null;
    $to = isset($_POST['to']) ? (new DateTime($_POST['to'], new DateTimeZone("Asia/Kolkata")))->modify("+1 day") : null;
    $from = isset($_POST['from']) ? (new DateTime($_POST['from'], new DateTimeZone("Asia/Kolkata")))->format("Y-m-d") : null;



    $lastMonth = date('m', strtotime('-1 month'));
    $lastYear = date('Y', strtotime('-1 month'));

    if($userid !== null){
        $timezone = new DateTimeZone("Asia/Kolkata");
        $today = (new DateTime('now', $timezone))->format('Y-m-d');

        // Get all unique dates for the current month
        $stmt = $pdo->prepare("SELECT DISTINCT DATE(entry_time) as entry_date 
                              FROM time_entries 
                              WHERE employee_id = ? 
                              AND entry_time BETWEEN ? AND ?
                              ORDER BY entry_date ASC");
        $stmt->execute([$userid, $from, $to->format("Y-m-d")]);
        $uniqueDates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = '';
        
        // Get system settings for attendance calculation
        $stmt = $pdo->query("SELECT * FROM system_settings");
        $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $work_start_time_str = '09:00:00'; // default
        $late_threshold = 15; // default in minutes
        
        foreach($sys as $row){
            if($row['setting_key'] == "work_start_time"){
                $work_start_time_str = $row['setting_value'];
                $work_start_time= new DateTime($row['setting_value'], new DateTimeZone("Asia/Kolkata"));
                $checkstart = $work_start_time->getTimestamp();
            }
            if($row['setting_key'] == "late_threshold"){
                $late_threshold = intval($row['setting_value']);
            }
        }
        
        foreach($uniqueDates as $dateRow){
            $formatted_date = $dateRow['entry_date'];
            $entry_date = new DateTime($formatted_date, $timezone);

            // Get all entries for this specific date
            $stmt = $pdo->prepare("SELECT * FROM time_entries 
                                  WHERE employee_id = ? 
                                  AND DATE(entry_time) = ? 
                                  ORDER BY entry_time ASC");
            $stmt->execute([$userid, $formatted_date]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Initialize variables
            $punchin = $lunchstart = $lunchend = $punchout = null;
            $total_worked_seconds = 0;
            $total_lunch_seconds = 0;
            $punch_in_time = null;
            $lunch_start_time = null;

            // Process all entries to calculate actual work time
            foreach($entries as $entry){
                switch($entry['entry_type']){
                    case "punch_in":
                        $punchin = $entry['entry_time'];
                        $punch_in_time = strtotime($entry['entry_time']);
                        break;
                    case "punch_out":
                        $punchout = $entry['entry_time'];
                        if ($punch_in_time !== null) {
                            $punch_out_time = strtotime($entry['entry_time']);
                            if ($punch_out_time > $punch_in_time) {
                                $total_worked_seconds += ($punch_out_time - $punch_in_time);
                            }
                            $punch_in_time = null;
                        }
                        break;
                    case "lunch_start":
                        $lunchstart = $entry['entry_time'];
                        $lunch_start_time = strtotime($entry['entry_time']);
                        break;
                    case "lunch_end":
                        $lunchend = $entry['entry_time'];
                        if ($lunch_start_time !== null) {
                            $lunch_end_time = strtotime($entry['entry_time']);
                            if ($lunch_end_time > $lunch_start_time) {
                                $total_lunch_seconds += ($lunch_end_time - $lunch_start_time);
                            }
                            $lunch_start_time = null;
                        }
                        break;
                }
            }

            // Handle incomplete sessions
            if ($lunch_start_time !== null) {
                // Handle incomplete lunch session
                $current_time = time();
                if ($current_time > $lunch_start_time) {
                    $total_lunch_seconds += ($current_time - $lunch_start_time);
                }
            }

            // Calculate net work time (work time minus lunch time)
            $net_work_seconds = $total_worked_seconds - $total_lunch_seconds;
            if ($net_work_seconds < 0) $net_work_seconds = 0;
            
            // Format the total time
            $worked_hours = floor($total_worked_seconds / 3600);
            $worked_minutes = floor(($total_worked_seconds % 3600) / 60);
            $worked_seconds = $total_worked_seconds % 60;
            $total_time = sprintf('%02d:%02d:%02d', $worked_hours, $worked_minutes, $worked_seconds);

            // Check for leave applications for this specific date
            $stmt = $pdo->prepare("SELECT * FROM applications 
                WHERE employee_id = ? AND status = 'approved'
                AND ? BETWEEN DATE(start_date) AND DATE(end_date) 
                ORDER BY created_at ASC LIMIT 1");
            $stmt->execute([$userid, $formatted_date]);
            $leaveRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // Determine attendance state
            $state = "Absent";
            $firstEntry = null;
            $lastEntry = null;

            if (!empty($entries)) {
                $firstEntry = $entries[0];
                $lastEntry = end($entries);

                // Check if employee is on leave/holiday (check the first entry type)
                if ($firstEntry && in_array($firstEntry['entry_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $firstEntry['entry_type'];
                }
                // Check if there's an approved leave record for this date
                elseif ($leaveRecord && in_array($leaveRecord['req_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
                    $state = $leaveRecord['req_type'];
                }
                // Check if employee punched in
                elseif ($firstEntry && $firstEntry['entry_type'] == "punch_in") {
                    // Check if employee is late - FIXED: Use the work start time for the specific date
                    $firstPunchTime = new DateTime($firstEntry['entry_time'], $timezone);
                    $firstPunchTimestamp = $firstPunchTime->getTimestamp();
                    
                    // Create expected start time for THIS specific date
                    $expectedStartTime = new DateTime(($formatted_date . ' ' . $work_start_time_str), new DateTimeZone("Asia/Kolkata"));
                    $checkstart = $expectedStartTime->getTimestamp();
                    // Calculate minutes late (positive value means late)
                    $minutesLate = ($firstPunchTimestamp - $checkstart) / 60;

                    if ($minutesLate <= $late_threshold) {
                        $state = "Present";
                    } else {
                        $state = "Late";
                    }
                }
                // Check for half day without punch in (approved half day leave)
                elseif ($leaveRecord && $leaveRecord['req_type'] == "half_day") {
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

            // Format timestamps for display
            $punchin_display = $punchin ? (new DateTime($punchin, $timezone))->format('H:i:s') : '-';
            $lunchstart_display = $lunchstart ? (new DateTime($lunchstart, $timezone))->format('H:i:s') : '-';
            $lunchend_display = $lunchend ? (new DateTime($lunchend, $timezone))->format('H:i:s') : '-';
            $punchout_display = $punchout ? (new DateTime($punchout, $timezone))->format('H:i:s') : '-';

            // Generate table row
            $output .= '<tr>
                <td style="color:blue;cursor:pointer;" data-date="'.$formatted_date.'" class="searchDate">'.$entry_date->format("M d, Y").'</td>
                <td>'.$punchin_display.'</td>
                <td>'.$lunchstart_display.'</td>
                <td>'.$lunchend_display.'</td>
                <td>'.$punchout_display.'</td>
                <td>'.$total_time.'</td>';

            if($state != "Present" && $state != "Late" && $state != "Absent"){
                $output .= '<td><span class="status-badge status-Late" style="color: orange;">'.$state.'</span></td></tr>';
            } else {
                $output .= '<td><span class="status-badge status-'.$state.'">'.$state.'</span></td></tr>';
            }
        }

        if(!empty($output)){
            echo $output;
        } else {
            echo "<h2 style='position:absolute; color:red; margin-left: 20%;'>No Data Found! </h2>";
        }
    }
}






?>