<?php
// Initialize variables to prevent undefined variable errors
$title = "Invoice Details";
$invoice = null;
$reservation = null;

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Get invoice ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to invoice list
if ($id <= 0) {
    $_SESSION['flash']['error'] = "No invoice ID specified";
    header('Location: index.php?page=invoices');
    exit;
}

// Fetch invoice data from database
if (isset($conn)) {
    try {
        // Get invoice details
        $stmt = $conn->prepare("
            SELECT i.*, 
                   r.id as reservation_id, 
                   r.start_date, 
                   r.end_date, 
                   r.equipment_type,
                   r.equipment_id,
                   c.id as client_id, 
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
                   END AS equipment_name
            FROM invoices i
            JOIN reservations r ON i.reservation_id = r.id
            JOIN clients c ON r.client_id = c.id
            LEFT JOIN jet_skis j ON r.equipment_type = 'jet_ski' AND r.equipment_id = j.id
            LEFT JOIN boats b ON r.equipment_type = 'boat' AND r.equipment_id = b.id
            WHERE i.id = ?
        ");
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $invoice = $result->fetch_assoc();
            $title = "Invoice #" . $invoice['invoice_number'];
            
            // Set up reservation data from invoice data
            $reservation = [
                'id' => $invoice['reservation_id'],
                'start_date' => $invoice['start_date'],
                'end_date' => $invoice['end_date'],
                'equipment_type' => $invoice['equipment_type'],
                'equipment_id' => $invoice['equipment_id'],
                'equipment_name' => $invoice['equipment_name']
            ];
            
            // Calculate some needed values
            $invoice['client_name'] = $invoice['first_name'] . ' ' . $invoice['last_name'];
            $invoice['client_address'] = $invoice['address'] . ', ' . $invoice['city'] . ', ' . $invoice['state'] . ' ' . $invoice['zip_code'];
        } else {
            $_SESSION['flash']['error'] = "Invoice not found";
            header('Location: index.php?page=invoices');
            exit;
        }
        
        $stmt->close();
        
        // Get payment history if available
        if (isset($invoice['id'])) {
            $pay_stmt = $conn->prepare("
                SELECT * FROM payments 
                WHERE invoice_id = ?
                ORDER BY payment_date DESC
            ");
            
            if ($pay_stmt !== false) {
                $pay_stmt->bind_param("i", $id);
                $pay_stmt->execute();
                $pay_result = $pay_stmt->get_result();
                
                $payments = [];
                while ($row = $pay_result->fetch_assoc()) {
                    $payments[] = $row;
                }
                
                $invoice['payments'] = $payments;
                $pay_stmt->close();
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
    }
}

// Use dummy data if no invoice found or no database connection
if ($invoice === null) {
    $currentDate = date('Y-m-d');
    // Create dummy invoice data
    $invoice = [
        'id' => $id,
        'invoice_number' => 'INV-2023-' . sprintf("%04d", $id),
        'reservation_id' => 101,
        'client_id' => 201,
        'client_name' => 'John Smith',
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'john.smith@example.com',
        'phone' => '555-123-4567',
        'address' => '123 Main St',
        'city' => 'Seaside',
        'state' => 'FL',
        'zip_code' => '32541',
        'client_address' => '123 Main St, Seaside, FL 32541',
        'issue_date' => $currentDate,
        'due_date' => date('Y-m-d', strtotime($currentDate . ' +7 days')),
        'payment_date' => null,
        'subtotal' => 500.00,
        'tax_rate' => 7.5,
        'tax_amount' => 37.50,
        'discount_amount' => 0.00,
        'total_amount' => 537.50,
        'deposit_amount' => 100.00,
        'balance_due' => 437.50,
        'notes' => 'This is sample data as the actual invoice could not be found.',
        'status' => 'partially_paid',
        'payments' => [
            [
                'id' => 301,
                'invoice_id' => $id,
                'amount' => 100.00,
                'payment_method' => 'credit_card',
                'payment_date' => date('Y-m-d', strtotime($currentDate . ' -2 days')),
                'transaction_id' => 'TXN-' . rand(10000, 99999),
                'notes' => 'Initial deposit'
            ]
        ]
    ];
    
    $reservation = [
        'id' => 101,
        'start_date' => date('Y-m-d H:i:s', strtotime($currentDate . ' +1 day 10:00:00')),
        'end_date' => date('Y-m-d H:i:s', strtotime($currentDate . ' +1 day 14:00:00')),
        'equipment_type' => 'jet_ski',
        'equipment_id' => 1,
        'equipment_name' => 'Yamaha WaveRunner FX'
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
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .invoice-header {
            margin-bottom: 2rem;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .invoice-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .invoice-table {
            margin-top: 2rem;
        }
        .invoice-total {
            margin-top: 1rem;
            text-align: right;
        }
        .payment-method {
            text-transform: capitalize;
        }
        .invoice-actions {
            margin-top: 2rem;
        }
        @media print {
            .sidebar, .top-bar, .invoice-actions, .btn, .no-print {
                display: none !important;
            }
            .main-content {
                margin-left: 0;
                padding: 0;
            }
            .card {
                box-shadow: none;
                border: none;
            }
            body {
                background-color: white;
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
            <div>
                <a href="index.php?page=invoices" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Invoices
                </a>
                <button type="button" class="btn btn-outline-primary ms-2" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="index.php?page=invoices/download&id=<?php echo $invoice['id']; ?>" class="btn btn-outline-success ms-2">
                    <i class="bi bi-download"></i> Download PDF
                </a>
                <?php if ($invoice['status'] !== 'paid' && $invoice['status'] !== 'cancelled'): ?>
                    <a href="index.php?page=invoices/edit&id=<?php echo $invoice['id']; ?>" class="btn btn-primary ms-2">
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

        <div class="card">
            <div class="card-body">
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h2>Water Sports Rental</h2>
                            <p>123 Beach Boulevard<br>
                            Destin, FL 32541<br>
                            Phone: (850) 555-1234<br>
                            Email: info@watersportsrental.com</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h1 class="invoice-number"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
                            <p>
                                <span class="badge status-<?php echo $invoice['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $invoice['status'])); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="invoice-info">
                    <div>
                        <h5>Bill To</h5>
                        <p>
                            <strong><?php echo htmlspecialchars($invoice['client_name']); ?></strong><br>
                            <?php echo htmlspecialchars($invoice['client_address'] ?? 'No address provided'); ?><br>
                            Phone: <?php echo htmlspecialchars($invoice['phone'] ?? 'N/A'); ?><br>
                            Email: <?php echo htmlspecialchars($invoice['email'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <div>
                        <h5>Invoice Details</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-end"><strong>Invoice Date:</strong></td>
                                <td><?php echo isset($invoice['issue_date']) ? date('M d, Y', strtotime($invoice['issue_date'])) : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td class="text-end"><strong>Due Date:</strong></td>
                                <td><?php echo isset($invoice['due_date']) ? date('M d, Y', strtotime($invoice['due_date'])) : 'N/A'; ?></td>
                            </tr>
                            <?php if ($invoice['status'] === 'paid' && isset($invoice['payment_date'])): ?>
                            <tr>
                                <td class="text-end"><strong>Paid Date:</strong></td>
                                <td><?php echo date('M d, Y', strtotime($invoice['payment_date'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="invoice-table">
                    <h5>Reservation Details</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($reservation['equipment_name'] ?? 'Equipment rental'); ?></strong><br>
                                    Reservation #<?php echo $reservation['id']; ?><br>
                                    Period: 
                                    <?php if (isset($reservation['start_date']) && isset($reservation['end_date'])): ?>
                                        <?php echo date('M d, Y H:i', strtotime($reservation['start_date'])); ?> to 
                                        <?php echo date('M d, Y H:i', strtotime($reservation['end_date'])); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">$<?php echo number_format($invoice['subtotal'] ?? 0, 2); ?></td>
                            </tr>
                            <?php if (($invoice['discount_amount'] ?? 0) > 0): ?>
                            <tr>
                                <td>Discount</td>
                                <td class="text-end">-$<?php echo number_format($invoice['discount_amount'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>Tax (<?php echo number_format($invoice['tax_rate'] ?? 0, 1); ?>%)</td>
                                <td class="text-end">$<?php echo number_format($invoice['tax_amount'] ?? 0, 2); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th class="text-end">$<?php echo number_format($invoice['total_amount'] ?? 0, 2); ?></th>
                            </tr>
                            <?php if (($invoice['deposit_amount'] ?? 0) > 0): ?>
                            <tr>
                                <th>Deposit Paid</th>
                                <th class="text-end">$<?php echo number_format($invoice['deposit_amount'], 2); ?></th>
                            </tr>
                            <tr>
                                <th>Balance Due</th>
                                <th class="text-end">$<?php echo number_format($invoice['balance_due'] ?? 0, 2); ?></th>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($invoice['payments'])): ?>
                <div class="mt-4">
                    <h5>Payment History</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice['payments'] as $payment): ?>
                            <tr>
                                <td><?php echo isset($payment['payment_date']) ? date('M d, Y', strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                                <td>$<?php echo number_format($payment['amount'] ?? 0, 2); ?></td>
                                <td class="payment-method"><?php echo str_replace('_', ' ', $payment['payment_method'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['notes'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!empty($invoice['notes'])): ?>
                <div class="mt-4">
                    <h5>Notes</h5>
                    <div class="p-3 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($invoice['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="invoice-actions no-print mt-4">
                    <?php if ($invoice['status'] !== 'paid' && $invoice['status'] !== 'cancelled'): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php if ($invoice['status'] !== 'paid'): ?>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                    <i class="bi bi-credit-card"></i> Record Payment
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle"></i> Cancel Invoice
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?page=invoices/recordPayment" method="POST">
                    <input type="hidden" name="id" value="<?php echo $invoice['id'] ?? 0; ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Record Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Payment Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="<?php echo number_format($invoice['balance_due'] ?? 0, 2, '.', ''); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="credit_card" selected>Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Invoice Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?page=invoices/cancel" method="POST">
                    <input type="hidden" name="id" value="<?php echo $invoice['id'] ?? 0; ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel">Confirm Cancellation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Are you sure you want to cancel this invoice?
                        </div>
                        <p>Cancelling this invoice will mark it as cancelled and it will no longer be considered in financial reports as active revenue.</p>
                        
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">Cancellation Reason</label>
                            <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No, Keep Invoice</button>
                        <button type="submit" class="btn btn-danger">Yes, Cancel Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 