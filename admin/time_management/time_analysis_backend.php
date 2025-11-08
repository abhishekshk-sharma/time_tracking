
<?php
require_once "../includes/config.php";

// Enable CORS for AJAX requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


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

// Get the action parameter
$info = isset($_POST['info']) ? $_POST['info'] : (isset($_GET['info']) ? $_GET['info'] : '');

if ($info == "getTimeAnalysis") {
    // Get filter parameters
    $period = isset($_POST['period']) ? $_POST['period'] : 'current';
    $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
    $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
    $department = isset($_POST['department']) ? $_POST['department'] : 'all';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    
    // Calculate date range based on period
    $timezone = new DateTimeZone("Asia/Kolkata");
    $now = new DateTime('now', $timezone);
    
    if ($period == 'current') {
        $startDate = $now->format('Y-m-01');
        $endDate = $now->format('Y-m-t');
    } elseif ($period == 'last') {
        $lastMonth = clone $now;
        $lastMonth->modify('first day of last month');
        $startDate = $lastMonth->format('Y-m-01');
        $endDate = $lastMonth->format('Y-m-t');
    } elseif ($period == 'custom' && $startDate && $endDate) {
        // Use the provided custom dates
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
    } else {
        // Default to current month if no valid period specified
        $startDate = $now->format('Y-m-01');
        $endDate = $now->format('Y-m-t');
    }
    
    // Get system settings
    $stmt = $pdo->query("SELECT * FROM system_settings");
    $sys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $work_start_time_str = '09:00:00'; // default
    $work_end_time_str = '18:00:00'; // default
    $lunch_duration = 60; // default in minutes
    $late_threshold = 15; // default in minutes
    $required_hours = 9; // default required working hours
    $half_day_hours = 4.5; // default half day hours
    
    foreach($sys as $row){
        switch($row['setting_key']) {
            case "work_start_time":
                $work_start_time_str = $row['setting_value'];
                break;
            case "work_end_time":
                $work_end_time_str = $row['setting_value'];
                break;
            case "lunch_duration":
                $lunch_duration = intval($row['setting_value']);
                break;
            case "late_threshold":
                $late_threshold = intval($row['setting_value']);
                break;
            case "required_hours":
                $required_hours = intval($row['setting_value']);
                break;
            case "half_day_time":
                $half_day_hours = floatval($row['setting_value']);
                break;
        }
    }
    
    // Calculate expected work hours (including lunch)
    $expected_work_seconds = (strtotime($work_end_time_str) - strtotime($work_start_time_str));
    $expected_work_hours = $expected_work_seconds / 3600;
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;
    
    // Build base SQL query - Include employees who were active during the selected period
    // This means: employees who had no end_date OR their end_date is after the start of the period
    $sql = "SELECT e.emp_id, e.full_name, e.department, e.position, e.end_date, e.status 
            FROM employees e 
            WHERE (e.end_date IS NULL OR e.end_date >= :period_start)";
    
    $countSql = "SELECT COUNT(*) as total FROM employees e WHERE (e.end_date IS NULL OR e.end_date >= :period_start)";
    
    // Add department filter
    if ($department != 'all') {
        $sql .= " AND e.department = :department";
        $countSql .= " AND e.department = :department";
    }
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (e.emp_id LIKE :search OR e.full_name LIKE :search)";
        $countSql .= " AND (e.emp_id LIKE :search OR e.full_name LIKE :search)";
    }
    
    // Add pagination
    $sql .= " ORDER BY e.full_name LIMIT :limit OFFSET :offset";
    
    // Prepare and execute count query
    $stmt = $pdo->prepare($countSql);
    $stmt->bindValue(':period_start', $startDate, PDO::PARAM_STR);
    
    if ($department != 'all') {
        $stmt->bindValue(':department', $department, PDO::PARAM_STR);
    }
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalEmployees / $limit);
    
    // Prepare and execute main query
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':period_start', $startDate, PDO::PARAM_STR);
    
    if ($department != 'all') {
        $stmt->bindValue(':department', $department, PDO::PARAM_STR);
    }
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize summary statistics
    $summaryStats = [
        'total_employees' => $totalEmployees,
        'active_employees' => 0,
        'resigned_employees' => 0,
        'inactive_employees' => 0,
        'avg_working_hours' => 0,
        'late_arrivals' => 0,
        'long_lunches' => 0,
        'insufficient_hours' => 0,
        'absent_days' => 0,
        'regularization_days' => 0,
        'casual_leave_days' => 0,
        'sick_leave_days' => 0,
        'half_days' => 0,
        'wfh_days' => 0,
        'holiday_days' => 0
    ];
    
    $employeeData = [];
    $issuesSummary = [
        'late_arrivals' => [],
        'long_lunches' => [],
        'insufficient_hours' => [],
        'absent_days' => [],
        'regularization_days' => [],
        'casual_leave_days' => [],
        'sick_leave_days' => [],
        'half_days' => [],
        'wfh_days' => [],
        'holiday_days' => []
    ];
    
    // Process each employee
    foreach ($employees as $employee) {
        $empId = $employee['emp_id'];
        $endDateEmp = $employee['end_date'];
        $empStatus = $employee['status'];
        
        // Update employment status counts
        if ($endDateEmp !== null) {
            $summaryStats['resigned_employees']++;
        } elseif ($empStatus === 'inactive') {
            $summaryStats['inactive_employees']++;
        } else {
            $summaryStats['active_employees']++;
        }
        
        // Calculate holidays for this employee and date range
        $holidays = calculateHolidays($startDate, $endDate, $empId, $pdo);
        
        // Get all time entries for the date range
        $stmt = $pdo->prepare("SELECT * FROM time_entries 
                              WHERE employee_id = :emp_id 
                              AND DATE(entry_time) BETWEEN :start_date AND :end_date 
                              ORDER BY entry_time ASC");
        $stmt->bindValue(':emp_id', $empId, PDO::PARAM_STR);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group entries by date
        $entriesByDate = [];
        foreach ($entries as $entry) {
            $date = date('Y-m-d', strtotime($entry['entry_time']));
            $entriesByDate[$date][] = $entry;
        }
        
        // Process each day
        $employeeDays = [];
        $totalWorkedSeconds = 0;
        $daysCount = 0;
        
        // Create date range iterator
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end = $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($begin, $interval, $end);
        
        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            
            // Skip dates after employee's end_date
            if ($endDateEmp !== null && $currentDate > $endDateEmp) {
                continue;
            }
            
            $dayEntries = isset($entriesByDate[$currentDate]) ? $entriesByDate[$currentDate] : [];
            
            // Check if this date is a holiday
            if (isset($holidays[$currentDate])) {
                $formattedDate = $date->format('M j, Y');
                $employeeDays[$currentDate] = [
                    'date' => $formattedDate,
                    'punch_in' => '-',
                    'punch_out' => '-',
                    'lunch_duration' => '-',
                    'total_hours' => '-',
                    'net_hours' => '-',
                    'net_hours_seconds' => 0,
                    'status' => 'Holiday', // Capitalized for frontend
                    'minutes_late' => 0,
                    'long_lunch' => false,
                    'insufficient_hours' => false,
                    'is_half_day' => false,
                    'expected_hours' => 0,
                    'entries' => [],
                    'holiday_reason' => $holidays[$currentDate]
                ];
                continue; // Skip regular processing for holidays
            }
            
            // Calculate work time for this day (regular processing)
            $dayData = calculateDayWorkTime($dayEntries, $currentDate, $work_start_time_str, $work_end_time_str, $lunch_duration, $late_threshold, $required_hours, $half_day_hours, $pdo, $empId);
            
            // Format the date properly for frontend
            $dayData['date'] = $date->format('M j, Y');
            
            // Capitalize status for frontend consistency
            $dayData['status'] = ucfirst($dayData['status']);

            // Check for WFH (Work From Home) from applications table
            $wfhstmt = $pdo->prepare("SELECT * FROM applications WHERE employee_id = ? AND status = 'approved' AND req_type = 'work_from_home' AND DATE(?) BETWEEN DATE(start_date) AND DATE(end_date)");
            $wfhstmt->execute([$empId, $date->format('Y-m-d')]);
            if($wfhstmt->rowCount() > 0){
                $dayData['status'] = 'WFH';
            }
            
            // Add employment status info to details if resigned or inactive
            $employmentInfo = "";
            if ($endDateEmp !== null) {
                $employmentInfo = " (Resigned on " . date('M j, Y', strtotime($endDateEmp)) . ")";
            } elseif ($empStatus === 'inactive') {
                $employmentInfo = " (Inactive Employee)";
            }
            
            if ($employmentInfo && isset($dayData['details'])) {
                $dayData['details'] .= $employmentInfo;
            } elseif ($employmentInfo) {
                $dayData['details'] = $employmentInfo;
            }
            
            // Add to employee days
            $employeeDays[$currentDate] = $dayData;
            
            // Update summary statistics for all status types
            switch($dayData['status']) {
                case 'Late':
                    $summaryStats['late_arrivals']++;
                    updateIssuesSummary($issuesSummary, 'late_arrivals', $empId, $employee['full_name']);
                    break;
                    
                case 'Absent':
                    $summaryStats['absent_days']++;
                    updateIssuesSummary($issuesSummary, 'absent_days', $empId, $employee['full_name']);
                    break;
                    
                case 'Regularization':
                    $summaryStats['regularization_days']++;
                    updateIssuesSummary($issuesSummary, 'regularization_days', $empId, $employee['full_name']);
                    break;
                    
                case 'Casual Leave':
                    $summaryStats['casual_leave_days']++;
                    updateIssuesSummary($issuesSummary, 'casual_leave_days', $empId, $employee['full_name']);
                    break;
                    
                case 'Sick Leave':
                    $summaryStats['sick_leave_days']++;
                    updateIssuesSummary($issuesSummary, 'sick_leave_days', $empId, $employee['full_name']);
                    break;
                    
                case 'Half Day':
                    $summaryStats['half_days']++;
                    updateIssuesSummary($issuesSummary, 'half_days', $empId, $employee['full_name']);
                    break;
                    
                case 'WFH':
                    $summaryStats['wfh_days']++;
                    updateIssuesSummary($issuesSummary, 'wfh_days', $empId, $employee['full_name']);
                    break;
                    
                case 'Holiday':
                    $summaryStats['holiday_days']++;
                    updateIssuesSummary($issuesSummary, 'holiday_days', $empId, $employee['full_name']);
                    break;
            }
            
            // Additional checks for specific conditions
            if ($dayData['long_lunch']) {
                $summaryStats['long_lunches']++;
                updateIssuesSummary($issuesSummary, 'long_lunches', $empId, $employee['full_name']);
            }
            
            if ($dayData['insufficient_hours']) {
                $summaryStats['insufficient_hours']++;
                updateIssuesSummary($issuesSummary, 'insufficient_hours', $empId, $employee['full_name']);
            }
            
            // Add to total worked time (only for non-holiday, worked days)
            // Now using total_hours_seconds which includes lunch time
            if ($dayData['total_hours_seconds'] > 0 && $dayData['status'] != 'Holiday' && $dayData['status'] != 'WFH') {
                $totalWorkedSeconds += $dayData['total_hours_seconds'];
                $daysCount++;
            }
        }
        
        // Calculate average working hours (now including lunch)
        if ($daysCount > 0) {
            $avgSeconds = $totalWorkedSeconds / $daysCount;
            $avgHours = floor($avgSeconds / 3600);
            $avgMinutes = floor(($avgSeconds % 3600) / 60);
            $avgWorkTime = sprintf('%d:%02d', $avgHours, $avgMinutes);
        } else {
            $avgWorkTime = '0:00';
        }
        
        // Add employee to response data
        $employeeData[] = [
            'emp_id' => $empId,
            'name' => $employee['full_name'],
            'department' => $employee['department'],
            'position' => $employee['position'],
            'end_date' => $endDateEmp,
            'employment_status' => $empStatus,
            'avg_work_time' => $avgWorkTime,
            'days' => $employeeDays
        ];
    }
    
    // Calculate overall average working hours (including lunch)
    if (count($employeeData) > 0) {
        $totalAvgSeconds = 0;
        $employeeCount = 0;
        
        foreach ($employeeData as $emp) {
            if ($emp['avg_work_time'] != '0:00') {
                list($hours, $minutes) = explode(':', $emp['avg_work_time']);
                $totalAvgSeconds += ($hours * 3600) + ($minutes * 60);
                $employeeCount++;
            }
        }
        
        if ($employeeCount > 0) {
            $avgSeconds = $totalAvgSeconds / $employeeCount;
            $avgHours = floor($avgSeconds / 3600);
            $avgMinutes = floor(($avgSeconds % 3600) / 60);
            $summaryStats['avg_working_hours'] = sprintf('%d:%02d', $avgHours, $avgMinutes);
        }
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'period' => $period,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'summary_stats' => $summaryStats,
        'employee_data' => $employeeData,
        'issues_summary' => $issuesSummary,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_employees' => $totalEmployees,
            'limit' => $limit
        ],
        'system_settings' => [
            'work_start_time' => $work_start_time_str,
            'work_end_time' => $work_end_time_str,
            'expected_hours' => $expected_work_hours,
            'half_day_hours' => $half_day_hours
        ]
    ];
    
    echo json_encode($response);
    exit;
}

