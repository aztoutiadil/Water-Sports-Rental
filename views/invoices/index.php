<?php
// Initialize variables to prevent undefined variable errors
$title = "Invoices";
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$payment_status = $_GET['payment_status'] ?? '';
$invoices = []; // Empty array by default
$current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$items_per_page = 10;
$total_pages = 1;

// In a real implementation, these values would be fetched from the database
// Example code to fetch invoices:
if (isset($conn)) {
    try {
        $query = "SELECT i.id, i.invoice_number, i.issue_date, i.total_amount, i.balance_due, i.status, 
                        c.first_name, c.last_name, c.email, 
                        CASE 
                            WHEN r.equipment_type = 'jet_ski' THEN CONCAT(j.brand, ' ', j.model)
                            WHEN r.equipment_type = 'boat' THEN b.name
                            ELSE 'Unknown'
                        END AS equipment_name,
                        r.equipment_type
                FROM invoices i
                JOIN reservations r ON i.reservation_id = r.id
                JOIN clients c ON r.client_id = c.id
                LEFT JOIN jet_skis j ON r.equipment_type = 'jet_ski' AND r.equipment_id = j.id
                LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (i.invoice_number LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        // Apply status filter
        if (!empty($status)) {
            $query .= " AND i.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Apply payment status filter
        if (!empty($payment_status)) {
            $query .= " AND i.status = ?";
            $params[] = $payment_status;
            $types .= "s";
        }
        
        // Apply date filters
        if (!empty($date_from)) {
            $query .= " AND i.issue_date >= ?";
            $params[] = $date_from;
            $types .= "s";
        }
        
        if (!empty($date_to)) {
            $query .= " AND i.issue_date <= ?";
            $params[] = $date_to;
            $types .= "s";
        }
        
        $query .= " ORDER BY i.issue_date DESC";
        
        $stmt = $conn->prepare($query);
        
        // Check if prepare statement was successful
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        // Bind parameters if we have any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $invoices = $result->fetch_all(MYSQLI_ASSOC);
            
            // Process data for display
            foreach ($invoices as &$invoice) {
                $invoice['client_name'] = $invoice['first_name'] . ' ' . $invoice['last_name'];
                $invoice['client_email'] = $invoice['email'];
                $invoice['created_at'] = $invoice['issue_date'];
                // Initialize missing values to prevent undefined index errors
                $invoice['payment_date'] = $invoice['payment_date'] ?? null;
                $invoice['deposit_paid'] = $invoice['deposit_paid'] ?? 0;
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
    }
}

// Use dummy data if no invoices were found or if we're not connected to a database
if (empty($invoices)) {
    // Create dummy invoices for display when no database or in development
    $invoices = [
        [
            'id' => 1,
            'invoice_number' => 'INV-2023-001',
            'client_name' => 'John Smith',
            'client_email' => 'john.smith@example.com',
            'equipment_name' => 'Yamaha WaveRunner FX',
            'equipment_type' => 'jet_ski',
            'created_at' => date('Y-m-d', strtotime('-2 days')),
            'payment_date' => null,
            'total_amount' => 375.00,
            'balance_due' => 275.00,
            'deposit_paid' => 100.00,
            'status' => 'partially_paid'
        ],
        [
            'id' => 2,
            'invoice_number' => 'INV-2023-002',
            'client_name' => 'Sarah Johnson',
            'client_email' => 'sarah.j@example.com',
            'equipment_name' => 'Luxury Cruiser',
            'equipment_type' => 'boat',
            'created_at' => date('Y-m-d', strtotime('-5 days')),
            'payment_date' => date('Y-m-d', strtotime('-4 days')),
            'total_amount' => 750.00,
            'balance_due' => 0.00,
            'deposit_paid' => 750.00,
            'status' => 'paid'
        ],
        [
            'id' => 3,
            'invoice_number' => 'INV-2023-003',
            'client_name' => 'Michael Brown',
            'client_email' => 'michael.brown@example.com',
            'equipment_name' => 'Sea-Doo GTI SE',
            'equipment_type' => 'jet_ski',
            'created_at' => date('Y-m-d', strtotime('-1 day')),
            'payment_date' => null,
            'total_amount' => 195.00,
            'balance_due' => 195.00,
            'deposit_paid' => 0.00,
            'status' => 'pending'
        ]
    ];
    
    // Set pagination variables for dummy data
    $total_pages = 1;
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
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: #003585;
        }
        .search-box {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .table-responsive {
            margin-bottom: 0;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
        }
        .table td {
            vertical-align: middle;
            padding: 1rem;
        }
        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
        }
        .page-link {
            color: var(--primary-color);
        }
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-paid {
            background-color: #28a745;
        }
        .status-partially_paid {
            background-color: #17a2b8;
        }
        .status-cancelled {
            background-color: #dc3545;
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
            <li><a href="index.php?page=invoices" class="active"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="h4 mb-0">Invoices</h1>
            <a href="index.php?page=reservations" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create Invoice from Reservation
            </a>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> mb-3">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Search and Filter Box -->
        <div class="search-box">
            <form action="index.php" method="GET" class="row g-3">
                <input type="hidden" name="page" value="invoices">
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search by invoice number or client name" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="partially_paid" <?php echo $status === 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="payment_status" class="form-label">Payment Status</label>
                    <select class="form-select" id="payment_status" name="payment_status">
                        <option value="">All Payment Statuses</option>
                        <option value="paid" <?php echo $payment_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="partially_paid" <?php echo $payment_status === 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                        <option value="pending" <?php echo $payment_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="cancelled" <?php echo $payment_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="col-12 d-flex justify-content-end">
                    <a href="index.php?page=invoices" class="btn btn-outline-secondary me-2">Reset</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($invoices)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> No invoices found matching your criteria.
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Equipment</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?page=invoices/view&id=<?php echo $invoice['id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($invoice['client_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($invoice['client_email']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($invoice['equipment_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($invoice['equipment_type']); ?></small>
                                    </td>
                                    <td>
                                        <div>
                                            <?php echo date('M d, Y', strtotime($invoice['created_at'])); ?>
                                        </div>
                                        <?php if ($invoice['payment_date']): ?>
                                            <small class="text-muted">
                                                Paid: <?php echo date('M d, Y', strtotime($invoice['payment_date'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold">$<?php echo number_format($invoice['total_amount'], 2); ?></div>
                                        <?php if ($invoice['deposit_paid'] > 0): ?>
                                            <small class="text-muted">Deposit: $<?php echo number_format($invoice['deposit_paid'], 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($invoice['status'] === 'paid'): ?>
                                            <span class="text-success">$0.00</span>
                                        <?php else: ?>
                                            <span class="<?php echo $invoice['balance_due'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                                $<?php echo number_format($invoice['balance_due'], 2); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge status-<?php echo $invoice['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $invoice['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=invoices/view&id=<?php echo $invoice['id']; ?>" class="btn btn-outline-primary" title="View Invoice">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($invoice['status'] !== 'cancelled' && $invoice['status'] !== 'paid'): ?>
                                                <a href="index.php?page=invoices/edit&id=<?php echo $invoice['id']; ?>" class="btn btn-outline-primary" title="Edit Invoice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="index.php?page=invoices/download&id=<?php echo $invoice['id']; ?>" class="btn btn-outline-success" title="Download PDF">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Invoice pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=invoices&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&payment_status=<?php echo urlencode($payment_status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page_num=<?php echo $current_page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=invoices&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&payment_status=<?php echo urlencode($payment_status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page_num=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=invoices&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&payment_status=<?php echo urlencode($payment_status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page_num=<?php echo $current_page + 1; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 