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

$info = isset($_POST['info'])?$_POST['info']: "";

if($info == 'filterLastMonth'){

    $id = isset($_POST['id'])?($_POST['id']):null;
    $lastMonth = date('m', strtotime('-1 month'));
    $lastYear = date('Y', strtotime('-1 month'));

    if($id !== null){

        $sql = "SELECT * FROM time_entries WHERE employee_id = ? AND MONTH(entry_time) = ? AND YEAR(entry_time) = ? GROUP BY DATE(entry_time)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $lastMonth, $lastYear]);
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);




        $output = '';
        $total = null;  
        foreach($getuserid as $row){

                $entry_date = new DateTime($row['entry_time'], new DateTimeZone("asia/kolkata"));
                $formatted_date = $entry_date->format('Y-m-d'); 


                $stmt = $pdo->prepare("SELECT a.id, b.* FROM employees a LEFT JOIN time_entries b ON a.id = b.employee_id WHERE b.employee_id = ? AND DAY(entry_time) = DAY(?) AND MONTH(entry_time) = MONTH(?) 
                AND YEAR(entry_time) = YEAR(?)  ORDER BY b.entry_time ASC ");
    
                $stmt->execute([$row['employee_id'],  $formatted_date, $formatted_date, $formatted_date]);
                $getdetail = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


            $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
            $stmt->execute([$row['employee_id'], "$formatted_date%"]);
            $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);

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

                if($lastEntry['entry_type'] == "regularization" || $lastEntry['entry_type'] == "casual_leave" || $lastEntry['entry_type'] == "sick_leave" || $lastEntry['entry_type'] == "holiday"){
                    
                        $state = $lastEntry['entry_type'];
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
         
        
            if($lastEntry['entry_type'] == "regularization" || $lastEntry['entry_type'] == "casual_leave" || $lastEntry['entry_type'] == "sick_leave" || $lastEntry['entry_type'] == "holiday"){
                
                  
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





if($info == 'filterCustom'){

    $id = isset($_POST['id'])?($_POST['id']):null;
    $from = isset($_POST['from'])?(new DateTime($_POST['from'], new DateTimeZone("asia/kolkata")))->format("Y-m-d H:i:s"): "";
    $to = isset($_POST['to'])?(new DateTime($_POST['to'], new DateTimeZone("asia/kolkata")))->format("Y-m-d H:i:s"): "";

    if($id !== null){

        $sql = "SELECT * FROM time_entries WHERE employee_id = ? AND entry_time BETWEEN ? AND ? GROUP BY DATE(entry_time)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $from, $to]);
        $getuserid = $stmt->fetchAll(PDO::FETCH_ASSOC);




        $output = '';
        $total = null;  
        foreach($getuserid as $row){

                $entry_date = new DateTime($row['entry_time'], new DateTimeZone("asia/kolkata"));
                $formatted_date = $entry_date->format('Y-m-d'); 


                $stmt = $pdo->prepare("SELECT a.id, b.* FROM employees a LEFT JOIN time_entries b ON a.id = b.employee_id WHERE b.employee_id = ? AND DAY(entry_time) = DAY(?) AND MONTH(entry_time) = MONTH(?) 
                AND YEAR(entry_time) = YEAR(?)  ORDER BY b.entry_time ASC ");
    
                $stmt->execute([$row['employee_id'],  $formatted_date, $formatted_date, $formatted_date]);
                $getdetail = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


            $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND entry_time LIKE ? ORDER BY entry_time ASC LIMIT 1");
            $stmt->execute([$row['employee_id'], "$formatted_date%"]);
            $firstEntry = $stmt->fetch(PDO::FETCH_ASSOC);

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

                if($lastEntry['entry_type'] == "regularization" || $lastEntry['entry_type'] == "casual_leave" || $lastEntry['entry_type'] == "sick_leave" || $lastEntry['entry_type'] == "holiday"){
                    
                        $state = $lastEntry['entry_type'];
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
         
        
            if($lastEntry['entry_type'] == "regularization" || $lastEntry['entry_type'] == "casual_leave" || $lastEntry['entry_type'] == "sick_leave" || $lastEntry['entry_type'] == "holiday"){
                
                  
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
?>