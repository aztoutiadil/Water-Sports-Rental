<?php
// Initialize variables to prevent undefined variable errors
$title = "Add New Tourist Boat";
$is_edit = false;
$boat = [
    'id' => '',
    'name' => '',
    'type' => '',
    'capacity' => '',
    'year' => date('Y'),
    'registration_number' => '',
    'status' => 'available',
    'hourly_rate' => '150.00',
    'daily_rate' => '750.00',
    'last_maintenance_date' => date('Y-m-d'),
    'next_maintenance_date' => date('Y-m-d', strtotime('+30 days')),
    'features' => '',
    'notes' => '',
    'image_url' => '',
];

// Restore form data if available (after validation error)
if (isset($_SESSION['form_data'])) {
    foreach ($_SESSION['form_data'] as $key => $value) {
        if (isset($boat[$key])) {
            $boat[$key] = $value;
        }
    }
    unset($_SESSION['form_data']);
}

// Ensure user is logged in and has admin role
// Check session status before starting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Include the form template
include 'views/boats/form.php'; 