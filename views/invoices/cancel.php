<?php
// Initialize variables to prevent undefined variable errors
$title = "Cancel Invoice";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['id', 'cancel_reason'];
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
        $invoice_id = intval($_POST['id']);
        $cancel_reason = $_POST['cancel_reason'];
        
        // Success flag
        $success = false;
        
        if (isset($conn)) {
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Get invoice and reservation details
                $invoiceStmt = $conn->prepare("
                    SELECT i.id, i.status, i.balance_due, i.reservation_id, r.equipment_type, r.equipment_id  
                    FROM invoices i
                    JOIN reservations r ON i.reservation_id = r.id
                    WHERE i.id = ?
                ");
                
                if ($invoiceStmt === false) {
                    throw new Exception("Error preparing invoice statement: " . $conn->error);
                }
                
                $invoiceStmt->bind_param("i", $invoice_id);
                $invoiceStmt->execute();
                $invoiceResult = $invoiceStmt->get_result();
                
                if ($invoiceResult && $invoiceResult->num_rows > 0) {
                    $invoice = $invoiceResult->fetch_assoc();
                    
                    // Check if invoice is already cancelled
                    if ($invoice['status'] === 'cancelled') {
                        throw new Exception("This invoice is already cancelled");
                    }
                    
                    // Update invoice status
                    $updateStmt = $conn->prepare("
                        UPDATE invoices
                        SET status = 'cancelled', 
                            notes = CONCAT(IFNULL(notes, ''), '\n\nCANCELLED: ', ?)
                        WHERE id = ?
                    ");
                    
                    if ($updateStmt === false) {
                        throw new Exception("Error preparing update statement: " . $conn->error);
                    }
                    
                    $updateStmt->bind_param("si", $cancel_reason, $invoice_id);
                    $updateResult = $updateStmt->execute();
                    $updateStmt->close();
                    
                    if (!$updateResult) {
                        throw new Exception("Error updating invoice: " . $conn->error);
                    }
                    
                    // Update reservation status if needed
                    if ($invoice['reservation_id']) {
                        $resUpdateStmt = $conn->prepare("
                            UPDATE reservations
                            SET status = 'cancelled', 
                                notes = CONCAT(IFNULL(notes, ''), '\n\nCANCELLED: ', ?)
                            WHERE id = ?
                        ");
                        
                        if ($resUpdateStmt !== false) {
                            $resUpdateStmt->bind_param("si", $cancel_reason, $invoice['reservation_id']);
                            $resUpdateStmt->execute();
                            $resUpdateStmt->close();
                            
                            // Make equipment available again
                            if ($invoice['equipment_type'] && $invoice['equipment_id']) {
                                $table = $invoice['equipment_type'] === 'jet_ski' ? 'jet_skis' : 'boats';
                                $equipStmt = $conn->prepare("
                                    UPDATE {$table}
                                    SET status = 'available'
                                    WHERE id = ?
                                ");
                                
                                if ($equipStmt !== false) {
                                    $equipStmt->bind_param("i", $invoice['equipment_id']);
                                    $equipStmt->execute();
                                    $equipStmt->close();
                                }
                            }
                        }
                    }
                    
                    // Record cancellation note as a system event
                    $noteStmt = $conn->prepare("
                        INSERT INTO system_logs (
                            user_id, action_type, entity_type, entity_id, description, created_at
                        ) VALUES (
                            ?, 'cancel', 'invoice', ?, ?, NOW()
                        )
                    ");
                    
                    if ($noteStmt !== false) {
                        $userId = $_SESSION['user']['id'] ?? 1;
                        $description = "Invoice cancelled. Reason: " . $cancel_reason;
                        
                        $noteStmt->bind_param("iis", $userId, $invoice_id, $description);
                        $noteStmt->execute();
                        $noteStmt->close();
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    $success = true;
                    
                    $_SESSION['flash']['success'] = "Invoice has been cancelled successfully.";
                } else {
                    throw new Exception("Invoice not found");
                }
                
                $invoiceStmt->close();
                
            } catch (Exception $e) {
                // Roll back transaction on error
                $conn->rollback();
                $_SESSION['flash']['error'] = "Error: " . $e->getMessage();
            }
        } else {
            // Demo mode - simulate success
            $success = true;
            $_SESSION['flash']['success'] = "Invoice has been cancelled successfully (Demo Mode).";
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