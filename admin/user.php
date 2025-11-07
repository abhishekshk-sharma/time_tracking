    <?php
        require_once "../includes/config.php";
        
        // if(isset($_SESSION['ack'])){
            //     unset($_SESSION['info']);
            //     unset($_SESSION['ack']);
            // }
            $userID = isset($_SESSION['getUserById'])?$_SESSION['getUserById']:null;
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
            $stmt->execute([$userID]);
            $logindetails = $stmt->fetch(PDO::FETCH_ASSOC);
            $join = new DateTime($logindetails['hire_date']);
            
            if(($logindetails['end_date'] != null) && ($logindetails['end_date'] != "0000-00-00")){
                $end = new DateTime($logindetails['end_date']);
            }
            else{
                $end = null;
            }
            $status = $logindetails['status'];


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

            $stmt = $pdo->prepare("SELECT * FROM leavecount WHERE employee_id = ?");
            $stmt->execute([$userID]);
            $leave = $stmt->fetch(PDO::FETCH_ASSOC);

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
                position:relative;
                display: grid;
                grid-template-columns: 1fr 3fr;
                gap: 20px;
                /*left: -16%;*/
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
                width: 130%;
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
</style>

        <div class="container">
            <div class="main-content">
                <div class="sidebar">
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Admin Dashboard</span>
                    </a>
                    <a href="#" class="nav-item active">
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
                    <a href="logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>

                <div class="content-area">
                    <div class="employee-header">
                        <!-- <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Employee Photo" class="employee-photo"> -->
                        <div class="employee-info">
                            <h2><?php echo $logindetails['full_name']; ?></h2>
                            <p><?php echo $logindetails['emp_id']; ?> | <?php echo $logindetails['department']; ?></p>
                        </div>
                    </div>

                    <div class="details-grid">
                        <div class="detail-card">
                            <h3>Personal Information</h3>
                            <div class="detail-item">
                                <span class="label">Full Name:</span>
                                <span class="value"><?php echo $logindetails['full_name']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Employee ID:</span>
                                <span class="value"><?php echo $logindetails['emp_id']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo $logindetails['email']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Phone:</span>
                                <span class="value"><?php echo $logindetails['phone']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Date Of Birth:</span>
                                <span class="value"><?php echo ((new DateTime($logindetails['dob'], new DateTimeZone('Asia/Kolkata')))->format('d-m-Y')); ?></span>
                            </div>
                        </div>

                        <div class="detail-card">
                            <h3>Employment Details</h3>
                            <div class="detail-item">
                                <span class="label">Department:</span>
                                <span class="value"><?php echo $logindetails['department']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Position:</span>
                                <span class="value"><?php echo $logindetails['position']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Status:</span>
                                <?php 
                                $estatus = $logindetails['status'];
                                if($estatus == "active"){
                                    $ecolor = '--success';
                                }
                                else{
                                    $ecolor = '--accent';
                                }
                                ?>
                            <span class="value" style="color: var(<?php echo $ecolor; ?>);"><?php echo $estatus; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Joining Date:</span>
                                <span class="value"><?php echo $join->format("M d, Y"); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Ending Date:</span>
                                <span class="value"><?php echo $end ? $end->format("M d, Y") : "N/A"; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label"> Pending Sick Leave:</span>
                                <span class="value"><?php echo isset($leave['sick_leave'])?$leave['sick_leave']: ""; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Pending Casual Leave:</span>
                                <span class="value"><?php echo isset($leave['casual_leave'])?$leave['casual_leave']: ""; ?></span>
                            </div>

                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary" id="editEmployeeBtn">
                            <i class="fas fa-edit"></i> Edit Employee Details
                        </button>
                        <button class="btn btn-warning" Onclick="window.location.href = 'attnd_history.php'">
                            <i class="fas fa-clock"></i> View History Records
                        </button>
                        
                        <button class="btn btn-danger" id="deleteEmployeeBtn">
                            <i class="fas fa-trash"></i> Delete Employee
                        </button>

                    </div>

                    <div class="activity-table">
                    
                        <div class="today-summary">
                            <div class="summary-card">
                                <h3>Work Hours</h3>
                                <p id="worktime">0h 0m</p>
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
                                <h3>Punch In</h3>
                                <p id="punch_in">00:00 AM</p>
                            </div>
                            <div class="summary-card">
                                <h3>Total Hours</h3>
                                <p id="total_hours"> - </p>
                            </div>
                            <div class="summary-card">
                                <h3>Worked</h3>
                                <p id="network"> - </p>
                            </div>
                            <div class="summary-card">
                                <h3>Lunch</h3>
                                <p id="totalLunchByemp"> - </p>
                            </div>
                            <div class="summary-card">
                                <h3>status</h3>
                                <p id="late"> - </p>
                            </div>

                        </div>
                        <div class="recent-activity">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Employee Modal -->
        <div class="modal" id="editEmployeeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Employee Details</h3>
                    <button class="close-btn">&times;</button>
                </div>
                
                <form id="employeeForm">
                    <div class="form-group">
                        <label for="empName">Full Name</label>
                        <input type="text" id="empName" value="<?php echo $logindetails['full_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="empName">User Name</label>
                        <input type="text" id="empusername" value="<?php echo $logindetails['username']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="empId">Employee ID</label>
                        <input type="text" id="empId" data-id="<?php echo $logindetails['id']; ?>" value="<?php echo $logindetails['emp_id']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="empDept">Department</label>
                        <select id="empDept">
                            <?php
                                foreach( $dep as $row){
                                    echo "<option>".$row['name']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="empPosition">Position</label>
                        <input type="text" id="empPosition" value="<?php echo $logindetails['position']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="empEmail">Email</label>
                        <input type="email" id="empEmail" value="<?php echo $logindetails['email']; ?>" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="empPhone">Phone</label>
                        <input type="tel" id="empPhone" value="<?php echo $logindetails['phone']; ?>" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="empJoiningDate">Joining Date</label>
                        <input type="date" id="empJoiningDate" value="<?php echo $logindetails['hire_date']; ?>" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="empEndingDate">Ending Date</label>
                        <input type="date" id="empEndingDate" value="<?php echo $logindetails['end_date']; ?>" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="empCasualLeave">Casual Leave</label>
                        <input type="number" id="empCasualLeave" value="<?php echo isset($leave['casual_leave'])?$leave['casual_leave']: ""; ?>" required>
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="empSickLeave">Sick Leave</label>
                        <input type="number" id="empSickLeave" value="<?php echo isset($leave['sick_leave'])?$leave['sick_leave']: ""; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="empStatus">Status: <span class="value" style="color: var(<?php echo $ecolor; ?>);"><?php echo $estatus; ?></span></label>
                        <select id="empStatus">
                            <option><?php echo $estatus; ?></option>
                            <?php if($estatus == "active"): ?>
                                <option>Inactive</option>
                            <?php else: ?>
                                <option>Active</option>
                            <?php endif; ?>
                            <option>On Leave</option>
                        </select>
                    </div>
                </form>
                
                <div class="modal-footer">
                    <button class="cancel-btn">Cancel</button>
                    <button class="save-btn">Save Changes</button>
                </div>
                <div class="form-group">
                    <label for="empEmail">New Password</label>
                    <input type="password" id="newpass" value="" placeholder='Enter New Password'  required >
                </div>
                <div class="modal-footer">
                        <!-- <button class="cancelpass-btn">Cancel</button> -->
                        <button class="save-btn" id="savePass">Save Password</button>
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
                        
                            // $(".notification-content-head-div").removeClass("notification-item unread");

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


            // <<<<<<<======== ALL AJAX request ========>>>>>>>>>>
            // 
            // var hello = '<?php //echo $date->format("Y-m-d H:i:s"); ?>';
            // alert(hello);
            detailsById();
            function detailsById(){
                var info = "todaysActivity";
                var id = '<?php echo $userID; ?>';
            
                $.ajax({
                    url: "time_management/total.php",
                    method: "POST",
                    data: {info:info, id: id},
                    success: function(e){
                        if(e){
                            $(".recent-activity").html(e);
                            // alert("hello "+e);
                        }
                        
                    }
                });
            }


                timeWorked();
        function timeWorked(){
            var info = "timeWorked";
            var id = '<?php echo $userID; ?>';
            $.ajax({
                url: "time_management/total.php",
                type: "post",
                dataType: 'json', 
                data: {info : info, id:id},
                success: function(e){
                    $("#worktime").html(e.worktime);
                    $("#punchTime").html(e.punchTime);
                    $("#lunchDuation").html(e.lunchDuation);
                    $("#punch_in").html(e.punch_in);
                    $("#total_hours").html(e.total_hours);
                    $("#network").html(e.network);
                    $("#totalLunchByemp").html(e.totalLunchByemp);
                    $("#late").html(e.late);
                },
                error: function(r,s,e){
                    console.log(r.responseText);
                    console.log(s);
                    console.log(e);
                }
            });
        }

            // <<<<<<<========== This is for admin side user panel ===========>>>>>>>>>>

                    // Modal functionality
            const modal = document.getElementById('editEmployeeModal');
            const editBtn = document.getElementById('editEmployeeBtn');
            const closeBtn = document.querySelector('.close-btn');
            const cancelBtn = document.querySelector('.cancel-btn');
            
            editBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
            });
            
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
            });
            
            cancelBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
            });
            
            // window.addEventListener('click', (e) => {
            //     if (e.target === modal) {
            //         modal.style.display = 'none';
            //     }
            // });
            
            // Save changes functionality
            const saveBtn = document.querySelector('.save-btn');
            saveBtn.addEventListener('click', () => {
                // In a real application, this would save to a database
                // alert('Employee details updated successfully!');
                var info = "editUser";
                const empId = $("#empId").val();
                const fullname = $("#empName").val();
                const empusername = $("#empusername").val();
              
                const empDept = $("#empDept").val();
                const empPosition = $("#empPosition").val();
                const empEmail = $("#empEmail").val();
                const empPhone = $("#empPhone").val();
                const empJoiningDate = $("#empJoiningDate").val();
                const empEndingDate = $("#empEndingDate").val();
                const empCasualLeave = $("#empCasualLeave").val();
                const empSickLeave = $("#empSickLeave").val();
                const empStatus = $("#empStatus").val();

                const email_pattern = /^[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+$/;
                const phone_pattern = /^[6-9]\d{9}$/;
                errorCode = 0;
                theerror = '';
                if(!email_pattern.test(empEmail)){

                    errorCode = 1;
                    theerror = 'email';
                    $("#empEmail").css('color', 'red');
                }
                if(fullname == ""){
                    errorCode = 1;
                    theerror = 'fullname';
                    $("#empName").css('color', 'red');
                }
                if(empId == ""){
                    errorCode = 1;
                    theerror = 'empId';
                    $("#empId").css('color', 'red');
                }
                if(empusername == ""){
                    errorCode = 1;
                    theerror = 'empusername';
                    $("#empusername").css('color', 'red');
                }
                
                if(empDept == ""){
                    errorCode = 1;
                    theerror = 'empDepartment';
                    $("#empDept").css('color', 'red');

                }
                if(empPosition == ""){
                    errorCode = 1;
                    theerror = 'empPosition';
                    $("#empPosition").css('color', 'red');

                }
                if(!phone_pattern.test(empPhone)){
                    errorCode = 1;
                    theerror = 'empPhone';
                    $("#empPhone").css('color', 'red');

                }
                if(empJoiningDate == ''){
                    errorCode = 1;
                    theerror = 'empJoiningDate';
                    $("#empJoiningDate").css('color', 'red');

                }
                
                if(empCasualLeave == ''){
                    errorCode = 1;
                    theerror = 'empCasualLeave';
                    $("#empCasualLeave").css('color', 'red');

                }
                if(empSickLeave == ''){
                    errorCode = 1;
                    theerror = 'empSickLeave';
                    $("#empSickLeave").css('color', 'red');

                }
                if(empStatus == ''){
                    errorCode = 1;
                    theerror = 'empStatus';
                    $("#empStatus").css('color', 'red');

                }
                if(errorCode == 1){
                    alert('something went wrong! Please enter valid details: ' + theerror);
                }
                else{
                    let id = $("#empId").data("id");
                    $.ajax({
                        url: "time_management/total.php",
                        method: "POST",
                        data:{info:info, fullname:fullname, empusername:empusername, empId:empId, id:id, empDept:empDept, empPosition:empPosition, empEmail:empEmail, empPhone:empPhone, empJoiningDate:empJoiningDate, empEndingDate:empEndingDate, empCasualLeave:empCasualLeave, empSickLeave:empSickLeave, empStatus:empStatus},
                        success: function(e){
                            if(e == "Edited"){
                                Swal.fire({
                                    title: "Credentials Updated Successfully!",
                                    icon: "success",
                                    confirmButton: true
                                }).then(res=>{
                                    if(res.isConfirmed){
                                        modal.style.display = 'none';
                                        window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>';
                                    }
                                });
                                
                                // alert('!(from ajax Edited)' + p);
                            }
                            else{
                                Swal.fire({
                                        title: "Error! "+e,
                                        icon: "error",
                                        confirmButtonText: "Ok"
                                });
                            }
                            
                        }
                    }); 

                    $("#empEmail").css('color', 'black');
                    $("#empName").css('color', 'black');
                    $("#empusername").css('color', 'black');
                    $("#empId").css('color', 'black');
                    $("#empDept").css('color', 'black');
                    $("#empPosition").css('color', 'black');
                    $("#empPhone").css('color', 'black');
                    $("#empJoiningDate").css('color', 'black');
                    $("#empEndingDate").css('color', 'black');
                    $("#empCasualLeave").css('color', 'black');
                    $("#empSickLeave").css('color', 'black');
                    $("#empStatus").css('color', 'black');

                }

            });

            // <<<<<<<,=========== This is for change the password ==========>>>>>>>>>>>>

            $("#savePass").click(function(){
                // alert('clicked pass'); 
                let info = "ChangePassword"; 
                
                const newPass = $("#newpass").val();
                const empId = $("#empId").val();

                var error = 0;
                
                if(newPass == ''){
                    error = 1;
                }   
                if(error == 1){
                    Swal.fire({
                            title: "Error!",
                            text: "Please enter a valid password",
                            icon: "error",
                            confirmButtonText: "Ok"
                    });
                }
                else{
                    
                    Swal.fire({
                        title: 'Updating Password',
                        text: 'Please wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                        Swal.showLoading()
                        }
                    })

                    $.ajax({
                        url: "time_management/total.php",
                        method: "POST",
                        data: {info: info, newPass: newPass, empId: empId},
                    
                        success: function(e) {
                         // Close the loading alert

                            if (e === "changed") {
                                Swal.fire({
                                    title: "Password Updated Successfully!",
                                    icon: "success",
                                    confirmButtonText: "Ok"
                                });
                                $("#editEmployeeModal").hide();
                                $("#newpass").val('');  
                            } else {
                                alert('Something went wrong! ' + e.details);
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            alert("AJAX error: " + error);
                        }
                    });
                    
                }
            });
            
            
            
            $("#deleteEmployeeBtn").click(function(){
                let info = "deleteEmployee";
                let empId = '<?php echo $_SESSION['getUserById']; ?>';
            
                // alert(empId+" "+id);
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        swal.fire({
                            title: "For Security Reasons, Confirm Again",
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancleButton: true,
                            showConfirmButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: "Yes, Delete it!",
                            cancelButtonText: "No, Cancel!"
                        }).then((result)=>{
                            if(result.isConfirmed){

                                $.ajax({
                                    url: "time_management/total.php",
                                    method: "POST",
                                    data: {info:info, empId:empId},
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
                                            if(e == "Deleted"){
                                            Swal.fire(
                                                'Deleted!',
                                                'Employee has been deleted.',
                                                'success'
                                            ).then(res=>{
                                                if(res.isConfirmed){
                                                    window.location.href = 'index.php';
                                                }
                                            });

                                            }
                                            else{
                                                Swal.fire({
                                                    title: "Error! "+e,
                                                    icon: "error",
                                                    confirmButtonText: "Ok"
                                                });
                                            }
                                        });
                                        
                                    }
                                });
                            }
                        });                             
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

            // <<<<<<<<=========== This is for admin to get Idea about the employee =========>>>>>>>>>>>>>>>>
            
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