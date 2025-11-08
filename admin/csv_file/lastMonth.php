<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=attendance.csv");

require_once "../includes/config.php";

// Session validation
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 10800) {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit;
}

// Function to get second and fourth Saturdays of a month
function getSpecialSaturdays($year, $month) {
    $saturdays = [];
    $date = new DateTime("$year-$month-01", new DateTimeZone("asia/kolkata"));
    
    // Find all Saturdays in the month
    while ((int)$date->format('m') === (int)$month) {
        if ($date->format('N') == 6) { // 6 represents Saturday
            $saturdays[] = (int)$date->format('d');
        }
        $date->modify('+1 day');
    }
    
    // Return only the second and fourth Saturdays
    return [
        isset($saturdays[1]) ? $saturdays[1] : null,
        isset($saturdays[3]) ? $saturdays[3] : null
    ];
}

// Function to check if a day is a weekend (Sunday or special Saturday)
function isWeekend($day, $year, $month) {
    $date = new DateTime("$year-$month-$day", new DateTimeZone("asia/kolkata"));
    $dayOfWeek = $date->format('N'); // 1=Monday, 7=Sunday
    
    // Check if Sunday
    if ($dayOfWeek == 7) {
        return true;
    }
    
    // Check if second or fourth Saturday
    if ($dayOfWeek == 6) { // Saturday
        list($secondSat, $fourthSat) = getSpecialSaturdays($year, $month);
        return ($day == $secondSat || $day == $fourthSat);
    }
    
    return false;
}

// Function to check if employee has resigned and day is after resignation
function isAfterResignation($day, $year, $month, $resignationDate) {
    if (!$resignationDate) {
        return false;
    }
    
    $currentDate = new DateTime("$year-$month-$day", new DateTimeZone("asia/kolkata"));
    $resignation = new DateTime($resignationDate, new DateTimeZone("asia/kolkata"));
    
    return $currentDate > $resignation;
}

// Get previous month and year
$lastMonth = date('m', strtotime('-1 month'));
$lastYear = date('Y', strtotime('-1 month'));

$headerDate = sprintf('%02d%02d', $lastMonth,$lastYear);

// Get days in the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $lastMonth, $lastYear);

// Create CSV output
$output = fopen("php://output", "w");


fputcsv($output, ["Attendance Report"]);
fputcsv($output, ["Generated for: " . date('F Y', strtotime('-1 month'))]);


// Create header row with dates and summary columns
$headerRow = ['Employee Name'];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $headerRow[] = sprintf('%02d', $day); // Format as two-digit day
}

// Add summary headers
$headerRow = array_merge($headerRow, ['Sick_Leave', 'Casual_leave', 'Half_Day', 'Holiday', 'regularization', 'Absent', 'WFH', 'Short_Attendance', 'Present', 'Total']);
fputcsv($output, $headerRow);

// Fetch all employees
$stmt = $pdo->query("SELECT * FROM employees WHERE role != 'admin' AND username != 'dummy'");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);



$settingsStmt = $pdo->query("SELECT * FROM system_settings");
$settings = $settingsStmt->fetchAll(PDO::FETCH_ASSOC);

$sys_startTime = "";
$sys_endTime = "";
$sys_halfDay = "";
$min_working_hours = "";
foreach($settings as $row){
    if($row['setting_key'] == "work_start_time"){
        $sys_startTime = $row['setting_value'];
    }
    if($row['setting_key'] == "work_end_time"){
        $sys_endTime = $row['setting_value'];
    }
    if($row['setting_key'] == "half_day_time"){
        $sys_halfDay = $row['setting_value'];
    }
    if($row['setting_key'] == "min_working_hours"){
        $min_working_hours = $row['setting_value'];
    }
}
$fullDayHours = (strtotime($sys_endTime) - strtotime($sys_startTime)) / 3600;

list($hours, $minutes) = explode(":", $sys_halfDay);

$halfDayHours = intval($hours) + (intval($minutes) / 60);

list($hours, $minutes) = explode(":", $min_working_hours);
$minWorkingHours = intval($hours) + (intval($minutes) / 60);

