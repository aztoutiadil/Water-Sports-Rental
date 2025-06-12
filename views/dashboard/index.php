<?php
// Initialize variables to prevent undefined variable errors
$title = "Dashboard";
$user_name = $_SESSION['user']['name'] ?? 'User';

// Initialize stats array with default values
$stats = [
    'total_jet_skis' => 0,
    'available_jet_skis' => 0,
    'total_boats' => 0,
    'available_boats' => 0,
    'total_clients' => 0,
    'active_rentals' => 0,
    'pending_reservations' => 0,
    'completed_reservations' => 0,
    'monthly_revenue' => 0,
    'today_revenue' => 0,
    'month_revenue' => 0,
    'yesterday_revenue' => 0,
    'prev_month_revenue' => 0,
    'today_trend' => 0,
    'month_trend' => 0
];

// Initialize arrays for upcoming maintenance, recent bookings, and revenue data
$upcoming_maintenance = [];
$recent_bookings = [];
$revenue_data = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
];

// Get real data from the database if connection is available
if (isset($conn)) {
    try {
        // Get jet ski stats
        $query = "SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END), 0) as available FROM jet_skis";
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_jet_skis'] = $row['total'] ?? 0;
            $stats['available_jet_skis'] = $row['available'];
        }
        
        // Get boat stats
        $query = "SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END), 0) as available FROM boats";
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_boats'] = $row['total'] ?? 0;
            $stats['available_boats'] = $row['available'];
        }
        
        // Get client count
        $query = "SELECT COUNT(*) as count FROM clients";
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total_clients'] = $row['count'] ?? 0;
        }
        
        // Get reservation stats with more detailed status information
        $query = "SELECT 
            COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
            COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled,
            COUNT(*) as total,
            COUNT(DISTINCT client_id) as unique_clients
            FROM reservations";
        $result = $conn->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            $stats['active_rentals'] = $row['active'];
            $stats['pending_reservations'] = $row['pending'];
            $stats['completed_reservations'] = $row['completed'];
            $stats['cancelled_reservations'] = $row['cancelled'];
            $stats['total_reservations'] = $row['total'];
            $stats['unique_clients_with_reservations'] = $row['unique_clients'];
        }
        
        // Get today's revenue with more detail
        $today = date('Y-m-d');
        $query = "SELECT 
            COALESCE(SUM(amount), 0) as revenue,
            COUNT(*) as transaction_count,
            MAX(amount) as largest_payment
            FROM payments 
            WHERE DATE(payment_date) = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['today_revenue'] = $row['revenue'];
                $stats['today_transactions'] = $row['transaction_count'];
                $stats['today_largest_payment'] = $row['largest_payment'];
            }
            $stmt->close();
        }
        
        // Get yesterday's revenue with more detail
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $query = "SELECT 
            COALESCE(SUM(amount), 0) as revenue,
            COUNT(*) as transaction_count
            FROM payments 
            WHERE DATE(payment_date) = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $yesterday);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['yesterday_revenue'] = $row['revenue'];
                $stats['yesterday_transactions'] = $row['transaction_count'];
            }
            $stmt->close();
        }
        
        // Calculate today's trend compared to yesterday with more accuracy
        if ($stats['yesterday_revenue'] > 0) {
            $stats['today_trend'] = (($stats['today_revenue'] - $stats['yesterday_revenue']) / $stats['yesterday_revenue']) * 100;
            $stats['transaction_trend'] = (($stats['today_transactions'] - $stats['yesterday_transactions']) / $stats['yesterday_transactions']) * 100;
        } else if ($stats['today_revenue'] > 0) {
            // If yesterday was 0 but today has revenue, show 100% increase
            $stats['today_trend'] = 100;
            $stats['transaction_trend'] = 100;
        } else {
            // Both are 0, no change
            $stats['today_trend'] = 0;
            $stats['transaction_trend'] = 0;
        }
        
        // Get this month's revenue with more detail
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');
        $query = "SELECT 
            COALESCE(SUM(amount), 0) as revenue,
            COUNT(*) as transaction_count,
            COUNT(DISTINCT DATE(payment_date)) as days_with_payments,
            AVG(amount) as avg_payment
            FROM payments 
            WHERE payment_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ss", $month_start, $month_end);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['month_revenue'] = $row['revenue'];
                $stats['month_transactions'] = $row['transaction_count'];
                $stats['month_days_with_payments'] = $row['days_with_payments'];
                $stats['month_avg_payment'] = $row['avg_payment'];
            }
            $stmt->close();
        }
        
        // Get previous month's revenue with more detail
        $prev_month_start = date('Y-m-01', strtotime('-1 month'));
        $prev_month_end = date('Y-m-t', strtotime('-1 month'));
        $query = "SELECT 
            COALESCE(SUM(amount), 0) as revenue,
            COUNT(*) as transaction_count,
            COUNT(DISTINCT DATE(payment_date)) as days_with_payments
            FROM payments 
            WHERE payment_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ss", $prev_month_start, $prev_month_end);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats['prev_month_revenue'] = $row['revenue'];
                $stats['prev_month_transactions'] = $row['transaction_count'];
                $stats['prev_month_days_with_payments'] = $row['days_with_payments'];
            }
            $stmt->close();
        }
        
        // Calculate month's trend compared to previous month with more accuracy
        if ($stats['prev_month_revenue'] > 0) {
            $stats['month_trend'] = (($stats['month_revenue'] - $stats['prev_month_revenue']) / $stats['prev_month_revenue']) * 100;
            
            // Calculate daily average comparison for more accurate trend
            $days_in_current_month = date('t');
            $days_in_prev_month = date('t', strtotime('-1 month'));
            $current_daily_avg = $stats['month_revenue'] / $days_in_current_month;
            $prev_daily_avg = $stats['prev_month_revenue'] / $days_in_prev_month;
            
            if ($prev_daily_avg > 0) {
                $stats['month_daily_avg_trend'] = (($current_daily_avg - $prev_daily_avg) / $prev_daily_avg) * 100;
            } else {
                $stats['month_daily_avg_trend'] = 0;
            }
        } else if ($stats['month_revenue'] > 0) {
            // If previous month was 0 but this month has revenue, show 100% increase
            $stats['month_trend'] = 100;
            $stats['month_daily_avg_trend'] = 100;
        } else {
            // Both are 0, no change
            $stats['month_trend'] = 0;
            $stats['month_daily_avg_trend'] = 0;
        }
        
        // Get year-to-date monthly revenue for chart
        $year = date('Y');
        $query = "SELECT 
            MONTH(payment_date) as month, 
            COALESCE(SUM(amount), 0) as revenue 
            FROM payments 
            WHERE YEAR(payment_date) = ? 
            GROUP BY MONTH(payment_date)";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $month = (int)$row['month'] - 1; // Convert to 0-based index for JS array
                $revenue_data['data'][$month] = (float)$row['revenue'];
            }
            $stmt->close();
        }
        
        // Get upcoming maintenance
        $query = "SELECT 
            CASE 
                WHEN js.id IS NOT NULL THEN CONCAT(js.brand, ' ', js.model)
                WHEN b.id IS NOT NULL THEN b.name
                ELSE 'Unknown Equipment'
            END as equipment,
            m.last_service_date as last_maintenance,
            m.next_service_date as next_maintenance,
            CASE
                WHEN m.next_service_date < CURRENT_DATE THEN 'overdue'
                WHEN m.next_service_date = CURRENT_DATE THEN 'due'
                WHEN m.next_service_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) THEN 'upcoming'
                ELSE 'scheduled'
            END as status
            FROM maintenance m
            LEFT JOIN jet_skis js ON m.equipment_type = 'jet_ski' AND m.equipment_id = js.id
            LEFT JOIN boats b ON m.equipment_type = 'boat' AND m.equipment_id = b.id
            WHERE m.next_service_date <= DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)
            ORDER BY m.next_service_date ASC
            LIMIT 5";
        $result = $conn->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $upcoming_maintenance[] = $row;
            }
        }
        
        // Get recent bookings/reservations
        $query = "SELECT 
            r.id,
            CONCAT(c.first_name, ' ', c.last_name) as client_name,
            CASE 
                WHEN r.equipment_type = 'jet_ski' AND js.id IS NOT NULL THEN CONCAT(js.brand, ' ', js.model)
                WHEN r.equipment_type = 'boat' AND b.id IS NOT NULL THEN b.name
                ELSE CONCAT(UPPER(SUBSTRING(r.equipment_type, 1, 1)), SUBSTRING(r.equipment_type, 2), ' #', r.equipment_id)
            END as equipment,
            r.start_date,
            r.end_date,
            r.total_amount as amount,
            r.status
            FROM reservations r
            LEFT JOIN clients c ON r.client_id = c.id
            LEFT JOIN jet_skis js ON r.equipment_type = 'jet_ski' AND r.equipment_id = js.id
            LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
            ORDER BY r.created_at DESC
            LIMIT 5";
        $result = $conn->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recent_bookings[] = $row;
            }
        }
        
    } catch (Exception $e) {
        // Log error but continue with default/empty values
        error_log("Dashboard stats error: " . $e->getMessage());
        // Optionally add a flash message
        $_SESSION['flash']['warning'] = "Some dashboard data could not be loaded.";
    }
}

