<?php
// Initialize variables to prevent undefined variable errors
$title = "Reports";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0043a8;
            --secondary-color: #0099ff;
            --accent-color: #00c8ff;
            --bg-light: #f8f9fa;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --transition-speed: 0.3s;
        }
        
        body {
            min-height: 100vh;
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), #003080);
            padding: 1rem;
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all var(--transition-speed);
        }
        
        .sidebar-logo {
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-logo img {
            width: 50px;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }
        
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all var(--transition-speed);
            font-weight: 500;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-menu i {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin var(--transition-speed);
        }
        
        .report-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
            transition: transform var(--transition-speed);
        }
        
        .report-card:hover {
            transform: translateY(-5px);
        }
        
        .report-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 1rem;
            font-size: 1.8rem;
            background-color: rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
        }
        
        .report-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .date-range {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h4>Water Sports Rental</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php?page=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="index.php?page=jet-skis"><i class="bi bi-water"></i> Jet Skis</a></li>
            <li><a href="index.php?page=boats"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
            <li><a href="index.php?page=clients"><i class="bi bi-people"></i> Clients</a></li>
            <li><a href="index.php?page=reservations"><i class="bi bi-calendar-check"></i> Reservations</a></li>
            <li><a href="index.php?page=invoices"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports" class="active"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1 class="mb-4">Reports</h1>
        
        <div class="date-range">
            <h5 class="mb-3">Select Date Range</h5>
            <form class="row g-3" id="reportDateForm">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" id="report_type" name="report_type" value="">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" onclick="validateDateRange()">Apply Date Range</button>
                </div>
            </form>
        </div>
        
        <div class="row">
            <div class="col-md-6 col-xl-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h3 class="report-title">Revenue Report</h3>
                    <p class="text-muted mb-3">View revenue statistics by day, week, or month</p>
                    <a href="index.php?page=reports/generate&type=revenue&start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary generate-report" data-report-type="revenue">Generate Report</a>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-water"></i>
                    </div>
                    <h3 class="report-title">Equipment Usage</h3>
                    <p class="text-muted mb-3">See which equipment is most frequently rented</p>
                    <a href="index.php?page=reports/generate&type=equipment&start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary generate-report" data-report-type="equipment">Generate Report</a>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="report-title">Client Activity</h3>
                    <p class="text-muted mb-3">Track client activity and spending patterns</p>
                    <a href="index.php?page=reports/generate&type=clients&start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary generate-report" data-report-type="clients">Generate Report</a>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3 class="report-title">Reservation Trends</h3>
                    <p class="text-muted mb-3">Analyze booking patterns and seasonal trends</p>
                    <a href="index.php?page=reports/generate&type=reservation-trends&start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary generate-report" data-report-type="reservation-trends">Generate Report</a>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h3 class="report-title">Maintenance Report</h3>
                    <p class="text-muted mb-3">Track maintenance history and upcoming schedule</p>
                    <a href="index.php?page=reports/generate&type=maintenance&start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary generate-report" data-report-type="maintenance">Generate Report</a>
                </div>
            </div>
            
            <div class="col-md-6 col-xl-4">
                <div class="report-card">
                    <div class="report-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="report-title">Financial Summary</h3>
                    <p class="text-muted mb-3">Complete financial overview with revenue and expenses</p>
                    <a href="index.php?page=reports/generate&type=financial&start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-primary generate-report" data-report-type="financial">Generate Report</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update all report links with the selected date range
        function validateDateRange() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            // Validate dates
            if (!startDate || !endDate) {
                alert("Please select both start and end dates");
                return;
            }
            
            if (new Date(startDate) > new Date(endDate)) {
                alert("Start date cannot be after end date");
                return;
            }
            
            // Update all report links with the new date range
            const reportLinks = document.querySelectorAll('.generate-report');
            reportLinks.forEach(link => {
                const reportType = link.getAttribute('data-report-type');
                link.href = `index.php?page=reports/generate&type=${reportType}&start_date=${startDate}&end_date=${endDate}`;
            });
            
            alert("Date range applied successfully. You can now generate reports for the selected period.");
        }
        
        // Event handler for generate report buttons
        document.addEventListener('DOMContentLoaded', function() {
            const reportLinks = document.querySelectorAll('.generate-report');
            reportLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const reportType = this.getAttribute('data-report-type');
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    
                    // Update href dynamically
                    this.href = `index.php?page=reports/generate&type=${reportType}&start_date=${startDate}&end_date=${endDate}`;
                });
            });
        });
    </script>
</body>
</html> 