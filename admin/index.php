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


    // echo "<script> alert('".$count."')</script>";
?>


<?php
    require_once "includes/header.php";
    if(!empty($_SESSION['info'])){
        // $_SESSION['ack'] = 0;
        unset($_SESSION['info']);
        echo "<script> alert('$info')</script>";
    }

?>


<style>
        .content-area{

            position: relative;
            width: 100%;
            overflow-x: scroll;
        }
        .content-area::-webkit-scrollbar{
            display: none;
        }
        .indextable{

            position: relative;
            width: 98%;
            overflow-x: scroll;

        }
        .container {
            position: flex;
            margin: 20px auto;            
        }
        .time-card{
            position:relative;
            width: 100%;
        } 
        .pagination-nav{
            position: relative; 
            margin: 15px 40%;
        }
        
        
        @media (max-width: 768px){
            .main-content {
                
                left: 3%;
                
            }
            .pagination-nav{
              
                margin: 90px 25%;
            }
        }
</style>


    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                
                <a href="#" class="nav-item active" data-target="employee-section">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href = 'see_employees.php'">
                    <i class="fas fa-users"></i>
                    <span>Employee</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href = 'create.php'">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Employee</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='schedule.php'">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='report.php'">
                    <i class="fas fa-chart-bar"></i>
                    <span>Applications</span>
                    
                </a>
                <a href="#" class="nav-item " onclick="window.location.href='adminentry.php'">
                        <i class="fas fa-house-user"></i>
                        <span>Work From Home</span>
                </a>
                <a href="#" class="nav-item " onclick="window.location.href='employees_history.php'">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Employees History</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href = 'logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>

            <div class="content-area">
                <!-- Employee Section -->
                <div id="employee-section">
                    
                    
                    <div class="time-card">
                        <div class="current-date">Tuesday, June 11, 2023</div>
                        <div class="current-time">14:28:45</div>
                    </div>
                    
                    <div class="filters">
                        
                        <div class="filter-group">
                            <label for="searchInput">Search</label>
                            <input type="text" id="searchInput" placeholder="Employee username">
                        </div>
                        <div class="filter-group">
                            <label for="searchdate">Search Date</label>
                            <input type="date" id="searchdate" >
                        </div>
                        <div class="filter-group">
                            <label for="searchdate">Clear Filters</label>
                            <input type="button" class="clear-btn" id="clearFilters" value="Clear">
                        </div>
                        <button class="export-btn">
                            <i class="fas fa-file-excel"></i>
                            Export to Excel
                        </button>
                        
                    </div>
                    
                    <div class="indextable">
                        <table >
                            <thead>
                                <tr>
                                    <th>UserName</th>
                                    <th>Date</th>
                                    <th>Punch_In</th>
                                    <th>Lunch_Start</th>
                                    <th>Lunch_End</th>
                                    <th>Punch_Out</th>
                                    <th>Worked</th>
                                    <th>Status</th>
                                </tr>
    
                            </thead>
                            
                            <tbody id="Tbody">
    
                            </tbody>
    
                        </table>
                    </div>
                <nav aria-label="..." class="pagination-nav" >
                    <ul class="pagination">
                        <li class="page-item">
                            <a class="page-link" tabindex="-1" id="prev" style="cursor: pointer;">Previous</a>
                        </li>
                        <li class="page-item"><a class="page-link" id="sel_limit" val="10"><p id="setlimit">10</p></a></li>
                        <li class="page-item" >
                            <a class="page-link" id="next" style="cursor: pointer;">Next</a>
                        </li>
                    </ul>
                </nav>
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
    require_once "includes/footer.php";
