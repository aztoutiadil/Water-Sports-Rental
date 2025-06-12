<?php
// Check if previous steps are completed
if (empty($_SESSION['booking']['equipment_type']) || empty($_SESSION['booking']['equipment_id']) || 
    empty($_SESSION['booking']['start_date']) || empty($_SESSION['booking']['end_date']) ||
    empty($_SESSION['booking']['client']) || empty($_SESSION['booking']['payment'])) {
    header('Location: booking.php?step=1');
    exit;
}

// Process the actual booking
$bookingComplete = false;
$bookingId = null;
$errorMessage = null;

// Only process if not already processed
if (!isset($_SESSION['booking']['processed']) || $_SESSION['booking']['processed'] !== true) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get booking data
        $equipmentType = $_SESSION['booking']['equipment_type'];
        $equipmentId = $_SESSION['booking']['equipment_id'];
        $startDate = $_SESSION['booking']['start_date'];
        $endDate = $_SESSION['booking']['end_date'];
        $totalAmount = $_SESSION['booking']['total_amount'];
        $depositAmount = $_SESSION['booking']['deposit_amount'];
        $client = $_SESSION['booking']['client'];
        $payment = $_SESSION['booking']['payment'];
        
        // Check if client exists, if not create a new one
        $clientId = null;
        if (isset($_SESSION['booking']['client_id'])) {
            $clientId = $_SESSION['booking']['client_id'];
            
            // Update client information
            $sql = "UPDATE clients SET 
                    phone = ?, 
                    address = ?, 
                    city = ?, 
                    state = ?, 
                    zip_code = ?, 
                    country = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", 
                $client['phone'], 
                $client['address'], 
                $client['city'], 
                $client['state'], 
                $client['zip_code'], 
                $client['country'], 
                $clientId
            );
            $stmt->execute();
        } else {
            // Insert new client
            $sql = "INSERT INTO clients (
                    first_name, last_name, email, phone, 
                    address, city, state, zip_code, country, 
                    id_type, id_number, id_document_url, notes, 
                    status, registration_date, total_rentals, total_spent
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', CURDATE(), 0, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssssss", 
                $client['first_name'], 
                $client['last_name'], 
                $client['email'], 
                $client['phone'], 
                $client['address'], 
                $client['city'], 
                $client['state'], 
                $client['zip_code'], 
                $client['country'], 
                $client['id_type'], 
                $client['id_number'], 
                $client['id_document_url'] ?? null, 
                $client['notes']
            );
            $stmt->execute();
            $clientId = $conn->insert_id;
        }
        
        // Insert reservation
        $status = ($payment['method'] === 'cash') ? 'pending' : 'confirmed';
        $depositPaid = ($payment['method'] === 'cash') ? 0 : 1;
        
        $sql = "INSERT INTO reservations (
                client_id, equipment_type, equipment_id, 
                start_date, end_date, total_amount, 
                deposit_amount, deposit_paid, status, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssddiss", 
            $clientId, 
            $equipmentType, 
            $equipmentId, 
            $startDate, 
            $endDate, 
            $totalAmount, 
            $depositAmount, 
            $depositPaid, 
            $status, 
            $client['notes']
        );
        $stmt->execute();
        $reservationId = $conn->insert_id;
        
        // If payment is not cash, create an invoice
        if ($payment['method'] !== 'cash') {
            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($reservationId, 4, '0', STR_PAD_LEFT);
            
            // Calculate tax (assume 7% tax rate)
            $taxRate = 7.00;
            $taxAmount = $totalAmount * ($taxRate / 100);
            $totalWithTax = $totalAmount + $taxAmount;
            
            // Insert invoice
            $sql = "INSERT INTO invoices (
                    invoice_number, reservation_id, issue_date, due_date,
                    subtotal, tax_rate, tax_amount, total_amount,
                    deposit_paid, balance_due, status
                ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $balanceDue = $totalWithTax - $depositAmount;
            $invoiceStatus = $balanceDue > 0 ? 'partially_paid' : 'paid';
            $dueDate = date('Y-m-d', strtotime($startDate));
            $stmt->bind_param("sissddddds", 
                $invoiceNumber, 
                $reservationId, 
                $dueDate, 
                $totalAmount, 
                $taxRate, 
                $taxAmount, 
                $totalWithTax, 
                $depositAmount, 
                $balanceDue, 
                $invoiceStatus
            );
            $stmt->execute();
            $invoiceId = $conn->insert_id;
            
            // Record payment for the deposit
            if ($depositAmount > 0) {
                $sql = "INSERT INTO payments (
                        invoice_id, amount, payment_date, payment_method, transaction_reference
                    ) VALUES (?, ?, CURDATE(), ?, ?)";
                $stmt = $conn->prepare($sql);
                $transactionReference = $payment['transaction_id'] ?? null;
                $stmt->bind_param("idss", 
                    $invoiceId, 
                    $depositAmount, 
                    $payment['method'], 
                    $transactionReference
                );
                $stmt->execute();
            }
        }
        
        // Update equipment status
        $sql = "UPDATE " . ($equipmentType === 'jet_ski' ? 'jet_skis' : 'boats') . " 
                SET status = 'unavailable' 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $equipmentId);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Mark booking as processed and store ID
        $_SESSION['booking']['processed'] = true;
        $_SESSION['booking']['reservation_id'] = $reservationId;
        
        $bookingComplete = true;
        $bookingId = $reservationId;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $errorMessage = "An error occurred while processing your booking: " . $e->getMessage();
    }
}

