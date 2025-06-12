<?php
// Initialize variables
$title = "Water Sports Rental";

// Dummy data for equipment
$jetSkis = [
    [
        'id' => 1,
        'name' => 'Yamaha Waverunner VX',
        'image' => 'https://via.placeholder.com/300x200?text=Yamaha+Waverunner',
        'price' => 75,
        'capacity' => 3,
        'power' => '125 HP',
        'available' => true
    ],
    [
        'id' => 2,
        'name' => 'Sea-Doo Spark',
        'image' => 'https://via.placeholder.com/300x200?text=Sea-Doo+Spark',
        'price' => 65,
        'capacity' => 2,
        'power' => '90 HP',
        'available' => true
    ],
    [
        'id' => 3,
        'name' => 'Kawasaki Ultra 310LX',
        'image' => 'https://via.placeholder.com/300x200?text=Kawasaki+Ultra',
        'price' => 85,
        'capacity' => 3,
        'power' => '310 HP',
        'available' => false
    ],
    [
        'id' => 4,
        'name' => 'Yamaha FX Cruiser',
        'image' => 'https://via.placeholder.com/300x200?text=Yamaha+FX+Cruiser',
        'price' => 80,
        'capacity' => 3,
        'power' => '180 HP',
        'available' => true
    ]
];

