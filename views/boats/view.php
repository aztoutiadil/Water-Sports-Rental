<?php
// Initialize variables to prevent undefined variable errors
$title = "Tourist Boat Details";
$boat = null;
$reservation_history = [];
$maintenance_history = [];

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Get boat ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to boat list
if ($id <= 0) {
    $_SESSION['flash']['error'] = "No boat ID specified";
    header('Location: index.php?page=boats');
    exit;
}

// Fetch boat data from database
if (isset($conn)) {
    try {
        // Get boat details
        $stmt = $conn->prepare("SELECT * FROM boats WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $boat = $result->fetch_assoc();
            $title = "Tourist Boat: " . $boat['name'];
        } else {
            $_SESSION['flash']['error'] = "Tourist boat not found";
            header('Location: index.php?page=boats');
            exit;
        }
        
        $stmt->close();
        
        // Get reservation history
        $res_stmt = $conn->prepare("
            SELECT r.*, CONCAT(c.first_name, ' ', c.last_name) as client_name 
            FROM reservations r 
            JOIN clients c ON r.client_id = c.id 
            WHERE r.equipment_type = 'boat' AND r.equipment_id = ? 
            ORDER BY r.start_date DESC
        ");
        
        if ($res_stmt !== false) {
            $res_stmt->bind_param("i", $id);
            $res_stmt->execute();
            $res_result = $res_stmt->get_result();
            
            while ($row = $res_result->fetch_assoc()) {
                $reservation_history[] = $row;
            }
            
            $res_stmt->close();
        }
        
        // Get maintenance history
        $maint_stmt = $conn->prepare("
            SELECT * FROM boat_maintenance 
            WHERE boat_id = ? 
            ORDER BY date DESC
        ");
        
        if ($maint_stmt !== false) {
            $maint_stmt->bind_param("i", $id);
            $maint_stmt->execute();
            $maint_result = $maint_stmt->get_result();
            
            while ($row = $maint_result->fetch_assoc()) {
                $maintenance_history[] = $row;
            }
            
            $maint_stmt->close();
        }
        
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
    }
}

// Use dummy data if no boat found or no database connection
if ($boat === null) {
    // Create dummy boat data
    $boat = [
        'id' => $id,
        'name' => 'Example Boat',
        'type' => 'Pontoon Boat',
        'capacity' => 12,
        'year' => 2023,
        'registration_number' => 'TB-' . sprintf("%04d", $id),
        'hourly_rate' => 150.00,
        'daily_rate' => 750.00,
        'status' => 'available',
        'features' => 'GPS, Refrigerator, Bathroom, Sunshade',
        'last_maintenance_date' => date('Y-m-d', strtotime('-30 days')),
        'next_maintenance_date' => date('Y-m-d', strtotime('+30 days')),
        'notes' => 'This is sample data as the actual boat could not be found.',
        'image_url' => ''
    ];
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
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: #003585;
        }
        .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #495057;
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
        .badge-booked {
            background-color: #0d6efd;
        }
        .badge-out_of_service {
            background-color: #dc3545;
        }
        .detail-image {
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            font-weight: 500;
        }
        .tab-content {
            padding: 1.5rem 0;
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
            <li><a href="index.php?page=boats" class="active"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
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
            <h1 class="h4 mb-0"><?php echo htmlspecialchars($title); ?></h1>
            <div>
                <a href="index.php?page=boats" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Tourist Boats
                </a>
                <a href="index.php?page=boats/edit&id=<?php echo $boat['id']; ?>" class="btn btn-primary ms-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
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

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <?php if (!empty($boat['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($boat['image_url']); ?>" alt="<?php echo htmlspecialchars($boat['name']); ?>" class="detail-image">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="height: 200px; border-radius: 8px;">
                                <i class="bi bi-tsunami text-secondary" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="mb-1"><?php echo htmlspecialchars($boat['name'] ?? 'Unknown Boat'); ?></h3>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($boat['type'] ?? 'Unknown Type'); ?></p>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($boat['registration_number'] ?? 'No Registration'); ?></p>
                        
                        <?php
                        $statusClass = '';
                        switch($boat['status'] ?? 'out_of_service') {
                            case 'available':
                                $statusClass = 'badge-available';
                                break;
                            case 'maintenance':
                                $statusClass = 'badge-maintenance';
                                break;
                            case 'booked':
                                $statusClass = 'badge-booked';
                                break;
                            case 'out_of_service':
                                $statusClass = 'badge-out_of_service';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $statusClass; ?> mb-3"><?php echo ucfirst(str_replace('_', ' ', $boat['status'] ?? 'Out of Service')); ?></span>
                        
                        <div class="d-grid gap-2">
                            <?php if (($boat['status'] ?? '') === 'available'): ?>
                                <a href="index.php?page=reservations/create&boat_id=<?php echo $boat['id']; ?>" class="btn btn-success">
                                    <i class="bi bi-calendar-plus"></i> Book Now
                                </a>
                            <?php endif; ?>
                            
                            <?php if (($boat['status'] ?? '') !== 'maintenance'): ?>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                                    <i class="bi bi-tools"></i> Record Maintenance
                                </button>
                            <?php endif; ?>
                            
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash"></i> Delete Boat
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Boat Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Name</div>
                            <div class="col-7"><?php echo htmlspecialchars($boat['name'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Type</div>
                            <div class="col-7"><?php echo htmlspecialchars($boat['type'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Capacity</div>
                            <div class="col-7"><?php echo htmlspecialchars($boat['capacity'] ?? 'N/A'); ?> Persons</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Year</div>
                            <div class="col-7"><?php echo htmlspecialchars($boat['year'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Registration</div>
                            <div class="col-7"><?php echo htmlspecialchars($boat['registration_number'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Hourly Rate</div>
                            <div class="col-7">$<?php echo htmlspecialchars($boat['hourly_rate'] ?? '0.00'); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Daily Rate</div>
                            <div class="col-7">$<?php echo htmlspecialchars($boat['daily_rate'] ?? '0.00'); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Last Maintenance</div>
                            <div class="col-7"><?php echo isset($boat['last_maintenance_date']) ? date('M d, Y', strtotime($boat['last_maintenance_date'])) : 'N/A'; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 detail-label">Next Maintenance</div>
                            <div class="col-7"><?php echo isset($boat['next_maintenance_date']) ? date('M d, Y', strtotime($boat['next_maintenance_date'])) : 'N/A'; ?></div>
                        </div>
                        <?php if (!empty($boat['features'])): ?>
                            <div class="row mb-2">
                                <div class="col-5 detail-label">Features</div>
                                <div class="col-7"><?php echo htmlspecialchars($boat['features'] ?? 'N/A'); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($boat['notes'])): ?>
                            <div class="mt-3">
                                <div class="detail-label mb-2">Notes</div>
                                <div><?php echo nl2br(htmlspecialchars($boat['notes'] ?? '')); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white p-0">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations-tab-pane" type="button" role="tab" aria-controls="reservations-tab-pane" aria-selected="true">
                                    <i class="bi bi-calendar-check me-1"></i> Reservation History
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance-tab-pane" type="button" role="tab" aria-controls="maintenance-tab-pane" aria-selected="false">
                                    <i class="bi bi-tools me-1"></i> Maintenance History
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="reservations-tab-pane" role="tabpanel" aria-labelledby="reservations-tab" tabindex="0">
                                <?php if (empty($reservation_history)): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i> No reservation history found for this boat.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Reservation ID</th>
                                                    <th>Client</th>
                                                    <th>Period</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reservation_history as $reservation): ?>
                                                    <tr>
                                                        <td>#<?php echo isset($reservation['id']) ? $reservation['id'] : 'N/A'; ?></td>
                                                        <td><?php echo isset($reservation['client_name']) ? htmlspecialchars($reservation['client_name']) : 'Unknown Client'; ?></td>
                                                        <td>
                                                            <?php 
                                                                if (isset($reservation['start_date']) && isset($reservation['end_date'])) {
                                                                    echo date('M d, Y H:i', strtotime($reservation['start_date'])); 
                                                                    echo ' to ';
                                                                    echo date('M d, Y H:i', strtotime($reservation['end_date']));
                                                                } else {
                                                                    echo 'Date not available';
                                                                }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            if (isset($reservation['amount'])) {
                                                                echo '$' . number_format($reservation['amount'], 2);
                                                            } elseif (isset($reservation['total_amount'])) {
                                                                echo '$' . number_format($reservation['total_amount'], 2);
                                                            } else {
                                                                echo 'N/A';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                if (isset($reservation['status'])) {
                                                                    echo $reservation['status'] === 'completed' ? 'success' : 
                                                                        ($reservation['status'] === 'upcoming' ? 'primary' : 'warning');
                                                                } else {
                                                                    echo 'secondary';
                                                                }
                                                            ?>">
                                                                <?php echo isset($reservation['status']) ? ucfirst($reservation['status']) : 'Unknown'; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="index.php?page=reservations/view&id=<?php echo isset($reservation['id']) ? $reservation['id'] : '0'; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="tab-pane fade" id="maintenance-tab-pane" role="tabpanel" aria-labelledby="maintenance-tab" tabindex="0">
                                <?php if (empty($maintenance_history)): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i> No maintenance history found for this boat.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Performed By</th>
                                                    <th>Notes</th>
                                                    <th>Next Maintenance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($maintenance_history as $maintenance): ?>
                                                    <tr>
                                                        <td><?php echo isset($maintenance['date']) ? date('M d, Y', strtotime($maintenance['date'])) : 'N/A'; ?></td>
                                                        <td><?php echo htmlspecialchars($maintenance['performed_by'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($maintenance['notes'] ?? 'N/A'); ?></td>
                                                        <td><?php echo isset($maintenance['next_maintenance_date']) ? date('M d, Y', strtotime($maintenance['next_maintenance_date'])) : 'N/A'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Modal -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?page=boats/addMaintenance" method="POST">
                    <input type="hidden" name="id" value="<?php echo $boat['id'] ?? 0; ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="maintenanceModalLabel">Record Maintenance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="maintenance_date" class="form-label">Maintenance Date</label>
                            <input type="date" class="form-control" id="maintenance_date" name="maintenance_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="maintenance_notes" class="form-label">Maintenance Notes</label>
                            <textarea class="form-control" id="maintenance_notes" name="maintenance_notes" rows="3" required></textarea>
                            <div class="form-text">Describe the maintenance performed, parts replaced, etc.</div>
                        </div>
                        <div class="mb-3">
                            <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                            <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Maintenance Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this tourist boat?</p>
                    <p class="text-danger">This action cannot be undone. All related maintenance records will also be deleted.</p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i> 
                        Deleting a boat with existing reservations may cause inconsistencies in the system.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="index.php?page=boats/delete&id=<?php echo $boat['id'] ?? 0; ?>" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Boat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 