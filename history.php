<?php
    require_once "includes/config.php";
    
    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: login.php");
    }

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->execute([$userID]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);
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
            position: relative;
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
            position:relative;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .tablediv{
            position: relative;
            width: 100%;
            overflow-y: scroll;
        }
        .tablediv::-webkit-scrollbar{
            display: none;
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
                left: 2%;
                width:100%;
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
        @media (max-width: 548px) {
            /*.content-area{*/
            /*    width: 96%;*/
            /*}*/
            /*.sidebar{*/
            /*    width:96%;*/
            /*}*/
            .tablediv{
                width: 85vw;    
            }
        }
    </style>

    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <?php if($_SESSION['at_office']):?>
                <a href="index.php" class="nav-item " data-target="employee-section">
                    <i class="fas fa-user"></i>
                    <span>Employee Dashboard</span>
                </a>
            <?php endif; ?>
                <a href="applications.php" class="nav-item" onclick="window.location.href='applications.php'">
                    <i class="fas fa-chart-bar"></i>
                    <span>Applications</span>
                </a>
                <a href="#" class="nav-item active">
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
                <div class="employee-header">
                    <!-- <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Employee Photo" class="employee-photo"> -->
                    <div class="employee-info">
                        <h2><?php echo $details['full_name']; ?></h2>
                        <p><?php echo $details['department']; ?></p>
                    </div>
                </div>

                

                <div class="action-buttons">
                   


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

                    <div class="tablediv">
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

<?php
    // $currentTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    // $date = $currentTime;
    require_once "includes/footer.php";

?>

<script>
    
    
$(document).ready(function(){
    


    // prevent all kind of functions by user.
        
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

    
        
        // <<<<<<<======== ALL AJAX request ========>>>>>>>>>>
        // 
        // var hello = '<?php //echo $date->format("Y-m-d H:i:s"); ?>';
        // alert(hello);
        detailsById();
        function detailsById(){
            
            var click = "detailsById";
            var id = '<?php echo $userID; ?>';
          
            
            $.ajax({
                url: "time_management/time.php",
                method: "POST",
                data: {click:click, id: id},
                success: function(e){
                    if(e){
                        $("#Tbody").html(e);
                        // alert("hello "+e);
                    }
                    
                },
                error: function(e, s, x){
                    Swal.fire({
                        title: "Error!"+e,
                        icon: "error"
                    })
                }
            });
        }

        
        // This is filter for employees attendance history.

        $(document).on("change", "#dateFilter",function(){
            let select = $(this).val();
            let id = $(this).data("id");
            
            if(select == 'lastMonth'){
                let info = "filterLastMonth";
                // alert(id);

                $.ajax({
                    url: "time_management/time.php",
                    method: "POST",
                    data: {click:info, select: select, id:id},
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
                    url: "time_management/time.php",
                    method: "POST",
                    data: {click:info, id:id, from:from1, to:to1},
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