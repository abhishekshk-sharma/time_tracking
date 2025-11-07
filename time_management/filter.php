<?php
require_once "../includes/config.php";


        

$userid = isset($_SESSION['id'])?$_SESSION['id']:null;
if($userid == null){
    header("location: ../login.php");
}

$click = isset($_POST['click'])?$_POST['click']:'';

if($click == "filterReq"){

    $status = isset($_POST['status'])?$_POST['status']: '';
    $type = isset($_POST['type'])?$_POST['type']: '';
    $limit = isset($_POST['limit'])?$_POST['limit']: '';

    $time = new DateTime('now', new DateTimeZone("Asia/Kolkata"));
    

    $conditions = [];
    $params = [];

    $conditions[] = "employee_id = ?";
    $params[] = "$userid";
    
    if($status !== "all" || $type !== "all" || $limit !== "all"){

        if($type != "all" && $status != "all"){
            $conditions[] = "req_type = ? AND status = ?";
            $params[] = "$type";
            $params[] = "$status";
        }
        elseif($type != "all"){
            $conditions[] = "req_type = ? ";
            $params[] = "$type";
        }
        elseif($status != "all"){   
            $conditions[] = "status = ? ";
            $params[] = "$status";
        }

        if($limit == "month"){
            $conditions[] = "MONTH(created_at) = MONTH(CURRENT_DATE) ";
            
        }
        else if($limit == "quarter"){
            $conditions[] = "QUARTER(created_at) = QUARTER(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE) ";
        }
        else if ($limit == "year"){
            $conditions[] = "YEAR(created_at) = YEAR(CURRENT_DATE) ";
        }

        // $whereClause = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        // $whereClause = "WHERE " . implode(" AND ", $conditions);

        if (count($conditions) == 1) {
            $whereClause = "WHERE ".$conditions[0];
        } else{
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }

        // $params = implode(",", $paramsx);
        $sql = "SELECT * FROM applications $whereClause";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($fetch) >= 1){
            

            $output = "";
            foreach($fetch as $row){

                $create = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));           
                $time = $create->format("Y-m-d H:i:s");

                $start = new DateTime($row['start_date'], new DateTimeZone("asia/kolkata"));
                $end = isset($row['end_date'])? (new DateTime($row['end_date'], new DateTimeZone("asia/kolkata")))->format("M d"):"";
                $output .= "<tr>
                                <td>".$row['created_at']."</td>
                                <td>".$row['req_type']."</td>
                                <td>".$row['subject']."</td>
                                <td>".$start->format("M d")." - ".$end."</td>
                                <td><span class='status-badge status-".$row['status']."'>".$row['status']."</span></td>
                                <td>
                                    <button class='action-btn view-btn view_request' data-id='".$row['employee_id']."' data-type='".$row['req_type']."' data-time='".$time."''>
                                        <i class='fas fa-eye'></i> View
                                    </button>
                                </td>
                            </tr>";
            }

            echo $output;
            exit;
        }
        else{
            echo "<p style='color:red;'>Not Found!</p>";
        }
    }
    else{
        $id = $userid;
        $output = "";
        
        $stmt = $pdo->prepare("SELECT * FROM `applications` WHERE employee_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE) ORDER BY id DESC");
        $stmt->execute([$id]);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($fetch) >= 1){
            

            $output = "";
            foreach($fetch as $row){
                $create = new DateTime($row['created_at'], new DateTimeZone("asia/kolkata"));           
                $time = $create->format("Y-m-d H:i:s");

                $start = new DateTime($row['start_date'], new DateTimeZone("asia/kolkata"));
                $end = isset($row['end_date'])? (new DateTime($row['end_date'], new DateTimeZone("asia/kolkata")))->format("M d"):"";
                $output .= "<tr>
                                <td>".$row['created_at']."</td>
                                <td>".$row['req_type']."</td>
                                <td>".$row['subject']."</td>
                                <td>".$start->format("M d")." - ".$end."</td>
                                <td><span class='status-badge status-".$row['status']."'>".$row['status']."</span></td>
                                <td>
                                    <button class='action-btn view-btn view_request' data-id='".$row['employee_id']."' data-type='".$row['req_type']."' data-time='".$time."''>
                                        <i class='fas fa-eye'></i> View
                                    </button>
                                </td>
                            </tr>";
            }

            echo $output;
            exit;
        }
        else{
            echo "<p style='color:red;'>Not Found!</p>";
        }
    }

}

?>