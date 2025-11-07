
<?php
    require_once "includes/config.php";

    $userID = isset($_SESSION['id'])?$_SESSION['id']:null;
    if($userID == null){
        header("location: login.php");
    }

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->execute([$userID]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);
    

    // echo "<script>alert('".isset($_SESSION['CREATED'])?$_SESSION['CREATED']:'nothing'."')</script>"
?>

<?php
    require_once "includes/header.php";

    $minDate = date('Y-m-01');
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
            position: relative;
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
           
            margin-left: 2%;
            left:1%;
        }
        
        .sidebar {
            position:relative;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: fit-content;
            width: 110%;
            right: 10%;
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
            position: relative;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 110%;
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
        
        .application-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .app-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border-color: var(--secondary);
        }
        
        .app-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
        }
        
        .leave-icon {
            background-color: #e1f0ff;
            color: var(--secondary);
        }
        
        .sick-icon {
            background-color: #ffece8;
            color: var(--danger);
        }
        
        .complaint-icon {
            background-color: #fff4e6;
            color: var(--warning);
        }
        .regularization-icon {
            background-color: #d3eefdff;
            color: var(--success);
        }
        
        .other-icon {
            background-color: #e6f7ee;
            color: var(--success);
        }
        
        .app-card h3 {
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .app-card p {
            color: var(--gray);
            font-size: 14px;
        }
        
        .application-form {
            display: none;
            background: #fffbfbff;
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .back-button {
            background: var(--gray);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
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
        
        .btn-secondary {
            background-color: var(--gray);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .applications-history {
            margin-top: 40px;
            
        }
        
        .history-filters {
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow-y:scroll;
        }

        .tableflow{
            width: 100%;
            overflow-y: scroll;
        }
        .tableflow::-webkit-scrollbar{
            display:none;
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
        
        .notification {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .notification.success {
            background-color: #e7f6e9;
            color: var(--success);
            border: 1px solid #c8e6c9;
        }
        
        .notification.error {
            background-color: #ffebee;
            color: var(--danger);
            border: 1px solid #ffcdd2;
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
            top: 2%;
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
            position:fixed; 
            top:1%; 
            left:50%; 
            transform:translateX(-50%); 
            background:#fff; 
            padding:20px; 
            border:1px solid #ccc; 
            z-index:1001;
            height: 50vw;
            overflow-y: scroll;
        }
        
        .notification-content-head-div{
            margin-top: 20px;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
        }
        
        @media(max-width: 1378px){
            .content-area{
                width: 100%;
            }
            
            #imageModal{
                height: 46vw;
            }
        }
        @media(max-width: 1378px){
            .content-area{
                width: 60vw;
            }
            
            
        }
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                margin-left: 0%;
                left: -3%;
            }
            .content-area{
                width: 100%;
            }
            
            .application-cards {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .history-filters {
                flex-direction: column;
            }
            .tableflow{
                width: 87%;
                /* overflow-y: scroll; */
            }
            .sidebar {
            
                width: 97%;
                margin: 10%;
            }
            #imageModal{
                top: 3%;
                height: 95vh;
                width: 95vw;
            }
        }
        @media (max-width: 768px) {

            
            
            .tableflow{
                width: 88vw;
                /* overflow-y: scroll; */
            }
            .modal-content {
           
            left: 6%;
           
            }
            header{
                width: 102vw;
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
                <a href="#" class="nav-item active">
                    <i class="fas fa-file-alt"></i>
                    <span>Applications</span>
                </a>
                <a href="history.php" class="nav-item">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='schedule.php'">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>

            <div class="content-area">
                <h2 class="section-title">
                    <i class="fas fa-file-alt"></i>
                    Employee Applications
                </h2>
                
                <div id="notification" class="notification">
                    <i class="fas fa-info-circle"></i>
                    <span id="notification-text"></span>
                </div>
                
                <div class="application-cards">
                    <div class="app-card" data-type="casual_leave">
                        <div class="app-icon leave-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <h3>Casual Leave</h3>
                        <p>Request for personal time off</p>
                    </div>
                    
                    <div class="app-card" data-type="sick_leave">
                        <div class="app-icon sick-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3>Sick Leave</h3>
                        <p>Request for medical leave</p>
                    </div>
                    
                    <div class="app-card" data-type="half_leave">
                        <div class="app-icon leave-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Half Day Leave</h3>
                        <p>Request for half day off</p>
                    </div>
                    
                    <div class="app-card" data-type="complaint">
                        <div class="app-icon complaint-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Report Complaint</h3>
                        <p>Submit a workplace complaint</p>
                    </div>
                    
                    <div class="app-card" data-type="other">
                        <div class="app-icon other-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3>Other Request</h3>
                        <p>Submit other types of requests</p>
                    </div>
                    <div class="app-card" data-type="regularization">
                        <div class="app-icon regularization-icon">
                            <i class="fas  fa-suitcase"></i>
                        </div>
                        <h3>RegulariZation</h3>
                        <p>Submit RegulariZation</p>
                    </div>
                    <div class="app-card" data-type="punch_Out_regularization">
                        <div class="app-icon leave-icon">
                            <i class="fas  fa-person-circle-question"></i>
                        </div>
                        <h3>Missing Punch Out Request</h3>
                        <p>Submit Missing Punch Out Request</p>
                    </div>
                    <div class="app-card" data-type="work_from_home">
                        <div class="app-icon other-icon ">
                            <i class="fas  fa-house-user"></i>
                        </div>
                        <h3>Work From Home Request</h3>
                        <p>Submit Work From Home Request</p>
                    </div>
                </div>
                
                <div id="applicationForm" class="application-form">
                    <div class="form-header">
                        <h3 id="formTitle">Application Form</h3>
                        <button class="back-button" id="backButton">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                    
                    <form id="requestForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="requestType">Request Type</label>
                                <select id="requestType" name="requestType" required>
                                    <option value="">Select Request Type</option>
                                    <option value="casual_leave">Casual Leave</option>
                                    <option value="sick_leave">Sick Leave</option>
                                    <option value="half_leave">Half Day Leave</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="regularization">Regularization</option>
                                    <option value="punch_Out_regularization">Missing Punch Out Request</option>
                                    <option value="work_from_home">Work From Home Request</option>
                                    <option value="other">Other Request</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="halfDayGroup" style="display: none;">
                                <label for="halfDayType">Half Day Type</label>
                                <select id="halfDayType" name="halfDayType">
                                    <option value="first_half">First Half</option>
                                    <option value="second_half">Second Half</option>
                                </select>
                            </div>
                        </div>
                        <!-- min="<?php // echo $minDate ?>" -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="startDate">Start Date</label>
                                <input type="date" id="startDate" name="startDate"  required>
                            </div>
                            
                            <div class="form-group" id="endDateGroup">
                                <label for="endDate" id="request_end_day">End Date</label>
                                <input type="date" id="endDate" name="endDate" required>

                             
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Enter subject of your request" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Please provide details of your request" required></textarea>
                        </div>
                        
                        <div class="form-group" id="attachmentGroup">
                            <label for="attachment">Attachment (if any) <p style="color:red;">Limit is 2MB (JPEG & PNG)</p></label>
                            <input type="file" id="attachment" name="attachment">
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-primary" id="submit">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>

                <div class="today-summary">
                        <div class="summary-card">
                            <h3>Total Sick Leave </h3>
                            <p id="tsl" value=""></p>
                        </div>
                        <div class="summary-card">
                            <h3>Remaining Sick Leave</h3>
                            <p id="rsl" value=""></p>
                        </div>
                        <div class="summary-card">
                            <h3>Total Casual Leave</h3>
                            <p id="tcl" value=""></p>
                        </div>
                        <div class="summary-card">
                            <h3>Remaining Casual Leave</h3>
                            <p id="rcl" value=""></p>
                        </div>
                       
                    </div>
                
                <div class="applications-history">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        Application History
                    </h3>
                    
                    <div class="history-filters">
                        <div class="filter-group">
                            <label for="statusFilter">Status</label>
                            <select id="statusFilter">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="typeFilter">Type</label>
                            <select id="typeFilter">
                                <option value="all">All Types</option>
                                <option value="casual_leave">Casual Leave</option>
                                <option value="sick_leave">Sick Leave</option>
                                <option value="half_leave">Half Day Leave</option>
                                <option value="complaint">Complaint</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="dateFilter">Date Range</label>
                            <select id="dateFilter">
                                <option value="all">All Time</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button type="button" class="btn btn-primary" id="checkfilter">
                                <i class="fas fa-paper-plane"></i> check
                            </button>
                        </div>
                        
                    </div>
                    
                    
                    <div class="tableflow">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th width="25%">Period</th>
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
                    <div class="detail-value" id="detail-id">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Employee:</div>
                    <div class="detail-value" id="detail-employee">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Request Type:</div>
                    <div class="detail-value" id="detail-type">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Subject:</div>
                    <div class="detail-value" id="detail-subject">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Date Range:</div>
                    <div class="detail-value" id="detail-dates">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Half Day:</div>
                    <div class="detail-value" id="detail-halfday">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value" id="detail-description">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Attachment:</div>
                    <div class="detail-value" id="detail-attachment" value="">
                        <i class="fas fa-paperclip"></i> 
                        <a href="#" class="attachment-link">
                        </a>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Applied On:</div>
                    <div class="detail-value" id="detail-applied">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge status-pending" id="detail-status">Loading....</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="modalcancelBtn">
                    <i class="fas fa-times"></i> Close
                </button>
                
            </div>
        </div>
    </div>

    <div id="imageModal">
        <img id="modalImage" src="" alt="Attachment" style="max-width:100%; height:auto; margin-bottom:20px;">
        <button class="btn btn-secondary" onclick="document.getElementById('imageModal').style.display='none'">Close</button>
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
    require_once "includes/footer.php";
?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.documentElement.lang = "en-GB";

            
            $(document).on("click", ".view_request", function(){
            let info = "req_modal";
            let id = $(this).data("id");
            let type = $(this).data("type");
            let time = $(this).data("time");

            $.ajax({
                url: "time_management/requests.php",
                method: "POST",
                dataType: 'json', 
                data: {info:info, id:id, type:type, time:time},
                success: function(e){
                    // console.log(e);
                    $("#detail-id").html(e.id);
                    $("#detail-employee").html(e.name);
                    $("#detail-type").html(e.req_type);
                    $("#detail-subject").html(e.subject);
                    $("#detail-dates").html(e.startdate+" - "+e.enddate);
                    $("#detail-halfday").html(e.halfday);
                    $("#detail-description").html(e.description);
                    $(".attachment-link").html(e.file);
                    $(".attachment-link").val(e.file);
                    $("#detail-applied").html(e.createdat);
                    $("#detail-status").html(e.status);
                    if(e.status == "rejected"){
                        $("#detail-status").css("color", "red");
                        $(".status-pending").css({
                            "background-color":"#fabdb9",
                            "opacity": 0.6
                        });
                                                
                    }
                    if(e.status == "approved"){
                        $("#detail-status").css("color", "green");
          
                        $(".status-pending").css({
                            "background-color":"#b7f7a8",
                            "opacity": 0.6
                        });
                        
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


        $(".close-btn").click(function(){
            $("#applicationModal").hide();
            $(".status-pending").css({
                            "background-color":"#fff4e6",
                            "opacity": 0.6
                        });
        });
        $("#modalcancelBtn").click(function(){
            $("#applicationModal").hide();
            $(".status-pending").css({
                            "background-color":"#fff4e6",
                            "opacity": 0.6
                        });
        });


        $(".attachment-link").on("click", function(e) {
            e.preventDefault();
            var imgPath = $(this).val();
            $("#modalImage").attr("src", imgPath);
            $("#imageModal").fadeIn();
        });



            // Set today's date as default start date
            document.getElementById('startDate').valueAsDate = new Date();
            
            // Application card click handler
            const appCards = document.querySelectorAll('.app-card');
            const applicationForm = document.getElementById('applicationForm');
            const formTitle = document.getElementById('formTitle');
            const requestType = document.getElementById('requestType');
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notification-text');
            
            appCards.forEach(card => {
                card.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    applicationForm.style.display = 'block';
                    
                    // Set the form title based on application type
                    const titles = {
                        'casual_leave': 'Casual Leave Application',
                        'sick_leave': 'Sick Leave Application',
                        'half_leave': 'Half Day Leave Application',
                        'complaint': 'Report a Complaint',
                        'regularization': 'Apply For RegulariZation',
                        'punch_Out_regularization': 'Request for Missing Punch Out',
                        'other': 'Other Request'
                    };
                    
                    formTitle.textContent = titles[type];
                    requestType.value = type;
                    
                    // Show/hide half day options
                    toggleHalfDayOptions(type);
                    
                    // Show/hide end date for leave types
                    toggleEndDateField(type);
                    
                    // Scroll to form
                    applicationForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            // Back button handler
            document.getElementById('backButton').addEventListener('click', function() {
                applicationForm.style.display = 'none';
            });
            
            // Cancel button handler
            document.getElementById('cancelBtn').addEventListener('click', function() {
                applicationForm.style.display = 'none';
                document.getElementById('requestForm').reset();
            });
            
            // Request type change handler
            requestType.addEventListener('change', function() {
                const type = this.value;
                toggleHalfDayOptions(type);
                toggleEndDateField(type);
            });
            
            // Toggle half day options based on request type
            function toggleHalfDayOptions(type) {
                const halfDayGroup = document.getElementById('halfDayGroup');
                if (type === 'half_leave') {
                    halfDayGroup.style.display = 'block';
                } else {
                    halfDayGroup.style.display = 'none';
                }
            }
            
            // Toggle end date field based on request type
            function toggleEndDateField(type) {
                const endDateGroup = document.getElementById('endDateGroup');
                if (type === 'casual_leave' || type === 'sick_leave' || type === 'regularization' || type === 'punch_Out_regularization' || type === 'work_from_home') {

                    endDateGroup.style.display = 'block';
                    document.getElementById('endDate').setAttribute('required', 'required'); 
                    if(type == "punch_Out_regularization"){
                        document.getElementById('endDate').setAttribute('type', 'datetime-local'); 
                        
                        document.getElementById('request_end_day').innerHTML = "Punch Out Time";

                    }
                    else{

                        document.getElementById('endDate').setAttribute('type', 'date'); 
                        document.getElementById('request_end_day').innerHTML = "End Date";
                    }
                } else {
                    endDateGroup.style.display = 'none';
                    document.getElementById('endDate').removeAttribute('required');
                }
            }
            
           
            function showNotification(message, type) {
                notificationText.textContent = message;
                notification.className = 'notification ' + type;
                notification.style.display = 'flex';
                
                // Hide notification after 5 seconds
                setTimeout(hideNotification, 5000);
            }
            
            // Hide notification
            function hideNotification() {
                notification.style.display = 'none';
            }
        });
    </script>

    <script>
        
        $(document).ready(function(){
            // prevent all kind of functions by user.
                
            // document.addEventListener('contextmenu', e => e.preventDefault());
            // document.onkeydown = function (e) {
            // // F12
            // if (e.keyCode === 123) return false;
        
            // // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
            // if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].includes(e.key.toUpperCase())) return false;
        
            // // Ctrl+U (View Source)
            // if (e.ctrlKey && e.key.toUpperCase() === 'U') return false;
            // };
            
            // document.addEventListener('selectstart', e => e.preventDefault());
            // document.addEventListener('dragstart', e => e.preventDefault());
            // document.addEventListener('copy', e => e.preventDefault());
            
            
            



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


            checkrequests();
            leaveDayCheck();

            function leaveDayCheck(){
                info = "leaveDayCheck";
                // alert("leaveDayCheck");
                // alert("leaveDayCheck");
                // alert("leaveDayCheck");
                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    dataType: "json",
                    data: {info: info},
                    success: function(e){
                        if(e){
                            $("#tsl").html(e.tsl);
                            $("#tcl").html(e.tcl);
                            $("#rsl").html(e.rsl);
                            $("#rcl").html(e.rcl);
                            
                            $("#rsl").val(e.valrsl);
                            $("#rcl").val(e.valrcl);
                        }
                    },
                    error(response, status, error){
                        console.log("error: "+error);
                        console.log("response: "+response);
                        console.log("status: "+status);
                    }
                });
            }
            
            function checkrequests(){

                let info = "checkReq";
                let id = '<?php  echo "$userID"; ?>';

                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: {info:info, id:id},
                    success: function(e){
                        $("#Tbody").html(e);
                    }
                });
            }

            let reqType = "nothing";
            $(document).on("click", "#submit", function(e){
                // e.preventDefault();
                reqType = $("#requestType").val();
                // alert(reqType);


                if(reqType == "casual_leave"){
                    // alert(reqType);
                    let type = "casual_leave";
                    let start_date = $("#startDate").val();
                    let end_date = $("#endDate").val();
                    let start_date1 = new Date($("#startDate").val());
                    let end_date1 = new Date($("#endDate").val());
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';
                    let rcl = $("#rcl").val();

                 
                    //  alert("value of rsl "+rcl);

                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', 'casualLeave');
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    formdata.append('end_date', end_date);
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    formdata.append('rcl', rcl);
                    
                    formdata.append('image', image);

                    if(start_date1 > end_date1){
                        swal.fire({
                            text: "End Date can't less then Start Date!",
                            icon: "error"
                        });
                    }
                    else{

                        casualLeave(formdata);
                    }

                }



                if(reqType == "sick_leave"){
                    // alert(reqType);
                    let type = "sick_leave";
                    let start_date = $("#startDate").val();
                    let end_date = $("#endDate").val();
                    let start_date1 = new Date($("#startDate").val());
                    let end_date1 = new Date($("#endDate").val());
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';
                    let rsl = $("#rsl").val();


                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', type);
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    formdata.append('end_date', end_date);
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    formdata.append('rsl', rsl);
                    
                    formdata.append('image', image);

                    if(start_date1 > end_date1){
                        swal.fire({
                            text: "End Date can't less then Start Date! from ",
                            icon: "error"
                        });
                    }
                    else{
                        

                        sickLeave(formdata);
                    }

                }

                if(reqType == "half_leave"){
                    // alert(reqType);
                    let type = "half_day";
                    let start_date = $("#startDate").val();
                    let half_D_type = $("#halfDayType").val();
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';

                    // alert(start_date);
                    // alert(half_D_type);
                    // alert(subject);
                    // alert(description);
                    // alert(formdata);
                    // alert(image);
                    // alert(id);
                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', type);
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    formdata.append('half_D_type', half_D_type);
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    
                    formdata.append('image', image);

                   
                    half_leave(formdata);
                    

                }


                if(reqType == "work_from_home"){
                    // alert(reqType);
                    let type = "work_from_home";
                    let start_date = $("#startDate").val();
                    let end_date = $("#endDate").val();
                    let start_date1 = new Date($("#startDate").val());
                    let end_date1 = new Date($("#endDate").val());
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';
                    // let rsl = $("#rsl").val();


                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', type);
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    formdata.append('end_date', end_date);
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    formdata.append('rsl', rsl);
                    
                    formdata.append('image', image);

                    if(start_date1 > end_date1){
                        swal.fire({
                            text: "End Date can't less then Start Date! from ",
                            icon: "error"
                        });
                    }
                    else{
                        

                        work_from_home(formdata);
                    }

                }
                
                if(reqType == "complaint" || reqType == "other"){
                    // alert(reqType);
                    let type = reqType;
                    let start_date = $("#startDate").val();
                    
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';


                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', type);
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    
                    formdata.append('image', image);

                   
                    half_leave(formdata);
                    

                }


                if(reqType == "regularization"){
                    // alert(reqType);
                    let type = "regularization";
                    let start_date = $("#startDate").val();
                    let end_date = $("#endDate").val();
                    let start_date1 = new Date($("#startDate").val());
                    let end_date1 = new Date($("#endDate").val());
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';


                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', 'regularization');
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    formdata.append('end_date', end_date);
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    
                    formdata.append('image', image);

                    if(start_date1 > end_date1){
                        swal.fire({
                            text: "End Date can't less then Start Date!",
                            icon: "error"
                        });
                    }
                    else{

                        regularization(formdata);
                    }

                }
                
                
                
                
                if(reqType == "punch_Out_regularization"){
                    // alert(reqType);
                    let type = "punch_Out_regularization";
                    let start_date = $("#startDate").val();
                    let end_date = $("#endDate").val();
                    let start_date1 = new Date($("#startDate").val());
                    let end_date1 = new Date($("#endDate").val());
                    let subject = $("#subject").val();
                    let description = $("#description").val();
                    let formdata = new FormData();
                    let image = $("#attachment")[0].files[0];
                    let id = '<?php echo "$userID"; ?>';


                    if(image == null){
                        image == "";
                    }
                    formdata.append('info', 'punch_Out_regularization');
                    formdata.append('id', id);
                    formdata.append('type', type);
                    formdata.append('start_date', start_date);
                    formdata.append('end_date', end_date);
                    formdata.append('subject', subject);
                    formdata.append('description', description);
                    
                    formdata.append('image', image);

                    

                    punch_Out_regularization(formdata);
                    

                }
                
                
            });

            function casualLeave(formdata){
                checksession();

                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else{
                            $("#Tbody").html(e);
                            swal.fire({
                                text: "Error! "+e,
                                icon: "error"
                            });
                            
                        }
                        // else {
                        //     swal.fire({
                        //         text: "Something Went Wrong Please Try Again sfsfsfsdfs! "+e,
                        //         icon: "error"
                        //     });
                        //     // alert(e);
                        // }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " +error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                    }
                });

            }


            function sickLeave(formdata){
                
                
                checksession();
                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else {
                            $("#Tbody").html(e);
                            swal.fire({
                                text: "Error! "+e,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " + error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                    }
                });

            }




            function work_from_home(formdata){
                
                
                checksession();
                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else {
                            $("#Tbody").html(e);
                            swal.fire({
                                text: "Error! "+e,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " + error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                    }
                });

            }
            
            function half_leave(formdata){
                // alert("half_D_type");
                
                checksession();
                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else {
                            $("#Tbody").html(e);
                            swal.fire({
                                text: "Error! "+e,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " + error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                    }
                });

            }

            function complaint(formdata){
                // alert("half_D_type");
                
                checksession();
                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else {
                            $("#Tbody").html(e);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " + error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                    }
                });

            }

            function regularization(formdata){
                checksession();

                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else{
                            $("#Tbody").html(e);
                            swal.fire({
                                text: "Error!"+e,
                                icon: "info"
                            });
                            // alert(e);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " +error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                    }
                });

            }
            
            
            function punch_Out_regularization(formdata){
                checksession();

                $.ajax({
                    url: "time_management/requests.php",
                    method: "POST",
                    data: formdata,
                    contentType: false,         //  prevent jQuery from setting content type
                    processData: false,   
                    success: function(e){
                        // alert(e);
                        if(e == 100){
                            swal.fire({
                                text: "Request Submitted Successfully!",
                                icon:"success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                        }
                        else{
                            // $("#Tbody").html(e);
                            console.log(e);
                            swal.fire({
                                text: "Error!"+e,
                                icon: "info"
                            });
                            // alert(e);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                          title: "Oops!",
                          text: "Something went wrong. Please try again later.\nError: " +error,
                          icon: "error",
                          confirmButtonText: "OK"
                        });
                        
                        console.log(xhr);
                        console.log(status);
                        console.log(error);
                    }
                });

            }
            

            checksession();
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

            $(document).on("click", "#checkfilter", function(){
                
                let click = "filterReq";
                let status = $("#statusFilter").val();
                let type = $("#typeFilter").val();
                let limit = $("#dateFilter").val();

                $.ajax({
                    url: "time_management/filter.php",
                    method: "POST",
                    data: {click:click, status:status, type:type, limit:limit},
                    success: function(e){
                        if(e){
                            $("#Tbody").html(e);
                            
                        }
                    }
                });
            });

        });
    </script>
