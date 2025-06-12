<!-- Hero Section -->
<div class="bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Water Sports Adventure Awaits</h1>
                <p class="lead mb-4">Experience the thrill of jet skiing and scenic boat tours. Book your water adventure today!</p>
                <?php if (!isset($_SESSION['admin_id'])): ?>
                    <a href="index.php?page=login" class="btn btn-light btn-lg">Admin Login</a>
                <?php else: ?>
                    <a href="index.php?page=dashboard" class="btn btn-light btn-lg">Go to Dashboard</a>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <img src="images/hero-image.jpg" alt="Water Sports" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</div>

<!-- Services Section -->
<div class="container mb-5">
    <h2 class="text-center mb-4">Our Services</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-water fa-2x text-primary me-3"></i>
                        <h3 class="card-title mb-0">Jet Ski Rentals</h3>
                    </div>
                    <p class="card-text">Experience the thrill of riding our modern jet skis. Perfect for both beginners and experienced riders.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Latest models available</li>
                        <li><i class="fas fa-check text-success me-2"></i>Safety equipment provided</li>
                        <li><i class="fas fa-check text-success me-2"></i>Professional instruction</li>
                    </ul>
                    <p class="text-success mb-0">
                        <i class="fas fa-circle me-2"></i>
                        <?php echo $stats['availableJetSkis']; ?> jet skis available now
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-ship fa-2x text-primary me-3"></i>
                        <h3 class="card-title mb-0">Tourist Boat Tours</h3>
                    </div>
                    <p class="card-text">Enjoy scenic boat tours with family and friends. Perfect for sightseeing and special occasions.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Comfortable seating</li>
                        <li><i class="fas fa-check text-success me-2"></i>Experienced captains</li>
                        <li><i class="fas fa-check text-success me-2"></i>Scenic routes</li>
                    </ul>
                    <p class="text-success mb-0">
                        <i class="fas fa-circle me-2"></i>
                        <?php echo $stats['availableBoats']; ?> boats available now
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-light py-5 mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Why Choose Us</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>Safety First</h4>
                    <p>Top-quality safety equipment and thorough briefings for all activities.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-star fa-3x text-primary mb-3"></i>
                    <h4>Expert Staff</h4>
                    <p>Professional and experienced team to guide and assist you.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-smile fa-3x text-primary mb-3"></i>
                    <h4>Great Experience</h4>
                    <p>Memorable adventures and excellent customer service.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Section -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h3 class="mb-4">Contact Information</h3>
                            <p><i class="fas fa-map-marker-alt text-primary me-2"></i> 123 Beach Road, Coastal City</p>
                            <p><i class="fas fa-phone text-primary me-2"></i> +1 234 567 890</p>
                            <p><i class="fas fa-envelope text-primary me-2"></i> info@watersportsrental.com</p>
                            <p><i class="fas fa-clock text-primary me-2"></i> Open daily: 8:00 AM - 6:00 PM</p>
                        </div>
                        <div class="col-md-6">
                            <h3 class="mb-4">Location</h3>
                            <div class="ratio ratio-16x9">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.2219901290355!2d-74.00369368400567!3d40.71312937933185!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a3b5a8182db%3A0xa0f9aef21ddf4030!2sWall+Street!5e0!3m2!1sen!2sus!4v1560412037428!5m2!1sen!2sus" 
                                    width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 