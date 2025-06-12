<?php
// Initialize variables to prevent undefined variable errors
$title = "Reservation Details";
$reservation = null;
$client = null;
$equipment = null;

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Get reservation ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to reservation list
if ($id <= 0) {
    $_SESSION['flash']['error'] = "No reservation ID specified";
    header('Location: index.php?page=reservations');
    exit;
}

// Fetch reservation data from database
if (isset($conn)) {
    try {
        // Get reservation details with client and equipment info
        $stmt = $conn->prepare("
            SELECT r.*, 
                   c.first_name, 
                   c.last_name, 
                   c.email, 
                   c.phone,
                   c.address,
                   c.city,
                   c.state,
                   c.zip_code,
                   CASE 
                       WHEN r.equipment_type = 'jet_ski' THEN CONCAT(j.brand, ' ', j.model)
                       WHEN r.equipment_type = 'boat' THEN b.name
                       ELSE 'Unknown'
                   END AS equipment_name,
                   CASE
                       WHEN r.equipment_type = 'jet_ski' THEN j.registration_number
                       WHEN r.equipment_type = 'boat' THEN b.registration_number
                       ELSE ''
                   END AS registration_number,
                   CASE
                       WHEN r.equipment_type = 'jet_ski' THEN j.hourly_rate
                       WHEN r.equipment_type = 'boat' THEN b.hourly_rate
                       ELSE 0
                   END AS hourly_rate,
                   i.id AS invoice_id,
                   i.invoice_number,
                   i.status AS invoice_status
            FROM reservations r
            JOIN clients c ON r.client_id = c.id
            LEFT JOIN jet_skis j ON r.equipment_type = 'jet_ski' AND r.equipment_id = j.id
            LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
            LEFT JOIN invoices i ON r.id = i.reservation_id
            WHERE r.id = ?
        ");
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $reservation = $result->fetch_assoc();
            $title = "Reservation #" . $id;
            
            // Set up client data
            $client = [
                'id' => $reservation['client_id'],
                'name' => $reservation['first_name'] . ' ' . $reservation['last_name'],
                'first_name' => $reservation['first_name'],
                'last_name' => $reservation['last_name'],
                'email' => $reservation['email'],
                'phone' => $reservation['phone'],
                'address' => $reservation['address'],
                'city' => $reservation['city'],
                'state' => $reservation['state'],
                'zip_code' => $reservation['zip_code']
            ];
            
            // Set up equipment data
            $equipment = [
                'id' => $reservation['equipment_id'],
                'type' => $reservation['equipment_type'],
                'name' => $reservation['equipment_name'],
                'registration_number' => $reservation['registration_number'],
                'hourly_rate' => $reservation['hourly_rate']
            ];
        } else {
            $_SESSION['flash']['error'] = "Reservation not found";
            header('Location: index.php?page=reservations');
            exit;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
    }
}

// Use dummy data if no reservation found or no database connection
if ($reservation === null) {
    $currentDate = date('Y-m-d H:i:s');
    $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
    $tomorrowEnd = date('Y-m-d H:i:s', strtotime('+1 day +3 hours'));
    
    // Create dummy reservation data
    $reservation = [
        'id' => $id,
        'client_id' => 101,
        'equipment_type' => 'jet_ski',
        'equipment_id' => 1,
        'start_date' => $tomorrow,
        'end_date' => $tomorrowEnd,
        'total_amount' => 150.00,
        'deposit_amount' => 30.00,
        'payment_method' => 'credit_card',
        'status' => 'confirmed',
        'notes' => 'This is sample data as the actual reservation could not be found.',
        'created_at' => $currentDate,
        'updated_at' => $currentDate,
        'invoice_id' => null,
        'invoice_number' => null,
        'invoice_status' => null,
        'equipment_name' => 'Yamaha WaveRunner FX',
        'registration_number' => 'FL-12345-ABC',
        'hourly_rate' => 50.00
    ];
    
    $client = [
        'id' => 101,
        'name' => 'John Smith',
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'john.smith@example.com',
        'phone' => '555-123-4567',
        'address' => '123 Main St',
        'city' => 'Seaside',
        'state' => 'FL',
        'zip_code' => '32541'
    ];
    
    $equipment = [
        'id' => 1,
        'type' => 'jet_ski',
        'name' => 'Yamaha WaveRunner FX',
        'registration_number' => 'FL-12345-ABC',
        'hourly_rate' => 50.00
    ];
}

// Calculate rental duration in hours
$startDate = new DateTime($reservation['start_date']);
$endDate = new DateTime($reservation['end_date']);
$interval = $startDate->diff($endDate);
$hours = $interval->h + ($interval->days * 24);
$minutes = $interval->i;
$durationHours = $hours + ($minutes / 60);
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
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
        }
        .status-confirmed {
            background-color: #28a745;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-cancelled {
            background-color: #dc3545;
        }
        .status-completed {
            background-color: #6c757d;
        }
        .info-section {
            margin-bottom: 2rem;
        }
        .info-section h5 {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 1rem;
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
            <li><a href="index.php?page=reservations" class="active"><i class="bi bi-calendar-check"></i> Reservations</a></li>
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
                <a href="index.php?page=reservations" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reservations
                </a>
                <?php if ($reservation['status'] !== 'cancelled' && $reservation['status'] !== 'completed'): ?>
                    <a href="index.php?page=reservations/edit&id=<?php echo $reservation['id']; ?>" class="btn btn-primary ms-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                <?php endif; ?>
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
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="h5 mb-0">Reservation Information</h2>
                            <span class="badge status-<?php echo $reservation['status']; ?>">
                                <?php echo ucfirst($reservation['status']); ?>
                            </span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="detail-label">Reservation ID</div>
                                    <div><?php echo $reservation['id']; ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Created On</div>
                                    <div><?php echo isset($reservation['created_at']) ? date('M d, Y, h:i A', strtotime($reservation['created_at'])) : 'N/A'; ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Last Updated</div>
                                    <div><?php echo isset($reservation['updated_at']) ? date('M d, Y, h:i A', strtotime($reservation['updated_at'])) : 'N/A'; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="detail-label">Payment Method</div>
                                    <div><?php echo ucfirst(str_replace('_', ' ', $reservation['payment_method'] ?? 'N/A')); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Total Amount</div>
                                    <div>$<?php echo number_format($reservation['total_amount'] ?? 0, 2); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Deposit Paid</div>
                                    <div>$<?php echo number_format($reservation['deposit_amount'] ?? 0, 2); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h5>Rental Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="detail-label">Equipment Type</div>
                                        <div><?php echo ucfirst(str_replace('_', ' ', $equipment['type'])); ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="detail-label">Equipment Name</div>
                                        <div><?php echo htmlspecialchars($equipment['name']); ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="detail-label">Registration Number</div>
                                        <div><?php echo htmlspecialchars($equipment['registration_number'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="detail-label">Start Date & Time</div>
                                        <div><?php echo date('M d, Y, h:i A', strtotime($reservation['start_date'])); ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="detail-label">End Date & Time</div>
                                        <div><?php echo date('M d, Y, h:i A', strtotime($reservation['end_date'])); ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="detail-label">Duration</div>
                                        <div><?php echo number_format($durationHours, 1); ?> hours</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h5>Additional Services</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="instructorCheck" disabled <?php echo isset($reservation['add_instructor']) && $reservation['add_instructor'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="instructorCheck">
                                                Instructor
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="equipmentCheck" disabled <?php echo isset($reservation['add_equipment']) && $reservation['add_equipment'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="equipmentCheck">
                                                Safety Equipment
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="insuranceCheck" disabled <?php echo isset($reservation['add_insurance']) && $reservation['add_insurance'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="insuranceCheck">
                                                Premium Insurance
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($reservation['notes'])): ?>
                                        <div class="mb-3">
                                            <div class="detail-label">Notes</div>
                                            <div><?php echo nl2br(htmlspecialchars($reservation['notes'])); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($reservation['invoice_id'] !== null): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Invoice Information</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <span class="badge status-<?php echo $reservation['invoice_status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $reservation['invoice_status'])); ?>
                                </span>
                            </div>
                            <div>
                                <a href="index.php?page=invoices/view&id=<?php echo $reservation['invoice_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View Invoice #<?php echo $reservation['invoice_number']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Invoice</h5>
                        <p>No invoice has been created for this reservation yet.</p>
                        <a href="index.php?page=reservations/createInvoice&reservation_id=<?php echo $reservation['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-receipt"></i> Create Invoice
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Client Information</h5>
                        <div class="mb-3">
                            <div class="detail-label">Name</div>
                            <div><?php echo htmlspecialchars($client['name']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label">Email</div>
                            <div><?php echo htmlspecialchars($client['email']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label">Phone</div>
                            <div><?php echo htmlspecialchars($client['phone']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label">Address</div>
                            <div>
                                <?php echo htmlspecialchars($client['address'] ?? ''); ?><br>
                                <?php echo htmlspecialchars($client['city'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($client['state'] ?? ''); ?> 
                                <?php echo htmlspecialchars($client['zip_code'] ?? ''); ?>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="index.php?page=clients/view&id=<?php echo $client['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-person"></i> View Client Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Actions</h5>
                        <?php if ($reservation['status'] === 'confirmed'): ?>
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
                                    <i class="bi bi-check-circle"></i> Mark as Completed
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($reservation['status'] !== 'cancelled' && $reservation['status'] !== 'completed'): ?>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-x-circle"></i> Cancel Reservation
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Complete Reservation Modal -->
    <div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?page=reservations/complete" method="POST">
                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="completeModalLabel">Complete Reservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to mark this reservation as completed?</p>
                        <p>This will update the status and make the equipment available for new reservations.</p>
                        
                        <div class="mb-3">
                            <label for="completion_notes" class="form-label">Notes (optional)</label>
                            <textarea class="form-control" id="completion_notes" name="completion_notes" rows="3" placeholder="Any notes about the completed rental"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Complete Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Cancel Reservation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?page=reservations/cancel" method="POST">
                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel">Cancel Reservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Are you sure you want to cancel this reservation?
                        </div>
                        
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Cancellation Reason</label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="refundCheck" name="process_refund">
                            <label class="form-check-label" for="refundCheck">Process refund for deposit ($<?php echo number_format($reservation['deposit_amount'] ?? 0, 2); ?>)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 