<?php
    require_once "includes/config.php";
    
    // if(isset($_SESSION['ack'])){
        //     unset($_SESSION['info']);
        //     unset($_SESSION['ack']);
        // }
        $userID = isset($_SESSION['getUserById'])?$_SESSION['getUserById']:null;
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
        $stmt->execute([$userID]);
        $emp_details = $stmt->fetch(PDO::FETCH_ASSOC);
        $join = new DateTime($emp_details['hire_date'], new DateTimeZone("asia/kolkata"));
        $status = $emp_details['status'];


            $id = isset($_SESSION['id'])?$_SESSION['id']:null;
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
            $stmt->execute([$id]);
            $details = $stmt->fetch(PDO::FETCH_ASSOC);
            if($details['role'] != "admin"){
                header("location: ../index.php");
            }
        
        $stmt = $pdo->query("SELECT * FROM departments");
        $dep = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        if($userID == null){
            $_SESSION['info'] = "Please Select User First!";
            header("location: index.php");
    
        }

?>
<?php
    require_once "includes/header.php";
?>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 10px 10px;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            font-size: 28px;
        }
        
        .logo h1 {
            font-weight: 600;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
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
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        
        .employee-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .employee-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary);
        }
        
        .employee-info h2 {
            margin-bottom: 5px;
        }
        
        .employee-info p {
            color: var(--gray);
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .detail-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .detail-card h3 {
            font-size: 16px;
            color: var(--secondary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-item .label {
            color: var(--gray);
            font-weight: 500;
        }
        
        .detail-item .value {
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .activity-table {
            margin-top: 30px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }
        
        select, input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .export-btn {
            background-color: var(--success);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .Add-btn {
            background-color: var(--secondary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-present {
            background-color: #e7f6e9;
            color: #2ecc71;
        }
        
        .status-absent {
            background-color: #fbebec;
            color: #e74c3c;
        }
        
        .status-lunch {
            background-color: #fef5e9;
            color: #f39c12;
        }
        
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        
        .view-btn {
            background: var(--secondary);
            color: white;
        }
        
        .edit-btn-sm {
            background: var(--warning);
            color: white;
        }
        
        .delete-btn {
            background: var(--danger);
            color: white;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
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
            height: 600px;
            overflow-y: scroll;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .modal-content::-webkit-scrollbar{
            display:none;
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
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .cancel-btn {
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .save-btn {
            padding: 10px 20px;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                left: 3%;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .employee-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
    
    
    <style>
        .content-area{

            position: relative;
            width: 100%;
            overflow-x: scroll;
        }
        .content-area::-webkit-scrollbar{
            display: none;
        }
        #attndtable{

            position: relative;
            width: 98%;
            overflow-x: scroll;

        }
        .container {
            position: flex;
            margin: 20px auto;            
        }
        
       
</style>

    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <a href="index.php" class="nav-item" data-target="employee-section">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
                <a href="" class="nav-item active" >
                    <i class="fas fa-users"></i>
                    <span>Employee</span>
                </a>
                <a href="create.php" class="nav-item" onclick="window.location.href = 'create.php'">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Employee</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='schedule.php'">
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
                <div class="employee-header">
                    <!-- <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Employee Photo" class="employee-photo"> -->
                    <div class="employee-info">
                        <h2><?php echo $emp_details['full_name']; ?></h2>
                        <p><?php echo $emp_details['emp_id']; ?> | <?php echo $emp_details['department']; ?></p>
                    </div>
                </div>

                

                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="window.location.href='user.php'">
                        <i class="fas fa-door-open"></i> Back
                    </button>


                </div>

                <div class="activity-table">
                    <div class="table-header">
                        <h3 class="section-title">
                            <i class="fas fa-history"></i>
                            Monthly Activity
                        </h3>
                        <div class="filters">
                            <div class="filter-group">
                                <label for="dateFilter">Date Range</label>
                                <select id="dateFilter" data-id="<?php echo $userID; ?>">                                    
                                    <option class="select-item" value="thisMonth" >Current Month</option>
                                    <option class="select-item" value="lastMonth" >Last Month</option>
                                    <option class="select-item" value="custom">Custom Range</option>
                                </select>
                            </div>
                            
                        </div>
                    </div>

                      <div id="attndtable">
                           <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Punch In</th>
                                    <th>Lunch Start</th>
                                    <th>Lunch End</th>
                                    <th>Punch Out</th>
                                    <th>Total Hours</th>
                                    <th>Status</th>
                                   
                                </tr>
                            </thead>
                            <tbody id="Tbody">
                                
                                <tr>
                                    <td>Jun 10, 2023</td>
                                    <td>08:50 AM</td>
                                    <td>04:45 PM</td>
                                    <td>12:20 PM</td>
                                    <td>01:05 PM</td>
                                    <td>7h 55m</td>
                                    <td><span class="status-badge status-present">Present</span></td>
                                    <td>
                                        <button class="action-btn view-btn"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit-btn-sm"><i class="fas fa-edit"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jun 9, 2023</td>
                                    <td>10:30 AM</td>
                                    <td>06:00 PM</td>
                                    <td>01:00 PM</td>
                                    <td>01:45 PM</td>
                                    <td>7h 30m</td>
                                    <td><span class="status-badge status-lunch">Late</span></td>
                                    <td>
                                        <button class="action-btn view-btn"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit-btn-sm"><i class="fas fa-edit"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Jun 8, 2023</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>0h</td>
                                    <td><span class="status-badge status-absent">Absent</span></td>
                                    <td>
                                        <button class="action-btn view-btn"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit-btn-sm"><i class="fas fa-edit"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                      </div>
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



<?php
    // $currentTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    // $date = $currentTime;
    require_once "includes/footer.php";

?>

<script>
    
    
    $(document).ready(function(){




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

        // This is filter for employees attendance history.

        $(document).on("change", "#dateFilter",function(){
            let select = $(this).val();
            let id = $(this).data("id");
            
            if(select == 'lastMonth'){
                let info = "filterLastMonth";
                // alert(id);

                $.ajax({
                    url: "time_management/filter.php",
                    method: "POST",
                    data: {info:info, select: select, id:id},
                    success: function(e){
                        if(e){
                        $("#Tbody").html(e);
                        // alert("hello "+e);
                        }
                        else{
                            // alert("hello from else"+e);
                        
                        }
                    }
                });
            }
            if(select == 'thisMonth'){
                detailsById();
            }

           
            if(select == 'custom'){
                Swal.fire({
                title: "Select Departure Range",
                html: `
                  <label>From: <input type="date" id="date-from" class="swal2-input"></label>
                  <label>To: <input type="date" id="date-to" class="swal2-input"></label>
                `,
                focusConfirm: false,
                preConfirm: () => {
                  const from = document.getElementById('date-from').value;
                  const to = document.getElementById('date-to').value;

                  if (!from || !to) {
                    Swal.showValidationMessage('Both dates are required');
                    return false;
                  }
                  else if(from > to){
                    Swal.showValidationMessage("'From' Date Can't be Less then 'To' Date!");
                    return false;
                  }

                  return { from, to };
                }
                }).then((result) => {
                  if (result.isConfirmed) {
                    const { from, to } = result.value;
                    Swal.fire(`Selected Range`, `From: ${from}<br>To: ${to}`, 'success');

                    // You can now use jQuery to store or send these values
                    get_time(from, to);
                  }
                });

               
                
            }
            function get_time(from, to){
                let id = $("#dateFilter").data("id");
                let from1 = from;
                let to1 = to;
                let info = "filterCustom";
                // alert(id);

                $.ajax({
                    url: "time_management/filter.php",
                    method: "POST",
                    data: {info:info, id:id, from:from1, to:to1},
                    success: function(e){
                        if(e){
                        $("#Tbody").html(e);
                        // alert("hello "+e);
                        }
                        else{
                            // alert("hello from else"+e);
                        
                        }
                    }
                });
               
            }
        });
        
        
        // <<<<<<<======== ALL AJAX request ========>>>>>>>>>>
        // 
        // var hello = '<?php //echo $date->format("Y-m-d H:i:s"); ?>';
        // alert(hello);
        detailsById();
        function detailsById(){
            var info = "detailsById";
            var id = '<?php echo $userID; ?>';
          
            $.ajax({
                url: "time_management/total.php",
                method: "POST",
                data: {info:info, id: id},
                success: function(e){
                    if(e){
                        $("#Tbody").html(e);
                        // alert("hello "+e);
                    }
                    
                }
            });
        }

        // <<<<<<<========== This is for admin side user panel ===========>>>>>>>>>>


        $(document).on("click", ".searchDate", function(){
            let date = $(this).data('date');
            // alert("Please Try Again! Something Went Wrong.");
            let info = "createdate";
            $.ajax({
                url: "includes/session_create.php",
                method: "POST",
                data: {info:info, date:date},
                success: function(e){
                    if(e == 0){
                        window.location.href = "ByDayDetails.php";
                    }
                    else if(e == 1){
                        alert("Please Try Again! Something Went Wrong.");
                    }

                }
            });
        });
               
        
        // Table sorting functionality (simplified)
        const headers = document.querySelectorAll('th');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                // This would implement sorting in a real application
                console.log('Sorting by ' + header.textContent);
            });
        });

        function checksession(){
            var info = "check";
            // alert("Something Went Wrong! from function");
            $.ajax({
                    url: "includes/checksession.php",
                    method: "POST",
                    data: {info:info},
                    success: function(e){
                        if(e == "expired"){
                            window.location.href = "../login.php";
                        }
                    }

                });
        }

        // This is all about refreshing the page and see if the session is not older than 5 minutes
        $(document).ready(function() {
            checksession();
        });

        // Run on any click or touch
        $(document).on("click touchstart", function() {
            checksession();
        });

        document.addEventListener("visibilitychange", function() {
            if (document.visibilityState === "visible") {
                checksession(); // Run your session validation logic
                // Optionally force a reload:
                // location.reload();
            }
        });

        


    });
</script>