<?php
// Initialize variables to prevent undefined variable errors
$title = "Process Invoice";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Check and update database schema if needed
if (isset($conn)) {
    try {
        // Check if deposit_amount column exists in invoices table
        $checkColumnQuery = "SHOW COLUMNS FROM invoices LIKE 'deposit_amount'";
        $columnResult = $conn->query($checkColumnQuery);
        
        if ($columnResult && $columnResult->num_rows == 0) {
            // Column doesn't exist, so add it
            $alterTableQuery = "ALTER TABLE invoices ADD COLUMN deposit_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total_amount";
            $conn->query($alterTableQuery);
            
            // Also check for balance_due column and add it if needed
            $checkBalanceQuery = "SHOW COLUMNS FROM invoices LIKE 'balance_due'";
            $balanceResult = $conn->query($checkBalanceQuery);
            
            if ($balanceResult && $balanceResult->num_rows == 0) {
                $alterBalanceQuery = "ALTER TABLE invoices ADD COLUMN balance_due DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER deposit_amount";
                $conn->query($alterBalanceQuery);
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash']['warning'] = "Schema check warning: " . $e->getMessage();
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['reservation_id', 'invoice_number', 'issue_date', 'due_date', 'subtotal', 'tax_amount', 'total_amount', 'deposit_amount', 'balance_due'];
    $errors = [];
    
    // Check each required field
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    // If validation passes
    if (empty($errors)) {
        // Get form data
        $reservation_id = $_POST['reservation_id'];
        $invoice_number = $_POST['invoice_number'];
        $issue_date = $_POST['issue_date'];
        $due_date = $_POST['due_date'];
        $subtotal = floatval($_POST['subtotal']);
        $tax_rate = floatval($_POST['tax_rate']);
        $tax_amount = floatval($_POST['tax_amount']);
        $total_amount = floatval($_POST['total_amount']);
        $deposit_amount = floatval($_POST['deposit_amount']);
        $balance_due = floatval($_POST['balance_due']);
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        $status = $balance_due > 0 ? ($deposit_amount > 0 ? 'partially_paid' : 'pending') : 'paid';
        $payment_date = $balance_due <= 0 ? date('Y-m-d') : null;
        $discount_amount = isset($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
        
        // Save to database if connection is available
        $success = false;
        $invoice_id = 0;
        
        if (isset($conn)) {
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Insert invoice
                $stmt = $conn->prepare("
                    INSERT INTO invoices (
                        reservation_id, invoice_number, issue_date, due_date, 
                        subtotal, tax_rate, tax_amount, discount_amount, total_amount, 
                        deposit_amount, balance_due, notes, status, payment_date
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");
                
                if ($stmt === false) {
                    throw new Exception("Error preparing statement: " . $conn->error);
                }
                
                $stmt->bind_param(
                    "isssdddddddsss", 
                    $reservation_id, 
                    $invoice_number, 
                    $issue_date, 
                    $due_date, 
                    $subtotal, 
                    $tax_rate, 
                    $tax_amount, 
                    $discount_amount, 
                    $total_amount, 
                    $deposit_amount, 
                    $balance_due, 
                    $notes, 
                    $status, 
                    $payment_date
                );
                
                $result = $stmt->execute();
                
                if ($result) {
                    $invoice_id = $conn->insert_id;
                    
                    // If there was a deposit, record it as a payment
                    if ($deposit_amount > 0) {
                        // Get reservation payment method from reservations table
                        $paymentMethod = 'credit_card'; // default
                        $pmtStmt = $conn->prepare("SELECT payment_method FROM reservations WHERE id = ?");
                        if ($pmtStmt) {
                            $pmtStmt->bind_param("i", $reservation_id);
                            $pmtStmt->execute();
                            $pmtResult = $pmtStmt->get_result();
                            if ($pmtRow = $pmtResult->fetch_assoc()) {
                                $paymentMethod = $pmtRow['payment_method'];
                            }
                            $pmtStmt->close();
                        }
                        
                        // Record deposit as payment
                        $paymentStmt = $conn->prepare("
                            INSERT INTO payments (
                                invoice_id, amount, payment_method, payment_date, transaction_id, notes
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?
                            )
                        ");
                        
                        if ($paymentStmt) {
                            $paymentDate = date('Y-m-d');
                            $transactionId = 'DEPOSIT-' . date('YmdHis');
                            $paymentNotes = 'Initial deposit from reservation';
                            
                            $paymentStmt->bind_param(
                                "idssss",
                                $invoice_id,
                                $deposit_amount,
                                $paymentMethod,
                                $paymentDate,
                                $transactionId,
                                $paymentNotes
                            );
                            
                            $paymentStmt->execute();
                            $paymentStmt->close();
                        }
                    }
                    
                    // Mark the invoice as created in the reservation
                    $updateStmt = $conn->prepare("UPDATE reservations SET invoice_created = 1 WHERE id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("i", $reservation_id);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    $success = true;
                } else {
                    throw new Exception("Error executing statement: " . $stmt->error);
                }
                
                $stmt->close();
                
            } catch (Exception $e) {
                // Roll back transaction on error
                $conn->rollback();
                $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
            }
        } else {
            // Demo mode - simulate success
            $invoice_id = rand(1000, 9999);
            $success = true;
        }
        
        if ($success) {
            $_SESSION['flash']['success'] = "Invoice #{$invoice_number} has been created successfully";
            // Redirect to view the invoice
            header("Location: index.php?page=invoices/view&id=" . $invoice_id);
            exit;
        }
    } else {
        // Store errors in session
        $_SESSION['flash']['error'] = implode("<br>", $errors);
        // Redirect back to the form
        header('Location: index.php?page=invoices/create');
        exit;
    }
} else {
    // Not a POST request, redirect to the form
    header('Location: index.php?page=invoices/create');
    exit;
}
?> 