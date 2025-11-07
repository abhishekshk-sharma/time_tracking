<?php

    require_once "includes/config.php";

    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: ../login.php");
    }
    $info = isset($_SESSION['info'])?$_SESSION['info']:'';

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->execute([$userID]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

            if($details['role'] != "admin"){
            header("location: ../index.php");
        }

    $stmt = $pdo->query("SELECT count(*) as total FROM employees");
    $count = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = $count[0]['total'];

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
            width: 100vw;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 10px 10px;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: auto;
            margin-right: 50px;
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
            position:relative;
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
            /*margin-left: -20%;*/
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
            width: 110%;
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
        
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: none;
        }
        
        .notification.success {
            background-color: #e7f6e9;
            color: #27ae60;
            border-left: 4px solid #2ecc71;
        }
        
        .notification.error {
            background-color: #fde8e6;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }
        
        .settings-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        input[type="text"],
        input[type="time"],
        input[type="number"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        
        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            padding: 14px 20px;
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
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .setting-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-left: 4px solid var(--secondary);
        }
        
        .setting-key {
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .setting-value {
            margin-bottom: 10px;
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .setting-description {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
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
                left: 3%;
            }
            
            .settings-form {
                grid-template-columns: 1fr;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
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
            <a href="schedule.php" class="nav-item ">
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
            <a href="employees_history.php" class="nav-item " onclick="window.location.href='employees_history.php'">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Employees History</span>
            </a>
            <a href="settings.php" class="nav-item active">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="#" class="nav-item" onclick="window.location.href = 'logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
            
            <div class="content-area">
                <div class="section-title">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </div>
                
                <div id="notification" class="notification"></div>
                
                <form id="settings-form" class="settings-form">
                    <!-- <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" value="ST ZK DM Solutions">
                        <small class="setting-description">The name of the company</small>
                    </div> -->
                    
                    <div class="form-group">
                        <label for="work_start_time">Work Start Time</label>
                        <input type="time" id="work_start_time" name="work_start_time" value="">
                        <small class="setting-description">Default work start time</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="work_end_time">Work End Time</label>
                        <input type="time" id="work_end_time" name="work_end_time" value="">
                        <small class="setting-description">Default work end time</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="lunch_duration">Lunch Duration (minutes)</label>
                        <input type="number" id="lunch_duration" name="lunch_duration" value="" min="0">
                        <small class="setting-description">Default lunch duration in minutes</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="late_threshold">Late Threshold (minutes)</label>
                        <input type="number" id="late_threshold" name="late_threshold" value="" min="0">
                        <small class="setting-description">Minutes after start time considered late</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="casual_leave">Casual Leave (days per year)</label>
                        <input type="number" id="casual_leave" name="casual_leave" value="" min="0">
                        <small class="setting-description">Per Year Casual Leave</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="sick_leave">Sick Leave (days per year)</label>
                        <input type="number" id="sick_leave" name="sick_leave" value="" min="0">
                        <small class="setting-description">Per Year Sick Leave</small>
                    </div>
                    <div class="form-group">
                        <label for="hHour">Half Day Duration</label>
                        <input type="time" id="hHour" name="hHour" value="" min="0">
                        <small class="setting-description">Working hours for Half Day</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="office_radius">Office Radius</label>
                        <input type="number" id="office_radius" name="office_radius" value="" min="0">
                        <small class="setting-description">The Area from Which Only The User Allowed to Access The Office Login</small>
                    </div>

                    <div class="form-group">
                        <label for="min_working_hours">Minimum Working Hours For Full Day Salary</label>
                        <input type="time" id="min_working_hours" name="min_working_hours" value="" min="0">
                        <small class="setting-description">The Minimum Working Hours to Consider for Full Day Attendance for Salary</small>
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <button type="button" class="btn btn-success" id="submit-set">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
                
                <div class="section-title">
                    <i class="fas fa-info-circle"></i>
                    <span>Current Settings Overview</span>
                </div>
                
                <div class="settings-grid">
                   
                    
                    <div class="setting-item">
                        <div class="setting-key">Work Hours</div>
                        <div class="setting-value" id="workTime">10:30 - 19:30</div>
                        <div class="setting-description">Default work schedule</div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-key">Lunch Duration</div>
                        <div class="setting-value" id="L_Duration">60 minutes</div>
                        <div class="setting-description">Default lunch duration in minutes</div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-key">Late Threshold</div>
                        <div class="setting-value" id="Late_thres">15 minutes</div>
                        <div class="setting-description">Minutes after start time considered late</div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-key">Leave Allowance</div>
                        <div class="setting-value" id="totalLeave">Casual: 10, Sick: 10</div>
                        <div class="setting-description">Per year leave allocation</div>
                    </div>                 

                    <div class="setting-item">
                        <div class="setting-key">Half Day Duration</div>
                        <div class="setting-value" id="halfduration"></div>
                        <div class="setting-description">Working hours for Half day</div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-key">Office Radius</div>
                        <div class="setting-value" id="office_radiusInfo"></div>
                        <div class="setting-description">The Area From Which only the user Allowed to access the office login</div>
                    </div>
                    <div class="setting-item">
                        <div class="setting-key">Minimum Working Hours For Full Day Salary</div>
                        <div class="setting-value" id="min_working_hoursInfo"></div>
                        <div class="setting-description">The Minimum Working Hours to Consider for Full Day Attendance for Salary</div>
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

        
<?php require_once "includes/footer.php"; ?>
    </div>

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




            runFirst();
            function runFirst(){
                let info = "check_setting";
     
                
              
                $.ajax({
                     url: "time_management/total.php",
                    method: "POST",
                    dataType: "json",
                    data: {
                        info:info
                    },
                    success: function(e){
                        if(e){
                           
                            // console.log(e);
                            $("#workTime").html(e.work_start_time+"-"+e.work_end_time);                         
                            $("#L_Duration").html(e.lunch_duration+" Minutes");
                            $("#Late_thres").html(e.late_threshold+" Minutes");
                            $("#totalLeave").html("Casual: "+e.casual_leave+", Sick:"+e.sick_leave);
                            $("#halfduration").html(e.half_day_time);
                            $("#work_start_time").val(e.work_start_time);
                            $("#work_end_time").val(e.work_end_time);
                            $("#lunch_duration").val(e.lunch_duration);
                            $("#late_threshold").val(e.late_threshold);
                            $("#casual_leave").val(e.casual_leave);
                            $("#sick_leave").val(e.sick_leave);
                            $("#hHour").val(e.half_day_time);
                            
                            $("#office_radius").val(e.office_radius);
                            $("#office_radiusInfo").html(e.office_radius);
                            $("#min_working_hours").val(e.min_working_hours);
                            $("#min_working_hoursInfo").html(e.min_working_hours);
                        }
                        else if(e.status == "error"){
                            alert("error");
                        }
                    }
                });
            }

            $("#submit-set").click(function(){
                // form.preventDefault();
                let info = "update_seettings";
                let work_start_time = $("#work_start_time").val();
                let work_end_time = $("#work_end_time").val();
                let lunch_duration = $("#lunch_duration").val();
                let late_threshold = $("#late_threshold").val();
                let casual_leave = $("#casual_leave").val();
                let sick_leave = $("#sick_leave").val();
                let second_half = $("#second_half").val();
                let office_radius = $("#office_radius").val();
                let min_working_hours = $("#min_working_hours").val();
                let hHour = $("#hHour").val();
                
                

                $.ajax({
                    url: "time_management/total.php",
                    method: "POST",
                    dataType: "json",
                    data: {
                        info:info,
                        workStartTime:work_start_time,
                        workEndTime:work_end_time,
                        lunchDuration:lunch_duration,
                        late:late_threshold,
                        cl_leave: casual_leave,
                        sl_casual: sick_leave,
                        second_half: second_half,
                        hHour:hHour,
                        office_radius:office_radius,
                        min_working_hours:min_working_hours
                    },
                    success: function(e){
                        if(e.status == "success"){
                            swal.fire({
                                title: "Success!",
                                text: "Sttings Updated Successfully!",
                                icon: "success"
                                
                            })
                              
                            // console.log(e);
                            $("#work_start_time").val(e.work_start_time);
                            $("#work_end_time").val(e.work_end_time);
                            $("#lunch_duration").val(e.lunch_duration);
                            $("#late_threshold").val(e.late_threshold);
                            $("#casual_leave").val(e.casual_leave);
                            $("#sick_leave").val(e.sick_leave);
                            $("#hHour").val(e.half_day_time);
                            $("#office_radius").val(e.office_radius);                          
                            $("#min_working_hours").val(e.min_working_hours);
                       
                            runFirst();
                        }
                        else if(e.status == "error"){
                            alert("error");
                        }
                    }
                });
            });
        });

    </script>


