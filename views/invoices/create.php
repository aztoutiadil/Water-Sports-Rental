<?php
// Initialize variables to prevent undefined variable errors
$title = "Create New Invoice";
$reservations = [];

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Fetch available reservations from database (those without invoices)
if (isset($conn)) {
    try {
        $stmt = $conn->prepare("
            SELECT r.*, 
                   c.first_name, 
                   c.last_name,
                   CASE 
                       WHEN r.equipment_type = 'jet_ski' THEN CONCAT(j.brand, ' ', j.model)
                       WHEN r.equipment_type = 'boat' THEN b.name
                       ELSE 'Unknown'
                   END AS equipment_name
            FROM reservations r
            JOIN clients c ON r.client_id = c.id
            LEFT JOIN jet_skis j ON r.equipment_type = 'jet_ski' AND r.equipment_id = j.id
            LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
            LEFT JOIN invoices i ON r.id = i.reservation_id
            WHERE i.id IS NULL
            ORDER BY r.start_date DESC
        ");
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
    }
}

// If no database connection, use sample data
if (empty($reservations)) {
    $currentDate = date('Y-m-d H:i:s');
    $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
    $nextWeek = date('Y-m-d H:i:s', strtotime('+1 week'));
    
    // Create dummy reservation data
    $reservations = [
        [
            'id' => 1,
            'client_id' => 101,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'equipment_type' => 'jet_ski',
            'equipment_id' => 1,
            'equipment_name' => 'Yamaha WaveRunner FX',
            'start_date' => $tomorrow,
            'end_date' => date('Y-m-d H:i:s', strtotime($tomorrow . ' +2 hours')),
            'total_amount' => 150.00,
            'deposit_amount' => 30.00,
            'payment_method' => 'credit_card',
            'status' => 'confirmed'
        ],
        [
            'id' => 2,
            'client_id' => 102,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'equipment_type' => 'boat',
            'equipment_id' => 1,
            'equipment_name' => 'Sea Ray 240',
            'start_date' => $nextWeek,
            'end_date' => date('Y-m-d H:i:s', strtotime($nextWeek . ' +4 hours')),
            'total_amount' => 350.00,
            'deposit_amount' => 70.00,
            'payment_method' => 'credit_card',
            'status' => 'confirmed'
        ]
    ];
}

// Handling the reservation ID from URL (for direct invoice creation)
$selected_reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : 0;
$selected_reservation = null;

if ($selected_reservation_id > 0) {
    foreach ($reservations as $res) {
        if ($res['id'] == $selected_reservation_id) {
            $selected_reservation = $res;
            break;
        }
    }
}

// Get current date for invoice defaults
$currentDate = date('Y-m-d');
$dueDate = date('Y-m-d', strtotime('+7 days'));

// Generate a unique invoice number
$invoicePrefix = 'INV-' . date('Y') . '-';
$nextInvoiceNumber = 1;

if (isset($conn)) {
    try {
        // Find the highest existing invoice number with the current year prefix
        $stmt = $conn->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) as max_number 
            FROM invoices 
            WHERE invoice_number LIKE ?
        ");
        
        if ($stmt !== false) {
            $prefixParam = $invoicePrefix . '%';
            $stmt->bind_param("s", $prefixParam);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $nextInvoiceNumber = ($row['max_number'] ?? 0) + 1;
            }
            
            $stmt->close();
        }
    } catch (Exception $e) {
        // If there's an error, use timestamp as fallback to ensure uniqueness
        $nextInvoiceNumber = date('His');
    }
}

