<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_INACTIVE) {
    session_start();
}

// Clear any existing booking session data when starting fresh
if (!isset($_GET['step'])) {
    unset($_SESSION['booking']);
    $_SESSION['booking'] = [
        'step' => 1,
        'equipment_type' => '',
        'equipment_id' => '',
        'start_date' => '',
        'end_date' => '',
        'client' => [],
        'payment' => []
    ];
}

// Get current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$_SESSION['booking']['step'] = $step;

// Define steps
$steps = [
    1 => 'Choose Equipment Type',
    2 => 'Select Equipment',
    3 => 'Choose Dates',
    4 => 'Your Information',
    5 => 'Payment Method',
    6 => 'Confirmation'
];

// Include database connection
require_once '../config/database.php';

// Include the appropriate step file based on the current step
$stepFile = 'booking_steps/step' . $step . '.php';

// Helper function to get available equipment
function getAvailableEquipment($type, $conn) {
    if ($type === 'jet_ski') {
        $sql = "SELECT * FROM jet_skis WHERE status = 'available' ORDER BY brand, model";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    } elseif ($type === 'boat') {
        $sql = "SELECT * FROM boats WHERE status = 'available' ORDER BY name";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// Helper function to check if dates are available for equipment
function areDatesAvailable($equipmentType, $equipmentId, $startDate, $endDate, $conn) {
    $sql = "SELECT COUNT(*) as count FROM reservations 
            WHERE equipment_type = ? AND equipment_id = ? AND 
            ((start_date BETWEEN ? AND ?) OR 
             (end_date BETWEEN ? AND ?) OR 
             (start_date <= ? AND end_date >= ?)) AND 
            status IN ('pending', 'confirmed')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssss", $equipmentType, $equipmentId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] == 0;
}

// Helper function to calculate rental cost
function calculateRentalCost($equipmentType, $equipmentId, $startDate, $endDate, $conn) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    
    // Total hours
    $hours = $interval->h + ($interval->days * 24);
    
    if ($equipmentType === 'jet_ski') {
        $sql = "SELECT hourly_rate, daily_rate FROM jet_skis WHERE id = ?";
    } else {
        $sql = "SELECT hourly_rate, daily_rate FROM boats WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $equipmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipment = $result->fetch_assoc();
    
    // If rental is less than 24 hours, use hourly rate
    if ($hours < 24) {
        return $equipment['hourly_rate'] * $hours;
    } else {
        // Calculate days (round up)
        $days = ceil($hours / 24);
        return $equipment['daily_rate'] * $days;
    }
}

// Helper function to get equipment details
function getEquipmentDetails($type, $id, $conn) {
    if ($type === 'jet_ski') {
        $sql = "SELECT id, brand, model, hourly_rate, daily_rate, image_url FROM jet_skis WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $equipment = $result->fetch_assoc();
        $equipment['display_name'] = $equipment['brand'] . ' ' . $equipment['model'];
        return $equipment;
    } elseif ($type === 'boat') {
        $sql = "SELECT id, name, type, capacity, hourly_rate, daily_rate, image_url FROM boats WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $equipment = $result->fetch_assoc();
        $equipment['display_name'] = $equipment['name'] . ' (' . $equipment['type'] . ')';
        return $equipment;
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Water Equipment - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0043a8;
            --secondary-color: #0099ff;
        }
        body {
            background-color: #f8f9fa;
            padding-top: 56px;
            min-height: 100vh;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
        }
        .booking-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .booking-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        .booking-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .booking-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }
        .step {
            position: relative;
            z-index: 2;
            background-color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }
        .step.active {
            color: white;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .step.completed {
            color: white;
            background-color: #28a745;
            border-color: #28a745;
        }
        .step-label {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.8rem;
            color: #6c757d;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #003585;
            border-color: #003585;
        }
        .equipment-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .equipment-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .equipment-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(0, 67, 168, 0.05);
        }
        .equipment-img {
            height: 160px;
            object-fit: cover;
            border-radius: 6px;
            width: 100%;
        }
        .footer {
            background-color: #333;
            color: white;
            padding: 2rem 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-water me-2"></i>
                Water Sports Rental
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="booking.php">Book Now</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipment.php">Our Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="booking-container">
            <div class="booking-header">
                <h2>Book Your Water Equipment</h2>
                <p class="text-muted">Follow the steps below to complete your booking</p>
            </div>
            
            <div class="booking-steps">
                <?php foreach($steps as $stepNum => $stepName): ?>
                <div class="step-wrapper text-center">
                    <div class="step <?php echo $stepNum < $step ? 'completed' : ($stepNum == $step ? 'active' : ''); ?>">
                        <?php if($stepNum < $step): ?>
                            <i class="bi bi-check"></i>
                        <?php else: ?>
                            <?php echo $stepNum; ?>
                        <?php endif; ?>
                    </div>
                    <div class="step-label"><?php echo $stepName; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php
            // Load the current step content
            if (file_exists($stepFile)) {
                include $stepFile;
            } else {
                echo "<div class='alert alert-danger'>Error: Step file not found.</div>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Water Sports Rental</h5>
                    <p>The best equipment for your water adventures. Experience the thrill with our top-quality jet skis and boats.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="booking.php" class="text-white">Book Now</a></li>
                        <li><a href="equipment.php" class="text-white">Our Equipment</a></li>
                        <li><a href="about.php" class="text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address>
                        <i class="bi bi-geo-alt me-2"></i> 123 Beach Road, Miami, FL<br>
                        <i class="bi bi-telephone me-2"></i> (123) 456-7890<br>
                        <i class="bi bi-envelope me-2"></i> info@watersportsrental.com
                    </address>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Water Sports Rental. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 