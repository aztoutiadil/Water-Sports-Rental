<?php
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Get the requested page from URL
$page = $_GET['page'] ?? 'dashboard';

// Define public pages that don't require authentication
$public_pages = [
    'auth/login',
    'auth/authenticate',
    'auth/logout',
    'auth/forgot-password',
    'auth/reset-password',
];

// Define admin pages that require authentication
$admin_pages = [
    'dashboard',
    'jet-skis',
    'jet-skis/create',
    'jet-skis/edit',
    'jet-skis/view',
    'jet-skis/store',
    'boats',
    'boats/create',
    'boats/edit',
    'boats/view',
    'boats/store',
    'clients',
    'clients/create',
    'clients/edit',
    'clients/view',
    'clients/createReservation',
    'reservations',
    'reservations/create',
    'reservations/edit',
    'reservations/view',
    'reservations/store',
    'reservations/createInvoice',
    'invoices',
    'invoices/create',
    'invoices/edit',
    'invoices/view',
    'invoices/download',
    'invoices/store',
    'invoices/recordPayment',
    'invoices/cancel',
    'reports',
    'reports/generate',
    'dashboard/profile',
    'dashboard/settings',
];

// Check if the requested page requires authentication
$requires_auth = in_array($page, $admin_pages);

// Check if user is authenticated
$is_authenticated = isset($_SESSION['user']) && $_SESSION['user']['logged_in'] === true;

// If page requires authentication and user is not authenticated, redirect to login
if ($requires_auth && !$is_authenticated) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Map routes to their respective view files
$routes = [
    'dashboard' => 'views/index.php',
    'jet-skis' => 'views/jet-skis/index.php',
    'jet-skis/create' => 'views/jet-skis/create.php',
    'jet-skis/edit' => 'views/jet-skis/edit.php',
    'jet-skis/view' => 'views/jet-skis/view.php',
    'jet-skis/store' => 'views/jet-skis/store.php',
    
    'boats' => 'views/boats/index.php',
    'boats/create' => 'views/boats/create.php',
    'boats/edit' => 'views/boats/edit.php',
    'boats/view' => 'views/boats/view.php',
    'boats/store' => 'views/boats/store.php',
    
    'clients' => 'views/clients/index.php',
    'clients/create' => 'views/clients/create.php',
    'clients/edit' => 'views/clients/edit.php',
    'clients/view' => 'views/clients/view.php',
    'clients/createReservation' => 'views/reservations/create.php',
    'clients/store' => 'views/clients/store.php',
    
    'reservations' => 'views/reservations/index.php',
    'reservations/create' => 'views/reservations/create.php',
    'reservations/edit' => 'views/reservations/edit.php',
    'reservations/view' => 'views/reservations/view.php',
    'reservations/store' => 'views/reservations/store.php',
    'reservations/createInvoice' => 'views/invoices/create.php',
    
    'invoices' => 'views/invoices/index.php',
    'invoices/create' => 'views/invoices/create.php',
    'invoices/edit' => 'views/invoices/edit.php',
    'invoices/view' => 'views/invoices/view.php',
    'invoices/download' => 'views/invoices/download.php',
    'invoices/store' => 'views/invoices/store.php',
    'invoices/recordPayment' => 'views/invoices/recordPayment.php',
    'invoices/cancel' => 'views/invoices/cancel.php',
    
    'reports' => 'views/reports/index.php',
    'reports/generate' => 'views/reports/generate.php',
    'dashboard/profile' => 'views/dashboard/profile.php',
    'dashboard/settings' => 'views/dashboard/settings.php',
    
    // Auth routes
    'auth/login' => 'views/auth/login.php',
    'auth/authenticate' => 'views/auth/authenticate.php',
    'auth/logout' => 'views/auth/logout.php',
];

// Check if the requested page exists in our routes
if (isset($routes[$page])) {
    $filepath = $routes[$page];
    
    // Check if the file exists
    if (file_exists($filepath)) {
        // Database connection
        require_once 'config/database.php';
        $conn = getDbConnection();
        
        // Include the page
        include $filepath;
    } else {
        // File doesn't exist
        header("HTTP/1.0 404 Not Found");
        echo "Page not found: {$filepath}";
    }
} else {
    // Route doesn't exist
    header("HTTP/1.0 404 Not Found");
    echo "Route not found: {$page}";
} 