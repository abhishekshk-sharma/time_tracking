<?php
    require_once "includes/config.php";

    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: login.php");
    }
    
    if($_SESSION['at_office'] === false){
        
            header("location: profile.php");
        
    }

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->execute([$userID]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt =$pdo->prepare("SELECT * FROM applications WHERE employee_id = ? AND req_type = ? AND DATE(start_date) = DATE(CURRENT_DATE) AND status = ?");
    $stmt->execute([$userID, "half_day", "approved"]);
    $is_half = $stmt->fetch(PDO::FETCH_ASSOC);

    if($is_half){
        $is_half = true;
    }
    else{
        $is_half = false;
    }

    if($is_half){
        $stmt = $pdo->query("SELECT setting_value AS value FROM system_settings WHERE setting_key = 'half_day_time'");
        $getHalftime = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // echo "<script>alert('".isset($_SESSION['CREATED'])?$_SESSION['CREATED']:'nothing'."')</script>"
?>

<?php
    require_once "includes/header.php";
?>

<style>

    @media(max-width: 768px){
        .main-content{
            left:2%;
            width:106.5%;
        }
    }
    .action-buttons{
        
        margin-left: 12%;
        margin-right: 8%;
    }
</style>



    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <?php if($_SESSION['at_office']):?>
                <a href="index.php" class="nav-item active" data-target="employee-section">
                    <i class="fas fa-user"></i>
                    <span>Employee Dashboard</span>
                </a>
            <?php endif; ?>
                <a href="#" class="nav-item" onclick="window.location.href='applications.php'">
                    <i class="fas fa-chart-bar"></i>
                    <span>Applications</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='history.php'">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='schedule.php'">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="logout.php" class="nav-item" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>

            <div class="content-area">
                <!-- Employee Section -->
                <div id="employee-section">
                    <h2 class="section-title">
                        <i class="fas fa-user-clock"></i>
                        Employee Time Tracking
                    </h2>
                    
                    <div class="time-card">
                        <div class="current-date">Tuesday, June 11, 2023</div>

                        <?php
                            if($is_half):
                        ?>
                        <div class="current-date" value="<?php echo $getHalftime; ?>" style="color:orange;">Today Is Your Half Day Of: <?php echo (new DateTime($getHalftime['value']))->format("H:i"); ?>H</div>
                        <?php endif; ?>

                        <?php
                            if(isset($_SESSION['WFHsuccess']) && $_SESSION['WFHsuccess'] === true):
                        ?>
                        <div class="current-date" style="color:red;"> Today Is Your Work From Home!</div>
                        <?php endif; ?>
                        <div class="current-time">14:28:45</div>
                        
                        
                        <div class="action-buttons">
                            <button class="btn btn-primary" id="punch-in">
                                <i class="fas fa-fingerprint"></i>
                                Punch In
                            </button>
                            <button class="btn btn-warning" id="lunch-start" disabled>
                                <i class="fas fa-utensils"></i>
                                Start Lunch
                            </button>
                            <button class="btn btn-warning" id="lunch-end" disabled>
                                <i class="fas fa-utensils"></i>
                                End Lunch
                            </button>
                            <button class="btn btn-danger" id="punch-out" disabled>
                                <i class="fas fa-door-open"></i>
                                Punch Out
                            </button>
                        </div>
                    </div>
                    
                    
                    <!-- door-open utensils fingerprint bullseye -->
                    
                    <div class="today-summary">
                        <div class="summary-card">
                            <h3>Work Hours</h3>
                            
                            <p id="worktime" >0h 0m</p>
                        </div>
                        <div class="summary-card">
                            <h3>Punch Time</h3>
                            <p id="punchTime">00:00 AM</p>
                        </div>
                        <div class="summary-card">
                            <h3>Lunch Time</h3>
                            <p id="lunchDuation">0M</p>
                        </div>
                        <div class="summary-card">  
                            <i class="fas fa-fingerprint" style="color:blue; font-size: 30px;"></i>
                            <h3>Punch In</h3>
                            <p id="punch_in">00:00 AM</p>
                        </div>
                        <div class="summary-card">
                            <i class="fas fa-business-time" style="color:blue; font-size: 30px;"></i>
                            <h3>Total Hours</h3>
                            
                            <p id="total_hours"> - </p>
                        </div>
                        <div class="summary-card">
                            <i class="fas fa-suitcase" style="color:blue; font-size: 30px;"></i>
                            <h3>Worked</h3>
                            
                            <p id="network"> - </p>
                        </div>
                        <div class="summary-card">
                            <i class="fas fa-utensils" style="color:blue; font-size: 30px;"></i>
                            <h3>Lunch</h3>
                            <p id="totalLunchByemp"> - </p>
                        </div>
                        <div class="summary-card">
                            <i class="fas fa-square-poll-vertical" style="color:blue; font-size: 30px;"></i>
                            <h3>status</h3>
                            <p id="late"> - </p>
                        </div>

                    </div>
                    
                    <div class="recent-activity">
                        <h3 class="section-title">
                            <i class="fas fa-history"></i>
                            Today's Activity
                        </h3>
                        
                        <ul class="activity-list">
                            
                        </ul>
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

<script src="js/jQuery.min.js"></script>
<?php
    require_once "includes/footer.php";
?>
    



<script>
$(document).ready(function(){

    // // prevent all kind of functions by user.
        
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.onkeydown = function (e) {
    // F12
    if (e.keyCode === 123) return false;

    // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
    if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;

    // Ctrl+U (View Source)
    if (e.ctrlKey && e.key.toUpperCase() === 'U') return false;
    };
    
    document.addEventListener('selectstart', e => e.preventDefault());
    document.addEventListener('dragstart', e => e.preventDefault());
    document.addEventListener('copy', e => e.preventDefault());




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
   
var i = 1;
checkfirstpunchin();
function checkfirstpunchin(){
    // var click = "checkfirstpunchin";
    var click = "checkfirstpunchin";
    $.ajax({
        url: "time_management/time.php",
        type: "post",
        data: {click : click},
        success: function(e){
            // alert(i); i++;
            // alert(e);
            if(e == 1){

                // Punch In
                // $(".recent-activity").html(e);
                document.getElementById('punch-in').disabled = true;
                document.getElementById('lunch-start').disabled = false;
                document.getElementById('punch-out').disabled = false;
                // alert("this is the result for 1 " + e);
                // checkfirstpunchin();
                Swal.fire({
                
                  text: "Punch In Successfull!",
                  icon: "success"
                });
                timeWorked();
                checksession();
            }
            else if(e == 2){

                // Lunch start
                // $(".recent-activity").html(e);
                document.getElementById('punch-in').disabled = true;
                document.getElementById('lunch-start').disabled = true;
                document.getElementById('punch-out').disabled = true;
                document.getElementById('lunch-end').disabled = false;
                // alert("this is the result for 0 " + e);
                // checkfirstpunchin();
                Swal.fire({

                      text: "Lunch Start Successfull!",
                      icon: "success"
                    });
                    timeWorked();
                    checksession();
            }
            else if(e == 3){
                // Lunch end
                // $(".recent-activity").html(e);
                document.getElementById('punch-in').disabled = true;
                document.getElementById('lunch-start').disabled = false;
                document.getElementById('punch-out').disabled = false;
                document.getElementById('lunch-end').disabled = true;
                // alert("this is the result for 0 " + e);
                // checkfirstpunchin();
                Swal.fire({
                
                  text: "Lunch End Successfull!",
                  icon: "success"
                });
                timeWorked();
                checksession();
            }
            else if(e == 4){
                    // Punch out
                    // $(".recent-activity").html(e);
                document.getElementById('punch-in').disabled = false;
                document.getElementById('lunch-start').disabled = true;
                document.getElementById('punch-out').disabled = true;
                document.getElementById('lunch-end').disabled = true;
                // alert("this is the result for 0 " + e);
                // checkfirstpunchin()
                Swal.fire({
                
                  text: "Punch out Successfull!",
                  icon: "success"
                });
                timeWorked();
                checksession();
            }
            else if (e == 5){
                Swal.fire({
                
                  text: "👋 Welcome Back!",
                  icon: "info"
                });
                checksession();
            }
            else{
                document.getElementById('punch-in').disabled = true;
                document.getElementById('lunch-start').disabled = true;
                document.getElementById('punch-out').disabled = true;
                document.getElementById('lunch-end').disabled = true;

                // alert("from else " + e);
                // $("#late").html(e);
                // $("#late").css("color", "orange");
            }
                                    
            }
        });
    }

function checkdata(){
    var click = "getDetails";
    $.ajax({
        url: "time_management/time.php",
        type: "post",
        data: {click : click},
        success: function(e){
            if(e){
                $(".recent-activity").html(e);
                checksession();
            }
        }
    });
}


                        
timeWorked();
function timeWorked(){
    // alert("hello timeworked");
    var click = "timeWorked";
    $.ajax({
        url: "time_management/time.php",
        type: "post",
        dataType: 'json', 
        data: {click : click},
        success: function(e){
            // alert(e.action);
            // console.log(e.action);
            // console.log(e);
            if(e.action == "run"){
                // $(".recent-activity").html(e);
                console.log(e);

                if(e.isHalf){
                    
                    $(".current-date").show();
                    if(e.com_half){
                        document.getElementById('punch-in').disabled = true;
                        document.getElementById('lunch-start').disabled = true;
                        document.getElementById('lunch-end').disabled = true;
                        document.getElementById('punch-out').disabled = false;
                    }
                    else{
                        document.getElementById('punch-in').disabled = true;
                        document.getElementById('lunch-start').disabled = true;
                        document.getElementById('lunch-end').disabled = true;
                        document.getElementById('punch-out').disabled = true;
                    }
                }
                else{
                    // $(".current-date").hide();
                }
                $("#worktime").html(e.worktime);
                $("#punchTime").html(e.punchTime);
                $("#lunchDuation").html(e.lunchDuation);
                $("#punch_in").html(e.punch_in);
                $("#total_hours").html(e.total_hours);
                $("#network").html(e.network);
                $("#totalLunchByemp").html(e.totalLunchByemp);
                $("#late").html(e.late);
                checksession();
            }
            else if(e.action == "block"){
                document.getElementById('punch-in').disabled = true;
                document.getElementById('lunch-start').disabled = true;
                document.getElementById('lunch-end').disabled = true;
                document.getElementById('punch-out').disabled = true;
                
                $("#worktime").html(e.worktime);
                $("#punchTime").html(e.punchTime);
                $("#lunchDuation").html(e.lunchDuation);
                $("#punch_in").html(e.punch_in);
                $("#total_hours").html(e.total_hours);
                $("#network").html(e.network);
                $("#totalLunchByemp").html(e.totalLunchByemp);
                $("#late").html(e.late);
            }
           
        },
        error: function(e,s,x){
            console.log(e);
            console.log(s);
            console.log(x);
        }
    });
}                        
checkdata();
$("#punch-in").click(function(){
    var info = "check";
    
    // alert("Something Went Wrong! from function");
    $.ajax({
        url: "includes/checksession.php",
        method: "POST",
        data: {info:info},
        success: function(e){
           
            if(e =='expired'){
                window.location.href = 'login.php';
            }
            if(e =='valid'){
                var click = "punch_in";
                $.ajax({
                    url: "time_management/time.php",
                    type: "post",
                    data: {click : click},
                    success: function(e, status, error){
                        if(e == "nothing"){
                            
                            checkfirstpunchin();
                            checkdata();
                        }
                        else{
                            window.location.href = 'login.php';
                        }
                        
                    }
                });
            }
        }
    });
    
    
});



$("#lunch-start").click(function(){
    
    var info = "check";
    
    // alert("Something Went Wrong! from function");
    $.ajax({
        url: "includes/checksession.php",
        method: "POST",
        data: {info:info},
        success: function(e){
           
            if(e =='expired'){
                window.location.href = 'login.php';
            }
            if(e =='valid'){
                var click = "lunch_start";
                 $.ajax({
                    url: "time_management/time.php",
                    type: "post",
                    data: {click : click},
                    success: function(e){
                        
                        if(e == "nothing"){
                            checkfirstpunchin();
                            checkdata();
                        }
                    }
                });
            }
        }
    });

});


$("#lunch-end").click(function(){
    var info = "check";
    
    // alert("Something Went Wrong! from function");
    $.ajax({
        url: "includes/checksession.php",
        method: "POST",
        data: {info:info},
        success: function(e){
           
            if(e =='expired'){
                window.location.href = 'login.php';
            }
            if(e =='valid'){
                var click = "lunch_end";
                $.ajax({
                    url: "time_management/time.php",
                    type: "post",
                    data: {click : click},
                    success: function(e){
                        if(e == "nothing"){
                        
                            checkfirstpunchin();
                            checkdata();
                        }

                    }
                });
            }
        }
    });
    
    
});
$("#punch-out").click(function(){
    var info = "check";
    
    // alert("Something Went Wrong! from function");
    $.ajax({
        url: "includes/checksession.php",
        method: "POST",
        data: {info:info},
        success: function(e){
           
            if(e =='expired'){
                window.location.href = 'login.php';
            }
            if(e =='valid'){
                var click = "punch_out";
                $.ajax({
                    url: "time_management/time.php",
                    type: "post",
                    data: {click : click},
                    success: function(e){
                        if(e == "nothing"){

                            checkfirstpunchin();
                            checkdata();
                        }

                    }
                });
            }
        }
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

                        
});
</script>


