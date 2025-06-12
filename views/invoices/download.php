<?php
// Initialize variables to prevent undefined variable errors
$title = "Download Invoice";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get invoice ID from URL
$invoice_id = $_GET['id'] ?? 0;
if (!$invoice_id) {
    $_SESSION['flash']['error'] = "Invoice ID is required";
    header('Location: index.php?page=invoices');
    exit;
}

// In a real implementation, fetch the invoice details from the database
// This is dummy data for demonstration purposes
$invoice = [
    'id' => $invoice_id,
    'number' => 'INV-' . str_pad($invoice_id, 5, '0', STR_PAD_LEFT),
    'date' => date('Y-m-d'),
    'due_date' => date('Y-m-d', strtotime('+30 days')),
    'client' => [
        'name' => 'John Smith',
        'email' => 'john.smith@example.com',
        'phone' => '+1 555-123-4567',
        'address' => '123 Main Street, City, Country'
    ],
    'company' => [
        'name' => 'Water Sports Rental',
        'email' => 'contact@watersportsrental.com',
        'phone' => '+1 555-987-6543',
        'address' => '456 Beach Road, Seaside, Ocean State',
        'website' => 'www.watersportsrental.com'
    ],
    'items' => [
        [
            'description' => 'Jet Ski Rental - Yamaha Waverunner',
            'hours' => 4,
            'rate' => 75.00,
            'amount' => 300.00
        ],
        [
            'description' => 'Instructor Service',
            'hours' => 1,
            'rate' => 50.00,
            'amount' => 50.00
        ],
        [
            'description' => 'Safety Equipment',
            'hours' => 1,
            'rate' => 25.00,
            'amount' => 25.00
        ]
    ],
    'subtotal' => 375.00,
    'tax_rate' => 10,
    'tax_amount' => 37.50,
    'total' => 412.50,
    'status' => 'paid',
    'payment_method' => 'Credit Card',
    'notes' => 'Thank you for your business!'
];

// Include TCPDF library
// In a real implementation, you would need to install TCPDF via composer
// For simplicity, we'll simulate the PDF generation process
if (isset($_GET['generate']) && $_GET['generate'] === 'true') {
    // Set headers to download PDF file
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $invoice['number'] . '.pdf"');
    
    // In a real implementation, generate PDF content here
    // For demonstration purposes, we'll just output a message
    echo "This would be the PDF content for invoice #{$invoice['number']}";
    exit;
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
            background: linear-gradient(135deg, var(--primary-color), #003080);
            padding: 1rem;
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
            transition: all 0.3s;
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
        }
        .invoice-preview {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .invoice-badge {
            padding: 0.5em 1em;
            border-radius: 50px;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .invoice-badge-paid {
            background-color: #28a745;
            color: white;
        }
        .invoice-badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .invoice-badge-unpaid {
            background-color: #dc3545;
            color: white;
        }
        .table th {
            font-weight: 600;
            color: #555;
        }
        .table-bottom-right {
            text-align: right;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: #003585;
        }
        @media print {
            .sidebar, .invoice-actions {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
            .invoice-preview {
                box-shadow: none;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Invoice Preview</h1>
            <div class="invoice-actions">
                <a href="index.php?page=invoices" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Invoices
                </a>
                <button onclick="window.print()" class="btn btn-outline-primary me-2">
                    <i class="bi bi-printer"></i> Print Invoice
                </button>
                <a href="index.php?page=invoices/download&id=<?php echo $invoice_id; ?>&generate=true" class="btn btn-primary">
                    <i class="bi bi-download"></i> Download PDF
                </a>
            </div>
        </div>
        
        <div class="invoice-preview">
            <div class="invoice-header">
                <div>
                    <h2><?php echo $invoice['company']['name']; ?></h2>
                    <p class="mb-1"><?php echo $invoice['company']['address']; ?></p>
                    <p class="mb-1"><?php echo $invoice['company']['email']; ?> | <?php echo $invoice['company']['phone']; ?></p>
                    <p class="mb-0"><?php echo $invoice['company']['website']; ?></p>
                </div>
                <div class="text-end">
                    <h1 class="h3 mb-3">INVOICE</h1>
                    <p class="mb-1"><strong>Invoice #:</strong> <?php echo $invoice['number']; ?></p>
                    <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y', strtotime($invoice['date'])); ?></p>
                    <p class="mb-3"><strong>Due Date:</strong> <?php echo date('F d, Y', strtotime($invoice['due_date'])); ?></p>
                    <?php 
                        $badgeClass = '';
                        switch($invoice['status']) {
                            case 'paid':
                                $badgeClass = 'invoice-badge-paid';
                                break;
                            case 'pending':
                                $badgeClass = 'invoice-badge-pending';
                                break;
                            case 'unpaid':
                                $badgeClass = 'invoice-badge-unpaid';
                                break;
                        }
                    ?>
                    <span class="invoice-badge <?php echo $badgeClass; ?>"><?php echo ucfirst($invoice['status']); ?></span>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Bill To:</h5>
                    <p class="mb-1"><strong><?php echo $invoice['client']['name']; ?></strong></p>
                    <p class="mb-1"><?php echo $invoice['client']['address']; ?></p>
                    <p class="mb-1"><?php echo $invoice['client']['email']; ?></p>
                    <p class="mb-0"><?php echo $invoice['client']['phone']; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Payment Method:</h5>
                    <p><?php echo $invoice['payment_method']; ?></p>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th class="text-center">Hours</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoice['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td class="text-center"><?php echo $item['hours']; ?></td>
                                <td class="text-end">$<?php echo number_format($item['rate'], 2); ?></td>
                                <td class="text-end">$<?php echo number_format($item['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <h5>Notes:</h5>
                        <p><?php echo htmlspecialchars($invoice['notes']); ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-bottom-right">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-end">Subtotal:</td>
                                <td class="text-end">$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-end">Tax (<?php echo $invoice['tax_rate']; ?>%):</td>
                                <td class="text-end">$<?php echo number_format($invoice['tax_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($invoice['total'], 2); ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-end">Amount Paid:</td>
                                <td class="text-end">$<?php echo $invoice['status'] === 'paid' ? number_format($invoice['total'], 2) : '0.00'; ?></td>
                            </tr>
                            <tr>
                                <td class="text-end"><strong>Balance Due:</strong></td>
                                <td class="text-end"><strong>$<?php echo $invoice['status'] === 'paid' ? '0.00' : number_format($invoice['total'], 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 