// Get equipment and booking details for display
$equipmentType = $_SESSION['booking']['equipment_type'];
$equipmentId = $_SESSION['booking']['equipment_id'];
$equipment = getEquipmentDetails($equipmentType, $equipmentId, $conn);
$startDate = new DateTime($_SESSION['booking']['start_date']);
$endDate = new DateTime($_SESSION['booking']['end_date']);
$totalAmount = $_SESSION['booking']['total_amount'];
$depositAmount = $_SESSION['booking']['deposit_amount'];
$client = $_SESSION['booking']['client'];
$payment = $_SESSION['booking']['payment'];
$reservationId = $_SESSION['booking']['reservation_id'] ?? null;

// Generate booking reference
$bookingReference = 'WSR-' . date('Ymd') . '-' . str_pad($reservationId, 4, '0', STR_PAD_LEFT);
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if ($bookingComplete || $reservationId): ?>
            <div class="text-center mb-4">
                <div class="display-1 text-success mb-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h2>Booking Confirmed!</h2>
                <p class="lead">Your reservation has been successfully processed.</p>
                <div class="alert alert-info">
                    <strong>Booking Reference:</strong> <?php echo $bookingReference; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Booking Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Equipment:</strong> 
                            <?php echo htmlspecialchars($equipment['display_name']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Type:</strong> 
                            <?php echo $equipmentType === 'jet_ski' ? 'Jet Ski' : 'Tourist Boat'; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Start Date:</strong> 
                            <?php echo $startDate->format('M d, Y h:i A'); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>End Date:</strong> 
                            <?php echo $endDate->format('M d, Y h:i A'); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Customer:</strong> 
                            <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong> 
                            <?php echo htmlspecialchars($client['email']); ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Total Amount:</strong> 
                            <span class="text-primary">$<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Payment Method:</strong> 
                            <?php echo ucfirst(str_replace('_', ' ', $payment['method'])); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Payment Status:</strong> 
                            <?php if ($payment['method'] === 'cash'): ?>
                                <span class="badge bg-warning text-dark">Pay at Location</span>
                            <?php else: ?>
                                <span class="badge bg-success">Deposit Paid</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($payment['method'] !== 'cash'): ?>
                                <strong>Remaining Balance:</strong> 
                                <span class="text-success">$<?php echo number_format($totalAmount - $depositAmount, 2); ?></span>
                                <small class="text-muted d-block">(To be paid at location)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Next Steps</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="me-3 text-primary">
                            <i class="bi bi-envelope-check fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold">Check Your Email</h6>
                            <p class="mb-0">We've sent a confirmation email to <?php echo htmlspecialchars($client['email']); ?> with all your booking details.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="me-3 text-primary">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold">Location & Check-in</h6>
                            <p class="mb-0">Please arrive at our rental location at least 30 minutes before your scheduled start time. Don't forget to bring your ID and booking reference.</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="me-3 text-primary">
                            <i class="bi bi-question-circle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold">Questions?</h6>
                            <p class="mb-0">If you have any questions or need to make changes to your booking, please contact us at (123) 456-7890 or info@watersportsrental.com.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center mb-4">
                <div class="display-1 text-danger mb-3">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h2>Booking Error</h2>
                <p class="lead">There was a problem processing your booking.</p>
                <div class="alert alert-danger">
                    <?php echo $errorMessage ?? 'An unexpected error occurred. Please try again or contact our support team.'; ?>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="booking.php?step=5" class="btn btn-outline-secondary me-2">Go Back</a>
                <a href="booking.php?step=1" class="btn btn-primary">Start New Booking</a>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary me-2">Return to Home</a>
            <?php if ($bookingComplete || $reservationId): ?>
                <a href="#" class="btn btn-primary" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Print Confirmation
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Clear booking session data after successful booking
if ($bookingComplete || $reservationId) {
    // Keep only the reservation ID and processed flag
    $_SESSION['booking'] = [
        'processed' => true,
        'reservation_id' => $reservationId
    ];
}
?> 