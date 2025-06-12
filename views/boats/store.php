<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    $_SESSION['flash']['error'] = 'You must be logged in to access this page';
    header('Location: index.php?page=auth/login');
    exit;
}

// Check if user has administrator role
if ($_SESSION['user']['role'] !== 'administrator') {
    $_SESSION['flash']['error'] = 'You do not have permission to add tourist boats. Administrator access required.';
    header('Location: index.php?page=boats');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=boats/create');
    exit;
}

// Validate required fields
$required_fields = ['name', 'type', 'capacity', 'year', 'registration_number', 'hourly_rate', 'daily_rate', 'status'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
    }
}

if (!empty($errors)) {
    $_SESSION['flash']['error'] = implode('<br>', $errors);
    $_SESSION['form_data'] = $_POST;
    header('Location: index.php?page=boats/create');
    exit;
}

// Process form data
$name = trim($_POST['name']);
$type = trim($_POST['type']);
$capacity = intval($_POST['capacity']);
$year = intval($_POST['year']);
$registration_number = trim($_POST['registration_number']);
$hourly_rate = floatval($_POST['hourly_rate']);
$daily_rate = floatval($_POST['daily_rate']);
$status = trim($_POST['status']);
$features = isset($_POST['features']) ? trim($_POST['features']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$last_maintenance_date = !empty($_POST['last_maintenance_date']) ? $_POST['last_maintenance_date'] : null;
$next_maintenance_date = !empty($_POST['next_maintenance_date']) ? $_POST['next_maintenance_date'] : null;

// Handle image upload
$image_url = '';
if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
    $upload_dir = 'uploads/boats/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $file_name;
    
    // Check file type
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        $_SESSION['flash']['error'] = 'Only JPG, JPEG, and PNG files are allowed';
        $_SESSION['form_data'] = $_POST;
        header('Location: index.php?page=boats/create');
        exit;
    }
    
    // Check file size (5MB max)
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $_SESSION['flash']['error'] = 'File size must be less than 5MB';
        $_SESSION['form_data'] = $_POST;
        header('Location: index.php?page=boats/create');
        exit;
    }
    
    // Upload file
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_url = $target_file;
    } else {
        $_SESSION['flash']['error'] = 'Failed to upload image';
        $_SESSION['form_data'] = $_POST;
        header('Location: index.php?page=boats/create');
        exit;
    }
}

// Insert into database
if (isset($conn)) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO boats (
                name, type, capacity, year, registration_number, hourly_rate, 
                daily_rate, status, features, notes, last_maintenance_date, 
                next_maintenance_date, image_url, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param(
            "ssiisddssssss", 
            $name, $type, $capacity, $year, $registration_number, $hourly_rate, 
            $daily_rate, $status, $features, $notes, $last_maintenance_date, 
            $next_maintenance_date, $image_url
        );
        
        $result = $stmt->execute();
        
        if ($result) {
            $_SESSION['flash']['success'] = "Tourist boat added successfully";
            header('Location: index.php?page=boats');
            exit;
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header('Location: index.php?page=boats/create');
        exit;
    }
} else {
    // If no database connection, simulate success for demonstration
    $_SESSION['flash']['success'] = "Tourist boat added successfully (Simulation - no database connection)";
    header('Location: index.php?page=boats');
    exit;
} 