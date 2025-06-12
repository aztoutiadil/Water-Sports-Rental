<?php
// Initialize variables to prevent undefined variable errors
$title = "Create Reservation";
$jet_ski_id = $_GET['jet_ski_id'] ?? '';
$boat_id = $_GET['boat_id'] ?? '';

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Get client ID from URL if available (for pre-filling client information)
$client_id = $_GET['client_id'] ?? null;

// Initialize clients array with dummy data if not in database context
$clients = [];

// Initialize client data
$client = null;
if ($client_id && isset($conn)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $client = $result->fetch_assoc();
        }
        
        $stmt->close();
    } catch (Exception $e) {
        // Handle errors
    }
}

// Get all clients for dropdown - moved outside the if block to always execute when DB connection exists
if (isset($conn)) {
    try {
        // Debug query to check total number of clients in the database
        $countStmt = $conn->query("SELECT COUNT(*) as total FROM clients");
        $countRow = $countStmt->fetch_assoc();
        $totalClients = $countRow['total'];
        
        // Get all clients for dropdown - no filters to ensure all clients are shown
        $clientsStmt = $conn->prepare("SELECT id, first_name, last_name, email FROM clients ORDER BY first_name, last_name");
        if ($clientsStmt !== false) {
            $clientsStmt->execute();
            $result = $clientsStmt->get_result();
            $clients = [];
            while ($row = $result->fetch_assoc()) {
                $clients[] = $row;
            }
            $clientsStmt->close();
        }
    } catch (Exception $e) {
        // Handle errors
        error_log("Error fetching clients: " . $e->getMessage());
    }
}

// For demo purposes, provide some dummy data if no database or client found
if (!isset($conn) || empty($clients)) {
    // Use dummy client data if database connection is not available
    $clients = [
        [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
        ],
        [
            'id' => 2,
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'sarah.j@example.com',
        ],
        [
            'id' => 3,
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'michael.brown@example.com',
        ],
        [
            'id' => 4,
            'first_name' => 'Emily',
            'last_name' => 'Wilson',
            'email' => 'emily.w@example.com',
        ],
    ];
    
    // Only use dummy client data if client ID was provided but not found in DB
    if ($client_id) {
        foreach ($clients as $c) {
            if ($c['id'] == $client_id) {
                $client = $c;
                break;
            }
        }
        
        if (!$client) {
            $client = [
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1 555-123-4567'
            ];
        }
    }
}

// Load equipment lists (jet skis and boats)
$jet_skis = [];
$boats = [];

if (isset($conn)) {
    try {
        // Get available jet skis
        $jet_ski_stmt = $conn->prepare("SELECT * FROM jet_skis WHERE status = 'available'");
        if ($jet_ski_stmt !== false) {
            $jet_ski_stmt->execute();
            $result = $jet_ski_stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['display_name'] = $row['brand'] . ' ' . $row['model'] . ' - $' . number_format($row['hourly_rate'], 2) . '/hour';
                $jet_skis[] = $row;
            }
            $jet_ski_stmt->close();
        }
        
        // Get available boats
        $boat_stmt = $conn->prepare("SELECT * FROM boats WHERE status = 'available'");
        if ($boat_stmt !== false) {
            $boat_stmt->execute();
            $result = $boat_stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                // Make sure name key exists, otherwise use brand and model or a default name
                $boatName = $row['name'] ?? ($row['brand'] ?? '') . ' ' . ($row['model'] ?? 'Boat');
                $row['display_name'] = $boatName . ' (' . ($row['length'] ?? '0') . 'ft) - $' . number_format($row['hourly_rate'] ?? 0, 2) . '/hour';
                $boats[] = $row;
            }
            $boat_stmt->close();
        }
    } catch (Exception $e) {
        // Handle errors
    }
}

