<?php
// Initialize variables to prevent undefined variable errors
$title = "Clients";

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
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$items_per_page = 10;
$offset = ($page_num - 1) * $items_per_page;

// Prepare the SQL query
$countSql = "SELECT COUNT(*) as total FROM clients";
$sql = "SELECT * FROM clients";

// Add search condition if search parameter is provided
if (!empty($search)) {
    $search_term = "%" . $conn->real_escape_string($search) . "%";
    $countSql .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $sql .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
}

// Add ordering and limit
$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

// Get total count
if (!empty($search)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
} else {
    $countStmt = $conn->prepare($countSql);
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$total_items = $countRow['total'];

$countStmt->close();

// Calculate total pages
$total_pages = ceil($total_items / $items_per_page);

// Get clients with pagination
$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $stmt->bind_param("ssssii", $search_term, $search_term, $search_term, $search_term, $items_per_page, $offset);
} else {
    $stmt->bind_param("ii", $items_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$clients = [];

while ($row = $result->fetch_assoc()) {
    // Get total reservations for this client
    $reservations_count = 0;
    $total_spent = 0;
    
    if (isset($conn)) {
        try {
            // Count reservations
            $res_stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE client_id = ?");
            if ($res_stmt) {
                $res_stmt->bind_param("i", $row['id']);
                $res_stmt->execute();
                $res_result = $res_stmt->get_result();
                if ($res_row = $res_result->fetch_assoc()) {
                    $reservations_count = $res_row['count'];
                }
                $res_stmt->close();
            }
            
            // Calculate total spent
            $spent_stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM reservations WHERE client_id = ? AND status != 'cancelled'");
            if ($spent_stmt) {
                $spent_stmt->bind_param("i", $row['id']);
                $spent_stmt->execute();
                $spent_result = $spent_stmt->get_result();
                if ($spent_row = $spent_result->fetch_assoc()) {
                    $total_spent = $spent_row['total'] ?? 0;
                }
                $spent_stmt->close();
            }
        } catch (Exception $e) {
            // Handle silently - we'll use default values
        }
    }
    
    // Add the reservation count and total spent to the client data
    $row['total_reservations'] = $reservations_count;
    $row['total_spent'] = $total_spent;
    
    $clients[] = $row;
}

$stmt->close();

// Apply search filter if provided
if (!empty($search)) {
    $clients = array_filter($clients, function($client) use ($search) {
        $search_lower = strtolower($search);
        return (
            strpos(strtolower($client['first_name']), $search_lower) !== false ||
            strpos(strtolower($client['last_name']), $search_lower) !== false ||
            strpos(strtolower($client['email']), $search_lower) !== false ||
            strpos(strtolower($client['phone']), $search_lower) !== false
        );
    });
}

// Apply status filter if provided
if (!empty($status)) {
    $clients = array_filter($clients, function($client) use ($status) {
        return $client['status'] === $status;
    });
}

// Apply date range filter if provided
if (!empty($date_from) && !empty($date_to)) {
    $clients = array_filter($clients, function($client) use ($date_from, $date_to) {
        $client_date = strtotime($client['created_at']);
        $from_date = strtotime($date_from);
        $to_date = strtotime($date_to);
        return $client_date >= $from_date && $client_date <= $to_date;
    });
}

// Get clients for current page
$clients_page = array_slice($clients, $offset, $items_per_page);
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
        
        .card-body {
            padding: 1.5rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            border-bottom-width: 1px;
            font-weight: 600;
            color: #555;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 20px;
        }
        
        .badge-active {
            background-color: #28a745;
        }
        
        .badge-inactive {
            background-color: #dc3545;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background: #003585;
        }
        
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .pagination .page-link {
            border: none;
            color: #555;
            margin: 0 0.2rem;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
        }
        
        .pagination .page-link:hover {
            background-color: rgba(0, 67, 168, 0.1);
            color: var(--primary-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
        }
        
        .client-name {
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .client-name:hover {
            text-decoration: underline;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 0.25rem;
        }
        
        .btn-outline-secondary {
            border-color: #e0e0e0;
            color: #666;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #333;
            border-color: #d0d0d0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 0.75rem;
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
            <li><a href="index.php?page=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="index.php?page=jet-skis"><i class="bi bi-water"></i> Jet Skis</a></li>
            <li><a href="index.php?page=boats"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
            <li><a href="index.php?page=clients" class="active"><i class="bi bi-people"></i> Clients</a></li>
            <li><a href="index.php?page=reservations"><i class="bi bi-calendar-check"></i> Reservations</a></li>
            <li><a href="index.php?page=invoices"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Clients</h1>
            <a href="index.php?page=clients/create" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i> Add New Client
            </a>
        </div>
        
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
                <input type="hidden" name="page" value="clients">
                
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search clients..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Date Added</th>
                                <th>Status</th>
                                <th>Reservations</th>
                                <th>Total Spent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clients_page)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No clients found matching your criteria</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clients_page as $client): ?>
                                    <tr>
                                        <td>
                                            <a href="index.php?page=clients/view&id=<?php echo $client['id']; ?>" class="client-name">
                                                <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($client['email']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($client['phone']); ?></div>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($client['created_at'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $client['status']; ?>">
                                                <?php echo ucfirst($client['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $client['total_reservations'] ?? 0; ?></td>
                                        <td>$<?php echo number_format($client['total_spent'] ?? 0, 2); ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="index.php?page=clients/view&id=<?php echo $client['id']; ?>" class="btn btn-icon btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="index.php?page=clients/edit&id=<?php echo $client['id']; ?>" class="btn btn-icon btn-outline-secondary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="index.php?page=clients/createReservation&client_id=<?php echo $client['id']; ?>" class="btn btn-icon btn-outline-primary" title="Create Reservation">
                                                    <i class="bi bi-calendar-plus"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-outline-danger" title="Delete" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $client['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                
                                                <!-- Delete Confirmation Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $client['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $client['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $client['id']; ?>">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete client <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>? This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="index.php?page=clients/delete&id=<?php echo $client['id']; ?>" class="btn btn-danger">Delete</a>
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
            
            <?php if ($total_pages > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> clients
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=clients&page_num=<?php echo $page_num - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page_num ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=clients&page_num=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=clients&page_num=<?php echo $page_num + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" aria-label="Next">
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