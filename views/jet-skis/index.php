<?php
// Initialize variables to prevent undefined variable errors
$title = "Jet Skis";
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$jet_skis = []; // Empty array by default
$current_page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
$items_per_page = 12; // Number of items to show per page
$total_pages = 1; // Default to 1 page

// In a real implementation, these values would be fetched from the database
// Example code to fetch jet skis:
if (isset($conn)) {
    try {
        // First, count total jet skis for pagination
        $countQuery = "SELECT COUNT(*) as total FROM jet_skis WHERE 1=1";
        
        $countParams = [];
        $countTypes = "";
        
        // Apply search filter to count query
        if (!empty($search)) {
            $countQuery .= " AND (brand LIKE ? OR model LIKE ? OR registration_number LIKE ?)";
            $searchParam = "%$search%";
            $countParams[] = $searchParam;
            $countParams[] = $searchParam;
            $countParams[] = $searchParam;
            $countTypes .= "sss";
        }
        
        // Apply status filter to count query
        if (!empty($status)) {
            $countQuery .= " AND status = ?";
            $countParams[] = $status;
            $countTypes .= "s";
        }
        
        $countStmt = $conn->prepare($countQuery);
        
        if (!empty($countParams)) {
            $countStmt->bind_param($countTypes, ...$countParams);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $total_items = $countRow['total'];
        
        // Calculate total pages
        $total_pages = ceil($total_items / $items_per_page);
        
        // Calculate offset for pagination
        $offset = ($current_page - 1) * $items_per_page;
        
        // Now get the actual data with pagination
        $query = "SELECT * FROM jet_skis WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Apply search filter
        if (!empty($search)) {
            $query .= " AND (brand LIKE ? OR model LIKE ? OR registration_number LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "sss";
        }
        
        // Apply status filter
        if (!empty($status)) {
            $query .= " AND status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $query .= " ORDER BY brand, model LIMIT ? OFFSET ?";
        $params[] = $items_per_page;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $jet_skis = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
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
        .sidebar-logo img {
            width: 50px;
            height: auto;
            margin-bottom: 0.5rem;
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
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .card-body {
            padding: 1.25rem;
        }
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
        }
        .badge-available {
            background-color: #28a745;
        }
        .badge-maintenance {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-rented {
            background-color: #0d6efd;
        }
        .badge-unavailable {
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
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .page-link {
            color: var(--primary-color);
        }
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
            <li><a href="index.php?page=jet-skis" class="active"><i class="bi bi-water"></i> Jet Skis</a></li>
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
            <h1 class="h4 mb-0">Jet Skis</h1>
            <a href="index.php?page=jet-skis/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Jet Ski
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
                <input type="hidden" name="page" value="jet-skis">
                
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search by model, brand, or registration number" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="maintenance" <?php echo $status === 'maintenance' ? 'selected' : ''; ?>>In Maintenance</option>
                        <option value="rented" <?php echo $status === 'rented' ? 'selected' : ''; ?>>Currently Rented</option>
                        <option value="unavailable" <?php echo $status === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <?php if (empty($jet_skis)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> No jet skis found matching your criteria.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($jet_skis as $jet_ski): ?>
                    <div class="col-md-4 col-xl-3">
                        <div class="card h-100">
                            <?php if (!empty($jet_ski['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($jet_ski['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($jet_ski['model']); ?>">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                    <i class="bi bi-water text-secondary" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($jet_ski['brand']); ?> <?php echo htmlspecialchars($jet_ski['model']); ?></h5>
                                <p class="card-text text-muted">ID: <?php echo htmlspecialchars($jet_ski['registration_number']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold"><?php echo htmlspecialchars('$' . $jet_ski['hourly_rate']); ?>/hour</span>
                                    <?php
                                    $statusClass = '';
                                    switch($jet_ski['status']) {
                                        case 'available':
                                            $statusClass = 'badge-available';
                                            break;
                                        case 'maintenance':
                                            $statusClass = 'badge-maintenance';
                                            break;
                                        case 'rented':
                                            $statusClass = 'badge-rented';
                                            break;
                                        case 'unavailable':
                                            $statusClass = 'badge-unavailable';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($jet_ski['status']); ?></span>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="index.php?page=jet-skis/view&id=<?php echo $jet_ski['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <div class="btn-group" role="group">
                                        <a href="index.php?page=jet-skis/edit&id=<?php echo $jet_ski['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="index.php?page=reservations/create&jet_ski_id=<?php echo $jet_ski['id']; ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-calendar-plus"></i> Book
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-white d-flex justify-content-between text-muted small">
                                <span>Year: <?php echo htmlspecialchars($jet_ski['year']); ?></span>
                                <span>
                                    <i class="bi bi-tools me-1"></i>
                                    <?php echo date('M d, Y', strtotime($jet_ski['next_maintenance_date'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Jet Ski pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=jet-skis&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&page_num=<?php echo $current_page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=jet-skis&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&page_num=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=jet-skis&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&page_num=<?php echo $current_page + 1; ?>">
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