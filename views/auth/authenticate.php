<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // In a real application, you would validate against a database
    // For demonstration, we'll use hardcoded credentials
    $valid_credentials = [
        'admin' => [
            'password' => 'admin123',
            'name' => 'Admin User',
            'role' => 'administrator',
            'email' => 'admin@example.com'
        ],
        'agent' => [
            'password' => 'agent123',
            'name' => 'Agent User',
            'role' => 'agent',
            'email' => 'agent@example.com'
        ]
    ];
    
    // Check if username exists and password matches
    if (isset($valid_credentials[$username]) && $valid_credentials[$username]['password'] === $password) {
        // Authentication successful
        
        // Store user information in session
        $_SESSION['user'] = [
            'id' => uniqid(), // In a real app, this would be the user ID from the database
            'username' => $username,
            'name' => $valid_credentials[$username]['name'],
            'role' => $valid_credentials[$username]['role'],
            'email' => $valid_credentials[$username]['email'],
            'logged_in' => true
        ];
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
            
            // In a real app, you would store this token in the database
            $_SESSION['remember_token'] = $token;
        }
        
        // Redirect to dashboard
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        // Authentication failed
        $_SESSION['login_error'] = 'Invalid username or password';
        
        // Redirect back to login page
        header('Location: index.php?page=auth/login');
        exit;
    }
} else {
    // If not a POST request, redirect to login page
    header('Location: index.php?page=auth/login');
    exit;
}
?> 