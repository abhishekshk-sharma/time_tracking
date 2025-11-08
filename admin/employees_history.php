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

    $stmt = $pdo->query("SELECT count(*) as total FROM employees WHERE status = 'active'");
    $count = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = $count[0]['total'];



    $depstmt = $pdo->query("SELECT * FROM departments");
    $departments = $depstmt->fetchAll(PDO::FETCH_ASSOC);


    ?>


    <?php
    require_once "includes/header.php";
    if(!empty($_SESSION['info'])){
        unset($_SESSION['info']);
        echo "<script> alert('$info')</script>";
    }
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
                --holiday: #9b59b6;
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
                position: flex;
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
                margin-left: -20px;
            }
            
            .sidebar {
                position:relative;
                background: white;
                border-radius: 10px;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                height: fit-content;
                width: 100%;
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
                width: 120%;
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
            
            .time-card {
                background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
                color: white;
                border-radius: 15px;
                padding: 25px;
                margin-bottom: 25px;
                text-align: center;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
            
            .current-time {
                font-size: 48px;
                font-weight: 700;
                margin: 10px 0;
                font-family: 'Courier New', monospace;
            }
            
            .current-date {
                font-size: 18px;
                opacity: 0.9;
                margin-bottom: 20px;
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
                margin-left: auto;
            }
            
            .summary-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin-bottom: 25px;
            }
            
            .summary-card {
                background: white;
                border-radius: 10px;
                padding: 15px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                text-align: center;
                border-left: 4px solid var(--secondary);
            }
            
            .summary-card h3 {
                font-size: 14px;
                color: var(--gray);
                margin-bottom: 8px;
            }
            
            .summary-card p {
                font-size: 20px;
                font-weight: 700;
                color: var(--dark);
            }
            
            .indextable {
                position: relative;
                width: 98%;
                overflow-x: auto;
                margin-top: 20px;
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
                margin-right: 5px;
            }
            
            .status-Present {
                background-color: #e7f6e9;
                color: #2ecc71;
            }
            
            .status-Late {
                background-color: #fbf7eb;
                color: #f39c12;
            }
            
            .status-Absent {
                background-color: #fbebec;
                color: #e74c3c;
            }
            .status-regularization {
                background-color: #fbebf9ff;
                color: #f922d2ff;
            }
            .status-WFH {
                background-color: #ebf9fbff;
                color: #25b5e5ff;
            }

            .status-HalfDay {
                background-color: #e8f4fd;
                color: #3498db;
            }
            .status-Holiday {
                background-color: #f3e8fd;
                color: #9b59b6;
            }   
            .issue-row {
                background-color: #fff4f4;
            }
            
            .issue-row:hover {
                background-color: #ffe6e6;
            }
            
            .analysis-summary {
                margin-top: 40px;
                padding: 20px;
                background: #f9f9f9;
                border-radius: 10px;
            }
            
            .issues-list {
                margin-top: 20px;
            }
            
            .issue-item {
                display: flex;
                align-items: flex-start;
                margin-bottom: 25px;
                padding: 15px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            
            .issue-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 15px;
                background-color: #e1ebff;
                color: var(--secondary);
                font-size: 20px;
            }
            
            .issue-details {
                flex: 1;
            }
            
            .issue-details h4 {
                color: var(--dark);
                margin-bottom: 5px;
            }
            
            .issue-details p {
                color: var(--gray);
                margin-bottom: 10px;
            }
            
            .employee-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .employee-tag {
                padding: 5px 10px;
                background: #f0f5ff;
                border-radius: 15px;
                font-size: 13px;
                color: var(--secondary);
            }
            
            .pagination {
                display: flex;
                justify-content: center;
                margin-top: 30px;
                gap: 10px;
            }
            
            .page-btn {
                padding: 8px 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: white;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .page-btn:hover {
                background: #f0f5ff;
            }
            
            .page-btn.active {
                background: var(--secondary);
                color: white;
                border-color: var(--secondary);
            }
            
            .page-btn.disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            
            .employee-header {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 20px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 10px;
            }
            
            .employee-avatar {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: var(--secondary);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
                font-weight: bold;
            }
            
            .employee-info h3 {
                margin: 0;
                color: var(--dark);
            }
            
            .employee-info p {
                margin: 5px 0 0;
                color: var(--gray);
            }
            
            @keyframes pulse {
                0% { background-color: #fff4f4; }
                50% { background-color: #ffd6d6; }
                100% { background-color: #fff4f4; }
            }
            
            @media (max-width: 1478px){
                .content-area {
            
                    width: 100%;
            
                }
            }
            
            @media (max-width: 768px) {
                .main-content {
                    grid-template-columns: 1fr;
                    left: 2%;
                }
                
                .summary-cards {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .content-area {
            
                    width: 76%;
            
                }
                
                header{ 
                    width: 107vw;
                }
                
                .sidebar {
                    
                    width: 76%;
                }
                
                .filters {
                    flex-direction: column;
                }
                
                .export-btn {
                    margin-left: 0;
                }
                
                .issue-item {
                    flex-direction: column;
                }
                
                .issue-icon {
                    margin-right: 0;
                    margin-bottom: 15px;
                }
                
                .employee-header {
                    flex-direction: column;
                    text-align: center;
                }
            }
        </style>

        <style>
            .emp_history_table{
                position: relative;
                width: 100%;
                overflow-x: scroll;
                
            }

            .summary-card {
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.summary-card::after {
    content: '+';
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 18px;
    font-weight: bold;
    transition: transform 0.3s ease;
}

.summary-card.active::after {
    content: '−';
    transform: rotate(0deg);
}

.summary-details {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background-color: #f8f9fa;
    margin: 10px -15px -15px;
    border-radius: 0 0 8px 8px;
}

.summary-card.active .summary-details {
    max-height: 300px;
    padding: 15px;
}

.summary-details-content {
    padding: 10px 0;
}

.summary-details-content p {
    margin: 5px 0;
    font-size: 14px;
    color: #555;
}

.summary-details-content ul {
    margin: 5px 0;
    padding-left: 20px;
}

.summary-details-content li {
    margin: 3px 0;
    font-size: 13px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
    padding-bottom: 5px;
    border-bottom: 1px solid #eee;
}

.detail-label {
    font-weight: 600;
    color: #555;
}

.detail-value {
    color: #2c3e50;
}

.highlight {
    color: #e74c3c;
    font-weight: 600;
}

.employee-list-mini {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 8px;
}

.employee-tag-mini {
    padding: 3px 8px;
    background: #e1ebff;
    border-radius: 12px;
    font-size: 11px;
    color: var(--secondary);
}


/* Add these styles to your existing CSS */
.summary-details .issue-item {
    margin-bottom: 15px;
    padding: 12px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border-left: 3px solid var(--secondary);
}

.summary-details .issue-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    background-color: #e1ebff;
    color: var(--secondary);
    font-size: 16px;
}

.summary-details .issue-details h4 {
    color: var(--dark);
    margin-bottom: 5px;
    font-size: 14px;
}

.summary-details .issue-details p {
    color: var(--gray);
    margin-bottom: 8px;
    font-size: 13px;
}

.summary-details .employee-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.summary-details .employee-tag {
    padding: 4px 8px;
    background: #f0f5ff;
    border-radius: 12px;
    font-size: 11px;
    color: var(--secondary);
}

/* Ensure the summary details container has proper spacing */
.summary-details-content {
    padding: 15px;
}

/* Adjust the summary card active state for better appearance */
.summary-card.active {
    border-left: 4px solid var(--secondary);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}


/* Scroll to top button */
.scroll-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--secondary);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    z-index: 1000;
}

.scroll-to-top:hover {
    background: var(--primary);
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4);
}

.scroll-to-top:active {
    transform: translateY(-1px);
}
        </style>

        <div class="container">
            <div class="main-content">
                    <div class="sidebar">
                        <a href="index.php" class="nav-item" onclick="window.location.href='index.php'">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Admin Dashboard</span>
                        </a>
                        <a href="#" class="nav-item" onclick="window.location.href='see_employees.php'">
                            <i class="fas fa-users"></i>
                            <span>Employee</span>
                        </a>
                        <a href="create.php" class="nav-item" onclick="window.location.href='create.php'">
                            <i class="fas fa-user-plus"></i>
                            <span>Create Employee</span>
                        </a>
                        <a href="#" class="nav-item" onclick="window.location.href='schedule.php'">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Schedule</span>
                        </a>
                        <a href="report.php" class="nav-item" onclick="window.location.href='report.php'">
                            <i class="fas fa-chart-bar"></i>
                            <span>Applications</span>
                        </a>
                        <a href="adminentry.php" class="nav-item " onclick="window.location.href='adminentry.php'">
                            <i class="fas fa-house-user"></i>
                            <span>Work From Home</span>
                    </a>
                        <a href="employees_history.php" class="nav-item active" onclick="window.location.href='employees_history.php'">
                        <i class="fas fa-clock-rotate-left"></i>
                        <span>Employees History</span>
                        </a>
                        <a href="settings.php" class="nav-item" onclick="window.location.href='settings.php'">
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
                        <i class="fas fa-chart-bar"></i>
                        Employee Time Analysis
                    </h2>
                    
                    <div class="time-card">
                        <div class="current-date" id="current-date">Tuesday, June 11, 2023</div>
                        <div class="current-time" id="current-time"></div>
                    </div>
                    
                    <div class="filters">
                        <div class="filter-group">
                            <label for="time-period">Time Period</label>
                            <select id="time-period">
                                <option value="current">Current Month</option>
                                <option value="last">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        
                        <div class="filter-group" id="custom-range" style="display: none;">
                            <label for="start-date">Start Date</label>
                            <input type="date" id="start-date">
                        </div>
                        
                        <div class="filter-group" id="end-date-group" style="display: none;">
                            <label for="end-date">End Date</label>
                            <input type="date" id="end-date">
                        </div>
                        
                        <div class="filter-group">
                            <label for="department">Department</label>
                            <select id="department">
                                
                                <option value="all">All Departments</option>
                                <?php foreach($departments as $department): ?>
                                    <option value="<?php echo $department['name']; ?>"><?php echo $department['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="searchInput">Search Employee</label>
                            <input type="text" id="searchInput" placeholder="Employee name or ID">
                        </div>
                        

                    </div>


 <!-- Update the summary cards section in your HTML -->
<div class="summary-cards">
    <div class="summary-card" data-card="total-employees">
        <h3>Total Employees</h3>
        <p id="total-employees">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="total-employees-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="avg-hours">
        <h3>Average Working Hours</h3>
        <p id="avg-hours">0h</p>
        <div class="summary-details">
            <div class="summary-details-content" id="avg-hours-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="late-arrivals">
        <h3>Late Arrivals</h3>
        <p id="late-arrivals">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="late-arrivals-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="long-lunches">
        <h3>Long Lunches</h3>
        <p id="long-lunches">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="long-lunches-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="absent-days">
        <h3>Absent Days</h3>
        <p id="absent-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="absent-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="regularization-days">
        <h3>Regularization</h3>
        <p id="regularization-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="regularization-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="casual-leave-days">
        <h3>Casual Leave</h3>
        <p id="casual-leave-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="casual-leave-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="sick-leave-days">
        <h3>Sick Leave</h3>
        <p id="sick-leave-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="sick-leave-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="half-days">
        <h3>Half Days</h3>
        <p id="half-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="half-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="wfh-days">
        <h3>WFH Days</h3>
        <p id="wfh-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="wfh-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="holiday-days">
        <h3>Holidays</h3>
        <p id="holiday-days">0</p>
        <div class="summary-details">
            <div class="summary-details-content" id="holiday-days-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <div class="summary-card" data-card="see-more">
        <h3><a href="#employee-summary-name">See More Details</a></h3>
        <div class="summary-details">
            <div class="summary-details-content" id="see-more-details">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>
                    <div class="pagination" id="pagination" style="display: none;">
                        <button class="page-btn" id="first-page">First</button>
                        <button class="page-btn" id="prev-page">Previous</button>
                        <span id="page-info">Page 1 of 1</span>
                        <button class="page-btn" id="next-page">Next</button>
                        <button class="page-btn" id="last-page">Last</button>
                    </div>
                    <!-- Employee Header -->
                    <div class="employee-header" id="employee-header" style="display: none;">
                        <div class="employee-avatar" id="employee-avatar">JD</div>
                        <div class="employee-info">
                            <h3 id="employee-name">John Doe</h3>
                            <p id="employee-details">ID: stzk-102 | Department: Development | Position: Developer</p>
                        </div>
                    </div>
                    
                    <div class="emp_history_table">
                        <table class="indextable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Punch In</th>
                                    <th>Punch Out</th>
                                    <th>Lunch Duration</th>
                                    <th>Net Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            
                            <tbody id="Tbody">
                                <tr>
                                    <td colspan="6" style="text-align: center;">Select filters and click "Load Data" to view time analysis</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="analysis-summary">
                        <h3 class="section-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Monthly Summary for <span id="employee-summary-name">Selected Employee</span>
                        </h3>
                        
                        <div class="issues-list" id="issues-list">
                            <!-- Existing issue items -->
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Late Arrivals</h4>
                                    <p id="late-count">0 late arrivals this month</p>
                                    <div class="employee-list" id="late-dates">
                                        <span class="employee-tag">No late arrivals</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Long Lunch Breaks</h4>
                                    <p id="long-lunch-count">0 long lunch breaks this month</p>
                                    <div class="employee-list" id="long-lunch-dates">
                                        <span class="employee-tag">No long lunch breaks</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-business-time"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Insufficient Working Hours</h4>
                                    <p id="insufficient-hours-count">0 days with insufficient hours this month</p>
                                    <div class="employee-list" id="insufficient-hours-dates">
                                        <span class="employee-tag">No insufficient hours</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- New summary items -->
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Absent Days</h4>
                                    <p id="absent-count">0 absent days this month</p>
                                    <div class="employee-list" id="absent-dates">
                                        <span class="employee-tag">No absent days</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Regularization Days</h4>
                                    <p id="regularization-count">0 regularization days this month</p>
                                    <div class="employee-list" id="regularization-dates">
                                        <span class="employee-tag">No regularization days</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-umbrella-beach"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Casual Leave</h4>
                                    <p id="casual-leave-count">0 casual leave days this month</p>
                                    <div class="employee-list" id="casual-leave-dates">
                                        <span class="employee-tag">No casual leave</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-procedures"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Sick Leave</h4>
                                    <p id="sick-leave-count">0 sick leave days this month</p>
                                    <div class="employee-list" id="sick-leave-dates">
                                        <span class="employee-tag">No sick leave</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-clock-half"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Half Days</h4>
                                    <p id="half-day-count">0 half days this month</p>
                                    <div class="employee-list" id="half-day-dates">
                                        <span class="employee-tag">No half days</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-laptop-house"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Work From Home</h4>
                                    <p id="wfh-count">0 WFH days this month</p>
                                    <div class="employee-list" id="wfh-dates">
                                        <span class="employee-tag">No WFH days</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="issue-item">
                                <div class="issue-icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div class="issue-details">
                                    <h4>Holidays</h4>
                                    <p id="holiday-count">0 holidays this month</p>
                                    <div class="employee-list" id="holiday-dates">
                                        <span class="employee-tag">No holidays</span>
                                    </div>
                                </div>
                            </div>
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

<!-- Scroll to top button -->
        <button class="scroll-to-top" id="scrollToTop">
            <i class="fas fa-chevron-up"></i>
        </button>


        <script>
            $(document).ready(function(){   
                // Update current date and time
                // function updateDateTime() {
                //     const now = new Date();
                //     const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                //     $('#current-date').text(now.toLocaleDateString('en-US', options));
                //     $('#current-time').text(now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
                // }
                
                // updateDateTime();
                // setInterval(updateDateTime, 000);

                // Handle time period selection
                const timePeriodSelect = $('#time-period');
                const customRange = $('#custom-range');
                const endDateGroup = $('#end-date-group');
                
                timePeriodSelect.on('change', function() {
                    if (this.value === 'custom') {
                        customRange.show();
                        endDateGroup.show();
                    } else {
                        customRange.hide();
                        endDateGroup.hide();
                    }
                });
                
                // Set default dates for custom range
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
                
                $('#start-date').val(formatDate(firstDay));
                $('#end-date').val(formatDate(lastDay));
                
                // Format date as YYYY-MM-DD
                function formatDate(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }
                
                // Current page state
                let currentState = {
                    period: 'current',
                    startDate: '',
                    endDate: '',
                    department: 'all',
                    search: '',
                    currentPage: 1,
                    totalPages: 1,
                    currentEmployee: null,
                    employeeData: []
                };
                
                // Load data when filters change
                $('#time-period, #start-date, #end-date, #department').on('change', function() {
                    currentState.currentPage = 1;
                    loadTimeAnalysisData();
                });
                
                $(document).on('input', "#searchInput", function(){
                    currentState.currentPage = 1;
                    localStorage.setItem("searchInput", $(this).val());
                    loadTimeAnalysisData();
                });
                // Load data when page loads
                loadTimeAnalysisData();
                
                // Load time analysis data
        function loadTimeAnalysisData() {
        currentState.period = $('#time-period').val();
        currentState.startDate = $('#start-date').val();
        currentState.endDate = $('#end-date').val();
        currentState.department = $('#department').val();
        currentState.search = $('#searchInput').val();

        if(currentState.search == "" || currentState.search == null){
            currentState.search = localStorage.getItem("searchInput");
            $('#searchInput').val(currentState.search);
        }
        
        // Show loading state
        $('#Tbody').html('<tr><td colspan="6" style="text-align: center;">Loading data...</td></tr>');
        $('#employee-header').hide();
        $('#pagination').hide();
        
        // console.log('Sending request with data:', {
        //     period: currentState.period,
        //     startDate: currentState.startDate,
        //     endDate: currentState.endDate,
        //     department: currentState.department,
        //     search: currentState.search,
        //     page: currentState.currentPage,
        //     limit: 1
        // });
        
        // Make AJAX request to backend (remove includeHolidays parameter for now)
        $.ajax({
            url: 'time_management/time_analysis_backend.php',
            type: 'POST',
            dataType: 'json',
            data: {
                info: 'getTimeAnalysis',
                period: currentState.period,
                startDate: currentState.startDate,
                endDate: currentState.endDate,
                department: currentState.department,
                search: currentState.search,
                page: currentState.currentPage,
                limit: 1
            },
            success: function(response) {
                // console.log('Full response received:', response);
                
                if (response.success) {
                    // console.log('Employee data structure:', response.employee_data);
                    if (response.employee_data && response.employee_data.length > 0) {
                        // console.log('First employee days:', response.employee_data[0].days);
                    }
                    
                    currentState.employeeData = response.employee_data;
                    currentState.totalPages = response.pagination.total_pages;
                    renderTimeAnalysisData(response);
                } else {
                    $('#Tbody').html('<tr><td colspan="6" style="text-align: center; color: red;">Error: ' + response.error + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', error);
                console.log('Status:', status);
                console.log('Response Text:', xhr.responseText);
                $('#Tbody').html('<tr><td colspan="6" style="text-align: center; color: red;">Error loading data: ' + error + '</td></tr>');
            }
        });
    }
        
        // Render time analysis data to table
        function renderTimeAnalysisData(data) {
        // console.log('Rendering data:', data);
        
        // Safely update summary cards
        $('#total-employees').text(data.summary_stats?.total_employees || 0);
        $('#avg-hours').text((data.summary_stats?.avg_working_hours || '0') + 'h');
        $('#late-arrivals').text(data.summary_stats?.late_arrivals || 0);
        $('#long-lunches').text(data.summary_stats?.long_lunches || 0);
        $('#absent-days').text(data.summary_stats?.absent_days || 0);
        $('#regularization-days').text(data.summary_stats?.regularization_days || 0);
        $('#casual-leave-days').text(data.summary_stats?.casual_leave_days || 0);
        $('#sick-leave-days').text(data.summary_stats?.sick_leave_days || 0);
        $('#half-days').text(data.summary_stats?.half_days || 0);
        $('#wfh-days').text(data.summary_stats?.wfh_days || 0);
        $('#holiday-days').text(data.summary_stats?.holiday_days || 0);
        
        // Render employee data
        let html = '';
        
        if (!data.employee_data || data.employee_data.length === 0) {
            html = '<tr><td colspan="6" style="text-align: center;">No data available for the selected filters</td></tr>';
            $('#employee-header').hide();
            $('#pagination').hide();
        } else {
            const employee = data.employee_data[0];
            currentState.currentEmployee = employee;
            
            // Show employee header
            $('#employee-avatar').text(employee.name ? employee.name.split(' ').map(n => n[0]).join('').toUpperCase() : '?');
            $('#employee-name').text(employee.name || 'Unknown Employee');
            $('#employee-details').text(`ID: ${employee.emp_id || 'N/A'} | Department: ${employee.department || 'N/A'} | Position: ${employee.position || 'N/A'}`);
            $('#employee-header').show();
            
            // Update employee name in summary
            $('#employee-summary-name').text(employee.name || 'Selected Employee');
            
            // Add a row for each day
            let lateCount = 0;
            let longLunchCount = 0;
            let insufficientHoursCount = 0;
            let absentCount = 0;
            let regularizationCount = 0;
            let casualLeaveCount = 0;
            let sickLeaveCount = 0;
            let halfDayCount = 0;
            let wfhCount = 0;
            let holidayCount = 0;
            let lateDates = [];
            let longLunchDates = [];
            let insufficientHoursDates = [];
            let absentDates = [];
            let regularizationDates = [];
            let casualLeaveDates = [];
            let sickLeaveDates = [];
            let halfDayDates = [];
            let wfhDates = [];
            let holidayDates = [];
            
            if (employee.days) {
                for (const date in employee.days) {
                    const day = employee.days[date];
                    // console.log('Processing day:', date, day);
                    
                    const hasIssues = day.status === 'Late' || day.long_lunch || day.insufficient_hours;
                    
                    // Count issues for summary (excluding holidays)
                    if (day.status === 'Late') {
                        lateCount++;
                        lateDates.push(day.date || date);
                    }
                    if (day.long_lunch) {
                        longLunchCount++;
                        longLunchDates.push(day.date || date);
                    }
                    if (day.insufficient_hours) {
                        insufficientHoursCount++;
                        insufficientHoursDates.push(`${day.date || date} (${day.net_hours || '0'}/${(day.expected_hours || 0).toFixed(1)}h)`);
                    }
                    
                    // Determine row class based on status
                    let rowClass = '';
                    if (day.status === 'Holiday') {
                        rowClass = 'holiday-row';
                    } else if (hasIssues) {
                        rowClass = 'issue-row';
                    }

                    // Count different status types
                    switch (day.status) {
                        case 'Absent':
                            absentCount++;
                            absentDates.push(day.date || date);
                            break;
                        case 'Regularization':
                            regularizationCount++;
                            regularizationDates.push(day.date || date);
                            break;
                        case 'Casual Leave':
                            casualLeaveCount++;
                            casualLeaveDates.push(day.date || date);
                            break;
                        case 'Sick Leave':
                            sickLeaveCount++;
                            sickLeaveDates.push(day.date || date);
                            break;
                        case 'Half Day':
                            halfDayCount++;
                            halfDayDates.push(day.date || date);
                            break;
                        case 'WFH':
                            wfhCount++;
                            wfhDates.push(day.date || date);
                            break;
                        case 'Holiday':
                            holidayCount++;
                            holidayDates.push(`${day.date || date} (${day.holiday_reason || 'Holiday'})`);
                            break;
                    }
                    
                    html += `
                        <tr class="${rowClass}">
                            <td data-id="${employee.emp_id}" class="employee-date" style="color: #495af2; cursor:pointer;">${day.date || date}</td>
                            <td>${day.punch_in || '-'}</td>
                            <td>${day.punch_out || '-'}</td>
                            <td>${day.lunch_duration || '-'}</td>
                            <td>${day.net_hours || '-'}</td>
                            <td>
                    `;
                    
                    // Add status badges with safe fallbacks
                    if (day.status === 'Holiday') {
                        html += `<span class="status-badge status-Holiday">Holiday</span>`;
                    } else if (day.status === 'Late') {
                        html += `<span class="status-badge status-Late">Late</span>`;
                    } else if (day.status === 'Present') {
                        html += `<span class="status-badge status-Present">Present</span>`;
                    } else if (day.status === 'Absent') {
                        html += `<span class="status-badge status-Absent">Absent</span>`;
                    } else if (day.status === 'Regularization') {
                        html += `<span class="status-badge status-regularization">Regularization </span>`;
                    } else if (day.status === 'WFH') {
                        html += `<span class="status-badge status-WFH">WFH </span>`;
                    } else if (day.is_half_day) {
                        html += `<span class="status-badge status-HalfDay">Half Day (${day.expected_hours || 0}h)</span>`;
                    } else {
                        html += `<span class="status-badge">${day.status || 'Unknown'}</span>`;
                    }
                    
                    if (day.long_lunch) {
                        html += `<span class="status-badge status-Late">Long Lunch</span>`;
                    }
                    
                    if (day.insufficient_hours) {
                        html += `<span class="status-badge status-Late">Short Hours</span>`;
                    }
                    
                    html += `</td></tr>`;
                }
            } else {
                html = '<tr><td colspan="6" style="text-align: center;">No day data available for this employee</td></tr>';
            }
            
            // Update issues summary
            $('#late-count').text(`${lateCount} late arrival${lateCount !== 1 ? 's' : ''} this month`);
            $('#long-lunch-count').text(`${longLunchCount} long lunch break${longLunchCount !== 1 ? 's' : ''} this month`);
            $('#insufficient-hours-count').text(`${insufficientHoursCount} day${insufficientHoursCount !== 1 ? 's' : ''} with insufficient hours this month`);
            $('#absent-count').text(`${absentCount} absent day${absentCount !== 1 ? 's' : ''} this month`);
            $('#regularization-count').text(`${regularizationCount} regularization day${regularizationCount !== 1 ? 's' : ''} this month`);
            $('#casual-leave-count').text(`${casualLeaveCount} casual leave day${casualLeaveCount !== 1 ? 's' : ''} this month`);
            $('#sick-leave-count').text(`${sickLeaveCount} sick leave day${sickLeaveCount !== 1 ? 's' : ''} this month`);
            $('#half-day-count').text(`${halfDayCount} half day${halfDayCount !== 1 ? 's' : ''} this month`);
            $('#wfh-count').text(`${wfhCount} WFH day${wfhCount !== 1 ? 's' : ''} this month`);
            $('#holiday-count').text(`${holidayCount} holiday${holidayCount !== 1 ? 's' : ''} this month`);
            
            // Update dates lists
            let lateHtml = '';
            if (lateDates.length === 0) {
                lateHtml = '<span class="employee-tag">No late arrivals</span>';
            } else {
                lateDates.forEach(date => {
                    lateHtml += `<span class="employee-tag">${date}</span>`;
                });
            }
            $('#late-dates').html(lateHtml);
            
            let longLunchHtml = '';
            if (longLunchDates.length === 0) {
                longLunchHtml = '<span class="employee-tag">No long lunch breaks</span>';
            } else {
                longLunchDates.forEach(date => {
                    longLunchHtml += `<span class="employee-tag">${date}</span>`;
                });
            }
            
            $('#long-lunch-dates').html(longLunchHtml);
            
            let insufficientHoursHtml = '';
            if (insufficientHoursDates.length === 0) {
                insufficientHoursHtml = '<span class="employee-tag">No insufficient hours</span>';
            } else {
                insufficientHoursDates.forEach(date => {
                    insufficientHoursHtml += `<span class="employee-tag">${date}</span>`;
                });
            }
            $('#insufficient-hours-dates').html(insufficientHoursHtml);
            
            // Show pagination if there are multiple pages
            if (currentState.totalPages > 1) {
                $('#pagination').show();
                updatePagination();
            } else {
                $('#pagination').hide();
            }

            // Update the dates lists for new types
            updateDatesList('#absent-dates', absentDates, 'No absent days');
            updateDatesList('#regularization-dates', regularizationDates, 'No regularization days');
            updateDatesList('#casual-leave-dates', casualLeaveDates, 'No casual leave');
            updateDatesList('#sick-leave-dates', sickLeaveDates, 'No sick leave');
            updateDatesList('#half-day-dates', halfDayDates, 'No half days');
            updateDatesList('#wfh-dates', wfhDates, 'No WFH days');
            updateDatesList('#holiday-dates', holidayDates, 'No holidays');

            // Helper function to update dates lists
            function updateDatesList(selector, datesArray, emptyMessage) {
                let html = '';
                if (datesArray.length === 0) {
                    html = `<span class="employee-tag">${emptyMessage}</span>`;
                } else {
                    datesArray.forEach(date => {
                        html += `<span class="employee-tag">${date}</span>`;
                    });
                }
                $(selector).html(html);
            }
        }
        
        $('#Tbody').html(html);
    }
                // Update pagination
                function updatePagination() {
                    $('#page-info').text(`Page ${currentState.currentPage} of ${currentState.totalPages}`);
                    
                    // Enable/disable buttons
                    $('#first-page, #prev-page').prop('disabled', currentState.currentPage === 1);
                    $('#next-page, #last-page').prop('disabled', currentState.currentPage === currentState.totalPages);
                }
                
                // Handle pagination
                $('#first-page').on('click', function() {
                    if (currentState.currentPage > 1) {
                        currentState.currentPage = 1;
                        loadTimeAnalysisData();
                    }
                });
                
                $('#prev-page').on('click', function() {
                    if (currentState.currentPage > 1) {
                        currentState.currentPage--;
                        loadTimeAnalysisData();
                    }
                });
                
                $('#next-page').on('click', function() {
                    if (currentState.currentPage < currentState.totalPages) {
                        currentState.currentPage++;
                        loadTimeAnalysisData();
                    }
                });
                
                $('#last-page').on('click', function() {
                    if (currentState.currentPage < currentState.totalPages) {
                        currentState.currentPage = currentState.totalPages;
                        loadTimeAnalysisData();
                    }
                });
                
                // Export button handler
                $('#export-btn').on('click', function() {
                    if (!currentState.currentEmployee) {
                        Swal.fire({
                            title: 'Export Report',
                            text: 'Please select an employee first.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    Swal.fire({
                        title: 'Export Report',
                        text: `Export data for ${currentState.currentEmployee.name}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Export',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // In a real implementation, this would generate a CSV or PDF
                            Swal.fire({
                                title: 'Export Started',
                                text: 'Your report is being generated. You will receive a notification when it is ready.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            });
        </script>

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
                            // $(".notification-conten
                            // t-head-div").removeClass("notification-item unread");

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
            
            
            $.ajax({
                url: "leave_management/notification.php",
                method: "POST",
                dataType: "json",
                data: {click:click, appid:appid},
                success: function(e){
                    if(e){

                        if(e.count <=0){

                            $(".notybell").css({"color":"white"});
                        }
                        else{
                            
                            $(".notybell").css({"color":"red"});
                        }
                        
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



        // On click of date button it will redirect to ByDayDetails page

        $(document).on("click", ".employee-date", function(){
            let empid = $(this).data("id");
            let date = $(this).text();

            let dateObj = new Date(date);

            let year = dateObj.getFullYear();
            let month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
            let day = String(dateObj.getDate()).padStart(2, '0');

            let formattedDate = `${year}-${month}-${day}`;

            // alert(formattedDate);

            $.ajax({
                url: "includes/session_create.php",
                method: "POST",
                data: {info: "index",userId:empid},
                success: function(e){
                    if(e == 0){
                        $.ajax({
                            url: "includes/session_create.php",
                            method: "POST",
                            data: {info: "createdate", date:date},
                            success: function(e){
                                if(e == 0){
                                    window.location.href = "ByDayDetails.php";
                                }
                                else{
                                    Swal.fire({
                                        title: "Something Went Wrong!",
                                        text: "Error While Redirecting to Employee Details Page",
                                        icon: "info",
                                        confirmButtonText: "OK"
                                    })
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

<script>
// Make currentState globally accessible
let currentState = {
    period: 'current',
    startDate: '',
    endDate: '',
    department: 'all',
    search: '',
    currentPage: 1,
    totalPages: 1,
    currentEmployee: null,
    employeeData: []
};

$(document).ready(function() {
    // Handle summary card clicks
    $('.summary-card').on('click', function(e) {
        // Don't trigger if clicking on a link inside the card
        if ($(e.target).is('a')) {
            return;
        }
        
        const $card = $(this);
        const cardType = $card.data('card');
        
        // If this card is already active, close it
        if ($card.hasClass('active')) {
            $card.removeClass('active');
            $card.find('.summary-details').css('max-height', '0');
        } else {
            // Close any other open cards
            $('.summary-card.active').removeClass('active');
            $('.summary-details').css('max-height', '0');
            
            // Populate dynamic data for this card
            populateCardDetails(cardType);
            
            // Open this card
            $card.addClass('active');
            const $details = $card.find('.summary-details');
            const scrollHeight = $details.prop('scrollHeight');
            $details.css('max-height', scrollHeight + 'px');
        }
    });
    
    // Close all cards when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.summary-card').length) {
            $('.summary-card.active').removeClass('active');
            $('.summary-details').css('max-height', '0');
        }
    });
});

// Function to populate dynamic data in card details
function populateCardDetails(cardType) {
    const employee = currentState.currentEmployee;
    let detailsHtml = '';
    
    // Helper function to safely get text content
    function getCardValue(selector) {
        const element = document.getElementById(selector);
        return element ? element.textContent : '0';
    }
    
    // Helper function to get numeric value from card
    function getCardNumericValue(selector) {
        const value = getCardValue(selector);
        // Extract numbers from strings
        const numericMatch = value.match(/\d+/);
        return numericMatch ? parseInt(numericMatch[0]) : 0;
    }
    
    // Helper function to create issue item structure
    function createIssueItem(title, countText, datesArray, iconClass) {
        let datesHtml = '';
        
        // Only show dates if we have them
        if (datesArray.length > 0) {
            // Show only first 5 dates to avoid overflow
            datesArray.slice(0, 5).forEach(date => {
                datesHtml += `<span class="employee-tag">${date}</span>`;
            });
            if (datesArray.length > 5) {
                datesHtml += `<span class="employee-tag">+${datesArray.length - 5} more</span>`;
            }
        }
        
        return `
            <div class="issue-item">
                <div class="issue-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="issue-details">
                    <h4>${title}</h4>
                    <p>${countText}</p>
                    ${datesHtml ? `<div class="employee-list">${datesHtml}</div>` : ''}
                </div>
            </div>
        `;
    }
    
    switch(cardType) {
        case 'total-employees':
            const totalEmployees = getCardValue('total-employees');
            const department = document.getElementById('department') ? document.getElementById('department').value : 'all';
            detailsHtml = `
                <div class="issue-item">
                    <div class="issue-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="issue-details">
                        <h4>Total Employees</h4>
                        <p>${totalEmployees} employees in selected period</p>
                        <div class="employee-list">
                            <span class="employee-tag">Department: ${department === 'all' ? 'All Departments' : department}</span>
                            <span class="employee-tag">Period: ${getCurrentPeriod()}</span>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'avg-hours':
            const avgHours = getCardValue('avg-hours');
            const workingDays = employee && employee.days ? Object.keys(employee.days).length : 0;
            detailsHtml = `
                <div class="issue-item">
                    <div class="issue-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="issue-details">
                        <h4>Average Working Hours</h4>
                        <p>${avgHours} average working hours</p>
                        <div class="employee-list">
                            <span class="employee-tag">Based on: ${workingDays} working days</span>
                            <span class="employee-tag">Employee: ${employee ? employee.name : 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'late-arrivals':
            const lateCount = getCardNumericValue('late-arrivals');
            const lateDates = employee ? getStatusDates(employee, 'Late') : [];
            const lateCountText = `${lateCount} late arrival${lateCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Late Arrivals', 
                lateCountText, 
                lateDates, 
                'fas fa-clock'
            );
            break;
            
        case 'long-lunches':
            const longLunchCount = getCardNumericValue('long-lunches');
            const longLunchDates = employee ? getLongLunchDates(employee) : [];
            const longLunchCountText = `${longLunchCount} long lunch break${longLunchCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Long Lunch Breaks', 
                longLunchCountText, 
                longLunchDates, 
                'fas fa-utensils'
            );
            break;
            
        case 'absent-days':
            const absentCount = getCardNumericValue('absent-days');
            const absentDates = employee ? getStatusDates(employee, 'Absent') : [];
            const absentCountText = `${absentCount} absent day${absentCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Absent Days', 
                absentCountText, 
                absentDates, 
                'fas fa-user-times'
            );
            break;
            
        case 'regularization-days':
            const regularizationCount = getCardNumericValue('regularization-days');
            const regularizationDates = employee ? getStatusDates(employee, 'Regularization') : [];
            const regularizationCountText = `${regularizationCount} regularization day${regularizationCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Regularization Days', 
                regularizationCountText, 
                regularizationDates, 
                'fas fa-file-alt'
            );
            break;
            
        case 'casual-leave-days':
            const casualLeaveCount = getCardNumericValue('casual-leave-days');
            const casualLeaveDates = employee ? getStatusDates(employee, 'Casual Leave') : [];
            const casualLeaveCountText = `${casualLeaveCount} casual leave day${casualLeaveCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Casual Leave', 
                casualLeaveCountText, 
                casualLeaveDates, 
                'fas fa-umbrella-beach'
            );
            break;
            
        case 'sick-leave-days':
            const sickLeaveCount = getCardNumericValue('sick-leave-days');
            const sickLeaveDates = employee ? getStatusDates(employee, 'Sick Leave') : [];
            const sickLeaveCountText = `${sickLeaveCount} sick leave day${sickLeaveCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Sick Leave', 
                sickLeaveCountText, 
                sickLeaveDates, 
                'fas fa-procedures'
            );
            break;
            
        case 'half-days':
            const halfDaysCount = getCardNumericValue('half-days');
            const halfDayDates = employee ? getHalfDayDates(employee) : [];
            const halfDayCountText = `${halfDaysCount} half day${halfDaysCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Half Days', 
                halfDayCountText, 
                halfDayDates, 
                'fas fa-clock-half'
            );
            break;
            
        case 'wfh-days':
            const wfhCount = getCardNumericValue('wfh-days');
            const wfhDates = employee ? getStatusDates(employee, 'WFH') : [];
            const wfhCountText = `${wfhCount} WFH day${wfhCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Work From Home', 
                wfhCountText, 
                wfhDates, 
                'fas fa-laptop-house'
            );
            break;
            
        case 'holiday-days':
            const holidayCount = getCardNumericValue('holiday-days');
            const holidayDates = employee ? getStatusDates(employee, 'Holiday') : [];
            const holidayCountText = `${holidayCount} holiday${holidayCount !== 1 ? 's' : ''} this month`;
            detailsHtml = createIssueItem(
                'Holidays', 
                holidayCountText, 
                holidayDates, 
                'fas fa-gift'
            );
            break;
            
        case 'see-more':
            detailsHtml = `
                <div class="issue-item">
                    <div class="issue-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="issue-details">
                        <h4>Detailed Analysis</h4>
                        <p>View comprehensive employee time analysis</p>
                        <div class="employee-list">
                            <span class="employee-tag">Complete attendance history</span>
                            <span class="employee-tag">Detailed issue breakdown</span>
                            <span class="employee-tag">Monthly summaries</span>
                            <span class="employee-tag">Trend analysis</span>
                        </div>
                        <p style="margin-top: 10px; font-size: 13px; color: #666;">
                            Scroll down to the "Monthly Summary" section for comprehensive details.
                        </p>
                    </div>
                </div>
            `;
            break;
            
        default:
            detailsHtml = `
                <div class="issue-item">
                    <div class="issue-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="issue-details">
                        <h4>Information</h4>
                        <p>No detailed information available for this metric</p>
                        <div class="employee-list">
                            <span class="employee-tag">Select an employee and period</span>
                        </div>
                    </div>
                </div>
            `;
            break;
    }
    
    const detailsElement = document.getElementById(`${cardType}-details`);
    if (detailsElement) {
        detailsElement.innerHTML = detailsHtml;
    }
}


// Helper function to get dates for a specific status
function getStatusDates(employee, status) {
    const dates = [];
    if (employee && employee.days) {
        for (const date in employee.days) {
            const day = employee.days[date];
            if (day.status === status) {
                dates.push(day.date || date);
            }
        }
    }
    return dates;
}

// Helper function to get dates with long lunches
function getLongLunchDates(employee) {
    const dates = [];
    if (employee && employee.days) {
        for (const date in employee.days) {
            const day = employee.days[date];
            if (day.long_lunch === true) {
                dates.push(day.date || date);
            }
        }
    }
    return dates;
}

// Helper function to get half day dates
function getHalfDayDates(employee) {
    const dates = [];
    if (employee && employee.days) {
        for (const date in employee.days) {
            const day = employee.days[date];
            if (day.status === 'Half Day' || day.is_half_day === true) {
                dates.push(day.date || date);
            }
        }
    }
    return dates;
}

// Helper function to get current period description
function getCurrentPeriod() {
    const periodElement = document.getElementById('time-period');
    if (!periodElement) return 'Current Month';
    
    const period = periodElement.value;
    if (period === 'current') {
        return 'Current Month';
    } else if (period === 'last') {
        return 'Last Month';
    } else {
        const startDate = document.getElementById('start-date')?.value || '';
        const endDate = document.getElementById('end-date')?.value || '';
        return `Custom: ${startDate} to ${endDate}`;
    }
}


$(document).ready(function() {
            const scrollButton = $('#scrollToTop');
            
            // Show/hide scroll button based on scroll position
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    scrollButton.fadeIn(300);
                } else {
                    scrollButton.fadeOut(300);
                }
            });
            
            // Scroll to top when button is clicked
            scrollButton.click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
        });

</script>




