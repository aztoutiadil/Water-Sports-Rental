<?php
// Check if previous steps are completed
if (empty($_SESSION['booking']['equipment_type']) || empty($_SESSION['booking']['equipment_id']) || 
    empty($_SESSION['booking']['start_date']) || empty($_SESSION['booking']['end_date']) ||
    empty($_SESSION['booking']['client'])) {
    header('Location: booking.php?step=1');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $paymentMethod = $_POST['payment_method'];
    
    if ($paymentMethod === 'cash') {
        // Cash payment means they'll pay at the location
        $_SESSION['booking']['payment'] = [
            'method' => 'cash',
            'deposit_paid' => false,
            'status' => 'pending'
        ];
    } else {
        // Other payment methods would be processed through a payment gateway
        // For this demo, we'll just simulate a successful payment
        $_SESSION['booking']['payment'] = [
            'method' => $paymentMethod,
            'transaction_id' => 'DEMO' . mt_rand(100000, 999999),
            'deposit_paid' => true,
            'status' => 'paid'
        ];
    }
    
    // Redirect to the confirmation page
    header('Location: booking.php?step=6');
    exit;
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
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-4">Payment Method</h3>
        
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Deposit Required:</strong> A 25% deposit ($<?php echo number_format($depositAmount, 2); ?>) is required to confirm your booking. The remaining balance will be paid at the rental location.
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Booking Summary</h5>
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
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total Amount:</strong> 
                        <span class="text-primary">$<?php echo number_format($totalAmount, 2); ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Required Deposit (25%):</strong> 
                        <span class="text-success fw-bold">$<?php echo number_format($depositAmount, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="" id="paymentForm">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Choose Payment Method</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card payment-option h-100" data-method="credit_card">
                                <div class="card-body text-center">
                                    <div class="form-check">
                                        <input class="form-check-input visually-hidden" type="radio" name="payment_method" id="credit_card" value="credit_card">
                                        <label class="form-check-label w-100" for="credit_card">
                                            <i class="bi bi-credit-card text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="mt-3">Credit Card</h5>
                                            <p class="text-muted small">Pay deposit now with Visa, Mastercard, or American Express</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card payment-option h-100" data-method="paypal">
                                <div class="card-body text-center">
                                    <div class="form-check">
                                        <input class="form-check-input visually-hidden" type="radio" name="payment_method" id="paypal" value="paypal">
                                        <label class="form-check-label w-100" for="paypal">
                                            <i class="bi bi-paypal text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="mt-3">PayPal</h5>
                                            <p class="text-muted small">Pay deposit now securely with your PayPal account</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card payment-option h-100" data-method="cash">
                                <div class="card-body text-center">
                                    <div class="form-check">
                                        <input class="form-check-input visually-hidden" type="radio" name="payment_method" id="cash" value="cash">
                                        <label class="form-check-label w-100" for="cash">
                                            <i class="bi bi-cash text-success" style="font-size: 2rem;"></i>
                                            <h5 class="mt-3">Pay at Location</h5>
                                            <p class="text-muted small">Pay the full amount in cash when you arrive</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card payment-option h-100" data-method="bank_transfer">
                                <div class="card-body text-center">
                                    <div class="form-check">
                                        <input class="form-check-input visually-hidden" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                        <label class="form-check-label w-100" for="bank_transfer">
                                            <i class="bi bi-bank text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="mt-3">Bank Transfer</h5>
                                            <p class="text-muted small">Pay deposit via bank transfer (instructions will be provided)</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="payment_details" class="d-none">
                <!-- Credit Card Details (only shown when credit card is selected) -->
                <div id="credit_card_details" class="payment-details-section d-none">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Credit Card Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-shield-lock me-2"></i>
                                This is a demo version. No actual payment will be processed. In a real implementation, this would connect to a payment gateway.
                            </div>
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" placeholder="John Smith">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- PayPal Details (only shown when PayPal is selected) -->
                <div id="paypal_details" class="payment-details-section d-none">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">PayPal Details</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="alert alert-info">
                                <i class="bi bi-shield-lock me-2"></i>
                                This is a demo version. In a real implementation, you would be redirected to PayPal to complete your payment.
                            </div>
                            <i class="bi bi-paypal text-primary" style="font-size: 4rem;"></i>
                            <p class="mt-3">You will be redirected to PayPal to complete your payment of $<?php echo number_format($depositAmount, 2); ?>.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Bank Transfer Details (only shown when bank transfer is selected) -->
                <div id="bank_transfer_details" class="payment-details-section d-none">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Bank Transfer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-shield-lock me-2"></i>
                                This is a demo version. In a real implementation, you would receive bank transfer instructions.
                            </div>
                            <p>Please transfer the deposit amount of $<?php echo number_format($depositAmount, 2); ?> to the following bank account:</p>
                            <div class="bg-light p-3 rounded mb-3">
                                <p class="mb-2"><strong>Bank Name:</strong> Demo Bank</p>
                                <p class="mb-2"><strong>Account Name:</strong> Water Sports Rental</p>
                                <p class="mb-2"><strong>Account Number:</strong> 123456789</p>
                                <p class="mb-2"><strong>Routing Number:</strong> 987654321</p>
                                <p class="mb-0"><strong>Reference:</strong> Please include your name and email as reference</p>
                            </div>
                            <p class="mb-0">Your booking will be confirmed once we receive your payment.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Cash Payment Information (only shown when cash is selected) -->
                <div id="cash_details" class="payment-details-section d-none">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Pay at Location</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Your booking is not guaranteed until payment is received. Equipment availability may change.
                            </div>
                            <p>You have chosen to pay the full amount of $<?php echo number_format($totalAmount, 2); ?> in cash at our location.</p>
                            <p>Please arrive at least 30 minutes before your rental start time to complete the payment and paperwork.</p>
                            <p class="mb-0">Bring a valid ID and your booking confirmation.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="booking.php?step=4" class="btn btn-outline-secondary me-2">Back</a>
                <button type="submit" class="btn btn-primary" id="continueBtn">Complete Booking</button>
            </div>
        </form>
    </div>
</div>

<style>
.payment-option {
    cursor: pointer;
    border: 2px solid #e9ecef;
    transition: all 0.3s;
}
.payment-option:hover {
    border-color: #0043a8;
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.payment-option.selected {
    border-color: #0043a8;
    background-color: rgba(0, 67, 168, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const radioInputs = document.querySelectorAll('input[type="radio"]');
    const paymentDetails = document.getElementById('payment_details');
    const paymentDetailsSections = document.querySelectorAll('.payment-details-section');
    const continueBtn = document.getElementById('continueBtn');
    
    // Add click event to payment options
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            const method = this.dataset.method;
            const radio = document.getElementById(method);
            radio.checked = true;
            
            // Update styles
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            // Show/hide payment details based on selection
            paymentDetails.classList.remove('d-none');
            paymentDetailsSections.forEach(section => section.classList.add('d-none'));
            document.getElementById(method + '_details').classList.remove('d-none');
            
            // Update button text based on payment method
            if (method === 'cash') {
                continueBtn.textContent = 'Reserve Without Payment';
            } else {
                continueBtn.textContent = 'Pay Deposit & Complete Booking';
            }
        });
    });
    
    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        let paymentSelected = false;
        radioInputs.forEach(input => {
            if (input.checked) {
                paymentSelected = true;
            }
        });
        
        if (!paymentSelected) {
            e.preventDefault();
            alert('Please select a payment method.');
            return false;
        }
        
        // In a real implementation, you would validate payment details here
        // For this demo, we'll just submit the form
        return true;
    });
});</script> 