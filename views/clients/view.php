<?php
// Initialize variables to prevent undefined variable errors
$title = "Client Details";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get client ID from URL
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if client ID is provided
if (!$client_id) {
    $_SESSION['flash']['error'] = "No client ID specified";
    header('Location: index.php?page=clients');
    exit;
}

// Initialize client data
$client = null;
$reservations = [];
$documents = [];

// Fetch client data from database
if (isset($conn)) {
    try {
        // Get client details
        $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $client = $result->fetch_assoc();
        } else {
            $_SESSION['flash']['error'] = "Client not found";
            header('Location: index.php?page=clients');
            exit;
        }
        
        $stmt->close();
        
        // Get client reservations
        $reservationStmt = $conn->prepare("
            SELECT r.*, 
                   CASE WHEN r.equipment_type = 'jet_ski' 
                        THEN (SELECT CONCAT(brand, ' ', model) FROM jet_skis WHERE id = r.equipment_id)
                        ELSE (SELECT name FROM boats WHERE id = r.equipment_id)
                   END as equipment_name,
                   r.equipment_type
            FROM reservations r
            WHERE r.client_id = ?
            ORDER BY r.start_date DESC
        ");
        
        if ($reservationStmt !== false) {
            $reservationStmt->bind_param("i", $client_id);
            $reservationStmt->execute();
            $reservationResult = $reservationStmt->get_result();
            
            while ($row = $reservationResult->fetch_assoc()) {
                // Calculate duration
                $start = new DateTime($row['start_date']);
                $end = new DateTime($row['end_date']);
                $interval = $start->diff($end);
                $hours = $interval->h + ($interval->days * 24);
                
                $row['duration'] = $hours . ' hours';
                $reservations[] = $row;
            }
            
            $reservationStmt->close();
        }
        
    } catch (Exception $e) {
        error_log("Error fetching client data: " . $e->getMessage());
        $_SESSION['flash']['error'] = "Error fetching client data: " . $e->getMessage();
    }
}

// Use dummy data only if no client was found in database
if (!$client) {
    // Using dummy data as fallback
    $client = [
        'id' => $client_id,
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'john.smith@example.com',
        'phone' => '+1 555-123-4567',
        'address' => '123 Main St, Anytown, USA',
        'city' => 'Anytown',
        'state' => 'CA',
        'zip_code' => '12345',
        'country' => 'USA',
        'status' => 'active',
        'created_at' => '2023-01-15',
        'total_rentals' => 5,
        'total_spent' => 1250.75,
        'notes' => 'Preferred customer. Likes jet skis.',
        'registration_date' => '2023-01-01',
        'last_rental' => '2023-07-10',
        'birthdate' => '1970-01-01',
        'id_type' => 'Passport',
        'id_number' => 'P12345678'
    ];

    // Dummy data for reservations
    $reservations = [
        [
            'id' => 101,
            'date' => '2023-05-15',
            'equipment' => 'Jet Ski - Yamaha WaveRunner',
            'equipment_type' => 'Jet Ski',
            'equipment_name' => 'Yamaha WaveRunner',
            'start_date' => '2023-05-15 10:00:00',
            'end_date' => '2023-05-15 12:00:00',
            'duration' => '2 hours',
            'amount' => 150.00,
            'total_amount' => 150.00,
            'status' => 'completed'
        ],
        [
            'id' => 102,
            'date' => '2023-06-20',
            'equipment' => 'Boat - Bayliner 30ft',
            'equipment_type' => 'Boat',
            'equipment_name' => 'Bayliner 30ft',
            'start_date' => '2023-06-20 09:00:00',
            'end_date' => '2023-06-20 13:00:00',
            'duration' => '4 hours',
            'amount' => 450.00,
            'total_amount' => 450.00,
            'status' => 'completed'
        ],
        [
            'id' => 103,
            'date' => '2023-07-10',
            'equipment' => 'Jet Ski - Sea-Doo Spark',
            'equipment_type' => 'Jet Ski',
            'equipment_name' => 'Sea-Doo Spark',
            'start_date' => '2023-07-10 14:00:00',
            'end_date' => '2023-07-10 17:00:00',
            'duration' => '3 hours',
            'amount' => 225.00,
            'total_amount' => 225.00,
            'status' => 'upcoming'
        ]
    ];
}

