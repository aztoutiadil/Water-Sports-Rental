<?php
// Initialize variables to prevent undefined variable errors
$title = "Edit Jet Ski";
$is_edit = true;
$jet_ski = [
    'id' => '',
    'brand' => '',
    'model' => '',
    'year' => date('Y'),
    'registration_number' => '',
    'status' => 'available',
    'hourly_rate' => '75.00',
    'daily_rate' => '600.00',
    'last_maintenance_date' => '',
    'next_maintenance_date' => '',
    'notes' => '',
    'image_url' => '',
];

// Ensure user is logged in and has admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    $_SESSION['flash']['error'] = 'You must be logged in to access this page';
    header('Location: index.php?page=auth/login');
    exit;
}

// Check if user has administrator role
if ($_SESSION['user']['role'] !== 'administrator') {
    $_SESSION['flash']['error'] = 'You do not have permission to edit jet skis. Administrator access required.';
    header('Location: index.php?page=jet-skis');
    exit;
}

// Get jet ski ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to jet ski list
if ($id <= 0) {
    $_SESSION['flash']['error'] = "No jet ski ID specified";
    header('Location: index.php?page=jet-skis');
    exit;
}

// Fetch jet ski data from database
if (isset($conn)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM jet_skis WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $jet_ski = $result->fetch_assoc();
            $title = "Edit Jet Ski: " . $jet_ski['brand'] . " " . $jet_ski['model'];
        } else {
            $_SESSION['flash']['error'] = "Jet ski not found";
            header('Location: index.php?page=jet-skis');
            exit;
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['flash']['error'] = "Database error: " . $e->getMessage();
    }
}

// Restore form data if available (after validation error)
if (isset($_SESSION['form_data'])) {
    foreach ($_SESSION['form_data'] as $key => $value) {
        if (isset($jet_ski[$key])) {
            $jet_ski[$key] = $value;
        }
    }
    unset($_SESSION['form_data']);
}

// Include the form template
include 'views/jet-skis/form.php'; 