$boats = [
    [
        'id' => 1,
        'name' => 'Sunseeker Luxury Yacht',
        'image' => 'https://via.placeholder.com/300x200?text=Sunseeker+Yacht',
        'price' => 250,
        'capacity' => 12,
        'length' => '40 ft',
        'available' => true
    ],
    [
        'id' => 2,
        'name' => 'Bayliner Bowrider',
        'image' => 'https://via.placeholder.com/300x200?text=Bayliner+Bowrider',
        'price' => 150,
        'capacity' => 8,
        'length' => '30 ft',
        'available' => true
    ],
    [
        'id' => 3,
        'name' => 'Monterey Sport Boat',
        'image' => 'https://via.placeholder.com/300x200?text=Monterey+Sport',
        'price' => 180,
        'capacity' => 6,
        'length' => '25 ft',
        'available' => true
    ],
    [
        'id' => 4,
        'name' => 'Pontoon Party Boat',
        'image' => 'https://via.placeholder.com/300x200?text=Pontoon+Party+Boat',
        'price' => 200,
        'capacity' => 15,
        'length' => '28 ft',
        'available' => false
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0043a8;
            --secondary-color: #0099ff;
            --accent-color: #00c8ff;
            --light-blue: #e6f7ff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .hero {
            background: linear-gradient(rgba(0, 67, 168, 0.8), rgba(0, 153, 255, 0.8)), url('https://images.unsplash.com/photo-1564415060716-49971f59d6c7?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0;
            margin-bottom: 3rem;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            padding: 1rem 1.25rem;
            transition: all 0.3s;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: white;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #003585;
            border-color: #003585;
        }
        
        .btn-outline-light {
            border-width: 2px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }
        
        .section-title {
            margin-bottom: 3rem;
            position: relative;
            text-align: center;
        }
        
        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            margin: 1rem auto 0;
        }
        
        .equipment-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            margin-bottom: 2rem;
            border: none;
        }
        
        .equipment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .equipment-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .equipment-card .card-body {
            padding: 1.5rem;
        }
        
        .equipment-card .card-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .equipment-card .price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .equipment-card .badge {
            font-weight: 500;
            padding: 0.5em 1em;
            border-radius: 20px;
        }
        
        .features-section {
            background-color: var(--light-blue);
            padding: 5rem 0;
            margin: 5rem 0;
        }
        
        .feature-item {
            text-align: center;
            padding: 2rem;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .feature-item h3 {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .cta-section {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 5rem 0;
            margin-bottom: 5rem;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .footer {
            background-color: #333;
            color: rgba(255, 255, 255, 0.7);
            padding: 4rem 0 2rem;
        }
        
        .footer h5 {
            color: white;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
        }
        
        .footer ul li {
            margin-bottom: 0.75rem;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .social-icons a {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-align: center;
            line-height: 40px;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            margin-top: 3rem;
        }
        
        .nav-pills .nav-link {
            color: #333;
            font-weight: 500;
            border-radius: 30px;
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-water me-2"></i>
                Water Sports Rental
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#equipment">Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="booking.php">Book Now</a>
                    </li>
                </ul>
                <div class="ms-3">
                    <a href="#" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-person me-1"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1>Experience the Thrill of Water Sports</h1>
            <p class="mx-auto">Rent top-quality jet skis and boats for your perfect day on the water. Easy booking, competitive prices, and unforgettable experiences.</p>
            <a href="booking.php" class="btn btn-light btn-lg">
                <i class="bi bi-calendar-check me-2"></i>Book Now
            </a>
        </div>
    </section>

    <!-- Equipment Section -->
    <section class="container mb-5" id="equipment">
        <h2 class="section-title">Our Equipment</h2>
        
        <ul class="nav nav-pills justify-content-center mb-4" id="equipmentTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="jetski-tab" data-bs-toggle="tab" data-bs-target="#jetski-tab-pane" type="button" role="tab" aria-controls="jetski-tab-pane" aria-selected="true">Jet Skis</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="boat-tab" data-bs-toggle="tab" data-bs-target="#boat-tab-pane" type="button" role="tab" aria-controls="boat-tab-pane" aria-selected="false">Tourist Boats</button>
            </li>
        </ul>
        
        <div class="tab-content" id="equipmentTabContent">
            <!-- Jet Skis -->
            <div class="tab-pane fade show active" id="jetski-tab-pane" role="tabpanel" aria-labelledby="jetski-tab" tabindex="0">
                <div class="row">
                    <?php foreach ($jetSkis as $jetSki): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="equipment-card card h-100">
                                <img src="<?php echo htmlspecialchars($jetSki['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($jetSki['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($jetSki['name']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="price">$<?php echo htmlspecialchars($jetSki['price']); ?>/hr</span>
                                        <?php if ($jetSki['available']): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unavailable</span>
                                        <?php endif; ?>
                                    </div>
                                    <ul class="list-unstyled mb-4">
                                        <li><i class="bi bi-people me-2"></i> Capacity: <?php echo htmlspecialchars($jetSki['capacity']); ?> persons</li>
                                        <li><i class="bi bi-lightning me-2"></i> Power: <?php echo htmlspecialchars($jetSki['power']); ?></li>
                                    </ul>
                                    <a href="booking.php?equipment_type=jetski&id=<?php echo $jetSki['id']; ?>" class="btn btn-primary w-100 <?php echo !$jetSki['available'] ? 'disabled' : ''; ?>">
                                        <i class="bi bi-calendar-plus me-1"></i> Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Boats -->
            <div class="tab-pane fade" id="boat-tab-pane" role="tabpanel" aria-labelledby="boat-tab" tabindex="0">
                <div class="row">
                    <?php foreach ($boats as $boat): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="equipment-card card h-100">
                                <img src="<?php echo htmlspecialchars($boat['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($boat['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($boat['name']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="price">$<?php echo htmlspecialchars($boat['price']); ?>/hr</span>
                                        <?php if ($boat['available']): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unavailable</span>
                                        <?php endif; ?>
                                    </div>
                                    <ul class="list-unstyled mb-4">
                                        <li><i class="bi bi-people me-2"></i> Capacity: <?php echo htmlspecialchars($boat['capacity']); ?> persons</li>
                                        <li><i class="bi bi-rulers me-2"></i> Length: <?php echo htmlspecialchars($boat['length']); ?></li>
                                    </ul>
                                    <a href="booking.php?equipment_type=boat&id=<?php echo $boat['id']; ?>" class="btn btn-primary w-100 <?php echo !$boat['available'] ? 'disabled' : ''; ?>">
                                        <i class="bi bi-calendar-plus me-1"></i> Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Safety First</h3>
                        <p>All our equipment is regularly maintained and inspected to ensure your safety on the water.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h3>Best Prices</h3>
                        <p>Competitive pricing with special discounts for longer rentals and returning customers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h3>Expert Support</h3>
                        <p>Our team of experienced instructors is always ready to assist you and ensure a great experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container text-center">
            <h2>Ready for an Adventure?</h2>
            <p class="mb-4">Book your water sports equipment now and make unforgettable memories!</p>
            <a href="booking.php" class="btn btn-light btn-lg">
                <i class="bi bi-calendar-check me-2"></i>Book Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5>Water Sports Rental</h5>
                    <p>Experience the thrill of water sports with our premium jet skis and boats. We provide top-quality equipment for unforgettable adventures on the water.</p>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#equipment">Equipment</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="booking.php">Book Now</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Information</h5>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt me-2"></i> 123 Beach Road, Miami, FL</li>
                        <li><i class="bi bi-telephone me-2"></i> +1 (555) 123-4567</li>
                        <li><i class="bi bi-envelope me-2"></i> info@watersportsrental.com</li>
                        <li><i class="bi bi-clock me-2"></i> Mon-Sun: 8:00 AM - 8:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Water Sports Rental. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <button class="btn btn-outline-primary w-100 mb-3">
                            <i class="bi bi-google me-2"></i> Continue with Google
                        </button>
                        <button class="btn btn-outline-primary w-100">
                            <i class="bi bi-telephone me-2"></i> Continue with Phone Number
                        </button>
                    </div>
                    
                    <div class="text-center mb-3">
                        <span class="text-muted">OR</span>
                    </div>
                    
                    <form>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" placeholder="name@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter your password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none">Forgot password?</a>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <p class="mb-0">Don't have an account? <a href="#" class="text-decoration-none">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 