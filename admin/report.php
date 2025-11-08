<?php
    require_once "includes/config.php";

    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: ../login.php");
    }

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->execute([$userID]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

            if($details['role'] != "admin"){
            header("location: ../index.php");
        }


    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM applications WHERE YEAR(created_at) = YEAR(CURRENT_DATE) ORDER BY id");
    $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
    // echo "<script>alert('".$fetch['total']."')</script>";


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
            position: flex;
            margin: 20px auto; 
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
            position: relative;
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            /*width: 100%;*/
            /*left: -18%;*/
            
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
            width: 100%;
            overflow: initial;
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
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
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
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-left: 4px solid var(--secondary);
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 8px;
        }
        
        .stat-card.pending {
            border-left-color: var(--warning);
        }
        
        .stat-card.approved {
            border-left-color: var(--success);
        }
        
        .stat-card.rejected {
            border-left-color: var(--danger);
        }
        
        .stat-card p {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
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
            cursor: pointer;
        }
        
        th:hover {
            background-color: #f0f2f5;
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
        
        .status-pending {
            background-color: #fff4e6;
            color: var(--warning);
        }
        
        .status-approved {
            background-color: #e7f6e9;
            color: var(--success);
        }
        
        .status-rejected {
            background-color: #fbebec;
            color: var(--danger);
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
            position: absolute;
            left: calc(50% - 300px);
            top: 5%;
            background: white;
            border-radius: 10px;
            width: 600px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
        }
        
        .application-details {
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 15px;
        }
        
        .detail-label {
            width: 120px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .detail-value {
            flex: 1;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
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
        
        .btn-secondary {
            background: var(--gray);
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
        
        .attachment-link {
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .attachment-link:hover {
            text-decoration: underline;
        }
        
        #imageModal{
            display:none; 
            position:fixed; top:2%; 
            left:50%; 
            transform:translateX(-50%);
            background:#fff;        
            padding:20px; 
            border:1px solid #ccc; 
            z-index:1001;
            height: 95vh;
            overflow-y: scroll;

        }
        
        #modal-content::-webkit-scrollbar{
            display: none;
        }
        
        .paginations-nav{
            position: relative; 
            margin: 90px 45%;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                
                left: 2%;
            }
            
            .content-area {
            
            width: 100vw;
           
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filters {
                flex-direction: column;
            }
            #reporttable{
            position: relative;
            width: 98%;
            overflow-x: scroll;
            }
            
            .modal-content {

                left: calc(50% - 204px);
                top: 3%;

            }
            #imageModal{
            width: 95vw;
            height: auto;
            margin: auto;
            }
            .modal-content{
                margin-left: 5%;
                /*margin: 10px auto;*/
                width: 99vw;
            }
            .paginations-nav{
                margin: 90px 28%;
            }
        }
    </style>
    
    <style>
    
        .content-area{
            position: relative;
            width: 100%;
            overflow-y: scroll;
        }
        .content-area::-webkit-scrollbar{
            display: none;
        }
        
        #reporttable{
            position: relative;
            width: 100%;
            overflow-x: scroll;
        }