// Ensure uniqueness by adding timestamp if needed
$generatedInvoiceNumber = $invoicePrefix . sprintf('%04d', $nextInvoiceNumber);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
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
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .form-section-title {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .reservation-card {
            cursor: pointer;
            transition: all 0.2s;
        }
        .reservation-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .reservation-card.selected {
            border: 2px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .reservation-summary {
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
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
            <h1 class="h4 mb-0"><?php echo htmlspecialchars($title); ?></h1>
            <a href="index.php?page=invoices" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Invoices
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

        <form action="index.php?page=invoices/store" method="POST" id="invoiceForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-card">
                        <div class="form-section">
                            <h3 class="form-section-title">Select Reservation</h3>
                            
                            <?php if (empty($reservations)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No uninvoiced reservations found. Create a reservation first.
                                </div>
                                <a href="index.php?page=reservations/create" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create Reservation
                                </a>
                            <?php else: ?>
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="reservationSearch" placeholder="Search reservations by client name or equipment...">
                                </div>
                                
                                <div class="row row-cols-1 row-cols-md-2 g-4" id="reservationCards">
                                    <?php foreach ($reservations as $reservation): ?>
                                        <div class="col">
                                            <div class="card reservation-card h-100 <?php echo ($selected_reservation_id == $reservation['id']) ? 'selected' : ''; ?>" 
                                                 data-reservation-id="<?php echo $reservation['id']; ?>" 
                                                 data-client-name="<?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?>"
                                                 data-equipment="<?php echo htmlspecialchars($reservation['equipment_name']); ?>"
                                                 data-total="<?php echo number_format($reservation['total_amount'], 2, '.', ''); ?>"
                                                 data-deposit="<?php echo number_format($reservation['deposit_amount'], 2, '.', ''); ?>"
                                                 onclick="selectReservation(this)">
                                                <div class="card-body">
                                                    <h5 class="card-title">Reservation #<?php echo $reservation['id']; ?></h5>
                                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></h6>
                                                    <p class="card-text">
                                                        <strong>Equipment:</strong> <?php echo htmlspecialchars($reservation['equipment_name']); ?><br>
                                                        <strong>Date:</strong> <?php echo date('M d, Y', strtotime($reservation['start_date'])); ?><br>
                                                        <strong>Time:</strong> <?php echo date('H:i', strtotime($reservation['start_date'])); ?> - <?php echo date('H:i', strtotime($reservation['end_date'])); ?><br>
                                                        <strong>Total:</strong> $<?php echo number_format($reservation['total_amount'], 2); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <input type="hidden" name="reservation_id" id="selectedReservationId" value="<?php echo $selected_reservation_id; ?>" required>
                            <?php endif; ?>
                        </div>

                        <div class="form-section mt-4" id="invoiceDetailsSection" style="<?php echo empty($selected_reservation) ? 'display:none;' : ''; ?>">
                            <h3 class="form-section-title">Invoice Details</h3>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                                           value="<?php echo $generatedInvoiceNumber; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label">Issue Date</label>
                                    <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?php echo $currentDate; ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo $dueDate; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                           value="7.5" min="0" max="100" step="0.1" 
                                           onchange="calculateTotals()">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" id="invoiceSummaryCol" style="<?php echo empty($selected_reservation) ? 'display:none;' : ''; ?>">
                    <div class="form-card">
                        <h3 class="h5 mb-3">Invoice Summary</h3>
                        
                        <div class="reservation-summary">
                            <div id="summaryClientName" class="h6">Client: <?php echo $selected_reservation ? htmlspecialchars($selected_reservation['first_name'] . ' ' . $selected_reservation['last_name']) : ''; ?></div>
                            <div id="summaryEquipment" class="mb-3">Equipment: <?php echo $selected_reservation ? htmlspecialchars($selected_reservation['equipment_name']) : ''; ?></div>
                            
                            <table class="table table-sm">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end">$<span id="summarySubtotal"><?php echo $selected_reservation ? number_format($selected_reservation['total_amount'], 2) : '0.00'; ?></span></td>
                                </tr>
                                <tr>
                                    <td>Tax (<span id="summaryTaxRate">7.5</span>%):</td>
                                    <td class="text-end">$<span id="summaryTax"><?php 
                                        echo $selected_reservation ? 
                                            number_format($selected_reservation['total_amount'] * 0.075, 2) : 
                                            '0.00'; 
                                    ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong>$<span id="summaryTotal"><?php 
                                        echo $selected_reservation ? 
                                            number_format($selected_reservation['total_amount'] * 1.075, 2) : 
                                            '0.00'; 
                                    ?></span></strong></td>
                                </tr>
                                <tr>
                                    <td>Deposit Paid:</td>
                                    <td class="text-end">$<span id="summaryDeposit"><?php 
                                        echo $selected_reservation ? 
                                            number_format($selected_reservation['deposit_amount'], 2) : 
                                            '0.00'; 
                                    ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Balance Due:</strong></td>
                                    <td class="text-end"><strong>$<span id="summaryBalance"><?php 
                                        echo $selected_reservation ? 
                                            number_format(($selected_reservation['total_amount'] * 1.075) - $selected_reservation['deposit_amount'], 2) : 
                                            '0.00'; 
                                    ?></span></strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <input type="hidden" name="subtotal" id="subtotalInput">
                        <input type="hidden" name="tax_amount" id="taxAmountInput">
                        <input type="hidden" name="total_amount" id="totalAmountInput">
                        <input type="hidden" name="deposit_amount" id="depositAmountInput">
                        <input type="hidden" name="balance_due" id="balanceDueInput">
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-receipt"></i> Create Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date pickers
            flatpickr("#issue_date", {
                dateFormat: "Y-m-d"
            });
            
            flatpickr("#due_date", {
                dateFormat: "Y-m-d"
            });
            
            // Initialize with selected reservation if any
            const selectedId = document.getElementById('selectedReservationId').value;
            if (selectedId) {
                calculateTotals();
                updateHiddenFields();
            }
            
            // Reservation search functionality
            document.getElementById('reservationSearch').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const cards = document.querySelectorAll('.reservation-card');
                
                cards.forEach(card => {
                    const clientName = card.getAttribute('data-client-name').toLowerCase();
                    const equipment = card.getAttribute('data-equipment').toLowerCase();
                    
                    if (clientName.includes(searchTerm) || equipment.includes(searchTerm)) {
                        card.closest('.col').style.display = '';
                    } else {
                        card.closest('.col').style.display = 'none';
                    }
                });
            });
        });
        
        function selectReservation(element) {
            // Remove selected class from all cards
            document.querySelectorAll('.reservation-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            element.classList.add('selected');
            
            // Get reservation data
            const id = element.getAttribute('data-reservation-id');
            const clientName = element.getAttribute('data-client-name');
            const equipment = element.getAttribute('data-equipment');
            const total = parseFloat(element.getAttribute('data-total'));
            const deposit = parseFloat(element.getAttribute('data-deposit'));
            
            // Update hidden input
            document.getElementById('selectedReservationId').value = id;
            
            // Update summary
            document.getElementById('summaryClientName').textContent = 'Client: ' + clientName;
            document.getElementById('summaryEquipment').textContent = 'Equipment: ' + equipment;
            
            // Show hidden sections
            document.getElementById('invoiceDetailsSection').style.display = '';
            document.getElementById('invoiceSummaryCol').style.display = '';
            
            calculateTotals();
            updateHiddenFields();
        }
        
        function calculateTotals() {
            // Get selected reservation card
            const selected = document.querySelector('.reservation-card.selected');
            if (!selected) return;
            
            const subtotal = parseFloat(selected.getAttribute('data-total'));
            const deposit = parseFloat(selected.getAttribute('data-deposit'));
            const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
            
            // Calculate values
            const taxAmount = subtotal * (taxRate / 100);
            const total = subtotal + taxAmount;
            const balance = total - deposit;
            
            // Update summary display
            document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
            document.getElementById('summaryTaxRate').textContent = taxRate.toFixed(1);
            document.getElementById('summaryTax').textContent = taxAmount.toFixed(2);
            document.getElementById('summaryTotal').textContent = total.toFixed(2);
            document.getElementById('summaryDeposit').textContent = deposit.toFixed(2);
            document.getElementById('summaryBalance').textContent = balance.toFixed(2);
            
            updateHiddenFields(subtotal, taxAmount, total, deposit, balance);
        }
        
        function updateHiddenFields(subtotal, taxAmount, total, deposit, balance) {
            // If no arguments provided, get values from summary elements
            if (arguments.length === 0) {
                subtotal = parseFloat(document.getElementById('summarySubtotal').textContent);
                taxAmount = parseFloat(document.getElementById('summaryTax').textContent);
                total = parseFloat(document.getElementById('summaryTotal').textContent);
                deposit = parseFloat(document.getElementById('summaryDeposit').textContent);
                balance = parseFloat(document.getElementById('summaryBalance').textContent);
            }
            
            // Update hidden form fields
            document.getElementById('subtotalInput').value = subtotal.toFixed(2);
            document.getElementById('taxAmountInput').value = taxAmount.toFixed(2);
            document.getElementById('totalAmountInput').value = total.toFixed(2);
            document.getElementById('depositAmountInput').value = deposit.toFixed(2);
            document.getElementById('balanceDueInput').value = balance.toFixed(2);
        }
    </script>
</body>
</html> 