/**
 * Helper function to update issues summary
 */
function updateIssuesSummary(&$issuesSummary, $issueType, $empId, $empName) {
    if (!isset($issuesSummary[$issueType][$empId])) {
        $issuesSummary[$issueType][$empId] = [
            'name' => $empName,
            'count' => 0
        ];
    }
    $issuesSummary[$issueType][$empId]['count']++;
}

/**
 * Calculate holidays for a date range
 */
function calculateHolidays($startDate, $endDate, $empId, $pdo) {
    $holidays = [];
    
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    foreach ($period as $date) {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = $date->format('w'); // 0 = Sunday, 6 = Saturday
        $dayOfMonth = $date->format('j');
        $weekOfMonth = ceil($dayOfMonth / 7);
        
        // Check if it's a Sunday
        if ($dayOfWeek == 0) {
            $holidays[$dateStr] = 'Sunday';
            continue;
        }
        
        // Check if it's 2nd or 4th Saturday
        if ($dayOfWeek == 6 && ($weekOfMonth == 2 || $weekOfMonth == 4)) {
            $holidays[$dateStr] = 'Saturday Holiday';
            continue;
        }
        
        // Check if there's a holiday entry in the database for this employee
        $stmt = $pdo->prepare("SELECT * FROM time_entries WHERE employee_id = ? AND DATE(entry_time) = ? AND entry_type = 'holiday'");
        $stmt->execute([$empId, $dateStr]);
        if ($stmt->rowCount() > 0) {
            $holidays[$dateStr] = 'Company Holiday';
        }
        
        // Also check for holiday leave applications
        $stmt = $pdo->prepare("SELECT * FROM applications 
                              WHERE employee_id = ? AND status = 'approved' AND req_type = 'holiday'
                              AND DATE(?) BETWEEN DATE(start_date) AND DATE(end_date)");
        $stmt->execute([$empId, $dateStr]);
        if ($stmt->rowCount() > 0) {
            $holidays[$dateStr] = 'Company Holiday';
        }
    }
    
    return $holidays;
}

/**
 * Calculate work time for a single day
 */
function calculateDayWorkTime($entries, $date, $work_start_time_str, $work_end_time_str, $lunch_duration, $late_threshold, $required_hours, $half_day_hours, $pdo, $empId) {
    $timezone = new DateTimeZone("Asia/Kolkata");
    
    // Create expected start and end times for this day
    $expected_start_time = strtotime($date . ' ' . $work_start_time_str);
    $expected_end_time = strtotime($date . ' ' . $work_end_time_str);
    
    // Calculate expected work seconds for this day (including lunch)
    $expected_work_seconds = $expected_end_time - $expected_start_time;
    $expected_work_hours = $expected_work_seconds / 3600;
    
    // Check for approved leave applications for this date
    $stmt = $pdo->prepare("SELECT * FROM applications 
                          WHERE employee_id = ? AND status = 'approved'
                          AND DATE(?) BETWEEN DATE(start_date) AND DATE(end_date) 
                          ORDER BY created_at ASC LIMIT 1");
    $stmt->execute([$empId, $date]);
    $leaveApplication = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $is_half_day = false;
    $leave_type = null;
    
    if ($leaveApplication) {
        $leave_type = $leaveApplication['req_type'];
        if ($leave_type == 'half_day') {
            $is_half_day = true;
            $expected_work_seconds = $half_day_hours * 3600;
            $expected_work_hours = $half_day_hours;
        }
    }
    
    // Calculate lunch time
    $total_lunch_seconds = 0;
    $lunch_start = null;
    $long_lunch = false;

    foreach ($entries as $entry) {
        if ($entry['entry_type'] == 'lunch_start') {
            $lunch_start = strtotime($entry['entry_time']);
        } elseif ($entry['entry_type'] == 'lunch_end' && $lunch_start !== null) {
            $lunch_end = strtotime($entry['entry_time']);
            
            if ($lunch_end > $lunch_start) {
                $lunch_seconds = $lunch_end - $lunch_start;
                $total_lunch_seconds += $lunch_seconds;
                
                // Check if lunch is longer than allowed
                if ($lunch_seconds > ($lunch_duration * 60)) {
                    $long_lunch = true;
                }
            }
            $lunch_start = null;
        }
    }

    // Handle unmatched lunch_start
    if ($lunch_start !== null) {
        // Use last entry time if available
        if (!empty($entries)) {
            $last_entry = end($entries);
            $fallback_end = strtotime($last_entry['entry_time']);
            if ($fallback_end > $lunch_start) {
                $lunch_seconds = $fallback_end - $lunch_start;
                $total_lunch_seconds += $lunch_seconds;
                
                // Check if lunch is longer than allowed
                if ($lunch_seconds > ($lunch_duration * 60)) {
                    $long_lunch = true;
                }
            }
        }
    }

    $lunch_hours = floor($total_lunch_seconds / 3600);
    $lunch_minutes = floor(($total_lunch_seconds % 3600) / 60);
    $lunch_display = sprintf('%d:%02d', $lunch_hours, $lunch_minutes);

    // Get first and last entries
    $firstEntry = !empty($entries) ? $entries[0] : null;
    $lastEntry = !empty($entries) ? end($entries) : null;

    // Determine attendance state
    $state = "Absent";
    $minutesLate = 0;
    
    if (!empty($entries)) {
        // Check if first entry is a special type
        if ($firstEntry && in_array($firstEntry['entry_type'], ["regularization", "casual_leave", "sick_leave", "holiday"])) {
            $state = $firstEntry['entry_type'];
        }
        // Check if there's an approved leave record for this date
        elseif ($leaveApplication && in_array($leaveApplication['req_type'], ["regularization", "casual_leave", "sick_leave", "holiday", "half_day"])) {
            $state = $leaveApplication['req_type'];
        }
        // Check if employee punched in
        elseif ($firstEntry && $firstEntry['entry_type'] == "punch_in") {
            // Calculate minutes late
            $firstPunchTime = new DateTime($firstEntry['entry_time'], $timezone);
            $firstPunchTimestamp = $firstPunchTime->getTimestamp();
            
            if ($firstPunchTimestamp > $expected_start_time) {
                $minutesLate = ($firstPunchTimestamp - $expected_start_time) / 60;
            }
            
            if ($minutesLate <= $late_threshold) {
                $state = "Present";
            } else {
                $state = "Late";
            }
        }
    }
    
    // Additional check for holidays if still absent
    if ($state == "Absent") {
        // Check if any entry indicates a holiday
        foreach ($entries as $entry) {
            if ($entry['entry_type'] === 'holiday') {
                $state = "Holiday";
                break;
            }
        }
        
        // Check if there's a holiday in leave applications
        if ($state == "Absent" && $leaveApplication && $leaveApplication['req_type'] === 'holiday') {
            $state = "Holiday";
        }
    }

    // Calculate total worked time (from first punch_in to last punch_out, including lunch)
    $total_worked_seconds = 0;
    if (!empty($entries)) {
        $first_punch_time = strtotime($entries[0]['entry_time']);
        $last_punch_time = strtotime($entries[count($entries) - 1]['entry_time']);
        
        if ($last_punch_time > $first_punch_time) {
            $total_worked_seconds = $last_punch_time - $first_punch_time;
        }
    }
    
    // Calculate net work time (total time including lunch - this is now the main metric)
    $net_work_seconds = $total_worked_seconds;
    $net_hours = floor($net_work_seconds / 3600);
    $net_minutes = floor(($net_work_seconds % 3600) / 60);
    $net_display = sprintf('%d:%02d', $net_hours, $net_minutes);
    
    // Check for insufficient hours (now comparing total time including lunch)
    $insufficient_hours = false;
    if ($net_work_seconds < ($expected_work_seconds * 0.9) && $state == "Present") {
        $insufficient_hours = true;
    }
    
    // Get first punch time for display
    $first_punch_display = "N/A";
    if ($firstEntry && $firstEntry['entry_type'] == 'punch_in') {
        $firstPunch = new DateTime($firstEntry['entry_time'], $timezone);
        $first_punch_display = $firstPunch->format('H:i A');
    }
    
    // Get last punch time for display
    $last_punch_display = "N/A";
    if ($lastEntry && $lastEntry['entry_type'] == 'punch_out') {
        $lastPunch = new DateTime($lastEntry['entry_time'], $timezone);
        $last_punch_display = $lastPunch->format('H:i A');
    }
    
    return [
        'date' => $date, // This will be formatted later
        'punch_in' => $first_punch_display,
        'punch_out' => $last_punch_display,
        'lunch_duration' => $lunch_display,
        'total_hours' => $net_display, // Now showing total hours including lunch
        'total_hours_seconds' => $total_worked_seconds, // Total time including lunch
        'net_hours' => $net_display, // Same as total hours now
        'net_hours_seconds' => $net_work_seconds, // Same as total seconds now
        'status' => $state,
        'minutes_late' => $minutesLate,
        'long_lunch' => $long_lunch,
        'insufficient_hours' => $insufficient_hours,
        'is_half_day' => $is_half_day,
        'expected_hours' => $expected_work_hours,
        'entries' => $entries,
        'details' => '' // Initialize details field
    ];
}

// If no valid action specified
echo json_encode(['error' => 'Invalid action specified']);
exit;