<?php
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipment_type'])) {
    $_SESSION['booking']['equipment_type'] = $_POST['equipment_type'];
    
    // Redirect to the next step
    header('Location: booking.php?step=2');
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h3 class="mb-4">Choose Your Equipment Type</h3>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="form-check">
                                <input class="form-check-input visually-hidden" type="radio" name="equipment_type" id="jetski" value="jet_ski" <?php echo ($_SESSION['booking']['equipment_type'] === 'jet_ski') ? 'checked' : ''; ?>>
                                <label class="form-check-label w-100" for="jetski">
                                    <img src="assets/images/jetski-icon.jpg" alt="Jet Ski" class="img-fluid mb-3" style="height: 150px;">
                                    <h4>Jet Ski</h4>
                                    <p class="text-muted">Perfect for solo riders or pairs looking for speed and excitement</p>
                                    <ul class="text-start">
                                        <li>Top speeds of 40-65 mph</li>
                                        <li>Easy to maneuver</li>
                                        <li>Perfect for beginners and experts</li>
                                        <li>1-2 person capacity</li>
                                    </ul>
                                    <h5 class="mt-3">From $65/hour</h5>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="form-check">
                                <input class="form-check-input visually-hidden" type="radio" name="equipment_type" id="boat" value="boat" <?php echo ($_SESSION['booking']['equipment_type'] === 'boat') ? 'checked' : ''; ?>>
                                <label class="form-check-label w-100" for="boat">
                                    <img src="assets/images/boat-icon.jpg" alt="Tourist Boat" class="img-fluid mb-3" style="height: 150px;">
                                    <h4>Tourist Boat</h4>
                                    <p class="text-muted">Ideal for groups and families wanting to explore the waters together</p>
                                    <ul class="text-start">
                                        <li>Comfortable seating for 6-12 people</li>
                                        <li>Stable and spacious</li>
                                        <li>Various amenities available</li>
                                        <li>Perfect for sightseeing</li>
                                    </ul>
                                    <h5 class="mt-3">From $120/hour</h5>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Continue to Selection</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    const radioInputs = document.querySelectorAll('input[type="radio"]');
    
    // Add click event to cards
    cards.forEach(card => {
        card.addEventListener('click', function() {
            // Find the radio input inside this card
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Update styles
            updateCardStyles();
        });
    });
    
    // Update card styles based on selection
    function updateCardStyles() {
        radioInputs.forEach(input => {
            const card = input.closest('.card');
            if (input.checked) {
                card.classList.add('border-primary', 'shadow');
            } else {
                card.classList.remove('border-primary', 'shadow');
            }
        });
    }
    
    // Initial style update
    updateCardStyles();
});
</script> 