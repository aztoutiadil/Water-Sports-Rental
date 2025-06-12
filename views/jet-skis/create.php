<?php
// Initialize variables to prevent undefined variable errors
$title = "Add New Jet Ski";
$is_edit = false;
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

// Restore form data if available (after validation error)
if (isset($_SESSION['form_data'])) {
    foreach ($_SESSION['form_data'] as $key => $value) {
        if (isset($jet_ski[$key])) {
            $jet_ski[$key] = $value;
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
    $_SESSION['flash']['error'] = 'You do not have permission to add jet skis. Administrator access required.';
    header('Location: index.php?page=jet-skis');
    exit;
}

// Include the form template
include 'views/jet-skis/form.php'; 