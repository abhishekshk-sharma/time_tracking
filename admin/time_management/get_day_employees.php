
<?php
require_once "../includes/config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if user is admin
$stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ? AND end_date IS NULL");
$stmt->execute([$_SESSION['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role'] != "admin") {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if (!isset($_GET['date'])) {
    echo json_encode(['success' => false, 'message' => 'Date parameter required']);
    exit;
}

$date = $_GET['date'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

try {
    // Build base query - include all employees who were working on the selected date
    // This means: employees whose hire_date <= selected date AND (end_date IS NULL OR end_date >= selected date)
    // Also exclude admin role
    $baseQuery = "FROM employees WHERE role != 'admin' AND hire_date <= ? AND (end_date IS NULL OR end_date >= ?)";
    $searchCondition = "";
    $params = [$date, $date]; // Parameters for date conditions
    
    if (!empty($search)) {
        $searchCondition = " AND full_name LIKE ?";
        $params[] = "%$search%";
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) " . $baseQuery . $searchCondition;
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalEmployees = $countStmt->fetchColumn();
    $totalPages = ceil($totalEmployees / $itemsPerPage);
    
    // Get employees with pagination
    $employeesQuery = "SELECT emp_id, full_name, end_date, status " . $baseQuery . $searchCondition . " ORDER BY full_name LIMIT $itemsPerPage OFFSET $offset";
    $employeesStmt = $pdo->prepare($employeesQuery);
    $employeesStmt->execute($params);
    $employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get time entries for the specific date
    $timeEntriesStmt = $pdo->prepare("
        SELECT te.*, e.full_name, e.end_date, e.status
        FROM time_entries te 
        JOIN employees e ON te.employee_id = e.emp_id 
        WHERE DATE(te.entry_time) = ? 
        AND e.role != 'admin'
        AND e.hire_date <= ?
        AND (e.end_date IS NULL OR e.end_date >= ?)
    ");
    $timeEntriesStmt->execute([$date, $date, $date]);
    $timeEntries = $timeEntriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize time entries by employee
    $employeeStatus = [];
    foreach ($timeEntries as $entry) {
        $employeeId = $entry['employee_id'];
        if (!isset($employeeStatus[$employeeId])) {
            $employeeStatus[$employeeId] = [
                'status' => 'working',
                'details' => '',
                'end_date' => $entry['end_date'],
                'emp_status' => $entry['status']
            ];
        }
        
        if ($entry['entry_type'] === 'holiday') {
            $employeeStatus[$employeeId]['status'] = 'holiday';
            $employeeStatus[$employeeId]['details'] = $entry['notes'];
        } elseif ($entry['entry_type'] === 'half_day') {
            $employeeStatus[$employeeId]['status'] = 'half_day';
            $employeeStatus[$employeeId]['details'] = $entry['notes'];
        }
    }
    
    // Prepare response data
    $responseData = [];
    $stats = [
        'total' => 0,
        'working' => 0,
        'holiday' => 0,
        'halfday' => 0,
        'absent' => 0,
        'resigned' => 0,
        'inactive' => 0
    ];
    
    foreach ($employees as $employee) {
        $employeeId = $employee['emp_id'];
        $endDate = $employee['end_date'];
        $empStatus = $employee['status'];
        
        // Determine employee status for the day
        if (isset($employeeStatus[$employeeId])) {
            $statusData = $employeeStatus[$employeeId];
            $dayStatus = $statusData['status'];
            $details = $statusData['details'];
        } else {
            $dayStatus = 'absent';
            $details = '';
        }
        
        // Add employment status information
        $employmentInfo = "";
        if ($empStatus === 'inactive') {
            $employmentInfo = " (Inactive Employee)";
            $stats['inactive']++;
        } elseif ($endDate !== null) {
            $employmentInfo = " (Resigned on " . date('M j, Y', strtotime($endDate)) . ")";
            $stats['resigned']++;
        }
        
        $details = $details ? $details . $employmentInfo : $employmentInfo;
        
        $responseData[] = [
            'id' => $employeeId,
            'name' => $employee['full_name'],
            'status' => $dayStatus,
            'details' => $details,
            'end_date' => $endDate,
            'employment_status' => $empStatus
        ];
        
        $stats['total']++;
        $stats[$dayStatus]++;
    }
    
    echo json_encode([
        'success' => true,
        'employees' => $responseData,
        'stats' => $stats,
        'totalPages' => $totalPages,
        'totalEmployees' => $totalEmployees,
        'currentPage' => $page
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>