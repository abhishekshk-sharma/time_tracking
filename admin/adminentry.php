<?php
// admin_time_entry.php
require_once "includes/config.php";

// Check if user is admin and logged in
$userID = isset($_SESSION['id']) ? $_SESSION['id'] : null;
if ($userID == null) {
    header("location: login.php");
    exit;
}

// Check if user has admin privileges (you'll need to adjust this based on your admin check logic)
$stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ? AND end_date IS NULL");
    $stmt->execute([$userID]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

            if($details['role'] != "admin"){
            header("location: ../index.php");
        }



// Get all employees for the dropdown
$stmt = $pdo->prepare("SELECT emp_id, username FROM employees WHERE end_date IS NULL ORDER BY username");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get system settings for default times
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings 
                    WHERE setting_key IN ('work_start_time', 'work_end_time')");
$systemSettings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $systemSettings[$row['setting_key']] = $row['setting_value'];

    if($row['setting_key'] == "work_start_time"){
        $work_start_time = $row['setting_value'];
    }
    if($row['setting_key'] == "work_end_time"){
        $work_end_time = $row['setting_value'];
    }
}

$lunchstart = "13:00";
$lunchend = "13:50";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_entry'])) {
    $employeeId = $_POST['employee_id'];
    $entryDate = $_POST['entry_date'];
    
    // Validate inputs
    if (empty($employeeId) || empty($entryDate)) {
        $error = "Please select an employee and date.";
    } else {
        try {
            // Check if entry already exists for this employee on this date
            $stmt = $pdo->prepare("SELECT id FROM time_entries WHERE employee_id = ? AND DATE(entry_time) = ?");
            $stmt->execute([$employeeId, $entryDate]);
            
            if ($stmt->rowCount() > 0) {
                $error = "An entry already exists for this employee on the selected date.";
            } else {
                // Create the time entries
                $punchInTime = $entryDate . ' ' . $work_start_time;

                $punchOutTime = $entryDate . ' ' . $work_end_time;
                
                $lunchstarttime = $entryDate . ' ' . $lunchstart;

                $lunchendtime = $entryDate . ' ' . $lunchend;
                
         
                
                // Insert punch in
                $stmt = $pdo->prepare("INSERT INTO time_entries (employee_id, entry_type, entry_time) VALUES (?, 'punch_in', ?)");
                $stmt->execute([$employeeId, $punchInTime]);
                
                // Insert lunch start
                $stmt = $pdo->prepare("INSERT INTO time_entries (employee_id, entry_type, entry_time) VALUES (?, 'lunch_start', ?)");
                $stmt->execute([$employeeId, $lunchstarttime]);
                
                // Insert Lunch End
                $stmt = $pdo->prepare("INSERT INTO time_entries (employee_id, entry_type, entry_time) VALUES (?, 'lunch_end', ?)");
                $stmt->execute([$employeeId, $lunchendtime]);
                
                // Insert punch out
                $stmt = $pdo->prepare("INSERT INTO time_entries (employee_id, entry_type, entry_time) VALUES (?, 'punch_out', ?)");
                $stmt->execute([$employeeId, $punchOutTime]);
                
                $success = "Time entry created successfully for the selected employee.";
            }
        } catch (PDOException $e) {
            $error = "Error creating time entry: " . $e->getMessage();
        }
    }
}
?>

<?php require_once "includes/header.php"; ?>

<style>
    .admin-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }
    
    .btn-create {
        background-color: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }
    
    .btn-create:hover {
        background-color: #45a049;
    }
    
    .system-times {
        margin-top: 30px;
        padding: 15px;
        background-color: #e9f7ef;
        border-radius: 4px;
        border-left: 4px solid #4CAF50;
    }
    
    .system-times h3 {
        margin-top: 0;
        color: #2e7d32;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
</style>

<div class="container">
    <div class="main-content">
        
                <div class="sidebar">
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Admin Dashboard</span>
                    </a>
                    <a href="user.php" class="nav-item ">
                        <i class="fas fa-users"></i>
                        <span>Employee</span>
                    </a>
                    <a href="create.php" class="nav-item">
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

                    <a href="adminentry.php" class="nav-item active">
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
            <div class="admin-container">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Create Employee Time Entry
                </h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="employee_id">Select Employee</label>
                        <select class="form-control" id="employee_id" name="employee_id" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['emp_id']; ?>" 
                                    <?php if (isset($_POST['employee_id']) && $_POST['employee_id'] == $employee['emp_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($employee['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="entry_date">Entry Date</label>
                        <input type="date" class="form-control" id="entry_date" name="entry_date" 
                               value="<?php echo isset($_POST['entry_date']) ? $_POST['entry_date'] : date('Y-m-d'); ?>" required>
                    </div>
                    
                    <button type="submit" name="create_entry" class="btn-create">
                        <i class="fas fa-plus-circle"></i> Create Entry
                    </button>
                </form>
                
                <div class="system-times">
                    <h3>System Default Times</h3>
                    <p><strong>Punch In:</strong> <?php echo htmlspecialchars($systemSettings['work_start_time'] ?? 'Not set'); ?></p>
                    <p><strong>Lunch Start:</strong> <?php echo $lunchstart; ?></p>
                    <p><strong>Lunch End:</strong> <?php echo $lunchend; ?></p>
                    <p><strong>Punch Out:</strong> <?php echo htmlspecialchars($systemSettings['work_end_time'] ?? 'Not set'); ?></p>
                    <p class="small">These times will be used to automatically create the entries.</p>
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


<script>
$(document).ready(function(){

    
           // For notification Section
        setInterval(notification, 10000);

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

    let triggerbutton = false;

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
                    
                  
                    if(triggerbutton == true){
            
                        $("#detailModal").hide();
                        triggerbutton = false;
                    }
                    else{
                        $("#detailModal").show();
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
        
        triggerbutton = true;

        
        let appid = $(".deletenoty").data("appid");
        click = "deletenoty";
        
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