</style>


    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <a href="index.php" class="nav-item " data-target="employee-section">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href = 'see_employees.php'">
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
                <a href="report.php" class="nav-item active" onclick="window.location.href='report.php'">
                    <i class="fas fa-chart-bar"></i>
                    <span>Applications</span>
                </a>
                <a href="adminentry.php" class="nav-item " onclick="window.location.href='adminentry.php'">
                        <i class="fas fa-house-user"></i>
                        <span>Work From Home</span>
                </a>
                <a href="employees_history.php" class="nav-item " onclick="window.location.href='employees_history.php'">
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
                    <i class="fas fa-file-alt"></i>
                    Employee Applications
                </h2>
                
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3>Total Applications</h3>
                        <p id="totappl">24</p>
                    </div>
                    <div class="stat-card pending">
                        <h3>Pending</h3>
                        <p id='totpend'>8</p>
                    </div>
                    <div class="stat-card approved">
                        <h3>Approved</h3>
                        <p id='totappr'>12</p>
                    </div>
                    <div class="stat-card rejected">
                        <h3>Rejected</h3>
                        <p id='totrej'>4</p>
                    </div>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="typeFilter">Type</label>
                        <select id="typeFilter">
                            <option value="">All Types</option>
                            <option value="casual_leave">Casual Leave</option>
                            <option value="sick_leave">Sick Leave</option>
                            <option value="half_leave">Half Day Leave</option>
                            <option value="complaint">Complaint</option>
                            <option value="regularization">Regularization</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="dateFilter">Date Range</label>
                        <select id="dateFilter">
                            <option value="">All Time</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="searchInput">Search</label>
                        <input type="text" id="searchInput" placeholder="Employee name or ID">
                    </div>
                </div>
                
                <div id="reporttable">
                    <table >
                    <thead>
                        <tr>
                            
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Date Range</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="Tbody">
                        
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Detail Modal -->
    <div class="modal" id="applicationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Application Details</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="application-details">
                <div class="detail-item">
                    <div class="detail-label">Application ID:</div>
                    <div class="detail-label" id="user_id" value="" style="display:none;"></div>
                    <div class="detail-value" id="detail-id" value="">APP-1001</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Employee:</div>
                    <div class="detail-value" id="detail-employee">Sarah Johnson (EMP-2021)</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Request Type:</div>
                    <div class="detail-value" id="detail-type" value=""></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Subject:</div>
                    <div class="detail-value" id="detail-subject">Family vacation</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label formissingpunchout_time">Date Range:</div>
                    <div class="detail-value" id="detail-dates" value="">Jun 20, 2023 - Jun 22, 2023</div>
                    <input type="hidden" id="punchouttimestore" value="">
                    <input type="hidden" id="punchintimestore" value="">
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Half Day:</div>
                    <div class="detail-value" id="detail-halfday">No</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value" id="detail-description">I need to take a few days off for a family vacation that was planned months ago. I'll be available on phone for any urgent matters.</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Attachment:</div>
                    <div class="detail-value" id="detail-attachment">
                        <i class="fas fa-paperclip"></i> 
                        <a href="#" class="attachment-link" val="">
                        </a>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Applied On:</div>
                    <div class="detail-value" id="detail-applied">Jun 15, 2023 at 10:30 AM</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge status-pending" id="detail-status">Pending</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn btn-danger" id="rejectBtn" value="">
                    <i class="fas fa-times-circle"></i> Reject
                </button>
                <button class="btn btn-success" id="approveBtn" value="">
                    <i class="fas fa-check-circle"></i> Approve
                </button>
            </div>
        </div>
    </div>


    <div id="imageModal" >
        <img id="modalImage" src="" alt="Attachment" style="max-width:100%; height:auto; margin-bottom:20px;">
        <button class="btn btn-secondary" onclick="document.getElementById('imageModal').style.display='none'">Close</button>
    </div>

    <nav aria-label="..." class="paginations-nav">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" tabindex="-1" id="prev" style="cursor: pointer;">Previous</a>
            </li>
            <li class="page-item"><a class="page-link" id="sel_limit" val=""><p id="setlimit">10</p></a></li>
            <li class="page-item" >
                <a class="page-link" id="next" style="cursor: pointer;">Next</a>
            </li>
        </ul>
    </nav>

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

        let row = <?php echo $fetch['total']; ?>;
        let limit = 10;
        let totalPages = Math.ceil(row/limit);
        let currentPage = 1;
        let offset = (currentPage - 1) * limit;
        let statusFilter = "";
        let typeFilter = "";
        let dateFilter = "";
        let searchInput = "";

        function getfilters(){
            let status = $("#statusFilter").val();
            let type = $("#typeFilter").val();
            let datefil = $("#dateFilter").val();
            let searId = $("#searchInput").val();
            statusFilter = status === "" ? undefined : status;
            typeFilter = type === "" ? undefined : type;
            dateFilter = datefil === "" ? undefined : datefil;
            searchInput = searId === "" ? undefined : searId;
          
        }
        $(document).on("change", "#statusFilter", function(){
            getfilters();
            totalPages = Math.ceil(row/limit);
            currentPage = 1;
            getRequests(currentPage, limit, statusFilter, typeFilter, dateFilter, searchInput);
        });
        $(document).on("change", "#typeFilter", function(){
            getfilters();
            totalPages = Math.ceil(row/limit);
            currentPage = 1;
            getRequests(currentPage, limit, statusFilter, typeFilter, dateFilter, searchInput);
        });
        $(document).on("change", "#dateFilter", function(){
            getfilters();
            totalPages = Math.ceil(row/limit);
            currentPage = 1;
            getRequests(currentPage, limit, statusFilter, typeFilter, dateFilter, searchInput);
        });
        $(document).on("input", "#searchInput", function(){
            getfilters();
            totalPages = Math.ceil(row/limit);
            currentPage = 1;
            getRequests(currentPage, limit, statusFilter, typeFilter, dateFilter, searchInput);
        });



        $("#sel_limit").click(function(){

            Swal.fire({
              title: 'Select Page Limit',
              input: 'select',
              inputOptions: {
                '10': '10',
                '25': '25',
                '50': '50',
                'custom': 'Custom'
              },
              inputPlaceholder: 'Choose limit',
              showCancelButton: true,
              confirmButtonText: 'Next',
              preConfirm: (value) => {
                if (value === 'custom') {
                  return Swal.fire({
                    title: 'Enter Custom Limit',
                    input: 'number',
                    inputAttributes: {
                      min: 1,
                      step: 1
                    },
                    inputPlaceholder: 'e.g. 100',
                    showCancelButton: true,
                    inputValidator: (val) => {
                      if (!val || val <= 0) {
                        return 'Please enter a valid number';
                      }
                    }
                  }).then(result => {
                    if (result.isConfirmed) {
                      return result.value;
                    }
                  });
                }
                return value;
              }
            }).then((result) => {
              if (result.isConfirmed) {
                let selectedLimit = result.value;
                // You can now use selectedLimit in your pagination logic
                $("#setlimit").html(selectedLimit);
                limit = selectedLimit;
                totalPages = Math.ceil(row/limit);
                getfilters();
                currentPage = 1;
                getRequests(currentPage, selectedLimit, statusFilter, typeFilter, dateFilter, searchInput);
              }
            });
        });

        $("#prev").click(function(e){
            e.preventDefault();
            
            if(currentPage > 1){
                currentPage--;
                
                $("#next").prop("dissabled", false);
                $("#next").css("opacity", 1);
                $("#prev").prop("dissabled", false);
                $("#prev").css("opacity", 1);
                getfilters();
                getRequests(currentPage, limit, statusFilter, typeFilter, dateFilter, searchInput);
            }
            else{
                $("#prev").prop("dissabled", true);
                $("#prev").css("opacity", 0.5);
                $("#next").prop("dissabled", false);
                $("#next").css("opacity", 1);
            }
        });
        $("#next").click(function(e){
            e.preventDefault();
            if(currentPage  < totalPages){
                currentPage++;
                
                $("#next").prop("dissabled", false);
                $("#next").css("opacity", 1);
                $("#prev").prop("dissabled", false);
                $("#prev").css("opacity", 1);
                getfilters();
                getRequests(currentPage, limit, statusFilter, typeFilter, dateFilter, searchInput);
            }
            else{
                $("#next").prop("dissabled", true);
                $("#next").css("opacity", 0.5);
                $("#prev").prop("dissabled", false);
                $("#prev").css("opacity", 1);
            }
        });


        getRequests();
        function getRequests(pages1 = 1, limit1  = 10, statusFilter1, typeFilter1, dateFilter1, searchInput1){
            let info = "getRequests";
            let pages = pages1;
            let limit = limit1;
            let statusFilter = statusFilter1;
            let typeFilter = typeFilter1;
            let dateFilter = dateFilter1;
            let searchInput = searchInput1;
            
            // alert(pages +" and limit: "+ limit +" ");
            // console.log(statusFilter +" "+ typeFilter +" "+ dateFilter+" "+searchInput);

            $.ajax({
                url: "time_management/request.php",
                method: "POST",
                dataType: "json",
                data: {info:info, pages:pages, limit:limit, statusFilter:statusFilter, typeFilter:typeFilter, dateFilter:dateFilter, searchInput:searchInput},
                success: function(e){
                    if(e.output){
                        $("#Tbody").html(e.output);
                         applicationsreport();
                        // let currentPage = 1;
                        totalPages = Math.ceil(e.row/limit);
                        if(currentPage  < totalPages){
                            
                            $("#next").prop("dissabled", false);
                            $("#next").css("opacity", 1);
                            
                        }else{
                            $("#next").prop("dissabled", true);
                            $("#next").css("opacity", 0.5);
                        }
                        // alert("totalpages "+totalPages);
                        // $("#prev").trigger("click", true);

                    }
                    else{
                        $("#Tbody").html(e.error);
                         applicationsreport();
                    }
                }
            });
        }

        applicationsreport();
        function applicationsreport(){

            let info = "applicationsreport";

            $.ajax({
                url: "time_management/request.php",
                method: "POST",
                dataType: "json",
                data: {info:info},
                success: function(e){
                    $("#totappl").html(e.total);
                    $("#totpend").html(e.pending);
                    $("#totappr").html(e.approved);
                    $("#totrej").html(e.rejected);
                }
            })
        }

        $(document).on("click", ".view_request", function(){
            let info = "req_modal";
            let id = $(this).data("id");
            let type = $(this).data("type");
            let time = $(this).data("time");

            $.ajax({
                url: "time_management/request.php",
                method: "POST",
                dataType: 'json', 
                data: {info:info, id:id, type:type, time:time},
                success: function(e){
                    // console.log(e);
                    $("#detail-id").html(e.id);
                    $("#detail-id").val(e.id);
                    $("#user_id").val(e.emp_id);
                    $("#detail-employee").html(e.name+" ("+e.emp_id+")");
                    $("#detail-type").html(e.req_type);
                    $("#detail-type").val(e.req_type);
                    $("#detail-subject").html(e.subject);
                    $("#detail-dates").html(e.startdate+" / "+e.enddate);
                    $("#detail-dates").val(e.startdate+" / "+e.enddate);
                    $("#punchintimestore").val(e.startdate);
                    $("#punchouttimestore").val(e.enddate);
                    $("#detail-halfday").html(e.halfday);
                    $("#detail-description").html(e.description);
                    $(".attachment-link").html(e.file);
                    $(".attachment-link").val(e.file);
                    $("#detail-applied").html(e.createdat);
                    $("#detail-status").html(e.status);
                    $("#rejectBtn").val(e.id);
                    $("#approveBtn").val(e.id);

                    if(e.status == "approved"){
                       
                        $("#approveBtn").css("opacity", 0.6);
                        $("#approveBtn").prop("disabled", true);
                        $("#rejectBtn").prop("disabled", false);                  
                        $("#rejectBtn").hide();
                        $("#approveBtn").show();
                        $("#approveBtn").html('Approved by '+e.actionby);

                       
                    }
                    if(e.status == "rejected"){
                        $("#rejectBtn").css("opacity", 0.6);
                        $("#rejectBtn").prop("disabled", true);
                        $("#approveBtn").prop("disabled", false);   
                        $("#approveBtn").hide();
                        $("#rejectBtn").show();
                        $("#rejectBtn").html('Rejected by '+e.actionby);

                    }
                    if(e.status == "pending"){
                        $("#approveBtn").css("opacity", 1);
                        $("#rejectBtn").css("opacity", 1);

                        $("#approveBtn").show();
                        $("#rejectBtn").show();
                        $("#rejectBtn").prop("disabled", false);
                        $("#approveBtn").prop("disabled", false);   

                    }
                    
                    if(e.req_type === "punch_Out_regularization"){
                        $(".formissingpunchout_time").html("Requested Punch Out Time:")
                    }
                    else{
                        $(".formissingpunchout_time").html("Date Range:")
                    }
                    
                }
            });

            $("#applicationModal").show();
        });


    $("#rejectBtn").click(function(){

  Swal.fire({
    title: 'Are you Sure to Reject!',
    icon: "info",
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Next',
    cancelButtonText: 'Cancel',
   
    }).then((result) => {
    if (result.isConfirmed) {
      

      Swal.fire({
        title: 'Confirm Submission',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        cancelButtonText: 'Edit',
        reverseButtons: true
      }).then((finalResult) => {
        if (finalResult.isConfirmed) {
          // Final submission logic here
          Swal.fire({
            title: 'Notification Saved',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
          doReject();
          // Example: send to backend
          // submitNotification(title, desc);
        }
      });
    }
  });




    function doReject(){
        let user_id = $("#user_id").val();
        let app_id = $("#detail-id").val();
        let created_by = '<?php echo $userID; ?>';
        let info = "reject"
        let val = $("#rejectBtn").val();
        let status = "rejected";
        $.ajax({
            url: "time_management/request.php",
            method: "POST",
            data: {info:info, val:val, status:status, created_by:created_by, user_id:user_id, app_id:app_id},
            success: function(e){
                if(e == 0){
                    $("#applicationModal").hide();
                    getRequests();
                 
                }
                else{
                    swal.fire({
                        text: "something went wrong",
                        icon: "error"
                    });
                }
            }
        });
    }
            
    });

    $("#approveBtn").click(function(){



    Swal.fire({
    title: 'Are You Sure!',
    icon: "info",
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Next',
    cancelButtonText: 'Cancel',
    
    }).then((result) => {
    if (result.isConfirmed) {
      
      Swal.fire({
        title: 'Confirm Submission',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        cancelButtonText: 'Edit',
        reverseButtons: true
      }).then((finalResult) => {
        if (finalResult.isConfirmed) {
          // Final submission logic here
          
            checksession();
      
            let requestedtype = $("#detail-type").val();

            if(requestedtype.trim() == "punch_Out_regularization"){ 
                let req_time = $("#punchouttimestore").val()
                Swal.fire({
                    title:"Please Set The Requested Punch Out Time in Employee Attendance",
                    html:"<input type='time' id='punchouttime' class='swal2-input' placeholder='Requested Punch Out Time' value='"+req_time+"'>",
                    confirmButtonText: 'Submit',
                    showCancelButton: true,
                    preConfirm: () => {
                        const punchouttime = Swal.getPopup().querySelector('#punchouttime').value;
                        if (!punchouttime) {
                            Swal.showValidationMessage(`Please enter the requested punch out time`);
                        }
                        return { punchouttime: punchouttime }
                    }
                }).then((timeResult) => {
                    if (timeResult.isConfirmed) {
                        const punchouttime = timeResult.value.punchouttime;
                        if (punchouttime) {
                            $("#punchouttime").val(punchouttime);
                            doapprove_punchouttime(punchouttime);
                            Swal.fire({
                                title: 'Notification Saved',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Action Not Completed',
                                text: 'You must set the requested punch out time to approve this request.',
                                icon: 'error',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        Swal.fire({
                            title: 'Action Not Completed',
                            text: 'You must set the requested punch out time to approve this request.',
                            icon: 'error',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                });

            } 
            if(requestedtype.trim() == "work_from_home"){ 
                // let req_time = $("#punchouttimestore").val()
                doapprove_wfh();
            } 
            else{
                doapprove();
            }
          
          // Example: send to backend
          // submitNotification(title, desc);
        }
      });
    }
  });





    function doapprove(){
        let app_id = $("#detail-id").val();
        let user_id = $("#user_id").val();
        let created_by = '<?php echo $userID; ?>';
        let date_range = $("#detail-dates").val();
        let detail_type = $("#detail-type").val();
       
        

        let info = "approve"
        let val = $("#approveBtn").val();
        let status = "approved";

        $.ajax({
            url: "time_management/request.php",
            method: "POST",
            // dataType: "json",
            data: {info:info, val:val, status:status, created_by:created_by, user_id:user_id, date_range:date_range, detail_type:detail_type, app_id:app_id},
            success: function(e){
                // alert(e);
                // alert(e);
                // console.log(e);
                if(e == 0){
                    swal.fire({
                        text: "Success!",
                        icon: "success"
                    });
                    $("#applicationModal").hide();
                    getRequests();
                   
                }
                else{
                    swal.fire({
                        text: "something went wrong" + e,
                        icon: "error"
                    });
                }
            },
            error: function(xhr, status, error) {
                // SweetAlert2 popup
                Swal.fire({
                  title: 'Error Occurred',
                  text: `Status: ${status}\nMessage: ${xhr.responseText || error}`,
                  icon: 'error',
                  confirmButtonText: 'OK'
                });

                // Console log for developers
                console.error('AJAX Error Details:', {
                  status: status,
                  error: error,
                  response: xhr.responseText,
                  xhr: xhr
                });
            }
        });
    }


    function doapprove_wfh(){
        let app_id = $("#detail-id").val();
        let user_id = $("#user_id").val();
        let created_by = '<?php echo $userID; ?>';
        let date_range = $("#detail-dates").val();
        let detail_type = $("#detail-type").val();
       let title = $("#detail-subject").html();
       let desc = $("#detail-description").html();
        

        let info = "doapprove_wfh"
        let val = $("#approveBtn").val();
        let status = "approved";

        $.ajax({
            url: "time_management/request.php",
            method: "POST",
            // dataType: "json",
            data: {info:info, val:val, status:status, created_by:created_by, user_id:user_id, date_range:date_range, detail_type:detail_type, app_id:app_id, title:title, desc:desc},
            success: function(e){
                // alert(e);
                // alert(e);
                // console.log(e);
                if(e == 0){
                    swal.fire({
                        text: "Success!",
                        icon: "success"
                    });
                    $("#applicationModal").hide();
                    getRequests();
                   
                }
                else{
                    swal.fire({
                        text: "something went wrong" + e,
                        icon: "error"
                    });
                }
            },
            error: function(xhr, status, error) {
                // SweetAlert2 popup
                Swal.fire({
                  title: 'Error Occurred',
                  text: `Status: ${status}\nMessage: ${xhr.responseText || error}`,
                  icon: 'error',
                  confirmButtonText: 'OK'
                });

                // Console log for developers
                console.error('AJAX Error Details:', {
                  status: status,
                  error: error,
                  response: xhr.responseText,
                  xhr: xhr
                });
            }
        });
    }
  
    function doapprove_punchouttime( time){
        let app_id = $("#detail-id").val();
        let user_id = $("#user_id").val();
        let created_by = '<?php echo $userID; ?>';
        let date_range = $("#detail-dates").val();
        let time_out = time;
        let detail_type = $("#detail-type").val();

        

        let info = "approve_punchouttime"
        let val = $("#approveBtn").val();
        let status = "approved";

        $.ajax({
            url: "time_management/request.php",
            method: "POST",
            // dataType: "json",
            data: {info:info, val:val, status:status, created_by:created_by, user_id:user_id, date_range:date_range, detail_type:detail_type, app_id:app_id, time_out:time_out},
            success: function(e){
                // alert(e);
                // alert(e);
                // console.log(e);
                if(e == "success"){
                    swal.fire({
                        text: "Success!",
                        icon: "success"
                    });
                    $("#applicationModal").hide();
                    getRequests();
                   
                }
                else{
                    swal.fire({
                        text: "something went wrong" + e,
                        icon: "error"
                    });
                }
            },
            error: function(xhr, status, error) {
                // SweetAlert2 popup
                Swal.fire({
                  title: 'Error Occurred',
                  text: `Status: ${status}\nMessage: ${xhr.responseText || error}`,
                  icon: 'error',
                  confirmButtonText: 'OK'
                });

                // Console log for developers
                console.error('AJAX Error Details:', {
                  status: status,
                  error: error,
                  response: xhr.responseText,
                  xhr: xhr
                });
            }
        });
    }
    
    
    });

        $(".close-btn").click(function(){
            $("#applicationModal").hide();
        });
        $("#cancelBtn").click(function(){
            $("#applicationModal").hide();
        });

        $(".attachment-link").on("click", function(e) {
            e.preventDefault();
            var imgPath = $(this).val();
            $("#modalImage").attr("src", "../"+imgPath);
            $("#imageModal").fadeIn();
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