?>

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
                        
                        $(".notification-content-head-div").removeClass("read_notification_modal");
                        
                        let notificationsec = $(".notification-content-head-div");

                        notificationsec.each(function(index, element) {
                            let elementappid = $(element).data("appid");

                            if(elementappid == appid){
                                $(element).removeClass("notification-item unread");
                            }
                        });

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

        $(this).addClass("read_notification_modal");
        
        
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
    
    
    setInterval(notification, 10000);
    let notiCount = 0;
    // let notiCount2 = 0;
    // let notiCount = <?php //echo $_SESSION['notification_session_count']; ?>;
    let notiCount2 = <?php echo isset($_SESSION['notification_session_count'])?$_SESSION['notification_session_count']:0; ?>;
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

        // Trigger this on your "Download CSV" button click
    $('.export-btn').on('click', function () {
        Swal.fire({
        title: "Select Export Range",
        html: `
          <select id="csv-range" class="swal2-input">
            <option value="current">Current Month</option>
            <option value="last">Last Month</option>
            <option value="custom">Custom</option>
          </select>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Next",
        preConfirm: () => {
          const selected = document.getElementById('csv-range').value;
          if (!selected) {
            Swal.showValidationMessage('Please select a range');
            return false;
          }

          if(selected ==  "current"){
            // alert("hello current");
            return window.location.href="csv_file/currentmont.php";
          }
          else if(selected == "last"){
            return window.location.href="csv_file/lastMonth.php";
          }

          return selected;
        }
          }).then((result) => {
        if (result.isConfirmed) {
          const range = result.value;

          if (range === 'custom') {
            // Trigger second SweetAlert for custom date range
            Swal.fire({
              title: "Select Custom Date Range",
              html: `
            <label>From: <input type="date" id="date-from" class="swal2-input"></label>
            <label>To: <input type="date" id="date-to" class="swal2-input"></label>
              `,
              focusConfirm: false,
              showCancelButton: true,
              confirmButtonText: "Download",
              preConfirm: () => {
            const from = document.getElementById('date-from').value;
            const to = document.getElementById('date-to').value;

            if (!from || !to) {
              Swal.showValidationMessage('Both dates are required');
              return false;
            }
            if (from > to) {
              Swal.showValidationMessage('From Date Can\'t be less then To Date');
              return false;
            }

            return { from, to };
              }
            }).then((customResult) => {
              if (customResult.isConfirmed) {
                const { from, to } = customResult.value;

                customCsv(from, to); // or trigger AJAX
              }
            });
          } 
        }
      });
        });


    function customCsv(from, to){
    let fromDate = from;
    let toDate = to;
    
     $.ajax({
                url: 'csv_file/custom.php',
                type: 'POST',
                data: {
                    from: fromDate,
                    to: toDate
                },
                xhrFields: {
                    responseType: 'blob' // Important for handling binary response
                },
                success: function(blob) {
                    // Create a download link for the blob
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'attendance_report.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    alert('Error generating report: ' + error);
                }
    });
    }

    


    // ///////////\\\\\\\\\\\\\\\ This is for pagination ///////////\\\\\\\\\\\\\\\\\\\


    let searchdate = "";
        updateSearchDate();
        function updateSearchDate(){
            let dateinput = $("#searchdate").val();

            if(dateinput === ""){
                let storedDate = localStorage.getItem("searchdate");
                searchdate = storedDate ? storedDate : "";
                $("#searchdate").val(searchdate);
                // console.log("Stored date used: "+searchdate);
            }
            else{
                searchdate = dateinput;
                localStorage.setItem("searchdate", searchdate);
                // alert("Date selected: "+searchdate);
                // console.log("Date selected: "+searchdate);
            }
        }

        let row = <?php echo $count; ?>;
        let limit = 10;
        let totalPages = Math.ceil(row/limit);
        let currentPage = 1;
        let offset = (currentPage - 1) * limit;
        let searchname = "";


        function getfilters(){
            
            let searinput = $("#searchInput").val();
            
            searchInput = searinput === "" ? undefined : searinput;
          
        }

        $("#clearFilters").click(function(){
            $("#searchInput").val("");
            $("#searchdate").val("");
            localStorage.removeItem("searchdate");
            searchdate = "";
            getfilters();
            totalPages = Math.ceil(row/limit);
            currentPage = 1;
            getDetails(currentPage, limit, searchInput, searchdate);
        });

        $(document).on("input", "#searchInput, #searchdate", function(){
            getfilters();
            updateSearchDate();
            totalPages = Math.ceil(row/limit);
            currentPage = 1;
            // alert("Date selected: "+searchdate);
            getDetails(currentPage, limit, searchInput, searchdate);
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
                getDetails(currentPage, limit, searchInput, searchdate);
              }
            });
        });

        $("#prev").click(function(e){
            e.preventDefault();
            getfilters();
            if(currentPage > 1){
                currentPage--;
                
                $("#next").prop("dissabled", false);
                $("#next").css("opacity", 1);
                $("#prev").prop("dissabled", false);
                $("#prev").css("opacity", 1);

                getDetails(currentPage, limit, searchInput, searchdate);
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
            getfilters();
            // alert(totalPages);
            if(currentPage  < totalPages){
                currentPage++;
                
                $("#next").prop("dissabled", false);
                $("#next").css("opacity", 1);
                $("#prev").prop("dissabled", false);
                $("#prev").css("opacity", 1);

                getDetails(currentPage, limit, searchInput, searchdate);
            }
            else{
                $("#next").prop("dissabled", true);
                $("#next").css("opacity", 0.5);
                $("#prev").prop("dissabled", false);
                $("#prev").css("opacity", 1);
            }
        });


        getDetails();
        function getDetails(currentPage1 = 1, limit1 = 10, searchInput1, searchdate1 = ""){

            var info = "GetAllDetails";
            let currentPage = currentPage1;
            let limit = limit1;
            let searchInput = searchInput1;
            let searchdate = searchdate1;

            if(searchdate === ""){
                let storedDate = localStorage.getItem("searchdate");
                searchdate = storedDate ? storedDate : "";
                $("#searchdate").val(searchdate);
                console.log("Stored date used: "+searchdate);
            }

            // alert("searchdate in function: "+searchdate);
            $.ajax({
                url: "time_management/total.php",
                type: "POST",
                dataType: 'json',
                data: { info: info, page:currentPage, limit:limit, searchInput:searchInput, searchdate:searchdate},
                success: function(e){
                    if(e){
                        $("#Tbody").html(e.output);
                        totalPages = Math.ceil(e.row/limit);
                        // alert(e.row+" pages: "+totalPages);

                        if(currentPage  < totalPages){
                            
                            $("#next").prop("dissabled", false);
                            $("#next").css("opacity", 1);
                            
                        }else{
                            $("#next").prop("dissabled", true);
                            $("#next").css("opacity", 0.5);
                        }
                        //
                    }
                }
            });
        }


        $(document).on("click", ".touser", function(){
            let id = $(this).data('id');
            // alert("Please Try Again! Something Went Wrong.");
            let info = "index";
            $.ajax({
                url: "includes/session_create.php",
                method: "POST",
                data: {info:info, userId:id},
                success: function(e){
                    if(e == 0){
                        window.location.href = "user.php";
                    }
                    else if(e == 1){
                        alert("Please Try Again! Something Went Wrong.");
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