// Use dummy data if no equipment data from database
if (empty($jet_skis)) {
    $jet_skis = [
        ['id' => 1, 'brand' => 'Yamaha', 'model' => 'WaveRunner FX', 'hourly_rate' => 75.00, 'display_name' => 'Yamaha WaveRunner FX - $75.00/hour'],
        ['id' => 2, 'brand' => 'Sea-Doo', 'model' => 'GTI SE', 'hourly_rate' => 65.00, 'display_name' => 'Sea-Doo GTI SE - $65.00/hour'],
        ['id' => 3, 'brand' => 'Kawasaki', 'model' => 'Ultra 310LX', 'hourly_rate' => 85.00, 'display_name' => 'Kawasaki Ultra 310LX - $85.00/hour']
    ];
}

if (empty($boats)) {
    $boats = [
        ['id' => 1, 'name' => 'Luxury Cruiser', 'length' => 30, 'hourly_rate' => 150.00, 'display_name' => 'Luxury Cruiser (30ft) - $150.00/hour'],
        ['id' => 2, 'name' => 'Sport Yacht', 'length' => 25, 'hourly_rate' => 120.00, 'display_name' => 'Sport Yacht (25ft) - $120.00/hour'],
        ['id' => 3, 'name' => 'Family Pontoon', 'length' => 22, 'hourly_rate' => 90.00, 'display_name' => 'Family Pontoon (22ft) - $90.00/hour']
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background: #003585;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-section-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .equipment-type {
            margin-bottom: 1.5rem;
        }
        
        .equipment-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all var(--transition-speed);
        }
        
        .equipment-card:hover {
            border-color: var(--secondary-color);
            background-color: rgba(0, 153, 255, 0.05);
        }
        
        .equipment-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(0, 67, 168, 0.05);
        }
        
        .equipment-card .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .equipment-card .equipment-name {
            font-weight: 600;
        }
        
        .equipment-card .equipment-rate {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .time-slots {
            margin-bottom: 1.5rem;
        }
        
        .pricing-summary {
            background-color: rgba(0, 153, 255, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1.5rem;
        }
        
        .pricing-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .pricing-total {
            font-weight: 700;
            font-size: 1.2rem;
            border-top: 1px solid #ddd;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
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
            <h1>Create Reservation</h1>
            <a href="index.php?page=reservations" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reservations
            </a>
        </div>
        
        <div class="form-card">
            <form action="index.php?page=reservations/store" method="POST">
                <!-- Client Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-person"></i> Client Information</h3>
                    
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Select Client</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            <option value="">-- Select Client --</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $client_id == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?> (<?php echo htmlspecialchars($c['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <a href="index.php?page=clients/create" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> Add New Client
                        </a>
                    </div>
                </div>
                
                <!-- Equipment Selection -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-tsunami"></i> Equipment Selection</h3>
                    
                    <div class="equipment-type">
                        <label class="form-label">Equipment Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="equipment_type" id="type_jet_ski" value="jet_ski" checked>
                                <label class="form-check-label" for="type_jet_ski">
                                    Jet Ski
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="equipment_type" id="type_boat" value="boat">
                                <label class="form-check-label" for="type_boat">
                                    Tourist Boat
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Jet Skis Selection -->
                    <div id="jet_ski_selection">
                        <label class="form-label">Select Jet Ski</label>
                        <div class="row">
                            <?php foreach ($jet_skis as $jet_ski): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="equipment-card <?php echo $jet_ski_id == $jet_ski['id'] ? 'selected' : ''; ?>" onclick="selectEquipment('jet_ski', <?php echo $jet_ski['id']; ?>)">
                                        <div class="card-header">
                                            <span class="equipment-name"><?php echo htmlspecialchars($jet_ski['brand'] . ' ' . $jet_ski['model']); ?></span>
                                            <span class="equipment-rate">$<?php echo htmlspecialchars($jet_ski['hourly_rate']); ?>/hr</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">ID: JS-<?php echo $jet_ski['id']; ?></span>
                                            <span class="badge bg-success">Available</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="jet_ski_id" id="selected_jet_ski_id" value="<?php echo $jet_ski_id; ?>">
                    </div>
                    
                    <!-- Boats Selection -->
                    <div id="boat_selection" style="display: none;">
                        <label class="form-label">Select Boat</label>
                        <div class="row">
                            <?php foreach ($boats as $boat): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="equipment-card <?php echo $boat_id == $boat['id'] ? 'selected' : ''; ?>" onclick="selectEquipment('boat', <?php echo $boat['id']; ?>)">
                                        <div class="card-header">
                                            <span class="equipment-name"><?php echo htmlspecialchars($boat['name'] ?? ($boat['brand'] ?? '') . ' ' . ($boat['model'] ?? 'Boat')); ?></span>
                                            <span class="equipment-rate">$<?php echo htmlspecialchars($boat['hourly_rate']); ?>/hr</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">ID: B-<?php echo $boat['id']; ?></span>
                                            <span class="badge bg-success">Available</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="boat_id" id="selected_boat_id" value="<?php echo $boat_id; ?>">
                    </div>
                </div>
                
                <!-- Reservation Time -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-calendar"></i> Reservation Time</h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date & Time</label>
                            <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Select date and time" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date & Time</label>
                            <input type="text" class="form-control" id="end_date" name="end_date" placeholder="Select date and time" required>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Services -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-plus-circle"></i> Additional Services</h3>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="add_instructor" name="add_instructor" value="1">
                                <label class="form-check-label" for="add_instructor">
                                    Instructor / Guide ($50/hour)
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="add_equipment" name="add_equipment" value="1">
                                <label class="form-check-label" for="add_equipment">
                                    Safety Equipment ($25 flat)
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="add_insurance" name="add_insurance" value="1">
                                <label class="form-check-label" for="add_insurance">
                                    Premium Insurance ($35 flat)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-credit-card"></i> Payment Information</h3>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="deposit_amount" class="form-label">Deposit Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" min="0" step="0.01" value="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="pricing-summary">
                        <h5 class="mb-3">Reservation Summary</h5>
                        
                        <div class="pricing-row">
                            <span>Duration</span>
                            <span><span id="rental-hours">0</span> hours</span>
                        </div>
                        
                        <div class="pricing-row">
                            <span>Base Price</span>
                            <span>$<span id="base-price">0.00</span>/hour</span>
                        </div>
                        
                        <div class="pricing-row">
                            <span>Equipment Rental</span>
                            <span>$<span id="rental-cost">0.00</span></span>
                        </div>
                        
                        <div class="pricing-row">
                            <span>Additional Services</span>
                            <span>$<span id="additional-services">0.00</span></span>
                        </div>
                        
                        <div class="pricing-row">
                            <span>Subtotal</span>
                            <span>$<span id="subtotal">0.00</span></span>
                        </div>
                        
                        <div class="pricing-row">
                            <span>Tax (10%)</span>
                            <span>$<span id="tax">0.00</span></span>
                        </div>
                        
                        <div class="pricing-row pricing-total">
                            <span>Total</span>
                            <span>$<span id="total">0.00</span></span>
                        </div>
                        <input type="hidden" name="total_amount" id="total_amount" value="0">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php?page=reservations" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Reservation</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date pickers
        flatpickr("#start_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            defaultDate: new Date().setHours(new Date().getHours() + 1)
        });
        
        flatpickr("#end_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            defaultDate: new Date().setHours(new Date().getHours() + 3)
        });
        
        // Equipment type toggle
        document.querySelectorAll('input[name="equipment_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'jet_ski') {
                    document.getElementById('jet_ski_selection').style.display = 'block';
                    document.getElementById('boat_selection').style.display = 'none';
                } else {
                    document.getElementById('jet_ski_selection').style.display = 'none';
                    document.getElementById('boat_selection').style.display = 'block';
                }
                calculatePrice();
            });
        });
        
        // Equipment selection
        function selectEquipment(type, id) {
            if (type === 'jet_ski') {
                document.getElementById('selected_jet_ski_id').value = id;
                document.querySelectorAll('#jet_ski_selection .equipment-card').forEach(card => {
                    card.classList.remove('selected');
                });
            } else {
                document.getElementById('selected_boat_id').value = id;
                document.querySelectorAll('#boat_selection .equipment-card').forEach(card => {
                    card.classList.remove('selected');
                });
            }
            
            event.currentTarget.classList.add('selected');
            calculatePrice();
        }

        // Automatic price calculation based on equipment type and duration
        function calculatePrice() {
            // Get equipment type
            const equipmentType = document.querySelector('input[name="equipment_type"]:checked').value;
            
            // Get selected equipment and its hourly rate
            let basePrice = 0;
            
            if (equipmentType === 'jet_ski') {
                const jetSkiId = document.getElementById('selected_jet_ski_id').value;
                if (jetSkiId) {
                    const selectedCard = document.querySelector(`#jet_ski_selection .equipment-card.selected .equipment-rate`);
                    if (selectedCard) {
                        basePrice = parseFloat(selectedCard.textContent.replace('$', '').replace('/hr', ''));
                    }
                }
            } else if (equipmentType === 'boat') {
                const boatId = document.getElementById('selected_boat_id').value;
                if (boatId) {
                    const selectedCard = document.querySelector(`#boat_selection .equipment-card.selected .equipment-rate`);
                    if (selectedCard) {
                        basePrice = parseFloat(selectedCard.textContent.replace('$', '').replace('/hr', ''));
                    }
                }
            }
            
            // Get start and end dates
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            // Calculate hours of rental (duration)
            let hours = 0;
            if (startDateInput.value && endDateInput.value) {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    // Calculate difference in milliseconds
                    const diff = endDate - startDate;
                    // Convert to hours and round up
                    hours = Math.max(1, Math.ceil(diff / (1000 * 60 * 60)));
                }
            }
            
            // Calculate base rental cost
            const rentalCost = basePrice * hours;
            
            // Calculate additional services
            let additionalServices = 0;
            if (document.getElementById('add_instructor').checked) {
                additionalServices += 50 * hours; // $50/hour for instructor
            }
            if (document.getElementById('add_equipment').checked) {
                additionalServices += 25; // $25 flat for safety equipment
            }
            if (document.getElementById('add_insurance').checked) {
                additionalServices += 35; // $35 flat for premium insurance
            }
            
            // Calculate subtotal
            const subtotal = rentalCost + additionalServices;
            
            // Calculate tax (assuming 10% tax rate)
            const tax = subtotal * 0.10;
            
            // Calculate total
            const total = subtotal + tax;
            
            // Update pricing summary in the DOM
            document.getElementById('rental-hours').textContent = hours;
            document.getElementById('base-price').textContent = basePrice.toFixed(2);
            document.getElementById('rental-cost').textContent = rentalCost.toFixed(2);
            document.getElementById('additional-services').textContent = additionalServices.toFixed(2);
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('tax').textContent = tax.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2);
            
            // Update hidden total field for form submission
            document.getElementById('total_amount').value = total.toFixed(2);
        }
        
        // Add event listeners for price calculation
        document.addEventListener('DOMContentLoaded', function() {
            // Initial calculation
            calculatePrice();
            
            // Equipment type selection
            document.querySelectorAll('input[name="equipment_type"]').forEach(radio => {
                radio.addEventListener('change', calculatePrice);
            });
            
            // Date and time changes
            document.getElementById('start_date').addEventListener('change', calculatePrice);
            document.getElementById('end_date').addEventListener('change', calculatePrice);
            
            // Additional services
            document.getElementById('add_instructor').addEventListener('change', calculatePrice);
            document.getElementById('add_equipment').addEventListener('change', calculatePrice);
            document.getElementById('add_insurance').addEventListener('change', calculatePrice);
        });
    </script>
</body>
</html>

/*
 * Need to create a store.php file in views/reservations/ to handle form submission
 * with the following functionality:
 * 1. Validate all required fields
 * 2. Insert record into reservations table
 * 3. Redirect to reservations list with success message
 */
?> 