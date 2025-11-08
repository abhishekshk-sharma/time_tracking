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
        
        // Count current employees
        $stmt = $pdo->query("SELECT count(*) as total FROM employees WHERE end_date IS NULL");
        $currentCount = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentCount = $currentCount['total'];
        
        // Count previous employees
        $stmt = $pdo->query("SELECT count(*) as total FROM employees WHERE end_date IS NOT NULL");
        $previousCount = $stmt->fetch(PDO::FETCH_ASSOC);
        $previousCount = $previousCount['total'];

        $getdepartments = $pdo->query("SELECT DISTINCT department FROM employees");
        $departments = $getdepartments->fetchAll(PDO::FETCH_COLUMN);
    ?>
    
    <?php require_once "includes/header.php"; ?>

    <style>
        .content-area {
            position: relative;
            width: 100%;
            overflow-x: scroll;
        }
        .content-area::-webkit-scrollbar {
            display: none;
        }
        .employee-table {
            position: relative;
            width: 98%;
            overflow-x: scroll;
        }
        .container {
            position: flex;
            margin: 20px auto;            
        }
        .pagination-nav {
            position: relative; 
            margin: 15px 40%;
        }
        
        @media (max-width: 768px) {
            .main-content {
                left: 3%;
            }
            .pagination-nav {
                margin: 90px 25%;
            }
        }
        
        .employee-row:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        .username-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .username-link:hover {
            text-decoration: underline;
        }
        .status-active {
            background-color: #e7f6e9;
            color: #2ecc71;
        }
        .status-inactive {
            background-color: #fbebec;
            color: #e74c3c;
        }
        .employee-type-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .current-employee {
            background-color: #e7f6e9;
            color: #2ecc71;
        }
        .previous-employee {
            background-color: #fef5e7;
            color: #e67e22;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
    </style>

<body>
    
    
    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <a href="#" class="nav-item" onclick="window.location.href = 'index.php'">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Dashboard</span>
                </a>
                <a href="#" class="nav-item active" onclick="window.location.href = 'see_employees.php'">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
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
                <a href="#" class="nav-item" onclick="window.location.href='adminentry.php'">
                    <i class="fas fa-house-user"></i>
                    <span>Work From Home</span>
                </a>
                <a href="#" class="nav-item" onclick="window.location.href='employees_history.php'">
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
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-users"></i>
                            Employee Management
                        </h2>
                        <div class="employee-stats">
                            <span class="badge bg-success">Current: <?php echo $currentCount; ?></span>
                            <span class="badge bg-warning text-dark">Previous: <?php echo $previousCount; ?></span>
                        </div>
                    </div>
                    
                    <div class="filters">
                        <div class="filter-group">
                            <label for="employeeType">Employee Type</label>
                            <select id="employeeType">
                                <option value="current">Current Employees</option>
                                <option value="previous">Previous Employees</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="searchInput">Search</label>
                            <input type="text" id="searchInput" placeholder="Employee name or ID">
                        </div>
                        <div class="filter-group">
                            <label for="departmentFilter">Department</label>
                            <select id="departmentFilter">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo htmlspecialchars($department); ?>"><?php echo htmlspecialchars($department); ?></option>
                                <?php endforeach; ?>
                                
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="statusFilter">Status</label>
                            <select id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="clearFilters">Clear Filters</label>
                            <input type="button" class="clear-btn" id="clearFilters" value="Clear">
                        </div>
                    </div>
                    
                    <div class="employee-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    <th>Hire Date</th>
                                    <th>End Date</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTbody">
                                <!-- Employee data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <nav aria-label="..." class="pagination-nav">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" tabindex="-1" id="prev" style="cursor: pointer;">Previous</a>
                            </li>
                            <li class="page-item"><a class="page-link" id="sel_limit"><p id="setlimit">10</p></a></li>
                            <li class="page-item">
                                <a class="page-link" id="next" style="cursor: pointer;">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            let row = <?php echo $currentCount; ?>;
            let limit = 10;
            let totalPages = Math.ceil(row / limit);
            let currentPage = 1;
            let offset = (currentPage - 1) * limit;
            let searchInput = "";
            let departmentFilter = "";
            let statusFilter = "";
            let employeeType = "current"; // Default to current employees
            
            // Initialize page with current employees
            getEmployees(currentPage, limit, searchInput, departmentFilter, statusFilter, employeeType);
            
            // Filter functions
            function getFilters() {
                searchInput = $("#searchInput").val() || "";
                departmentFilter = $("#departmentFilter").val() || "";
                statusFilter = $("#statusFilter").val() || "";
                employeeType = $("#employeeType").val() || "current";
            }
            
            $("#clearFilters").click(function() {
                $("#searchInput").val("");
                $("#departmentFilter").val("");
                $("#statusFilter").val("");
                $("#employeeType").val("current");
                getFilters();
                currentPage = 1;
                getEmployees(currentPage, limit, searchInput, departmentFilter, statusFilter, employeeType);
            });
            
            $(document).on("input change", "#searchInput, #departmentFilter, #statusFilter, #employeeType", function() {
                getFilters();
                currentPage = 1;
                getEmployees(currentPage, limit, searchInput, departmentFilter, statusFilter, employeeType);
            });
            
            // Pagination functions
            $("#sel_limit").click(function() {
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
                        $("#setlimit").html(selectedLimit);
                        limit = selectedLimit;
                        totalPages = Math.ceil(row / limit);
                        getFilters();
                        currentPage = 1;
                        getEmployees(currentPage, limit, searchInput, departmentFilter, statusFilter, employeeType);
                    }
                });
            });
            
            $("#prev").click(function(e) {
                e.preventDefault();
                getFilters();
                if (currentPage > 1) {
                    currentPage--;
                    getEmployees(currentPage, limit, searchInput, departmentFilter, statusFilter, employeeType);
                }
            });
            
            $("#next").click(function(e) {
                e.preventDefault();
                getFilters();
                if (currentPage < totalPages) {
                    currentPage++;
                    getEmployees(currentPage, limit, searchInput, departmentFilter, statusFilter, employeeType);
                }
            });
            
            // Main function to get employee data
            function getEmployees(currentPage = 1, limit = 10, searchInput = "", departmentFilter = "", statusFilter = "", employeeType = "current") {
                let info = "GetAllEmployees";
                let offset = (currentPage - 1) * limit;
                
                $.ajax({
                    url: "time_management/total.php", // Your common processing file
                    type: "POST",
                    dataType: 'json',
                    data: { 
                        info: info, 
                        page: currentPage, 
                        limit: limit, 
                        searchInput: searchInput, 
                        departmentFilter: departmentFilter,
                        statusFilter: statusFilter,
                        employeeType: employeeType
                    },
                    success: function(response) {
                        if (response) {
                            $("#employeeTbody").html(response.output);
                            row = response.row;
                            totalPages = Math.ceil(row / limit);
                            
                            // Update pagination buttons state
                            if (currentPage >= totalPages) {
                                $("#next").prop("disabled", true).css("opacity", 0.5);
                            } else {
                                $("#next").prop("disabled", false).css("opacity", 1);
                            }
                            
                            if (currentPage <= 1) {
                                $("#prev").prop("disabled", true).css("opacity", 0.5);
                            } else {
                                $("#prev").prop("disabled", false).css("opacity", 1);
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching employee data:", error);
                        $("#employeeTbody").html('<tr><td colspan="9" style="text-align:center; color: red;">Error loading employee data</td></tr>');
                    }
                });
            }
            
            // Redirect to user.php when clicking on username (only for current employees)
            $(document).on("click", ".username-link", function() {
                let empId = $(this).data('id');
                let employeeType = $("#employeeType").val();
                
                // Only allow redirection for current employees
                if (employeeType === "current") {
                    let info = "index";
                    
                    $.ajax({
                        url: "includes/session_create.php",
                        method: "POST",
                        data: { info: info, userId: empId },
                        success: function(response) {
                            if (response == 0) {
                                window.location.href = "user.php";
                            } else if (response == 1) {
                                alert("Please Try Again! Something Went Wrong.");
                            }
                        },
                        error: function() {
                            alert("Error redirecting to user profile.");
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Previous Employee',
                        text: 'This employee is no longer with the company. Viewing profile is not available.',
                        confirmButtonText: 'OK'
                    });
                }
            });
            
            // Session check function (from your existing code)
            function checksession() {
                var info = "check";
                $.ajax({
                    url: "includes/checksession.php",
                    method: "POST",
                    data: { info: info },
                    success: function(e) {
                        if (e == "expired") {
                            window.location.href = "../login.php";
                        }
                    }
                });
            }
            
            // Session management (from your existing code)
            $(document).ready(function() {
                checksession();
            });
            
            $(document).on("click touchstart", function() {
                checksession();
            });
            
            document.addEventListener("visibilitychange", function() {
                if (document.visibilityState === "visible") {
                    checksession();
                }
            });
        });
    </script>
</body>
</html>