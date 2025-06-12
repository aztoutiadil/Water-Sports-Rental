<?php
// Check if previous steps are completed
if (empty($_SESSION['booking']['equipment_type']) || empty($_SESSION['booking']['equipment_id'])) {
    header('Location: booking.php?step=1');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    // Validate dates
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $now = new DateTime();
    
    $error = null;
    
    if ($start < $now) {
        $error = "Start date and time must be in the future.";
    } elseif ($end <= $start) {
        $error = "End date and time must be after the start date and time.";
    } else {
        // Check if the equipment is available for the selected dates
        $equipmentType = $_SESSION['booking']['equipment_type'];
        $equipmentId = $_SESSION['booking']['equipment_id'];
        
        if (areDatesAvailable($equipmentType, $equipmentId, $startDate, $endDate, $conn)) {
            // Calculate the total amount
            $totalAmount = calculateRentalCost($equipmentType, $equipmentId, $startDate, $endDate, $conn);
            
            // Save the information in the session
            $_SESSION['booking']['start_date'] = $startDate;
            $_SESSION['booking']['end_date'] = $endDate;
            $_SESSION['booking']['total_amount'] = $totalAmount;
            $_SESSION['booking']['deposit_amount'] = $totalAmount * 0.25; // 25% deposit
            
            // Redirect to the next step
            header('Location: booking.php?step=4');
            exit;
        } else {
            $error = "Sorry, the selected equipment is not available for the chosen dates. Please select different dates.";
        }
    }
}

// Get equipment details
$equipmentType = $_SESSION['booking']['equipment_type'];
$equipmentId = $_SESSION['booking']['equipment_id'];
$equipment = getEquipmentDetails($equipmentType, $equipmentId, $conn);

// Set minimum date for datepicker (today)
$minDate = date('Y-m-d\TH:i');
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-4">Choose Your Rental Dates</h3>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <?php if (!empty($equipment['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($equipment['image_url']); ?>" alt="Equipment" class="me-3" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 100px; height: 60px; border-radius: 4px;">
                            <i class="bi bi-<?php echo $equipmentType === 'jet_ski' ? 'water' : 'tsunami'; ?> text-secondary" style="font-size: 2rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($equipment['display_name']); ?></h5>
                        <div class="d-flex">
                            <span class="badge bg-primary me-2">$<?php echo $equipment['hourly_rate']; ?>/hour</span>
                            <span class="badge bg-info">$<?php echo $equipment['daily_rate']; ?>/day</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="" id="dateForm">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date and Time</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                            min="<?php echo $minDate; ?>" 
                            value="<?php echo isset($_SESSION['booking']['start_date']) ? $_SESSION['booking']['start_date'] : ''; ?>"
                            required>
                        <div class="form-text">Please select the date and time you want to start your rental</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date and Time</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" 
                            min="<?php echo $minDate; ?>" 
                            value="<?php echo isset($_SESSION['booking']['end_date']) ? $_SESSION['booking']['end_date'] : ''; ?>"
                            required>
                        <div class="form-text">Please select the date and time you want to end your rental</div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Rental Duration and Cost Estimate</h5>
                    <div id="durationInfo" class="d-none">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <p class="mb-0"><strong>Duration:</strong> <span id="duration"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-0"><strong>Rate Applied:</strong> <span id="rateType"></span></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-0"><strong>Estimated Cost:</strong> <span id="estimatedCost" class="text-primary fw-bold"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-0"><strong>Required Deposit (25%):</strong> <span id="depositAmount" class="text-success fw-bold"></span></p>
                            </div>
                        </div>
                    </div>
                    <div id="durationError" class="text-danger small mt-2"></div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Rental Policy:</strong> Our rental periods are calculated from the actual pickup time to the return time. A 25% deposit is required to confirm your booking.
            </div>
            
            <div class="text-center mt-4">
                <a href="booking.php?step=2" class="btn btn-outline-secondary me-2">Back</a>
                <button type="submit" class="btn btn-primary" id="continueBtn">Continue to Information</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const durationInfo = document.getElementById('durationInfo');
    const durationSpan = document.getElementById('duration');
    const rateTypeSpan = document.getElementById('rateType');
    const estimatedCostSpan = document.getElementById('estimatedCost');
    const depositAmountSpan = document.getElementById('depositAmount');
    const durationError = document.getElementById('durationError');
    const continueBtn = document.getElementById('continueBtn');
    
    // Equipment rates
    const hourlyRate = <?php echo $equipment['hourly_rate']; ?>;
    const dailyRate = <?php echo $equipment['daily_rate']; ?>;
    
    function updateDurationAndCost() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        // Clear previous state
        durationInfo.classList.add('d-none');
        durationError.textContent = '';
        continueBtn.disabled = false;
        
        // Validate dates
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            return;
        }
        
        const now = new Date();
        
        if (startDate < now) {
            durationError.textContent = 'Start date and time must be in the future.';
            continueBtn.disabled = true;
            return;
        }
        
        if (endDate <= startDate) {
            durationError.textContent = 'End date and time must be after the start date and time.';
            continueBtn.disabled = true;
            return;
        }
        
        // Calculate duration
        const diffMs = endDate - startDate;
        const diffHours = diffMs / (1000 * 60 * 60);
        const days = Math.floor(diffHours / 24);
        const hours = Math.floor(diffHours % 24);
        const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        // Display duration
        let durationText = '';
        if (days > 0) {
            durationText += days + ' day' + (days > 1 ? 's' : '') + ' ';
        }
        if (hours > 0 || days > 0) {
            durationText += hours + ' hour' + (hours > 1 ? 's' : '') + ' ';
        }
        durationText += minutes + ' minute' + (minutes > 1 ? 's' : '');
        durationSpan.textContent = durationText;
        
        // Calculate cost
        let cost = 0;
        let rateType = '';
        
        if (diffHours < 24) {
            // Hourly rate
            cost = hourlyRate * diffHours;
            rateType = 'Hourly ($' + hourlyRate + '/hour)';
        } else {
            // Daily rate
            const totalDays = Math.ceil(diffHours / 24);
            cost = dailyRate * totalDays;
            rateType = 'Daily ($' + dailyRate + '/day)';
        }
        
        // Display cost information
        rateTypeSpan.textContent = rateType;
        estimatedCostSpan.textContent = '$' + cost.toFixed(2);
        depositAmountSpan.textContent = '$' + (cost * 0.25).toFixed(2);
        
        // Show the duration info
        durationInfo.classList.remove('d-none');
    }
    
    // Add event listeners
    startDateInput.addEventListener('change', updateDurationAndCost);
    endDateInput.addEventListener('change', updateDurationAndCost);
    
    // Initial calculation if values are already set
    if (startDateInput.value && endDateInput.value) {
        updateDurationAndCost();
    }
});
</script> 