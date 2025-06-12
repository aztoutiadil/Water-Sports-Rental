<?php
// Initialize variables to prevent undefined variable errors
$title = "Dashboard";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Dummy data for dashboard statistics
$stats = [
    'jet_skis' => [
        'total' => 15,
        'available' => 12,
        'change' => 3,
        'change_type' => 'increase'
    ],
    'boats' => [
        'total' => 10,
        'available' => 8,
        'change' => -1,
        'change_type' => 'decrease'
    ],
    'clients' => [
        'total' => 124,
        'new' => 24,
        'change' => 5,
        'change_type' => 'increase'
    ],
    'revenue' => [
        'weekly' => 7842,
        'change' => 12,
        'change_type' => 'increase'
    ],
    'payments' => [
        'total_invoices' => 45,
        'paid' => 32,
        'pending' => 8,
        'unpaid' => 5,
        'total_amount' => 9875.50,
        'paid_amount' => 7243.00,
        'pending_amount' => 1750.50,
        'unpaid_amount' => 882.00
    ]
];

// Dummy data for recent reservations
$recent_reservations = [
    [
        'id' => 1,
        'equipment' => 'Jet Ski - Yamaha Waverunner',
        'client' => 'John Smith',
        'duration' => '2 hours',
        'status' => 'confirmed'
    ],
    [
        'id' => 2,
        'equipment' => 'Tourist Boat - Sunseeker',
        'client' => 'Sarah Johnson',
        'duration' => '4 hours',
        'status' => 'pending'
    ],
    [
        'id' => 3,
        'equipment' => 'Jet Ski - Sea-Doo Spark',
        'client' => 'Michael Brown',
        'duration' => '3 hours',
        'status' => 'in_progress'
    ],
    [
        'id' => 4,
        'equipment' => 'Tourist Boat - Bayliner',
        'client' => 'Emily Wilson',
        'duration' => 'Full day',
        'status' => 'confirmed'
    ]
];

// Dummy data for recent clients
$recent_clients = [
    [
        'id' => 1,
        'name' => 'Lisa Anderson',
        'email' => 'lisa.anderson@example.com',
        'phone' => '+1 555-123-4567',
        'days_ago' => 1
    ],
    [
        'id' => 2,
        'name' => 'Robert Johnson',
        'email' => 'robert.j@example.com',
        'phone' => '+1 555-987-6543',
        'days_ago' => 2
    ],
    [
        'id' => 3,
        'name' => 'Jennifer Martinez',
        'email' => 'j.martinez@example.com',
        'phone' => '+1 555-789-0123',
        'days_ago' => 3
    ],
    [
        'id' => 4,
        'name' => 'David Thompson',
        'email' => 'david.t@example.com',
        'phone' => '+1 555-456-7890',
        'days_ago' => 4
    ]
];

