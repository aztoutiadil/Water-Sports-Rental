<?php
// Initialize variables to prevent undefined variable errors
$title = "Process Payment";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Check and update database schema if needed
if (isset($conn)) {
    try {
        // Check if payments table exists, if not create it
        $tableCheckResult = $conn->query("SHOW TABLES LIKE 'payments'");
        if ($tableCheckResult && $tableCheckResult->num_rows == 0) {
            // Create the payments table
            $createTableQuery = "
                CREATE TABLE payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    payment_method VARCHAR(50) NOT NULL,
                    payment_date DATE NOT NULL,
                    transaction_id VARCHAR(100) NULL,
                    notes TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (invoice_id),
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
                )
            ";
            $conn->query($createTableQuery);
        } else {
            // Check if transaction_id column exists in payments table
            $columnResult = $conn->query("SHOW COLUMNS FROM payments LIKE 'transaction_id'");
            
            if ($columnResult && $columnResult->num_rows == 0) {
                // Column doesn't exist, so add it
                $alterTableQuery = "ALTER TABLE payments ADD COLUMN transaction_id VARCHAR(100) NULL AFTER payment_date";
                $conn->query($alterTableQuery);
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash']['warning'] = "Schema check warning: " . $e->getMessage();
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['id', 'amount', 'payment_method', 'payment_date'];
    $errors = [];
    
    // Check each required field
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // Validate amount is a positive number
    if (isset($_POST['amount']) && (!is_numeric($_POST['amount']) || floatval($_POST['amount']) <= 0)) {
        $errors[] = 'Amount must be a positive number';
    }
    
    // If validation passes
    if (empty($errors)) {
        // Get form data
        $invoice_id = intval($_POST['id']);
        $amount = floatval($_POST['amount']);
        $payment_method = $_POST['payment_method'];
        $payment_date = $_POST['payment_date'];
        $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : 'PAYMENT-' . date('YmdHis');
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        
        // Success flag
        $success = false;
        
        if (isset($conn)) {
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Get invoice details first to calculate new balance
                $invoiceStmt = $conn->prepare("
                    SELECT total_amount, deposit_amount, balance_due, status
                    FROM invoices
                    WHERE id = ?
                ");
                
                if ($invoiceStmt === false) {
                    throw new Exception("Error preparing invoice statement: " . $conn->error);
                }
                
                $invoiceStmt->bind_param("i", $invoice_id);
                $invoiceStmt->execute();
                $invoiceResult = $invoiceStmt->get_result();
                
                if ($invoiceResult && $invoiceResult->num_rows > 0) {
                    $invoice = $invoiceResult->fetch_assoc();
                    
                    // Calculate new balance due
                    $newBalanceDue = max(0, $invoice['balance_due'] - $amount);
                    
                    // Determine new status
                    $newStatus = 'partially_paid';
                    if ($newBalanceDue <= 0) {
                        $newStatus = 'paid';
                    }
                    
                    // Record payment
                    $paymentStmt = $conn->prepare("
                        INSERT INTO payments (
                            invoice_id, amount, payment_method, payment_date, transaction_id, notes
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?
                        )
                    ");
                    
                    if ($paymentStmt === false) {
                        throw new Exception("Error preparing payment statement: " . $conn->error);
                    }
                    
                    $paymentStmt->bind_param(
                        "idssss",
                        $invoice_id,
                        $amount,
                        $payment_method,
                        $payment_date,
                        $transaction_id,
                        $notes
                    );
                    
                    $paymentResult = $paymentStmt->execute();
                    $paymentStmt->close();
                    
                    if (!$paymentResult) {
                        throw new Exception("Error recording payment: " . $conn->error);
                    }
                    
                    // Update invoice
                    $updateStmt = $conn->prepare("
                        UPDATE invoices
                        SET balance_due = ?, 
                            status = ?,
                            payment_date = CASE WHEN ? = 'paid' THEN ? ELSE payment_date END
                        WHERE id = ?
                    ");
                    
                    if ($updateStmt === false) {
                        throw new Exception("Error preparing update statement: " . $conn->error);
                    }
                    
                    $updateStmt->bind_param(
                        "dsssi",
                        $newBalanceDue,
                        $newStatus,
                        $newStatus,
                        $payment_date,
                        $invoice_id
                    );
                    
                    $updateResult = $updateStmt->execute();
                    $updateStmt->close();
                    
                    if (!$updateResult) {
                        throw new Exception("Error updating invoice: " . $conn->error);
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    $success = true;
                    
                    $_SESSION['flash']['success'] = "Payment of $" . number_format($amount, 2) . " has been recorded successfully.";
                } else {
                    throw new Exception("Invoice not found");
                }
                
                $invoiceStmt->close();
                
            } catch (Exception $e) {
                // Roll back transaction on error
                $conn->rollback();
                $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
            }
        } else {
            // Demo mode - simulate success
            $success = true;
            $_SESSION['flash']['success'] = "Payment of $" . number_format($amount, 2) . " has been recorded successfully (Demo Mode).";
        }
        
        // Redirect back to the invoice view
        header("Location: index.php?page=invoices/view&id=" . $invoice_id);
        exit;
    } else {
        // Store errors in session
        $_SESSION['flash']['error'] = implode("<br>", $errors);
        // Redirect back to the invoice
        header("Location: index.php?page=invoices/view&id=" . intval($_POST['id']));
        exit;
    }
} else {
    // Not a POST request, redirect to invoices list
    header('Location: index.php?page=invoices');
    exit;
}
?> 