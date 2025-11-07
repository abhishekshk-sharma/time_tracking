<?php
require_once "includes/config.php";

$userID = isset($_SESSION['id']) ? $_SESSION['id'] : null;
if ($userID == null) {
    header("location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
$stmt->execute([$userID]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

// Get current month and year for the calendar
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Calculate first and last day of the month
$firstDayOfMonth = date("{$currentYear}-{$currentMonth}-01");
$lastDayOfMonth = date("{$currentYear}-{$currentMonth}-t");

// Get all time entries for the current month
$timeEntriesStmt = $pdo->prepare("
    SELECT te.*, e.full_name 
    FROM time_entries te 
    JOIN employees e ON te.employee_id = e.emp_id 
    WHERE e.emp_id = ? AND DATE(te.entry_time) BETWEEN ? AND ?
    ORDER BY te.entry_time
");
$timeEntriesStmt->execute([$userID, $firstDayOfMonth, $lastDayOfMonth]);
$timeEntries = $timeEntriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all leave applications for the current month
$leaveStmt = $pdo->prepare("
    SELECT la.*, e.full_name 
    FROM applications la 
    JOIN employees e ON la.employee_id = e.emp_id 
    WHERE e.emp_id = ? AND la.status = 'approved' 
    AND req_type IN ('casual_leave', 'sick_leave', 'half_day', 'regularization', 'punch_Out_regularization')
    AND (
        (la.start_date BETWEEN ? AND ?) 
        OR (la.end_date BETWEEN ? AND ?)
        OR (la.start_date <= ? AND la.end_date >= ?)
    )
");
$leaveStmt->execute([$userID, $firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth]);
$leaveApplications = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);

// Get system settings for work hours and late threshold
$settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings");
$settingsStmt->execute();
$settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$workStartTime = $settings['work_start_time'] ?? '09:00:00';
$lateThreshold = isset($settings['late_threshold']) ? (int)$settings['late_threshold'] : 15; // in minutes

// Organize data by date for easier display
$dateWiseData = [];

// Process time entries
foreach ($timeEntries as $entry) {
    $date = date('Y-m-d', strtotime($entry['entry_time']));
    if (!isset($dateWiseData[$date])) {
        $dateWiseData[$date] = [];
    }
    
    if (!isset($dateWiseData[$date][$entry['employee_id']])) {
        $dateWiseData[$date][$entry['employee_id']] = [
            'name' => $entry['full_name'],
            'punch_in' => null,
            'punch_out' => null,
            'lunch_start' => null,
            'lunch_end' => null,
            'status' => 'absent', // Default status
            'is_late' => false
        ];
    }
    
    // Record the entries
    if ($entry['entry_type'] === 'punch_in') {
        $dateWiseData[$date][$entry['employee_id']]['punch_in'] = $entry['entry_time'];
        
        // Check if employee is late
        $punchInTime = strtotime($entry['entry_time']);
        $expectedStartTime = strtotime(date('Y-m-d', $punchInTime) . ' ' . $workStartTime);
        $minutesLate = ($punchInTime - $expectedStartTime) / 60;

        $wfhstmt = $pdo->prepare("SELECT * FROM wfh WHERE employee_id = ? AND DATE(`date`) = ?");
        $wfhstmt->execute([$entry['employee_id'], $date]);

        if($wfhstmt->rowCount() > 0){
            $dateWiseData[$date][$entry['employee_id']]['status'] = 'wfh';
        } elseif ($minutesLate > $lateThreshold) {

            $dateWiseData[$date][$entry['employee_id']]['is_late'] = true;
            $dateWiseData[$date][$entry['employee_id']]['status'] = 'late';
            
        } else {

            $dateWiseData[$date][$entry['employee_id']]['status'] = 'present';

        }
    } elseif ($entry['entry_type'] === 'punch_out') {
        $dateWiseData[$date][$entry['employee_id']]['punch_out'] = $entry['entry_time'];
    } elseif ($entry['entry_type'] === 'lunch_start') {
        $dateWiseData[$date][$entry['employee_id']]['lunch_start'] = $entry['entry_time'];
    } elseif ($entry['entry_type'] === 'lunch_end') {
        $dateWiseData[$date][$entry['employee_id']]['lunch_end'] = $entry['entry_time'];
    } elseif ($entry['entry_type'] === 'half_day') {
        $dateWiseData[$date][$entry['employee_id']]['status'] = 'half_day';
    } elseif ($entry['entry_type'] === 'holiday') {
        $dateWiseData[$date][$entry['employee_id']]['status'] = 'holiday';
    }
}

// Process leave applications
foreach ($leaveApplications as $leave) {
    $startDate = new DateTime($leave['start_date']);
    $endDate = new DateTime($leave['end_date']);
    $endDate->modify('+1 day'); // Include the end date
    
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($startDate, $interval, $endDate);
    
    foreach ($dateRange as $date) {
        $dateStr = $date->format('Y-m-d');
        if (!isset($dateWiseData[$dateStr])) {
            $dateWiseData[$dateStr] = [];
        }
        
        if (!isset($dateWiseData[$dateStr][$leave['employee_id']])) {
            $dateWiseData[$dateStr][$leave['employee_id']] = [
                'name' => $leave['full_name'],
                'status' => $leave['req_type'],
                'is_late' => false
            ];
            echo "<script> console.log(" . json_encode($endDate )   . ");</script>";
        } else {
            // Only override if not already marked as present/late
            if (!in_array($dateWiseData[$dateStr][$leave['employee_id']]['status'], ['present', 'late'])) {
                $dateWiseData[$dateStr][$leave['employee_id']]['status'] = $leave['req_type'];
            }
        }
    }
}

// Function to get detailed information for a specific date
function getDateDetails($date, $userID, $pdo, $workStartTime, $lateThreshold) {
    $details = [
        'status' => 'absent',
        'entries' => [],
        'leave_info' => null,
        'is_holiday' => false,
        'is_weekend' => false,
        'is_late' => false,
        'punch_in_time' => null,
        'work_start_time' => $workStartTime
    ];
    
    // Check if it's a weekend
    $dayOfWeek = date("N", strtotime($date));
    $isSunday = ($dayOfWeek == 7);
    $isSaturday = ($dayOfWeek == 6);
    
    // Check if it's 2nd or 4th Saturday
    $dayOfMonth = date("j", strtotime($date));
    $firstDayOfMonth = date("N", strtotime(date("Y-m-01", strtotime($date))));
    $weekOfMonth = floor(($dayOfMonth + $firstDayOfMonth - 1) / 7) + 1;
    $isSecondSaturday = ($isSaturday && $weekOfMonth == 2);
    $isFourthSaturday = ($isSaturday && $weekOfMonth == 4);
    
    $details['is_weekend'] = $isSunday || $isSecondSaturday || $isFourthSaturday;
    
    // Get time entries for this date
    $timeEntriesStmt = $pdo->prepare("
        SELECT * FROM time_entries 
        WHERE employee_id = ? AND DATE(entry_time) = ?
        ORDER BY entry_time
    ");
    $timeEntriesStmt->execute([$userID, $date]);
    $timeEntries = $timeEntriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($timeEntries) > 0) {
        $details['entries'] = $timeEntries;
        
        // Check if it's a holiday
        foreach ($timeEntries as $entry) {
            if ($entry['entry_type'] === 'holiday') {
                $details['status'] = 'holiday';
                $details['is_holiday'] = true;
                // Extract holiday info from notes
                if (strpos($entry['notes'], 'Holiday: ') === 0) {
                    $notes = substr($entry['notes'], 9);
                    $parts = explode(' - ', $notes, 2);
                    $details['holiday_title'] = $parts[0];
                    $details['holiday_description'] = count($parts) > 1 ? $parts[1] : '';
                }
                break;
            }
        }
        
        // If not holiday, check other statuses
        if (!$details['is_holiday']) {
            $hasPunchIn = false;
            $hasPunchOut = false;
            
            foreach ($timeEntries as $entry) {
                if ($entry['entry_type'] === 'punch_in') {
                    $hasPunchIn = true;
                    $details['punch_in_time'] = date('H:i:s', strtotime($entry['entry_time']));
                    
                    // Check if employee is late
                    $punchInTime = strtotime($entry['entry_time']);
                    $expectedStartTime = strtotime(date('Y-m-d', $punchInTime) . ' ' . $workStartTime);
                    $minutesLate = ($punchInTime - $expectedStartTime) / 60;
                    
                    if ($minutesLate > $lateThreshold) {
                        $details['is_late'] = true;
                        $details['status'] = 'late';
                    } else {
                        $details['status'] = 'present';
                    }
                }
                if ($entry['entry_type'] === 'punch_out') $hasPunchOut = true;
                if ($entry['entry_type'] === 'half_day') $details['status'] = 'half_day';
            }
            
            if ($hasPunchIn && $hasPunchOut && !$details['is_late']) {
                $details['status'] = 'present';
            } elseif ($hasPunchIn && !$hasPunchOut && !$details['is_late']) {
                $details['status'] = 'present (no punch out)';
            }
        }
    }
    
    // Check for leave applications
    $leaveStmt = $pdo->prepare("
        SELECT * FROM applications 
        WHERE employee_id = ? AND status = 'approved'
        AND ? BETWEEN start_date AND end_date
    ");
    $leaveStmt->execute([$userID, $date]);
    $leave = $leaveStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($leave) {
        // Only set leave status if not already present/late
        if (!in_array($details['status'], ['present', 'late', 'present (no punch out)'])) {
            $details['status'] = $leave['req_type'];
        }
        $details['leave_info'] = $leave;
    }
    
    // If no data but it's a weekend, mark as holiday
    if ($details['status'] === 'absent' && $details['is_weekend']) {
        $details['status'] = 'holiday';
    }
    
    
    $inputDate = new DateTime($date); // your date
    $today = new DateTime(); // current date and time

    if (($inputDate > $today) && !$details['is_weekend']) {
        $details['status'] = 'No Data';
    } 
    
    return $details;
}

// Handle AJAX request for date details
if (isset($_GET['get_date_details']) && isset($_GET['date'])) {
    $date = $_GET['date'];
    $details = getDateDetails($date, $userID, $pdo, $workStartTime, $lateThreshold);
    
    header('Content-Type: application/json');
    echo json_encode($details);
    exit;
}

// Now in your calendar display code, you can use:
foreach ($dateWiseData as $date => $employees) {
    foreach ($employees as $employeeId => $data) {
        $status = $data['status'];
        $isLate = $data['is_late'];
        
        // Add 'late' class if employee was late
        $statusClass = 'activity-' . $status;
        if ($isLate) {
            $statusClass .= ' activity-late';
        }
        
        // echo '<span class="day-activity ' . $statusClass . '" title="' . ucfirst($status) . ($isLate ? ' (Late)' : '') . '">';
        // echo substr($status, 0, 10) . ($isLate ? ' (Late)' : '');
        // echo '</span>';
    }
}
?>

<?php require_once "includes/header.php"; ?>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #3498db;
        --accent: #e74c3c;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --success: #2ecc71;
        --warning: #f39c12;
        --danger: #e74c3c;
        --gray: #95a5a6;
        --present: #2ecc71;
        --absent: #e74c3c;
        --halfday: #f39c12;
        --casual: #9b59b6;
        --sick: #3498db;
        --holiday: #1abc9c;
        --weekend: #7f8c8d;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background-color: #f5f7fa;
        color: #333;
        line-height: 1.6;
    }
    
    .container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .main-content {
        position: relative;
        display: grid;
        grid-template-columns: 1fr 3fr;
        gap: 20px;
        left: -8vw;
        margin-left: 8%;
    }
    
    .sidebar {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        height: fit-content;
    }
    
    .nav-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 8px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--dark);
        text-decoration: none;
    }
    
    .nav-item:hover {
        background-color: #f0f5ff;
        color: var(--secondary);
    }
    
    .nav-item.active {
        background-color: #e1ebff;
        color: var(--secondary);
        font-weight: 600;
    }
    
    .nav-item i {
        margin-right: 10px;
        font-size: 18px;
    }
    
    .content-area {
        position:relative;
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow-x: scroll;
        width: 100%;
    }
    /* .content-area::-webkit-scrollbar{
        display: none;
    }
     */
    .section-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: var(--secondary);
    }

    
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .month-navigation {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .nav-btn {
        background: var(--secondary);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        text-decoration: none;
    }
    
    .current-month {
        font-size: 20px;
        font-weight: 600;
        min-width: 180px;
        text-align: center;
    }
    
    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .calendar-day-header {
        text-align: center;
        padding: 10px;
        font-weight: 600;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .calendar-day {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 10px;
        min-height: 120px;
        position: relative;
    }
    
    .day-number {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .today .day-number {
        background: var(--secondary);
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .weekend {
        background: #f0f0f0;
    }
    
    .day-activity {
        font-size: 11px;
        padding: 3px 6px;
        border-radius: 4px;
        margin-top: 5px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .activity-present {
        background-color: #e7f6e9;
        color: var(--present);
    }
    .activity-late {
        background-color: #f6e8e7ff;
        color: var(--warning);
    }
    
    .activity-absent {
        background-color: #fbebec;
        color: var(--absent);
    }
    
    .activity-half_day {
        background-color: #fff4e6;
        color: var(--halfday);
    }
    
    .activity-casual_leave {
        background-color: #f3e8fd;
        color: var(--casual);
    }
    
    .activity-sick_leave {
        background-color: #e1f0ff;
        color: var(--sick);
    }
    .activity-regularization {
        background-color: rgba(225, 226, 255, 1);
        color: var(--sick);
    }
    
    .activity-holiday {
        background-color: #e0f6f2;
        color: var(--holiday);
    }
    .activity-wfh {
        background-color: #def2f8ff;
        color: var(--secondary);
    }
    
    .non-working-day {
        background: #f5f5f5;
        opacity: 0.7;
    }
    
    .legend {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
    }
    
    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
    }
    
    .legend-present {
        background: #e7f6e9;
    }
    
    .legend-absent {
        background: #fbebec;
    }
    
    .legend-halfday {
        background: #fff4e6;
    }
    
    .legend-casual {
        background: #f3e8fd;
    }
    
    .legend-sick {
        background: #e1f0ff;
    }
    
    .legend-holiday {
        background: #e0f6f2;
    }
    
    .legend-weekend {
        background: #f0f0f0;
    }
    .legend-wfh {
        background: #def2f8ff;
    }
    
    .employee-list {
        margin-top: 10px;
        max-height: 80px;
        overflow-y: auto;
    }
    
    .employee-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 10px;
        margin-bottom: 2px;
    }
    
    .employee-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 70%;
    }
    
    .employee-status {
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 9px;
    }
    
    @media (max-width: 1024px) {
        .calendar {
            grid-template-columns: repeat(7, 1fr);
        }
        
        .calendar-day {
            min-height: 100px;
        }
    }
    
    @media (max-width: 768px) {
        .main-content {
            grid-template-columns: 1fr;
            left: -3%;           
            margin-left: 0%;
            width: 106%;
    
        }
        
        
        .calendar-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .calendar {
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .calendar-day {
            min-height: 80px;
            padding: 5px;
            font-size: 12px;
        }
        
        .day-number {
            font-size: 14px;
        }
        
        .employee-item {
            flex-direction: column;
            align-items: flex-start;
        }
    }



        /* Date Details Modal */
    .date-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .date-modal-content {
        background: white;
        border-radius: 10px;
        width: 500px;
        max-width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .date-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .date-modal-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .date-close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--gray);
    }
    
    .date-status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .status-present {
        background-color: #e7f6e9;
        color: var(--present);
    }
    
    .status-absent {
        background-color: #fbebec;
        color: var(--absent);
    }
    
    .status-half_day {
        background-color: #fff4e6;
        color: var(--halfday);
    }
    
    .status-casual_leave {
        background-color: #f3e8fd;
        color: var(--casual);
    }
    
    .status-sick_leave {
        background-color: #e1f0ff;
        color: var(--sick);
    }
    
    .status-regularization {
        background-color: rgba(225, 226, 255, 1);
        color: var(--secondary);
    }
    
    .status-holiday {
        background-color: #e0f6f2;
        color: var(--holiday);
    }
    
    .time-entries {
        margin: 15px 0;
    }
    
    .time-entry {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .time-entry:last-child {
        border-bottom: none;
    }
    
    .entry-type {
        font-weight: 600;
        color: var(--dark);
    }
    
    .entry-time {
        color: var(--gray);
    }
    
    .holiday-info, .leave-info {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }
    
    .holiday-title, .leave-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark);
    }
    
    .holiday-description, .leave-reason {
        color: var(--gray);
        font-size: 14px;
    }
    
    .no-data {
        text-align: center;
        padding: 20px;
        color: var(--gray);
        font-style: italic;
    }
    
    .loading {
        text-align: center;
        padding: 30px;
    }
    
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid var(--secondary);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div class="container">
    <div class="main-content">
        <div class="sidebar">
            <?php if($_SESSION['at_office']):?>
                <a href="index.php" class="nav-item " data-target="employee-section">
                    <i class="fas fa-user"></i>
                    <span>Employee Dashboard</span>
                </a>
            <?php endif; ?>
            <a href="applications.php" class="nav-item">
                <i class="fas fa-file-alt"></i>
                <span>Applications</span>
            </a>
            <a href="history.php" class="nav-item">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a>
            <a href="schedule.php" class="nav-item active">
                <i class="fas fa-calendar-alt"></i>
                <span>Schedule</span>
            </a>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>

        <div class="content-area">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Employee Schedule - <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?>
            </h2>
            
            <div class="calendar-header">
                <div class="month-navigation">
                    <?php
                    $prevMonth = $currentMonth - 1;
                    $prevYear = $currentYear;
                    if ($prevMonth < 1) {
                        $prevMonth = 12;
                        $prevYear--;
                    }
                    
                    $nextMonth = $currentMonth + 1;
                    $nextYear = $currentYear;
                    if ($nextMonth > 12) {
                        $nextMonth = 1;
                        $nextYear++;
                    }
                    ?>
                    <a href="schedule.php?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="nav-btn">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <div class="current-month"><?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?></div>
                    <a href="schedule.php?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="nav-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="calendar">
                <!-- Day headers -->
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
                <div class="calendar-day-header">Sun</div>
                
                <!-- Calendar days -->
                <?php
                $firstDay = date("N", strtotime("$currentYear-$currentMonth-01"));
                $daysInMonth = date("t", strtotime("$currentYear-$currentMonth-01"));
                $today = date('j');
                
                // Add empty days for first week
                for ($i = 1; $i < $firstDay; $i++) {
                    echo '<div class="calendar-day non-working-day"></div>';
                }
                
                // Add days of the month
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = date("Y-m-d", strtotime("$currentYear-$currentMonth-$day"));
                    $dayOfWeek = date("N", strtotime($date));
                    $isSunday = ($dayOfWeek == 7); // Sunday is day 7 in ISO-8601
                    $isSaturday = ($dayOfWeek == 6); // Saturday is day 6 in ISO-8601

                    // Check if it's 2nd or 4th Saturday
                    $weekOfMonth = ceil($day / 7);
                    $isSecondSaturday = ($isSaturday && $weekOfMonth == 2);
                    $isFourthSaturday = ($isSaturday && $weekOfMonth == 4);

                    $isWeekend = $isSunday || $isSecondSaturday || $isFourthSaturday;
                    $isToday = ($day == $today && $currentMonth == date('n') && $currentYear == date('Y'));
                    
                    echo '<div class="calendar-day ' . ($isWeekend ? 'weekend' : '') . ($isToday ? ' today' : '') . '">';
                    echo '<div class="day-number">' . $day . '</div>';
                    
                    // Check if we have data for this date
                    if (isset($dateWiseData[$date])) {
                        $employeeCount = 0;
                        
                        // foreach ($dateWiseData[$date] as $employeeId => $employeeData) {
                        //     $employeeCount++;
                            
                            // Only show the first few employees in the main view
                            // if ($employeeCount <= 3) {
                                $statusClass = 'activity-' . $dateWiseData[$date][$entry['employee_id']]['status'];
                                echo '<span class="day-activity ' . $statusClass . '" title="' . ucfirst($dateWiseData[$date][$entry['employee_id']]['status']) . ($isLate ? ' (Late)' : '') . '">';
                                echo substr($dateWiseData[$date][$entry['employee_id']]['status'] , 0, 10) . '...';
                                echo '</span>';
                            // }
                        // }
                        
                        // Show more indicator if there are more employees
                        // if ($employeeCount > 3) {
                        //     echo '<span class="day-activity">+' . ($employeeCount - 3) . ' more</span>';
                        // }
                    } else {
                        // No data for this date
                        if ($isWeekend) {
                            echo '<span class="day-activity activity-holiday">Holiday</span>';
                        } else {
                            echo '<span class="day-activity activity-absent">No data</span>';
                        }
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color legend-present"></div>
                    <span>Present</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-absent"></div>
                    <span>Absent</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-halfday"></div>
                    <span>Half Day</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-casual"></div>
                    <span>Casual Leave</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-sick"></div>
                    <span>Sick Leave</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-holiday"></div>
                    <span>Holiday</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-weekend"></div>
                    <span>Weekend</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-wfh"></div>
                    <span>Work From Home</span>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Date Details Modal -->
<div class="date-modal" id="dateModal">
    <div class="date-modal-content">
        <div class="date-modal-header">
            <h3 class="date-modal-title" id="modalDateTitle">Date Details</h3>
            <button class="date-close-btn" id="dateCloseBtn">&times;</button>
        </div>
        
        <div id="modalDateContent">
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Loading date information...</p>
            </div>
        </div>
    </div>
</div>


       <!-- Notification Modal -->
    <div class="notification-modal" id="notificationModal">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button class="notification-close" id="notificationClose">&times;</button>
        </div>
        <div class="notification-body" id="notificationList">
            <!-- Notifications will be loaded here -->
        </div>
        <?php 
            if($count >=1):

        ?>
        <div class="notification-actions">
            <button class="mark-all-read" id="markAllRead">Mark all as read</button>
        </div>

        <?php
            else:
            
        ?>
        <div class="notification-actions">
            <button class="mark-all-read" id="nonotification" style='color: red; font-weight: 600;'>No Notification!</button>
        </div>
        <?php
            endif;
        ?>
    </div>

    <!-- Overlay -->
    <div class="notification-overlay" id="notificationOverlay"></div>
    <!-- End Notification -->


<?php require_once "includes/footer.php"; ?>

<script>







document.addEventListener('DOMContentLoaded', function() {
        const dateModal = document.getElementById('dateModal');
        const dateCloseBtn = document.getElementById('dateCloseBtn');
        const modalDateTitle = document.getElementById('modalDateTitle');
        const modalDateContent = document.getElementById('modalDateContent');
        
        // Function to show date details
        function showDateDetails(date) {
            // Show loading state
            modalDateContent.innerHTML = `
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Loading date information...</p>
                </div>
            `;
            
            // Format date for display
            const displayDate = new Date(date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            modalDateTitle.textContent = displayDate;
            
            // Fetch date details via AJAX
            fetch(`schedule.php?get_date_details=1&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    renderDateDetails(data, date);
                })
                .catch(error => {
                    console.error('Error fetching date details:', error);
                    modalDateContent.innerHTML = `
                        <div class="no-data">
                            <p>Error loading date information. Please try again.</p>
                        </div>
                    `;
                });
            
            // Show modal
            dateModal.style.display = 'flex';
        }
        
        // Function to render date details
        function renderDateDetails(data, date) {
            let html = '';
            
            // Status badge
            const statusClass = 'status-' + data.status.replace(/\s+/g, '_');
            html += `<div class="date-status-badge ${statusClass}">${data.status}</div>`;
            
            // Holiday information
            if (data.is_holiday && data.holiday_title) {
                html += `
                    <div class="holiday-info">
                        <div class="holiday-title">${data.holiday_title}</div>
                        ${data.holiday_description ? `<div class="holiday-description">${data.holiday_description}</div>` : ''}
                    </div>
                `;
            }
            
            // Leave information
            if (data.leave_info) {
                html += `
                    <div class="leave-info">
                        <div class="leave-title">Leave Application</div>
                        <div><strong>Type:</strong> ${data.leave_info.req_type}</div>
                        <div><strong>Period:</strong> ${data.leave_info.start_date} to ${data.leave_info.end_date}</div>
                        ${data.leave_info.reason ? `<div class="leave-reason"><strong>Reason:</strong> ${data.leave_info.reason}</div>` : ''}
                    </div>
                `;
            }
            
            // Time entries
            if (data.entries && data.entries.length > 0) {
                html += `<div class="time-entries"><strong>Time Entries:</strong></div>`;
                
                data.entries.forEach(entry => {
                    const entryTime = new Date(entry.entry_time).toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    let entryType = entry.entry_type;
                    // Format entry type for display
                    if (entryType === 'punch_in') entryType = 'Punch In';
                    if (entryType === 'punch_out') entryType = 'Punch Out';
                    if (entryType === 'lunch_start') entryType = 'Lunch Start';
                    if (entryType === 'lunch_end') entryType = 'Lunch End';
                    if (entryType === 'half_day') entryType = 'Half Day';
                    
                    html += `
                        <div class="time-entry">
                            <span class="entry-type">${entryType}</span>
                            <span class="entry-time">${entryTime}</span>
                        </div>
                    `;
                });
            } else if (data.status === 'No data') {
                html += `
                    <div class="no-data">
                        <p>No time entries or leave records found for this date.</p>
                    </div>
                `;
            }
            
            modalDateContent.innerHTML = html;
        }
        
        // Add click event to calendar days
        document.querySelectorAll('.calendar-day:not(.non-working-day)').forEach(day => {
            day.style.cursor = 'pointer';
            
            day.addEventListener('click', function() {
                const dayNumber = this.querySelector('.day-number').textContent;
                const date = '<?php echo $currentYear . '-' . sprintf("%02d", $currentMonth) . '-'; ?>' + dayNumber.padStart(2, '0');
                
                showDateDetails(date);
            });
        });
        
        // Close modal handlers
        dateCloseBtn.addEventListener('click', function() {
            dateModal.style.display = 'none';
        });
        
        dateModal.addEventListener('click', function(e) {
            if (e.target === dateModal) {
                dateModal.style.display = 'none';
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && dateModal.style.display === 'flex') {
                dateModal.style.display = 'none';
            }
        });
    });








    document.addEventListener('DOMContentLoaded', function() {


            // prevent all kind of functions by user.
        
    // document.addEventListener('contextmenu', e => e.preventDefault());
    // document.onkeydown = function (e) {
    // // F12
    // if (e.keyCode === 123) return false;

    // // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
    // if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;

    // // Ctrl+U (View Source)
    // if (e.ctrlKey && e.key.toUpperCase() === 'U') return false;
    // };
    
    // document.addEventListener('selectstart', e => e.preventDefault());
    // document.addEventListener('dragstart', e => e.preventDefault());
    // document.addEventListener('copy', e => e.preventDefault());


    // For notification Section


    $("#notificationBell").click(function(){
        notification();
        $(".notification-modal").show();

    });
    $("#notificationClose").click(function(){
        
        $(".notification-modal").hide();

    });

    $(document).on("click",".notification-content-head-div", function(){
        
        click = "changeNoteStatus";
        let appid = $(this).data("appid");
        
        $.ajax({
            url: "time_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click, appid:appid},
            success: function(e){
                if(e){
                    $("#notificationList").html(e.output);
                    $("#notificationBadge").html(e.count);
                    
                    if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"black"});
                    }
                }
            },
            error: function(e, s, x){
                console.log(e);
                console.log(s);
                console.log(x);
            }
        });
    });
    $(document).on("click","#markAllRead", function(){
        
        click = "markAllRead";
       
        
        $.ajax({
            url: "time_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click},
            success: function(e){
                if(e){
                    $("#notificationList").html(e.output);
                    $("#notificationBadge").html(e.count);
                    
                    if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"black"});
                    }
                }
            },
            error: function(e, s, x){
                console.log(e);
                console.log(s);
                console.log(x);
            }
        });
    });
    $(document).on("click",".deletenoty", function(){
        
        click = "deletenoty";
       let appid = $(".deletenoty").data("appid");
        
        $.ajax({
            url: "time_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click, appid:appid},
            success: function(e){
                if(e){
                    $("#notificationList").html(e.output);
                    $("#notificationBadge").html(e.count);
                    
                    if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"black"});
                    }
                }
            },
            error: function(e, s, x){
                console.log(e);
                console.log(s);
                console.log(x);
            }
        });
    });
    

    function notification(){

        click = "notification";

        $.ajax({
            url: "time_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click},
            success: function(e){
                if(e){
                    $("#notificationList").html(e.output);
                    $("#notificationBadge").html(e.count);
                    
                    if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"black"});
                    }
                }
            },
            error: function(e, s, x){
                console.log(e);
                console.log(s);
                console.log(x);
            }
        });
    }


        // Notification section end

        // Add click event to calendar days to show detailed view
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                const dayNumber = this.querySelector('.day-number').textContent;
                const date = '<?php echo $currentYear . '-' . sprintf("%02d", $currentMonth) . '-'; ?>' + dayNumber.padStart(2, '0');
                
                // In a real implementation, you would show a modal with detailed information
                // about all employees for the selected date
                // console.log('Clicked on date:', date);
                
                // For now, let's just alert the date
                // alert('Showing details for ' + date);
            });
        });


        
});


            checksession();
            function checksession(){
                var info = "check";
                // alert("Something Went Wrong! from function");
                $.ajax({
                    url: "includes/checksession.php",
                    method: "POST",
                    data: {info:info},
                    success: function(e){
                        if(e == "expired"){
                            window.location.href = "login.php";
                        }
                    }

                });
            }

            // This is all about refreshing the page and see if the session is not older than 5 minutes
            $(document).ready(function() {
                checksession();
            });

            // Run on any click or touch
            $(document).on("click", function() {
                checksession();
            });
            $(document).on("touchstart", function() {
                checksession();
            });

            document.addEventListener("visibilitychange", function() {
                if (document.visibilityState === "visible") {
                    checksession();
                }
            });

            window.addEventListener("pageshow", function(event) {
                // console.log("pageshow");
                checksession();
            });
</script>