// Dummy data for documents (since we don't have a documents table yet)
$documents = [
    [
        'id' => 201,
        'name' => 'ID Card.pdf',
        'type' => 'Identification',
        'uploaded_at' => '2023-01-15',
        'size' => '1.2 MB'
    ],
    [
        'id' => 202,
        'name' => 'Liability Waiver.pdf',
        'type' => 'Waiver',
        'uploaded_at' => '2023-01-15',
        'size' => '0.8 MB'
    ]
];

// Set ID document path if available
$client['id_document'] = $client['id_document_url'] ?? 'uploads/clients/id_passport.pdf';
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
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
        }
        .client-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .info-group {
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1rem;
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        .badge-active {
            background-color: #28a745;
        }
        .badge-inactive {
            background-color: #dc3545;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .document-preview {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .document-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .document-info {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }
        .document-info i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            color: var(--primary-color);
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
        <div class="top-bar">
            <h1 class="h4 mb-0">Client Details: <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h1>
            <div>
                <a href="index.php?page=clients" class="btn btn-outline-primary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Clients
                </a>
                <a href="index.php?page=clients/edit&id=<?php echo $client['id']; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit Client
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
            <!-- Client Profile Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <div class="client-avatar mx-auto">
                            <?php echo strtoupper(substr($client['first_name'], 0, 1) . substr($client['last_name'], 0, 1)); ?>
                        </div>
                        <h4 class="mb-1"><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($client['email']); ?></p>
                        
                        <?php
                        $statusClass = '';
                        switch($client['status']) {
                            case 'active':
                                $statusClass = 'badge-active';
                                break;
                            case 'inactive':
                                $statusClass = 'badge-inactive';
                                break;
                            case 'pending':
                                $statusClass = 'badge-pending';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $statusClass; ?> mb-3"><?php echo ucfirst($client['status']); ?></span>
                        
                        <div class="d-grid gap-2">
                            <a href="index.php?page=clients/createReservation&id=<?php echo $client['id']; ?>" class="btn btn-primary">
                                <i class="bi bi-calendar-plus"></i> Create Reservation
                            </a>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteClientModal">
                                <i class="bi bi-trash"></i> Delete Client
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Client Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Client Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h5 class="mb-1"><?php echo $client['total_rentals']; ?></h5>
                                <div class="text-muted">Total Rentals</div>
                            </div>
                            <div class="col-6 mb-3">
                                <h5 class="mb-1">$<?php echo number_format($client['total_spent'], 2); ?></h5>
                                <div class="text-muted">Total Spent</div>
                            </div>
                            <div class="col-6">
                                <h5 class="mb-1"><?php echo date('M d, Y', strtotime($client['registration_date'])); ?></h5>
                                <div class="text-muted">Registered</div>
                            </div>
                            <div class="col-6">
                                <h5 class="mb-1"><?php echo isset($client['last_rental']) ? date('M d, Y', strtotime($client['last_rental'])) : 'N/A'; ?></h5>
                                <div class="text-muted">Last Rental</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Client Details and History -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">
                                    <i class="bi bi-person me-1"></i> Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="reservations-tab" data-bs-toggle="tab" href="#reservations" role="tab" aria-controls="reservations" aria-selected="false">
                                    <i class="bi bi-calendar-check me-1"></i> Reservations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false">
                                    <i class="bi bi-file-earmark me-1"></i> Documents
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Client Details Tab -->
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">First Name</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['first_name']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">Last Name</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['last_name']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">Email</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['email']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">Phone</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['phone']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">Date of Birth</div>
                                            <div class="info-value"><?php echo date('M d, Y', strtotime($client['birthdate'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">ID Type & Number</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['id_type'] . ': ' . $client['id_number']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="info-group">
                                            <div class="info-label">Address</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['address']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">City</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['city']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">State/Province</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['state']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">Postal Code</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['zip_code']); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-group">
                                            <div class="info-label">Country</div>
                                            <div class="info-value"><?php echo htmlspecialchars($client['country']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($client['notes'])): ?>
                                <hr>
                                <div class="info-group">
                                    <div class="info-label">Notes</div>
                                    <div class="info-value"><?php echo nl2br(htmlspecialchars($client['notes'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Reservations Tab -->
                            <div class="tab-pane fade" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
                                <?php if (empty($reservations)): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i> This client has no reservations yet.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Reservation ID</th>
                                                    <th>Equipment</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reservations as $reservation): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="index.php?page=reservations/view&id=<?php echo $reservation['id']; ?>">
                                                                #<?php echo $reservation['id']; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($reservation['equipment_type'] . ': ' . $reservation['equipment_name']); ?>
                                                        </td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($reservation['start_date'])); ?></td>
                                                        <td><?php echo date('M d, Y H:i', strtotime($reservation['end_date'])); ?></td>
                                                        <td>$<?php echo number_format($reservation['total_amount'], 2); ?></td>
                                                        <td>
                                                            <?php
                                                            $statusBadge = '';
                                                            switch($reservation['status']) {
                                                                case 'confirmed':
                                                                    $statusBadge = 'bg-success';
                                                                    break;
                                                                case 'pending':
                                                                    $statusBadge = 'bg-warning text-dark';
                                                                    break;
                                                                case 'cancelled':
                                                                    $statusBadge = 'bg-danger';
                                                                    break;
                                                                case 'completed':
                                                                    $statusBadge = 'bg-info';
                                                                    break;
                                                                default:
                                                                    $statusBadge = 'bg-secondary';
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $statusBadge; ?>">
                                                                <?php echo ucfirst($reservation['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="index.php?page=reservations/view&id=<?php echo $reservation['id']; ?>" class="btn btn-outline-primary" title="View Reservation">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
                                                                <?php if ($reservation['status'] !== 'cancelled' && $reservation['status'] !== 'completed'): ?>
                                                                <a href="index.php?page=reservations/edit&id=<?php echo $reservation['id']; ?>" class="btn btn-outline-primary" title="Edit Reservation">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <a href="index.php?page=clients/createReservation&id=<?php echo $client['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i> New Reservation
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Documents Tab -->
                            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                <div class="mb-4">
                                    <h5>ID Document</h5>
                                    <?php if (!empty($client['id_document'])): ?>
                                        <div class="document-preview">
                                            <?php
                                            $fileExtension = pathinfo($client['id_document'], PATHINFO_EXTENSION);
                                            if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])):
                                            ?>
                                                <img src="<?php echo htmlspecialchars($client['id_document']); ?>" alt="ID Document" class="img-fluid">
                                            <?php else: ?>
                                                <div class="document-info">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <div>
                                                        <p class="mb-0"><?php echo htmlspecialchars(basename($client['id_document'])); ?></p>
                                                        <a href="<?php echo htmlspecialchars($client['id_document']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="bi bi-download"></i> Download Document
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i> No ID document has been uploaded for this client.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Client Modal -->
    <div class="modal fade" id="deleteClientModal" tabindex="-1" aria-labelledby="deleteClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteClientModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this client? This action cannot be undone.</p>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i> 
                        <strong>Warning:</strong> Deleting this client will also remove all associated records, including reservations and payment history.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="index.php?page=clients/delete&id=<?php echo $client['id']; ?>" class="btn btn-danger">Delete Client</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 