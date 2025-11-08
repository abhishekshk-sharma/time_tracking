<?php
require_once "includes/config.php";

$userID = isset($_SESSION['id']) ? $_SESSION['id'] : null;
if ($userID == null) {
    header("location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ? AND end_date IS NULL");
$stmt->execute([$userID]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user is admin
if ($details['role'] != "admin") {
    header("location: ../index.php");
    exit;
}

// Get current month and year for the calendar
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Handle holiday submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'] ?: $start_date; // If no end date, use start date
    $description = $_POST['description'] ?? '';
    
    // Get all active employees
    $employeesStmt = $pdo->prepare("SELECT emp_id FROM employees WHERE status = 'active' AND end_date IS NULL");
    $employeesStmt->execute();
    $employees = $employeesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Create date range
    $start = new DateTime($start_date, new DateTimeZone("asia/kolkata"));
    $end = new DateTime($end_date, new DateTimeZone("asia/kolkata"));
    $end->modify('+1 day'); // Include the end date
    
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($start, $interval, $end);
    
    // Insert holiday entries for each employee for each date
    foreach ($dateRange as $date) {
        $dateStr = $date->format('Y-m-d');
        
        foreach ($employees as $employeeId) {
            // Check if entry already exists for this date and employee
            $checkStmt = $pdo->prepare("
                SELECT id FROM time_entries 
                WHERE employee_id = ? AND DATE(entry_time) = ? AND entry_type = 'holiday' 
            ");
            $checkStmt->execute([$employeeId, $dateStr]);
            
            if (!$checkStmt->fetch()) {
                // Insert holiday entry
                $insertStmt = $pdo->prepare("
                    INSERT INTO time_entries (employee_id, entry_type, entry_time, notes)
                    VALUES (?, 'holiday', ?, ?)
                ");
                $insertStmt->execute([
                    $employeeId, 
                    $dateStr . ' 00:00:00', 
                    "Holiday: $title" . ($description ? " - $description" : "")
                ]);
            }
        }
    }
    
    // Success message
    $_SESSION['success_message'] = "Holiday successfully added for all employees!";
    header("Location: schedule.php?month=$currentMonth&year=$currentYear");
    exit;
}

// Calculate first and last day of the month
$firstDayOfMonth = date("{$currentYear}-{$currentMonth}-01");
$lastDayOfMonth = date("{$currentYear}-{$currentMonth}-t");

// Get all time entries for the current month
$timeEntriesStmt = $pdo->prepare("
    SELECT te.*, e.full_name 
    FROM time_entries te 
    JOIN employees e ON te.employee_id = e.emp_id 
    WHERE DATE(te.entry_time) BETWEEN ? AND ? AND e.end_date IS NULL
    ORDER BY te.entry_time
");
$timeEntriesStmt->execute([$firstDayOfMonth, $lastDayOfMonth]);
$timeEntries = $timeEntriesStmt->fetchAll(PDO::FETCH_ASSOC);

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
            'entries' => [],
            'status' => 'working' // Default status
        ];
    }
    
    // Record all entries for this employee on this date
    $dateWiseData[$date][$entry['employee_id']]['entries'][] = [
        'type' => $entry['entry_type'],
        'time' => $entry['entry_time'],
        'notes' => $entry['notes']
    ];
    
    // Update status based on entry type
    if ($entry['entry_type'] === 'holiday') {
        $dateWiseData[$date][$entry['employee_id']]['status'] = 'holiday';
    } elseif ($entry['entry_type'] === 'half_day') {
        $dateWiseData[$date][$entry['employee_id']]['status'] = 'half_day';
    }
}

// Get system settings for work hours
$settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings");
$settingsStmt->execute();
$settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$workStartTime = $settings['work_start_time'] ?? '09:00:00';
$workEndTime = $settings['work_end_time'] ?? '18:00:00';
$lateThreshold = $settings['late_threshold'] ?? 15;




// Handle holiday deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_holiday'])) {
    $holiday_time = isset($_POST['holiday_time'])?new DateTime($_POST['holiday_time'], new DateTimeZone("asia/kolkata")): "";
    $holiday_time = $holiday_time->format("Y-m-d");
    

    $stmt = $pdo->query("SELECT * FROM employees WHERE end_date IS NULL");
    $fetchempid = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Delete the holiday from time_entries
    $deleteStmt = $pdo->prepare("
        DELETE FROM time_entries 
        WHERE employee_id = ? AND entry_time LIKE ? AND entry_type = 'holiday'
    ");
    
    foreach($fetchempid as $row){

        if ($deleteStmt->execute([$row['emp_id'], "%$holiday_time%" ])) {
            $_SESSION['success_message'] = "Holiday successfully Deleted!";
        } else {
            $_SESSION['error_message'] = "Error updating holiday. Please try again.";
        }
        
    }

    echo "<script>window.location.href='schedule.php?month=$currentMonth&year=$currentYear'</script>" ;

    exit;
}

// Handle holiday update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_holiday'])) {
    $holiday_id = $_POST['holiday_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $time = isset($_POST['update_holiday'])?new DateTime($_POST['update_holiday'], new DateTimeZone("asia/kolkata")):"";
    $time = $time->format("Y-m-d");
    
    $stmt = $pdo->query("SELECT * FROM employees WHERE end_date IS NULL");
    $fetchempid = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Update the holiday entry
    $updateStmt = $pdo->prepare("
        UPDATE time_entries 
        SET notes = ?
        WHERE employee_id = ? AND entry_time LIKE ? AND entry_type = 'holiday'
    ");
    
    foreach($fetchempid as $row){

        if ($updateStmt->execute(["Holiday: $title" . ($description ? " - $description" : ""), $row['emp_id'], "%$time%"])) {
            $_SESSION['success_message'] = "Holiday successfully updated!";
        } else {
            $_SESSION['error_message'] = "Error updating holiday. Please try again.";
        }
        
    }

    echo "<script>window.location.href='schedule.php?month=$currentMonth&year=$currentYear'</script>" ;
    exit;
}

// Get all user-defined holidays for listing
$holidaysStmt = $pdo->prepare("
    SELECT te.*, GROUP_CONCAT(DISTINCT e.full_name ORDER BY e.full_name SEPARATOR ', ') as affected_employees,
           COUNT(DISTINCT te.employee_id) as employee_count
    FROM time_entries te
    JOIN employees e ON te.employee_id = e.emp_id
    WHERE te.entry_type = 'holiday' 
    AND MONTH(te.entry_time) = MONTH(CURRENT_DATE) 
    AND YEAR(te.entry_time) = YEAR(CURRENT_DATE)
    AND e.end_date IS NULL
    GROUP BY DATE(te.entry_time), te.notes
    ORDER BY te.entry_time DESC
");
$holidaysStmt->execute();
$holidays = $holidaysStmt->fetchAll(PDO::FETCH_ASSOC);

// Get holiday for editing
$edit_holiday = null;
if (isset($_GET['edit_holiday'])) {
    $holiday_id = $_GET['edit_holiday'];
    $editStmt = $pdo->prepare("
        SELECT * FROM time_entries 
        WHERE id = ? AND entry_type = 'holiday'
    ");
    $editStmt->execute([$holiday_id]);
    $edit_holiday = $editStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_holiday) {
        // Extract title and description from notes
        $notes = $edit_holiday['notes'];
        if (strpos($notes, 'Holiday: ') === 0) {
            $notes = substr($notes, 9); // Remove "Holiday: " prefix
            $parts = explode(' - ', $notes, 2);
            $edit_holiday['title'] = $parts[0];
            $edit_holiday['description'] = count($parts) > 1 ? $parts[1] : '';
        } else {
            $edit_holiday['title'] = $notes;
            $edit_holiday['description'] = '';
        }
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
        --holiday: #9b59b6;
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
        left: 5%;
    
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
        width: 90%;
        overflow-x: scroll;
    }
    
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
    
    .add-holiday-btn {
        background: var(--success);
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
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
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .calendar-day:hover {
        background: #f0f5ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    
    .activity-absent {
        background-color: #fbebec;
        color: var(--absent);
    }
    
    .activity-halfday {
        background-color: #fff4e6;
        color: var(--halfday);
    }
    
    .activity-holiday {
        background-color: #f3e8fd;
        color: var(--holiday);
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
    
    .legend-holiday {
        background: #f3e8fd;
    }
    
    .legend-weekend {
        background: #f0f0f0;
    }
    
    /* Modal Styles */
    .modal {
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
    
    .modal-content {
        background: white;
        border-radius: 10px;
        width: 500px;
        max-width: 90%;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--gray);
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-primary {
        background: var(--secondary);
        color: white;
    }
    
    .btn-success {
        background: var(--success);
        color: white;
    }
    
    .btn-danger {
        background: var(--danger);
        color: white;
    }
    
    .btn-secondary {
        background: var(--gray);
        color: white;
    }
    
    .alert {
        padding: 12px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background-color: #e7f6e9;
        color: var(--success);
        border: 1px solid #c3e6cb;
    }
    
    @media (max-width: 1378px) {
        .main-content {
            /*grid-template-columns: 1fr;*/
            left: 5%;
        }
        header{
            position:relative;
            width: 100%;
        }
    }
    @media (max-width: 1024px) {
        
        .calendar {
            grid-template-columns: repeat(7, 1fr);
        }
        
        
        .calendar-day {
            min-height: 100px;
        }
        
        /*.content-area{*/
        /*    position: relative;*/
        /*    width: 80%;*/
        /*    padding-right: 10px;*/
            
        /*}*/
        /*.main-content {*/
           
        /*    right: 2%;*/
        /*}*/
        

    }
    
    @media (max-width: 768px) {
        .main-content {
            grid-template-columns: 1fr;
            left: 2%;
        }
        
        .content-area{
            position: relative;
            width: 100%;
            overflow-x: scroll;
        }
        
        .calendar-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .calendar {
            position: relative;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            pading-right: 80px;
        }
        
        .calendar-day {
            min-height: 80px;
            padding: 5px;
            font-size: 12px;
        }
        
        .day-number {
            font-size: 14px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .content-area {
            position :relative;
            left 5%;
        }
        
        header{
            width: 100vw;
        }
    }


    /* ////////////\\\\\\\\\\\\\\\\\\\\\\\\\//////////////////////////////////\\\\\\\\\\\\ */

    .holidays-section {
        margin: 24%;
        margin-top: 30px;
        margin-left: 30%;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .holidays-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .holidays-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    
    .holidays-table th,
    .holidays-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .holidays-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: var(--dark);
    }
    
    .holidays-table tr:hover {
        background-color: #f9f9f9;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-edit {
        background: var(--warning);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-delete {
        background: var(--danger);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .btn-edit:hover {
        background: #e67e22;
    }
    
    .btn-delete:hover {
        background: #c0392b;
    }
    
    .no-holidays {
        text-align: center;
        padding: 30px;
        color: var(--gray);
        font-style: italic;
    }
    
    .employee-count {
        display: inline-block;
        padding: 3px 8px;
        background: #e1ebff;
        color: var(--secondary);
        border-radius: 12px;
        font-size: 12px;
        margin-left: 8px;
    }
    
    /* Modal adjustments for edit form */
    #editHolidayModal .form-group {
        margin-bottom: 15px;
    }
    
    #editHolidayModal .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    @media (max-width: 768px) {
        .holidays-table {
            display: block;
            overflow-x: auto;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        #editHolidayModal .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>




<div class="container">
    <div class="main-content">
        <div class="sidebar">
            <a href="index.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Admin Dashboard</span>
            </a>
            <a href="user.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Employee</span>
            </a>
            <a href="create.php" class="nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Create Employee</span>
            </a>
            <a href="schedule.php" class="nav-item active">
                <i class="fas fa-calendar-alt"></i>
                <span>Schedule</span>
            </a>
            <a href="report.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Applications</span>
            </a>
            <a href="adminentry.php" class="nav-item " onclick="window.location.href='adminentry.php'">
                        <i class="fas fa-house-user"></i>
                        <span>Work From Home</span>
                </a>
            <a href="employees_history.php" class="nav-item" onclick="window.location.href='employees_history.php'">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Employees History</span>
                </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="#" class="nav-item" onclick="window.location.href = 'logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
        </div>

        <div class="content-area">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Employee Schedule Management - <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?>
            </h2>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                
            <?php endif; ?>
            
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
                
                <button class="add-holiday-btn" id="addHolidayBtn">
                    <i class="fas fa-plus"></i> Add Holiday
                </button>
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
                    $isSunday = ($dayOfWeek == 7);
                    $isSaturday = ($dayOfWeek == 6);
                    
                    // Calculate which week of the month this Saturday falls in
                    if ($isSaturday) {
                        $dayOfMonth = date("j", strtotime($date));
                        $firstDayOfMonth = date("N", strtotime("$currentYear-$currentMonth-01"));
                        $weekOfMonth = floor(($dayOfMonth + $firstDayOfMonth - 1) / 7) + 1;
                        $isSecondSaturday = ($weekOfMonth == 2);
                        $isFourthSaturday = ($weekOfMonth == 4);
                    } else {
                        $isSecondSaturday = false;
                        $isFourthSaturday = false;
                    }
                    
                    $isWeekend = $isSunday || $isSecondSaturday || $isFourthSaturday;
                    $isToday = ($day == $today && $currentMonth == date('n') && $currentYear == date('Y'));
                    
                    echo '<div class="calendar-day ' . ($isWeekend ? 'weekend' : '') . ($isToday ? ' today' : '') . '">';
                    echo '<div class="day-number">' . $day . '</div>';
                    
                    // Check if we have data for this date
                    if (isset($dateWiseData[$date])) {
                        $holidayCount = 0;
                        $otherCount = 0;
                        
                        foreach ($dateWiseData[$date] as $employeeId => $employeeData) {
                            if ($employeeData['status'] === 'holiday') {
                                $holidayCount++;
                            } else {
                                $otherCount++;
                            }
                        }
                        
                        if ($holidayCount > 0) {
                            echo '<span class="day-activity activity-holiday" title="' . $holidayCount . ' employees on holiday">';
                            echo $holidayCount . ' on Holiday';
                            echo '</span>';
                        }
                        
                        if ($otherCount > 0) {
                            echo '<span class="day-activity activity-present" title="' . $otherCount . ' employees working">';
                            echo $otherCount . ' Working';
                            echo '</span>';
                        }
                    } else {
                        // No data for this date
                        if ($isWeekend) {
                            if ($isSunday) {
                                echo '<span class="day-activity activity-holiday">Sunday</span>';
                            } else if ($isSecondSaturday) {
                                echo '<span class="day-activity activity-holiday">2nd Sat</span>';
                            } else if ($isFourthSaturday) {
                                echo '<span class="day-activity activity-holiday">4th Sat</span>';
                            } else {
                                echo '<span class="day-activity activity-absent">Working Sat</span>';
                            }
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
                    <span>Working</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-absent"></div>
                    <span>No Data</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-holiday"></div>
                    <span>Holiday</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-weekend"></div>
                    <span>Weekend</span>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="holidays-section">
    <div class="holidays-header">
        <h3 class="section-title">
            <i class="fas fa-calendar-day"></i>
            Defined Holidays
        </h3>
    </div>
    
    <?php if (count($holidays) > 0): ?>
        <table class="holidays-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Description</th>
                 
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($holidays as $holiday): 
                    // Extract title and description from notes
                    $notes = $holiday['notes'];
                    if (strpos($notes, 'Holiday: ') === 0) {
                        $notes = substr($notes, 9); // Remove "Holiday: " prefix
                        $parts = explode(' - ', $notes, 2);
                        $title = $parts[0];
                        $description = count($parts) > 1 ? $parts[1] : '';
                    } else {
                        $title = $notes;
                        $description = '';
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($title); ?></td>
                        <td><?php echo date('M j, Y', strtotime($holiday['entry_time'])); ?></td>
                        <td><?php echo htmlspecialchars($description); ?></td>
                        
                        <td>
                            <div class="action-buttons">
                                <a href="schedule.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>&edit_holiday=<?php echo $holiday['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                    <input type="hidden" name="holiday_time" value="<?php echo $holiday['entry_time']; ?>">
                                    <button type="submit" name="delete_holiday" class="btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-holidays">
            <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 15px;"></i>
            <p>No user-defined holidays found.</p>
            <p>Click "Add Holiday" above to create your first holiday.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Holiday Modal -->
<div class="modal" id="editHolidayModal" style="<?php echo $edit_holiday ? 'display: flex;' : ''; ?>">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Holiday</h3>
            <a href="schedule.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>" class="close-btn">&times;</a>
        </div>
        
        <?php if ($edit_holiday): ?>
        <form method="POST">
            <input type="hidden" name="holiday_id" value="<?php echo $edit_holiday['id']; ?>">
            
            <div class="form-group">
                <label for="editHolidayTitle">Holiday Title</label>
                <input type="text" id="editHolidayTitle" name="title" value="<?php echo htmlspecialchars($edit_holiday['title']); ?>" placeholder="Enter holiday title" required>
            </div>
            
            <div class="form-group">
                <label for="editHolidayDate">Date</label>
                <input type="date" id="editHolidayDate" value="<?php echo date('Y-m-d', strtotime($edit_holiday['entry_time'])); ?>" disabled>
                <small style="color: var(--gray);">Note: Date cannot be changed. Create a new holiday for a different date.</small>
            </div>
            
            <div class="form-group">
                <label for="editHolidayDescription">Description</label>
                <textarea id="editHolidayDescription" name="description" placeholder="Enter holiday description"><?php echo htmlspecialchars($edit_holiday['description']); ?></textarea>
            </div>
            
            <div class="modal-footer">
                <a href="schedule.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-success" name="update_holiday" value="<?php echo $edit_holiday['entry_time']; ?>">
                    <i class="fas fa-save"></i> Update Holiday
                </button>
            </div>
        </form>
        <?php else: ?>
        <div class="no-holidays">
            <p>Holiday not found or no longer exists.</p>
            <a href="schedule.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>" class="btn btn-primary">Return to Schedule</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal" id="holidayModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Holiday</h3>
            <button class="close-btn">&times;</button>
        </div>
        
        <form method="POST" id="holidayForm">
            <div class="form-group">
                <label for="holidayTitle">Holiday Title</label>
                <input type="text" id="holidayTitle" name="title" placeholder="Enter holiday title" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="start_date" required>
                </div>
                
                <div class="form-group">
                    <label for="endDate">End Date (optional)</label>
                    <input type="date" id="endDate" name="end_date" placeholder="Leave blank for single day">
                </div>
            </div>
            
            <div class="form-group">
                <label for="holidayDescription">Description</label>
                <textarea id="holidayDescription" name="description" placeholder="Enter holiday description"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelHolidayBtn">
                    Cancel
                </button>
                <button type="submit" class="btn btn-success" name="add_holiday">
                    <i class="fas fa-save"></i> Add Holiday
                </button>
            </div>
        </form>
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
        <div class="notification-actions">
            <button class="mark-all-read" id="markAllRead">Mark all as read</button>
        </div>
    </div>

    <!-- Notification Detail Modal -->
    <div class="detail-modal" id="detailModal">
        <div class="detail-header">
            <h3>Notification Details</h3>
            <button class="detail-close" id="detailClose">&times;</button>
        </div>
        <div class="detail-body" id="detailContent">
            <!-- Notification details will be loaded here -->
        </div>
        <div class="detail-actions">
            <button class="btn btn-success" id="markAsReadBtn" value="">
                <i class="fas fa-check-circle"></i> Mark as Read
            </button>
            <button class="btn btn-primary" id="closeDetailBtn">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- Overlay -->
    <div class="modal-overlay" id="modalOverlay"></div>


<?php require_once "includes/footer.php"; ?>

<script>

    // document.addEventListener('DOMContentLoaded', function() {
    //     // Close edit modal when clicking outside
    //     const editModal = document.getElementById('editHolidayModal');
    //     if (editModal) {
    //         editModal.addEventListener('click', function(e) {
    //             if (e.target === editModal) {
    //                 window.location.href = 'schedule.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>';
    //             }
    //         });
    //     }

    //     // Close modal with Escape key
    //     document.addEventListener('keydown', function(e) {
    //         if (e.key === 'Escape' && editModal && editModal.style.display === 'flex') {
    //             window.location.href = 'schedule.php?month=<?php echo $currentMonth; ?>&year=<?php echo $currentYear; ?>';
    //         }
    //     });
    // });




    document.addEventListener('DOMContentLoaded', function() {
    // For notification Section


    $("#notificationBell").click(function(){
        notification();
        $(".notification-modal").show();

    });
    $("#notificationClose").click(function(){
        
        $(".notification-modal").hide();

    });
    $("#closeDetailBtn").click(function(){
        
        $("#detailModal").hide();

    });
    $("#detailClose").click(function(){
        
        $("#detailModal").hide();

    });
    $("#markAsReadBtn").click(function(){
        
        let appid = $("#markAsReadBtn").val();

        click = "markAsReadBtn";
        
        $.ajax({
            url: "leave_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click, appid:appid},
            success: function(e){
                if(e){
                    
                    if(e.success == "success"){
                        $("#detailModal").hide();
                        $("#notificationBadge").html(e.count);
                    
                        $(".notification-content-head-div").removeClass("notification-item unread");
                        if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"red"});
                    }
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

    $(document).on("click",".notification-content-head-div", function(){

        
        
        click = "changeNoteStatus";
        let appid = $(this).data("appid");
        
        $.ajax({
            url: "leave_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click, appid:appid},
            success: function(e){
                if(e){
                    
                    $("#markAsReadBtn").val(e.appid);

                    $("#detailContent").html(e.output);
                    $("#detailModal").show();
                    if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"red"});
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
            url: "leave_management/notification.php",
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
                        
                        $(".notybell").css({"color":"red"});
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
            url: "leave_management/notification.php",
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
                        
                        $(".notybell").css({"color":"red"});
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
    

    setInterval(notification, 10000);
    let notiCount = 0;
    // let notiCount2 = 0;
    // let notiCount = <?php //echo $_SESSION['notification_session_count']; ?>;
    let notiCount2 = <?php echo $_SESSION['notification_session_count']; ?>;
    // alert(notiCount21);

    let soundAllowed = false;

    $(document).one('click', function() {
      soundAllowed = true;
    //   alert(soundAllowed);
      notification();
    });
        function triggerNotification() {
        // alert("true1");
        if (soundAllowed) {
            //   alert("true2");
            notiCount = notiCount2;
            localStorage.setItem("notiCount", notiCount);
            const sound = $('#notifSound')[0];
            sound.pause();
            sound.currentTime = 0;
            sound.play();

      }
    }
    notification();
    function notification(){

        click = "notification";

        $.ajax({
            url: "leave_management/notification.php",
            method: "POST",
            dataType: "json",
            data: {click:click},
            success: function(e){

                // console.log(e);
                if(e){
                    $("#notificationList").html(e.output);
                    $("#notificationBadge").html(e.count);
                    
                    notiCount2 = e.count;

                    if(e.count <=0){

                        $(".notybell").css({"color":"white"});
                    }
                    else{
                        
                        $(".notybell").css({"color":"red"});
                    }
                    
                    if(e.count == 0){
                        notiCount = 0;
                    }

                    let storedCount = localStorage.getItem("notiCount") || 0;

                    

                    if(notiCount !== null && notiCount2 !== parseInt(storedCount)){
                        
                            triggerNotification();
                            // notiCount = notiCount2;
                        
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

        // Holiday modal functionality
        const holidayModal = document.getElementById('holidayModal');
        const addHolidayBtn = document.getElementById('addHolidayBtn');
        const cancelHolidayBtn = document.getElementById('cancelHolidayBtn');
        const closeBtns = document.querySelectorAll('.close-btn');
        
        // Set today as default for date inputs
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('startDate').value = today;
        
        // Open holiday modal
        addHolidayBtn.addEventListener('click', function() {
            holidayModal.style.display = 'flex';
        });
        
        // Close modal handlers
        cancelHolidayBtn.addEventListener('click', closeHolidayModal);
        closeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                holidayModal.style.display = 'none';
            });
        });
        
        function closeHolidayModal() {
            holidayModal.style.display = 'none';
        }
        
        window.addEventListener('click', function(e) {
            if (e.target === holidayModal) {
                closeHolidayModal();
            }
        });
        
        // Add click event to calendar days to show detailed view
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                const dayNumber = this.querySelector('.day-number').textContent;
                const date = '<?php echo $currentYear . '-' . sprintf("%02d", $currentMonth) . '-'; ?>' + dayNumber.padStart(2, '0');
                
                // In a real implementation, you would show a modal with detailed information
                // about all employees for the selected date
               
            });
        });
    });
</script>