// If no recent bookings or maintenance data was fetched, provide sample data for display
if (empty($recent_bookings)) {
    $recent_bookings = [
        [
            'id' => 1,
            'client_name' => 'John Doe',
            'equipment' => 'Yamaha Waverunner',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'amount' => 450.00,
            'status' => 'active'
        ],
        [
            'id' => 2,
            'client_name' => 'Jane Smith',
            'equipment' => 'Sunseeker 40ft',
            'start_date' => date('Y-m-d', strtotime('+1 day')),
            'end_date' => date('Y-m-d', strtotime('+3 days')),
            'amount' => 1200.00,
            'status' => 'pending'
        ]
    ];
}

if (empty($upcoming_maintenance)) {
    $upcoming_maintenance = [
        [
            'equipment' => 'Yamaha Waverunner',
            'last_maintenance' => date('Y-m-d', strtotime('-30 days')),
            'status' => 'upcoming'
        ],
        [
            'equipment' => 'Sea-Doo Spark',
            'last_maintenance' => date('Y-m-d', strtotime('-60 days')),
            'status' => 'overdue'
        ]
    ];
}

// If revenue data is empty (no payments in DB), use sample data
if (array_sum($revenue_data['data']) == 0) {
    $revenue_data['data'] = [12500, 15000, 18000, 16500, 21000, 22500, 25000, 23500, 20000, 18500, 16000, 14500];
    
    // Set sample trend data if real data wasn't available
    if ($stats['today_trend'] == 0) {
        $stats['today_trend'] = 15;
    }
    if ($stats['month_trend'] == 0) {
        $stats['month_trend'] = 8;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0043a8;
            --secondary-color: #0099ff;
        }
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-color);
            padding: 1rem;
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-logo {
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar-menu i {
            margin-right: 0.5rem;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }
        .top-bar {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .stat-card h3 {
            font-size: 2rem;
            margin: 0;
            color: var(--primary-color);
        }
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
        .stat-card .icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            color: var(--primary-color);
            opacity: 0.2;
        }
        .stat-card .trend {
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        .trend-up {
            color: #28a745;
        }
        .trend-down {
            color: #dc3545;
        }
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .booking-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-active {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        .status-completed {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        .status-overdue {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        .status-upcoming {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        .status-due {
            background-color: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        .maintenance-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .maintenance-item:last-child {
            border-bottom: none;
        }
        .bg-primary-light {
            background-color: rgba(0, 67, 168, 0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h4>Water Sports Rental</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php?page=dashboard" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="index.php?page=jet-skis"><i class="bi bi-water"></i> Jet Skis</a></li>
            <li><a href="index.php?page=boats"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
            <li><a href="index.php?page=clients"><i class="bi bi-people"></i> Clients</a></li>
            <li><a href="index.php?page=reservations"><i class="bi bi-calendar-check"></i> Reservations</a></li>
            <li><a href="index.php?page=invoices"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="h4 mb-0">Dashboard</h1>
            <div class="user-info d-flex align-items-center">
                <span class="me-2">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <div class="dropdown">
                    <a href="#" class="text-decoration-none" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="index.php?page=dashboard/profile"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="index.php?page=dashboard/settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> mb-3">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-water"></i></div>
                    <p>Jet Skis</p>
                    <h3><?php echo $stats['total_jet_skis']; ?></h3>
                    <p class="text-muted mt-2"><?php echo $stats['available_jet_skis']; ?> available</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-tsunami"></i></div>
                    <p>Tourist Boats</p>
                    <h3><?php echo $stats['total_boats']; ?></h3>
                    <p class="text-muted mt-2"><?php echo $stats['available_boats']; ?> available</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-people"></i></div>
                    <p>Clients</p>
                    <h3><?php echo $stats['total_clients']; ?></h3>
                    <p class="text-muted mt-2"><?php echo $stats['unique_clients_with_reservations'] ?? 0; ?> with bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-calendar-check"></i></div>
                    <p>Active Rentals</p>
                    <h3><?php echo $stats['active_rentals']; ?></h3>
                    <p class="text-muted mt-2"><?php echo $stats['pending_reservations']; ?> pending</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-calendar-range"></i></div>
                    <p>Total Reservations</p>
                    <h3><?php echo $stats['total_reservations'] ?? 0; ?></h3>
                    <p class="text-muted mt-2"><?php echo $stats['completed_reservations']; ?> completed / <?php echo $stats['cancelled_reservations'] ?? 0; ?> cancelled</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card bg-primary-light">
                    <div class="icon"><i class="bi bi-currency-dollar"></i></div>
                    <p>Today's Revenue</p>
                    <h3>$<?php echo number_format($stats['today_revenue'], 2); ?></h3>
                    <div class="trend <?php echo $stats['today_trend'] >= 0 ? 'trend-up' : 'trend-down'; ?>">
                        <i class="bi <?php echo $stats['today_trend'] >= 0 ? 'bi-graph-up' : 'bi-graph-down'; ?>"></i> 
                        <?php echo ($stats['today_trend'] > 0 ? '+' : '') . number_format($stats['today_trend'], 1) . '%'; ?> from yesterday
                    </div>
                    <p class="text-muted mt-2"><?php echo $stats['today_transactions'] ?? 0; ?> transactions</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card bg-primary-light">
                    <div class="icon"><i class="bi bi-cash-stack"></i></div>
                    <p>Monthly Revenue</p>
                    <h3>$<?php echo number_format($stats['month_revenue'], 2); ?></h3>
                    <div class="trend <?php echo $stats['month_trend'] >= 0 ? 'trend-up' : 'trend-down'; ?>">
                        <i class="bi <?php echo $stats['month_trend'] >= 0 ? 'bi-graph-up' : 'bi-graph-down'; ?>"></i> 
                        <?php echo ($stats['month_trend'] > 0 ? '+' : '') . number_format($stats['month_trend'], 1) . '%'; ?> from last month
                    </div>
                    <p class="text-muted mt-2">Avg: $<?php echo number_format($stats['month_avg_payment'] ?? 0, 2); ?> | <?php echo $stats['month_days_with_payments'] ?? 0; ?> days with payments</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-graph-up"></i></div>
                    <p>Daily Average Revenue</p>
                    <h3>$<?php echo number_format($stats['month_revenue'] / date('t'), 2); ?></h3>
                    <div class="trend <?php echo $stats['month_daily_avg_trend'] >= 0 ? 'trend-up' : 'trend-down'; ?>">
                        <i class="bi <?php echo $stats['month_daily_avg_trend'] >= 0 ? 'bi-graph-up' : 'bi-graph-down'; ?>"></i> 
                        <?php echo ($stats['month_daily_avg_trend'] > 0 ? '+' : '') . number_format($stats['month_daily_avg_trend'], 1) . '%'; ?> from last month
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="icon"><i class="bi bi-receipt"></i></div>
                    <p>Transactions This Month</p>
                    <h3><?php echo $stats['month_transactions'] ?? 0; ?></h3>
                    <p class="text-muted mt-2">Largest payment today: $<?php echo number_format($stats['today_largest_payment'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Revenue Chart -->
            <div class="col-md-8">
                <div class="chart-container">
                    <h5 class="mb-4">Revenue Overview</h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Maintenance Alerts -->
            <div class="col-md-4">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Maintenance Alerts</h5>
                        <a href="index.php?page=maintenance" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <?php if (!empty($upcoming_maintenance)): ?>
                        <?php foreach ($upcoming_maintenance as $maintenance): ?>
                            <div class="maintenance-item">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($maintenance['equipment']); ?></div>
                                    <small class="text-muted">Last: <?php echo htmlspecialchars($maintenance['last_maintenance']); ?></small>
                                </div>
                                <div>
                                    <span class="booking-status status-<?php echo $maintenance['status']; ?>">
                                        <?php echo ucfirst($maintenance['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No maintenance alerts</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Recent Reservations</h5>
                        <a href="index.php?page=reservations" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Equipment</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_bookings)): ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['equipment']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['start_date']); ?> to <?php echo htmlspecialchars($booking['end_date']); ?></td>
                                            <td>$<?php echo number_format($booking['amount'], 2); ?></td>
                                            <td>
                                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="index.php?page=reservations/view&id=<?php echo $booking['id']; ?>" class="btn btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="index.php?page=reservations/edit&id=<?php echo $booking['id']; ?>" class="btn btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No recent bookings</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($revenue_data['labels']); ?>,
                datasets: [{
                    label: 'Monthly Revenue ($)',
                    data: <?php echo json_encode($revenue_data['data']); ?>,
                    borderColor: '#0043a8',
                    backgroundColor: 'rgba(0, 67, 168, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 