// Dummy data for charts
$revenue_data = [980, 1200, 850, 1100, 1500, 2200, 1900];
$equipment_usage = [
    'Jet Skis' => 65,
    'Tourist Boats' => 30,
    'Other' => 5
];
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
        
        .welcome-banner {
            background-image: linear-gradient(to right, #0099ff, #0043a8);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1564415060716-49971f59d6c7?auto=format&fit=crop&w=600&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.3;
            border-top-right-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed);
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.jet-skis {
            border-left-color: #0099ff;
        }
        
        .stat-card.boats {
            border-left-color: #4CAF50;
        }
        
        .stat-card.clients {
            border-left-color: #FF9800;
        }
        
        .stat-card.revenue {
            border-left-color: #9C27B0;
        }
        
        .stat-card .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        
        .stat-card.jet-skis .stat-icon {
            background-color: rgba(0, 153, 255, 0.1);
            color: #0099ff;
        }
        
        .stat-card.boats .stat-icon {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .stat-card.clients .stat-icon {
            background-color: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }
        
        .stat-card.revenue .stat-icon {
            background-color: rgba(156, 39, 176, 0.1);
            color: #9C27B0;
        }
        
        .stat-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
        
        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .recent-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #333;
            font-weight: 600;
        }
        
        .recent-item {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            transition: background-color var(--transition-speed);
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }
        
        .recent-item:hover {
            background-color: rgba(0, 123, 255, 0.03);
            border-left-color: var(--primary-color);
        }
        
        .recent-item .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(0, 123, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary-color);
        }
        
        .recent-item .item-details {
            flex: 1;
        }
        
        .recent-item .item-details h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .recent-item .item-details p {
            margin: 0;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .recent-item .item-details .client-name {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .recent-item .item-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 20px;
        }
        
        .badge-available, .badge-confirmed {
            background-color: #28a745;
        }
        
        .badge-maintenance, .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-rented, .badge-in_progress {
            background-color: #0d6efd;
        }
        
        .badge-unavailable, .badge-cancelled {
            background-color: #dc3545;
        }
        
        .calendar-section, .chart-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .chart-section {
            height: 350px;
        }
        
        .chart-container {
            height: 280px;
        }
        
        .system-status {
            display: flex;
            align-items: center;
            padding: 1rem;
            background-color: rgba(0, 153, 255, 0.05);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .system-status i {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #0099ff;
        }
        
        .system-status p {
            margin: 0;
            font-size: 0.9rem;
            color: #333;
        }
        
        .system-status .badge {
            margin-left: auto;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .quick-action {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all var(--transition-speed);
            cursor: pointer;
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .quick-action:hover {
            transform: translateY(-5px);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .quick-action i {
            font-size: 2rem;
            margin-bottom: 1rem;
            transition: all var(--transition-speed);
        }
        
        .quick-action:hover i {
            transform: scale(1.2);
        }
        
        .quick-action span {
            font-weight: 500;
        }
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        @media (max-width: 992px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
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
            <div class="user-info text-center mt-2">
                <span class="d-block text-white-50 small">Logged in as</span>
                <span class="d-block text-white fw-bold"><?php echo htmlspecialchars($user['name']); ?></span>
                <span class="badge bg-light text-primary mt-1"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
            </div>
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
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div style="max-width: 60%;">
                <h1 class="mb-3">Welcome Back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p class="mb-4">Here's what's happening with your water sports rental business today.</p>
                <div class="d-flex">
                    <a href="index.php?page=reports" class="btn btn-light me-2">
                        <i class="bi bi-bar-chart me-1"></i> View Reports
                    </a>
                    <?php if ($user['role'] === 'administrator' || $user['role'] === 'agent'): ?>
                    <a href="index.php?page=clients/create" class="btn btn-outline-light">
                        <i class="bi bi-person-plus me-1"></i> Add New Client
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card jet-skis">
                <div class="stat-icon">
                    <i class="bi bi-water"></i>
                </div>
                <p>Available Jet Skis</p>
                <h3><?php echo $stats['jet_skis']['available']; ?></h3>
                <p>
                    <?php if ($stats['jet_skis']['change_type'] === 'increase'): ?>
                        <span class="text-success"><i class="bi bi-arrow-up"></i> <?php echo $stats['jet_skis']['change']; ?></span>
                    <?php else: ?>
                        <span class="text-danger"><i class="bi bi-arrow-down"></i> <?php echo abs($stats['jet_skis']['change']); ?></span>
                    <?php endif; ?>
                    from last week
                </p>
            </div>
            
            <div class="stat-card boats">
                <div class="stat-icon">
                    <i class="bi bi-tsunami"></i>
                </div>
                <p>Available Boats</p>
                <h3><?php echo $stats['boats']['available']; ?></h3>
                <p>
                    <?php if ($stats['boats']['change_type'] === 'increase'): ?>
                        <span class="text-success"><i class="bi bi-arrow-up"></i> <?php echo $stats['boats']['change']; ?></span>
                    <?php else: ?>
                        <span class="text-danger"><i class="bi bi-arrow-down"></i> <?php echo abs($stats['boats']['change']); ?></span>
                    <?php endif; ?>
                    from last week
                </p>
            </div>
            
            <div class="stat-card clients">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <p>New Clients</p>
                <h3><?php echo $stats['clients']['new']; ?></h3>
                <p>
                    <?php if ($stats['clients']['change_type'] === 'increase'): ?>
                        <span class="text-success"><i class="bi bi-arrow-up"></i> <?php echo $stats['clients']['change']; ?></span>
                    <?php else: ?>
                        <span class="text-danger"><i class="bi bi-arrow-down"></i> <?php echo abs($stats['clients']['change']); ?></span>
                    <?php endif; ?>
                    from last month
                </p>
            </div>
            
            <div class="stat-card revenue">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <p>Weekly Revenue</p>
                <h3>$<?php echo number_format($stats['revenue']['weekly']); ?></h3>
                <p>
                    <?php if ($stats['revenue']['change_type'] === 'increase'): ?>
                        <span class="text-success"><i class="bi bi-arrow-up"></i> <?php echo $stats['revenue']['change']; ?>%</span>
                    <?php else: ?>
                        <span class="text-danger"><i class="bi bi-arrow-down"></i> <?php echo abs($stats['revenue']['change']); ?>%</span>
                    <?php endif; ?>
                    from last week
                </p>
            </div>
        </div>
        
        <div class="system-status">
            <i class="bi bi-check-circle-fill"></i>
            <p>All systems are running smoothly. Last backup was 2 hours ago.</p>
            <span class="badge bg-success">Operational</span>
        </div>
        
        <h4 class="mt-4 mb-3">Quick Actions</h4>
        <div class="quick-actions mb-4">
            <a href="index.php?page=reservations/create" class="quick-action">
                <i class="bi bi-calendar-plus text-primary"></i>
                <span>New Reservation</span>
            </a>
            <a href="index.php?page=clients/create" class="quick-action">
                <i class="bi bi-person-plus text-success"></i>
                <span>Add Client</span>
            </a>
            <a href="index.php?page=invoices" class="quick-action">
                <i class="bi bi-receipt text-warning"></i>
                <span>Invoices</span>
            </a>
            <a href="index.php?page=reports" class="quick-action">
                <i class="bi bi-bar-chart text-info"></i>
                <span>Reports</span>
            </a>
        </div>
        
        <div class="two-columns">
            <!-- Recent Reservations -->
            <div class="recent-section">
                <h3><i class="bi bi-calendar-check me-2"></i> Recent Reservations</h3>
                
                <?php foreach ($recent_reservations as $reservation): ?>
                <div class="recent-item">
                    <div class="item-icon">
                        <?php if (strpos($reservation['equipment'], 'Jet Ski') !== false): ?>
                            <i class="bi bi-water"></i>
                        <?php else: ?>
                            <i class="bi bi-tsunami"></i>
                        <?php endif; ?>
                    </div>
                    <div class="item-details">
                        <h5><?php echo htmlspecialchars($reservation['equipment']); ?></h5>
                        <p>By <span class="client-name"><?php echo htmlspecialchars($reservation['client']); ?></span> • <?php echo htmlspecialchars($reservation['duration']); ?></p>
                    </div>
                    <span class="badge badge-<?php echo $reservation['status']; ?>"><?php echo ucfirst($reservation['status']); ?></span>
                </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-3">
                    <a href="index.php?page=reservations" class="btn btn-sm btn-outline-primary">View All Reservations</a>
                </div>
            </div>
            
            <!-- Recent Clients -->
            <div class="recent-section">
                <h3><i class="bi bi-people me-2"></i> Recent Clients</h3>
                
                <?php foreach ($recent_clients as $client): ?>
                <div class="recent-item">
                    <div class="item-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="item-details">
                        <h5><?php echo htmlspecialchars($client['name']); ?></h5>
                        <p><?php echo htmlspecialchars($client['email']); ?> • <?php echo htmlspecialchars($client['phone']); ?></p>
                    </div>
                    <div class="item-date">
                        <?php echo $client['days_ago'] === 1 ? '1 day ago' : $client['days_ago'] . ' days ago'; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-3">
                    <a href="index.php?page=clients" class="btn btn-sm btn-outline-primary">View All Clients</a>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i> Payment Status</h5>
                        <a href="index.php?page=invoices" class="btn btn-sm btn-outline-primary">View All Invoices</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="paymentStatusChart" height="200"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="payment-stats">
                                    <div class="mb-3">
                                        <h6 class="text-muted">Total Invoices</h6>
                                        <h3><?php echo $stats['payments']['total_invoices']; ?></h3>
                                        <p>Total: $<?php echo number_format($stats['payments']['total_amount'], 2); ?></p>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="bi bi-circle-fill text-success me-1"></i> Paid</span>
                                        <span class="text-success"><?php echo $stats['payments']['paid']; ?> ($<?php echo number_format($stats['payments']['paid_amount'], 2); ?>)</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="bi bi-circle-fill text-warning me-1"></i> Pending</span>
                                        <span class="text-warning"><?php echo $stats['payments']['pending']; ?> ($<?php echo number_format($stats['payments']['pending_amount'], 2); ?>)</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span><i class="bi bi-circle-fill text-danger me-1"></i> Unpaid</span>
                                        <span class="text-danger"><?php echo $stats['payments']['unpaid']; ?> ($<?php echo number_format($stats['payments']['unpaid_amount'], 2); ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="two-columns">
            <!-- Weekly Revenue Chart -->
            <div class="chart-section">
                <h3><i class="bi bi-graph-up me-2"></i> Weekly Revenue</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <!-- Equipment Usage Chart -->
            <div class="chart-section">
                <h3><i class="bi bi-pie-chart me-2"></i> Equipment Usage</h3>
                <div class="chart-container">
                    <canvas id="equipmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($revenue_data); ?>,
                    borderColor: '#0043a8',
                    backgroundColor: 'rgba(0, 67, 168, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
        
        // Equipment Usage Chart
        const equipmentCtx = document.getElementById('equipmentChart').getContext('2d');
        const equipmentChart = new Chart(equipmentCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($equipment_usage)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($equipment_usage)); ?>,
                    backgroundColor: ['#0099ff', '#4CAF50', '#FF9800'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Payment Status Chart
        const paymentCtx = document.getElementById('paymentStatusChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Unpaid'],
                datasets: [{
                    data: [
                        <?php echo $stats['payments']['paid']; ?>, 
                        <?php echo $stats['payments']['pending']; ?>, 
                        <?php echo $stats['payments']['unpaid']; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html> 