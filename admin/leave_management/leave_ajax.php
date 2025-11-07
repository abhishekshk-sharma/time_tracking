<?php
require_once "../includes/config.php";

if(isset($_POST['info'])){
    $info = $_POST['info'];
    
    if($info == "getLeaveApplications"){
        // Query to get leave applications with employee details
        $stmt = $pdo->prepare("
            SELECT la.*, e.username, e.id 
            FROM leave_applications la 
            JOIN employees e ON la.employee_id = e.id 
            ORDER BY la.created_at DESC
        ");
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($applications) > 0){
            foreach($applications as $application){
                $statusClass = "status-" . $application['status'];
                $typeClass = "type-" . str_replace('_', '', $application['application_type']);
                
                echo "<tr>
                    <td>{$application['id']}</td>
                    <td>{$application['username']} ({$application['id']})</td>
                    <td><span class='type-badge {$typeClass}'>" . str_replace('_', ' ', $application['application_type']) . "</span></td>
                    <td>{$application['subject']}</td>
                    <td>{$application['start_date']} to {$application['end_date']}</td>
                    <td>" . date('M j, Y', strtotime($application['created_at'])) . "</td>
                    <td><span class='status-badge {$statusClass}'>{$application['status']}</span></td>
                    <td>
                        <button class='action-btn view-btn view-leave' data-id='{$application['id']}'>View</button>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='8' style='text-align:center;'>No leave applications found</td></tr>";
        }
    }
    
    if($info == "leave_modal"){
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("
            SELECT la.*, e.username, e.id 
            FROM leave_applications la 
            JOIN employees e ON la.employee_id = e.id 
            WHERE la.id = ?
        ");
        $stmt->execute([$id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($application);
    }
    
    if($info == "approveLeave" || $info == "rejectLeave"){
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE leave_applications SET status = ? WHERE id = ?");
        if($stmt->execute([$status, $id])){
            echo "success";
        } else {
            echo "error";
        }
    }
    
    if($info == "getCalendarEvents"){
        $month = $_POST['month'];
        $year = $_POST['year'];
        
        // Get first and last day of the month
        $firstDay = date("{$year}-{$month}-01");
        $lastDay = date("{$year}-{$month}-t");
        
        $stmt = $pdo->prepare("
            SELECT la.*, e.username 
            FROM leave_applications la 
            JOIN employees e ON la.employee_id = e.id 
            WHERE la.status = 'approved' 
            AND (
                (la.start_date BETWEEN ? AND ?) 
                OR (la.end_date BETWEEN ? AND ?)
                OR (la.start_date <= ? AND la.end_date >= ?)
            )
        ");
        $stmt->execute([$firstDay, $lastDay, $firstDay, $lastDay, $firstDay, $lastDay]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($events);
    }
}
?>