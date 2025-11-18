<?php
    require_once "includes/config.php";

    require 'vendor/autoload.php';

    use StzkDm\JwtAuth\TokenManager;

    echo TokenManager::greet();


    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: login.php");
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






    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <?php if($_SESSION['at_office']):?>
                <a href="index.php" class="nav-item " data-target="employee-section">
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
                   <!-- This is for employee profile modal -->
            <div class="profile-container">
              <div class="profile-header">
                  <div class="profile-image">
                      <i class="fas fa-user"></i>
                  </div>
                  <div class="profile-info">
                      <h2><?php echo $details['full_name']?></h2>
                      <p><?php echo $details['position']?></p>
                      <p><?php echo $details['emp_id']?></p>
                  </div>
                </div>

                <div class="profile-details">
                    <div class="detail-card">
                        <h3><i class="fas fa-envelope"></i> Email Address</h3>
                        <p><?php echo $details['email']?></p>
                    </div>
                    <div class="detail-card">
                        <h3><i class="fas fa-phone"></i> Phone Number</h3>
                        <p><?php echo $details['phone']?></p>
                    </div>
                    <div class="detail-card">
                        <h3><i class="fas fa-building"></i> Department</h3>
                        <p><?php echo $details['department']?></p>
                    </div>
                    <div class="detail-card">
                        <h3><i class="fas fa-calendar-alt"></i> Join Date</h3>
                        <p><?php echo $details['hire_date']?></p>
                    </div>
                    <div class="detail-card editable" onclick="openDobModal()">
                        <h3><i class="fas fa-birthday-cake"></i> Date of Birth</h3>
                        <p id="dobDisplay"><?php echo (isset($details['dob'])?(new DateTime($details['dob'], new DateTimeZone("Asia/Kolkata")))->format('M d, Y'):"")?></p>
                        <span class="edit-icon"><i class="fas fa-edit"></i></span>
                    </div>
                    <div class="detail-card">
                        <h3><i class="fas fa-briefcase"></i> Position</h3>
                        <p><?php echo $details['position']?></p>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="openEmailModal()">
                        <i class="fas fa-envelope"></i> Change Email
                    </button>
                    <button class="btn btn-success" onclick="openPasswordModal()">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                    <button class="btn btn-info" onclick="openDobModal()">
                        <i class="fas fa-birthday-cake"></i> Update Date of Birth
                    </button>
                </div>
            </div>

            <!-- Email Change Modal -->
            <div class="modal" id="emailModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Change Email Address</h2>
                        <button class="close-btn" onclick="closeModal('emailModal')">&times;</button>
                    </div>
                    <div class="form-group">
                        <label for="currentEmail">Current Email</label>
                        <input type="email" id="currentEmail" value="<?php echo $details['email']?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="newEmail">New Email Address</label>
                        <input type="email" id="newEmail" placeholder="Enter new email address">
                    </div>
                    <div class="form-group">
                        <label for="confirmEmail">Confirm New Email</label>
                        <input type="email" id="confirmEmail" placeholder="Confirm new email address">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-cancel" onclick="closeModal('emailModal')">Cancel</button>
                        <button class="btn btn-primary" onclick="updateEmail()">Update Email</button>
                    </div>
                </div>
            </div>

            <!-- Password Change Modal -->
            <div class="modal" id="passwordModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Change Password</h2>
                        <button class="close-btn" onclick="closeModal('passwordModal')">&times;</button>
                    </div>
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" id="currentPassword" placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" placeholder="Confirm new password">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-cancel" onclick="closeModal('passwordModal')">Cancel</button>
                        <button class="btn btn-primary" onclick="updatePassword()">Update Password</button>
                    </div>
                </div>
            </div>

            <!-- Date of Birth Modal -->
            <div class="modal" id="dobModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Update Date of Birth</h2>
                        <button class="close-btn" onclick="closeModal('dobModal')">&times;</button>
                    </div>
                    <div class="form-group">
                        <label for="currentDob">Current Date of Birth</label>
                        <input type="text" id="currentDob" value="<?php echo (isset($details['dob'])?$details['dob']:"")?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="newDob">New Date of Birth</label>
                        <input type="date" id="newDob">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-cancel" onclick="closeModal('dobModal')">Cancel</button>
                        <button class="btn btn-primary" onclick="updateDob()">Update Date of Birth</button>
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



       // Open modals
        function openEmailModal() {
            document.getElementById('emailModal').style.display = 'flex';
        }

        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'flex';
        }

        function openDobModal() {
            document.getElementById('dobModal').style.display = 'flex';
        }

        // Close modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Format date to display
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }

        // Update email with SweetAlert
        function updateEmail() {
            let newEmail = $("#newEmail").val();
            let confirmEmail = $("#confirmEmail").val();
            
            if (!newEmail || !confirmEmail) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all fields!',
                });
                return;
            }
            
            if (newEmail !== confirmEmail) {
                Swal.fire({
                    icon: 'error',
                    title: 'Emails do not match',
                    text: 'Please make sure both email addresses are identical.',
                });
                return;
            }
            
            

            let click = "changeemail";
            $.ajax({
                url: "time_management/profileupdate.php",
                method: "POST",
                data: {click:click, newEmail:newEmail},
                success: function(e){
                        Swal.fire({
                       
                            title: 'Updating Email',
                            text: 'Please wait...',
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                            Swal.showLoading()
                            }
                        }).then(()=>{
                            if(e == "success"){
                            Swal.fire({
                            icon: 'success',
                            title: 'Email Updated',
                            text: 'Your email address has been successfully updated.',
                            timer: 3000
                            }).then(() => {
                                
                                $("#emailModal").hide();
                                // In a real app, you would update the UI with the new email
                                document.querySelector('.detail-card:nth-child(1) p').textContent = newEmail;
                            });
                            }
                            else{
                                Swal.fire({
                                icon: 'error',  
                                title: 'Email Not Updated',
                                text: 'Something Went Wrong Please Try Again Later!.',
                                });
                            }
                        });
                    
                }
            });
            
            
        }

        // Update password with SweetAlert
        function updatePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all fields!',
                });
                return;
            }
            
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Passwords do not match',
                    text: 'Please make sure both passwords are identical.',
                });
                return;
            }
            
            if (newPassword.length < 8) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password too short',
                    text: 'Password must be at least 8 characters long.',
                });
                return;
            }
            
            let click = "changepass";
            $.ajax({
                url: "time_management/profileupdate.php",
                method: "POST",
                data: {click:click, newPassword:newPassword, currentPassword:currentPassword},
                success: function(e){
                        Swal.fire({
                       
                            title: 'Updating Password',
                            text: 'Please wait...',
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                            Swal.showLoading()
                            }
                        }).then(()=>{
                            if(e == "success"){
                            Swal.fire({
                            icon: 'success',
                            title: 'Password Updated',
                            text: 'Your Password has been successfully updated.',
                            timer: 3000
                            }).then(() => {
                                
                                $("#passwordModal").hide();
                                // In a real app, you would update the UI with the new email
                                
                            });
                            }
                            else{
                                Swal.fire({
                                icon: 'error',  
                                title: 'Password Not Updated',
                                text: 'Something Went Wrong Please Try Again Later!.',
                                });
                            }
                        });
                    
                }
            });
        }

        // Update date of birth with SweetAlert
        function updateDob() {
            const newDob = document.getElementById('newDob').value;
            
            if (!newDob) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select a date of birth!',
                });
                return;
            }
            
            // Check if the date is in the future
            const selectedDate = new Date(newDob);
            const today = new Date();
            if (selectedDate > today) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Date of birth cannot be in the future.',
                });
                return;
            }
            
            let click = "changdob";
            $.ajax({
                url: "time_management/profileupdate.php",
                method: "POST",
                data: {click:click, newDob:newDob},
                success: function(e){
                        Swal.fire({
                       
                            title: 'Updating DOB',
                            text: 'Please wait...',
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                            Swal.showLoading()
                            }
                        }).then(()=>{
                            if(e == "success"){
                            Swal.fire({
                            icon: 'success',
                            title: 'DOB Updated',
                            text: 'Your DOB has been successfully updated.',
                            timer: 3000
                            }).then(() => {
                           
                                $("#dobModal").hide();
                                // In a real app, you would update the UI with the new email
                                document.querySelector('.detail-card:nth-child(5) p').textContent = newDob;
                            });
                            }
                            else{
                                Swal.fire({
                                icon: 'error',  
                                title: 'DOB Not Updated',
                                text: 'Something Went Wrong Please Try Again Later!.',
                                });
                            }
                        });
                    
                }
            });
        }
    
  


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

    });
</script>