function getWorkedHours($pdo, $empId, $date) {
    $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND DATE(entry_time) = ? ORDER BY entry_time ASC");
    $stmt->execute([$empId, $date]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalSeconds = 0;
    $lastIn = null;

    foreach ($entries as $entry) {
        if ($entry['entry_type'] === 'punch_in') {
            $lastIn = strtotime($entry['entry_time']);
        } elseif ($entry['entry_type'] === 'punch_out' && $lastIn !== null) {
            $out = strtotime($entry['entry_time']);
            $totalSeconds += max(0, $out - $lastIn);
            $lastIn = null;
        }
    }

    return $totalSeconds / 3600; // convert to hours
}




// Process each employee
foreach ($employees as $employee) {
    $row = [$employee['username']];
    
    // Initialize counters
    $counters = [
        'sick_leave' => 0,
        'casual_leave' => 0,
        'half_day' => 0,
        'holiday' => 0,
        'regularization' => 0,
        'absent' => 0,
        'Short_Attendance' => 0,
        'present' => 0,
        'wfh' => 0  
    ];
    
    // Get resignation date if exists
    $resignationDate = $employee['end_date'] ?? null;
    
    for ($day = 1; $day <= $daysInMonth; $day++) {
        // Check if employee has resigned and this day is after resignation
        if ($resignationDate && isAfterResignation($day, $lastYear, $lastMonth, $resignationDate)) {
            $row[] = "No longer with organization";
            continue;
        }
        
        // Check if weekend
        if (isWeekend($day, $lastYear, $lastMonth)) {
            $row[] = "Holiday";
            $counters['holiday']++;
            continue;
        }
        
        // Check attendance record
        $sql = "SELECT * FROM time_entries 
                WHERE employee_id = ? 
                AND DAY(entry_time) = ? 
                AND MONTH(entry_time) = ? 
                AND YEAR(entry_time) = ? 
                GROUP BY DATE(entry_time)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employee['emp_id'], $day, $lastMonth, $lastYear]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($attendance)) {
            $row[] = "A"; // Absent
            $counters['absent']++;
        } else {
            foreach ($attendance as $record) {
                $dateStr = "$lastYear-$lastMonth-" . sprintf('%02d', $day);
                $workedHours = getWorkedHours($pdo, $employee['emp_id'], $dateStr);

                $stmt = $pdo->prepare("SELECT * FROM applications WHERE employee_id = ? AND start_date = ? AND status = 'approved' AND req_type = 'half_day'");
                $stmt->execute([$employee['emp_id'], $dateStr]);
                $checkhlafdate = $stmt->fetch(PDO::FETCH_ASSOC);

                $wfhstmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
                $wfhstmt->execute([$employee['emp_id'], $dateStr]);

                if ($record['entry_type'] === 'sick_leave') {
                    $row[] = "SL";
                    $counters['sick_leave']++;
                } elseif ($record['entry_type'] === 'casual_leave') {
                    $row[] = "CL";
                    $counters['casual_leave']++;
                } elseif ($record['entry_type'] === 'holiday') {
                    $row[] = "Holiday";
                    $counters['holiday']++;
                } elseif ($record['entry_type'] === 'regularization') {
                    $row[] = "Regularization";
                    $counters['regularization']++;
                } elseif ($checkhlafdate) {

                    if($checkhlafdate['req_type'] === 'half_day' && $checkhlafdate['status'] === 'approved') {
                        if ($workedHours >= $halfDayHours) {
                            $row[] = "H";
                            $counters['half_day'] += 0.5;
                        } else {
                            $row[] = "Short Half Attendance";
                        }

                    }
                }elseif($wfhstmt->rowCount() > 0){
                    $row[] = "WFH";
                    $counters['wfh']++;
                } else {
                    if ($workedHours >= $fullDayHours || $workedHours >= $minWorkingHours) {
                        $row[] = "P";
                        $counters['present']++;
                    } elseif ($workedHours >= $halfDayHours) {
                        $row[] = "H";
                        $counters['half_day'] += 0.5;
                    } else if($workedHours < $halfDayHours){
                        $row[] = "Short Attendance";
                        $counters['Short_Attendance']++;
                    }else {
                        $row[] = "A"; // Absent
                        $counters['absent']++;
                    }
                }
            }
        }
    }
    
    // Add summary data to the row
    $row[] = $counters['sick_leave'];
    $row[] = $counters['casual_leave'];
    $row[] = $counters['half_day'];
    $row[] = $counters['holiday'];
    $row[] = $counters['regularization'];
    $row[] = $counters['absent'];
    $row[] = $counters['wfh'];
    $row[] = $counters['Short_Attendance'];
    $row[] = $counters['present'];
    $row[] = array_sum([$counters['sick_leave'], $counters['casual_leave'], $counters['half_day'], $counters['regularization'], $counters['present'], $counters['wfh'], $counters['holiday']]);

    fputcsv($output, $row);
}

fclose($output);

exit;
?>