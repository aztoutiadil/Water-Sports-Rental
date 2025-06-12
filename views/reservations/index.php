<?php
// Initialize variables to prevent undefined variable errors
$title = "Reservations";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Initialize variables for search and pagination
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$items_per_page = 10;
$offset = ($page_num - 1) * $items_per_page;
$reservations = []; // Empty array by default

// Check if the tables exist
$tablesExist = true;
if (isset($conn)) {
    // Check for tables
    $tables = ['reservations', 'clients', 'jet_skis', 'boats'];
    foreach ($tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$check || $check->num_rows === 0) {
            $tablesExist = false;
            break;
        }
    }
}

// Only attempt to fetch from database if tables exist
if (isset($conn) && $tablesExist) {
    try {
        // Count total reservations for pagination
        $countSql = "SELECT COUNT(*) as total FROM reservations";
        $countWhere = [];
        $countParams = [];
        $countTypes = "";
        
        if (!empty($search)) {
            $countSql = "SELECT COUNT(*) as total FROM reservations r 
                         JOIN clients c ON r.client_id = c.id
                         LEFT JOIN jet_skis j ON r.equipment_type = 'jet_ski' AND r.equipment_id = j.id
                         LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
                         WHERE ";
            
            $searchTerms = [
                "CONCAT(c.first_name, ' ', c.last_name) LIKE ?",
                "c.email LIKE ?",
                "r.id LIKE ?",
                "(r.equipment_type = 'jet_ski' AND CONCAT(j.brand, ' ', j.model) LIKE ?)",
                "(r.equipment_type = 'boat' AND b.name LIKE ?)"
            ];
            
            $countWhere[] = "(" . implode(" OR ", $searchTerms) . ")";
            $searchParam = "%$search%";
            for ($i = 0; $i < 5; $i++) {
                $countParams[] = $searchParam;
                $countTypes .= "s";
            }
        }
        
        // Apply status filter
        if (!empty($status)) {
            $countWhere[] = "r.status = ?";
            $countParams[] = $status;
            $countTypes .= "s";
        }
        
        // Apply equipment type filter
        if (!empty($type)) {
            if ($type === 'Jet Ski') {
                $countWhere[] = "r.equipment_type = 'jet_ski'";
            } else if ($type === 'Boat') {
                $countWhere[] = "r.equipment_type = 'boat'";
            }
        }
        
        // Apply date filters
        if (!empty($date_from)) {
            $countWhere[] = "r.start_date >= ?";
            $countParams[] = $date_from;
            $countTypes .= "s";
        }
        
        if (!empty($date_to)) {
            $countWhere[] = "r.end_date <= ?";
            $countParams[] = $date_to;
            $countTypes .= "s";
        }
        
        // Add WHERE clause if filters exist
        if (!empty($countWhere)) {
            if (strpos($countSql, "WHERE") === false) {
                $countSql .= " WHERE ";
            }
            $countSql .= implode(" AND ", $countWhere);
        }
        
        $countStmt = $conn->prepare($countSql);
        
        if ($countStmt === false) {
            throw new Exception("Error preparing count statement: " . $conn->error);
        }
        
        // Bind params for count query if there are any
        if (!empty($countParams)) {
            $countStmt->bind_param($countTypes, ...$countParams);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $total_items = $countRow['total'];
        $countStmt->close();
        
        // Calculate total pages
        $total_pages = ceil($total_items / $items_per_page);
        
        // Main query for reservations
        $query = "SELECT r.id, r.start_date, r.end_date, r.status, r.total_amount, r.created_at,
                         c.first_name, c.last_name, c.email,
                         CASE 
                             WHEN r.equipment_type = 'jet_ski' THEN CONCAT(j.brand, ' ', j.model)
                             WHEN r.equipment_type = 'boat' THEN b.name
                             ELSE 'Unknown'
                         END AS equipment_name,
                         r.equipment_type
                  FROM reservations r
                  JOIN clients c ON r.client_id = c.id
                  LEFT JOIN jet_skis j ON r.equipment_type = 'jet_ski' AND r.equipment_id = j.id
                  LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
                  WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (CONCAT(c.first_name, ' ', c.last_name) LIKE ? 
                          OR c.email LIKE ? 
                          OR r.id LIKE ?
                          OR (r.equipment_type = 'jet_ski' AND CONCAT(j.brand, ' ', j.model) LIKE ?)
                          OR (r.equipment_type = 'boat' AND b.name LIKE ?))";
            $searchParam = "%$search%";
            for ($i = 0; $i < 5; $i++) {
                $params[] = $searchParam;
                $types .= "s";
            }
        }
        
        // Apply status filter
        if (!empty($status)) {
            $query .= " AND r.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Apply equipment type filter
        if (!empty($type)) {
            if ($type === 'Jet Ski') {
                $query .= " AND r.equipment_type = 'jet_ski'";
            } else if ($type === 'Boat') {
                $query .= " AND r.equipment_type = 'boat'";
            }
        }
        
        // Apply date filters
        if (!empty($date_from)) {
            $query .= " AND r.start_date >= ?";
            $params[] = $date_from;
            $types .= "s";
        }
        
        if (!empty($date_to)) {
            $query .= " AND r.end_date <= ?";
            $params[] = $date_to;
            $types .= "s";
        }
        
        $query .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $items_per_page;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind params if there are any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error executing query: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result === false) {
            throw new Exception("Error getting result: " . $stmt->error);
        }
        
        $reservations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
    } catch (Exception $e) {
        $error_message = "Database error: " . $e->getMessage();
        // For development purposes, display the error. In production, log it instead.
        // error_log($error_message);
    }
} else {
    // If tables don't exist or no database connection, use dummy data
    $reservations = [
        [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'equipment_type' => 'jet_ski',
            'equipment_name' => 'Yamaha FX Cruiser',
            'start_date' => '2023-06-15 09:00:00',
            'end_date' => '2023-06-15 15:00:00',
            'status' => 'completed',
            'total_amount' => 450.00,
            'created_at' => '2023-06-10 14:30:00'
        ],
        [
            'id' => 2,
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'sarah.j@example.com',
            'equipment_type' => 'boat',
            'equipment_name' => '30ft Yacht',
            'start_date' => '2023-06-20 10:00:00',
            'end_date' => '2023-06-20 16:00:00',
            'status' => 'confirmed',
            'total_amount' => 1200.00,
            'created_at' => '2023-06-15 09:15:00'
        ],
        [
            'id' => 3,
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'michael.brown@example.com',
            'equipment_type' => 'jet_ski',
            'equipment_name' => 'Sea-Doo Spark',
            'start_date' => '2023-06-22 11:00:00',
            'end_date' => '2023-06-22 13:00:00',
            'status' => 'pending',
            'total_amount' => 180.00,
            'created_at' => '2023-06-20 16:45:00'
        ]
    ];
    
    // Set pagination values
    $total_items = count($reservations);
    $total_pages = ceil($total_items / $items_per_page);
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 1.25rem 1.5rem;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
        }
        
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-confirmed {
            background-color: #28a745;
            color: white;
        }
        
        .badge-in_progress, .badge-in-progress {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-completed {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-cancelled {
            background-color: #dc3545;
            color: white;
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
            <li><a href="index.php?page=reservations" class="active"><i class="bi bi-calendar-check"></i> Reservations</a></li>
            <li><a href="index.php?page=invoices"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Reservations</h1>
            <a href="index.php?page=reservations/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create New Reservation
            </a>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> mb-4">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <div class="search-box">
            <form action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="reservations">
                
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search reservations..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="Jet Ski" <?php echo $type === 'Jet Ski' ? 'selected' : ''; ?>>Jet Ski</option>
                        <option value="Boat" <?php echo $type === 'Boat' ? 'selected' : ''; ?>>Boat</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" placeholder="From" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" placeholder="To" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Equipment</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservations)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No reservations found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td>#<?php echo $reservation['id']; ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($reservation['email']); ?></div>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($reservation['equipment_name']); ?></div>
                                            <div class="text-muted small"><?php echo ucfirst(str_replace('_', ' ', $reservation['equipment_type'])); ?></div>
                                        </td>
                                        <td>
                                            <div>
                                                <?php 
                                                    $start = new DateTime($reservation['start_date']);
                                                    echo $start->format('M d, Y'); 
                                                ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?php 
                                                    echo $start->format('h:i A') . ' - ';
                                                    $end = new DateTime($reservation['end_date']);
                                                    echo $end->format('h:i A'); 
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $reservation['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $reservation['status'])); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($reservation['total_amount'], 2); ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="index.php?page=reservations/view&id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="index.php?page=reservations/edit&id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="index.php?page=reservations/createInvoice&reservation_id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Create Invoice">
                                                    <i class="bi bi-receipt"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Cancel" 
                                                        data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $reservation['id']; ?>"
                                                        <?php echo in_array($reservation['status'], ['completed', 'cancelled']) ? 'disabled' : ''; ?>>
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                                
                                                <!-- Cancel Confirmation Modal -->
                                                <div class="modal fade" id="cancelModal<?php echo $reservation['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Cancel Reservation</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to cancel this reservation? This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <a href="index.php?page=reservations/cancel&id=<?php echo $reservation['id']; ?>" class="btn btn-danger">Cancel Reservation</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (isset($total_pages) && $total_pages > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> reservations
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=reservations&page_num=<?php echo $page_num - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&type=<?php echo urlencode($type); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page_num ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=reservations&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&type=<?php echo urlencode($type); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=reservations&page_num=<?php echo $page_num + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&type=<?php echo urlencode($type); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 