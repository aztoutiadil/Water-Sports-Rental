<?php
// Check if equipment type is set
if (empty($_SESSION['booking']['equipment_type'])) {
    header('Location: booking.php?step=1');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipment_id'])) {
    $_SESSION['booking']['equipment_id'] = $_POST['equipment_id'];
    
    // Redirect to the next step
    header('Location: booking.php?step=3');
    exit;
}

// Get equipment based on type
$equipmentType = $_SESSION['booking']['equipment_type'];
$equipment = getAvailableEquipment($equipmentType, $conn);
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <h3 class="mb-4">
            Select Your <?php echo ($equipmentType === 'jet_ski') ? 'Jet Ski' : 'Tourist Boat'; ?>
        </h3>
        
        <?php if (empty($equipment)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No available <?php echo ($equipmentType === 'jet_ski') ? 'jet skis' : 'boats'; ?> at the moment. Please try again later or choose a different equipment type.
            </div>
            <div class="text-center mt-4">
                <a href="booking.php?step=1" class="btn btn-outline-primary">Go Back</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="row">
                    <?php foreach ($equipment as $item): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card equipment-card h-100 <?php echo ($_SESSION['booking']['equipment_id'] == $item['id']) ? 'selected' : ''; ?>" data-id="<?php echo $item['id']; ?>">
                                <div class="card-body">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="equipment-img mb-3" alt="<?php echo $equipmentType === 'jet_ski' ? $item['brand'] . ' ' . $item['model'] : $item['name']; ?>">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="height: 160px; border-radius: 6px;">
                                            <i class="bi bi-<?php echo $equipmentType === 'jet_ski' ? 'water' : 'tsunami'; ?> text-secondary" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h5 class="card-title">
                                        <?php if ($equipmentType === 'jet_ski'): ?>
                                            <?php echo htmlspecialchars($item['brand'] . ' ' . $item['model']); ?>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        <?php endif; ?>
                                    </h5>
                                    
                                    <?php if ($equipmentType === 'jet_ski'): ?>
                                        <p class="text-muted small">Year: <?php echo $item['year']; ?></p>
                                    <?php else: ?>
                                        <p class="text-muted small">
                                            <?php echo htmlspecialchars($item['type']); ?> | 
                                            Capacity: <?php echo $item['capacity']; ?> people
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <div class="fw-bold text-primary">$<?php echo $item['hourly_rate']; ?>/hour</div>
                                            <div class="small text-muted">$<?php echo $item['daily_rate']; ?>/day</div>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="equipment_id" id="equipment<?php echo $item['id']; ?>" value="<?php echo $item['id']; ?>" <?php echo ($_SESSION['booking']['equipment_id'] == $item['id']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label visually-hidden" for="equipment<?php echo $item['id']; ?>">
                                                Select
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="booking.php?step=1" class="btn btn-outline-secondary me-2">Back</a>
                    <button type="submit" class="btn btn-primary">Continue to Dates</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.equipment-card');
    const radioInputs = document.querySelectorAll('input[type="radio"]');
    
    // Add click event to cards
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const id = this.dataset.id;
            // Find the radio input with this ID
            const radio = document.querySelector('input[value="' + id + '"]');
            radio.checked = true;
            
            // Update styles
            cards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Initial selection
    radioInputs.forEach(input => {
        if (input.checked) {
            const card = input.closest('.equipment-card');
            card.classList.add('selected');
        }
    });
});
</script> 