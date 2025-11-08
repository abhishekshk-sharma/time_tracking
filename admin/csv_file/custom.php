<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=attendance.csv");

require_once "../includes/config.php";

// Session validation
// session_start(); // Add session start
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 10800) {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit;
}

// Get date range from POST data
$fromDate = isset($_POST['from']) ? $_POST['from'] : date('Y-m-01'); // Default to start of current month
$toDate = isset($_POST['to']) ? $_POST['to'] : date('Y-m-d', strtotime('-1 day')); // Default to yesterday

// Validate dates
if (!strtotime($fromDate) || !strtotime($toDate) || strtotime($fromDate) > strtotime($toDate)) {
    die("Invalid date range provided.");
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
function isWeekend($date) {
    $dayOfWeek = $date->format('N'); // 1=Monday, 7=Sunday
    $day = (int)$date->format('d');
    $month = (int)$date->format('m');
    $year = (int)$date->format('Y');
    
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
function isAfterResignation($date, $resignationDate) {
    if (!$resignationDate) {
        return false;
    }
    
    $currentDate = new DateTime($date, new DateTimeZone("asia/kolkata"));
    $resignation = new DateTime($resignationDate, new DateTimeZone("asia/kolkata"));
    
    return $currentDate > $resignation;
}

// Function to generate all dates between from and to dates
function getDateRange($from, $to) {
    $dates = [];
    $current = new DateTime($from, new DateTimeZone("asia/kolkata"));
    $end = new DateTime($to, new DateTimeZone("asia/kolkata"));
    
    while ($current <= $end) {
        $dates[] = $current->format('Y-m-d');
        $current->modify('+1 day');
    }
    
    return $dates;
}

// Create CSV output
$output = fopen("php://output", "w");


fputcsv($output, ["Attendance Report"]);
fputcsv($output, ["Date Range: $fromDate to $toDate"]);




// Get all dates in the range
$dateRange = getDateRange($fromDate, $toDate);

// Create header row with dates and summary columns
$headerRow = ['Employee Name'];
foreach ($dateRange as $date) {
    $dateObj = new DateTime($date, new DateTimeZone("asia/kolkata"));
    // Use day number only to avoid Excel auto-formatting issues
    $headerRow[] = 'Day ' . $dateObj->format('d'); // Format as "Day 01", "Day 02", etc.
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
    
    foreach ($dateRange as $date) {
        $dateObj = new DateTime($date, new DateTimeZone("asia/kolkata"));
        
        // Check if employee has resigned and this day is after resignation
        if ($resignationDate && isAfterResignation($date, $resignationDate)) {
            $row[] = "No longer with organization";
            continue;
        }
        
        // Check if weekend
        if (isWeekend($dateObj)) {
            $row[] = "Holiday";
            $counters['holiday']++;
            continue;
        }
        
        // Check attendance record
        $sql = "SELECT * FROM time_entries 
                WHERE employee_id = ? 
                AND DATE(entry_time) = ? 
                GROUP BY DATE(entry_time)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employee['emp_id'], $date]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($attendance)) {
            $row[] = "A"; // Absent
            $counters['absent']++;
        } else {
            foreach ($attendance as $record) {
                $workedHours = getWorkedHours($pdo, $employee['emp_id'], $dateObj->format('Y-m-d'));

                $stmt = $pdo->prepare("SELECT * FROM applications WHERE employee_id = ? AND start_date = ? AND status = 'approved' AND req_type = 'half_day'");
                $stmt->execute([$employee['emp_id'], $dateObj->format('Y-m-d')]);
                $checkhlafdate = $stmt->fetch(PDO::FETCH_ASSOC);

                $wfhstmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
                $wfhstmt->execute([$employee['emp_id'], $dateObj->format('Y-m-d')]);

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