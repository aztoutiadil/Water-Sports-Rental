<?php
// Initialize variables to prevent undefined variable errors
$title = "Process Client";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $address_line2 = $_POST['address_line2'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip_code = $_POST['zip_code'] ?? '';
    $country = $_POST['country'] ?? '';
    $id_type = $_POST['id_type'] ?? '';
    $id_number = $_POST['id_number'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($city)) {
        $errors[] = "City is required";
    }
    
    if (empty($country)) {
        $errors[] = "Country is required";
    }
    
    // Handle file upload for ID document
    $id_document_path = '';
    if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/clients/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = time() . '_' . basename($_FILES['id_document']['name']);
        $target_file = $upload_dir . $filename;
        
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only PDF, JPG, JPEG, and PNG files are allowed for ID document";
        } else {
            if (move_uploaded_file($_FILES['id_document']['tmp_name'], $target_file)) {
                $id_document_path = $target_file;
            } else {
                $errors[] = "Failed to upload ID document";
            }
        }
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        $_SESSION['flash']['error'] = "Please fix the following errors: " . implode(", ", $errors);
        
        // Redirect back to the form
        header('Location: index.php?page=clients/create');
        exit;
    }
    
    // Save to database
    try {
        // First, check if the necessary columns exist in the clients table
        // Get table structure
        $columnsQuery = "DESCRIBE clients";
        $columnsResult = $conn->query($columnsQuery);
        
        if ($columnsResult === false) {
            throw new Exception("Failed to get table structure: " . $conn->error);
        }
        
        // Initialize arrays to track columns
        $existingColumns = [];
        while ($column = $columnsResult->fetch_assoc()) {
            $existingColumns[] = strtolower($column['Field']);
        }
        
        // Check if we need to add columns
        $missingColumns = [];
        if (!in_array('state', $existingColumns)) $missingColumns[] = "ADD COLUMN state VARCHAR(50) DEFAULT '' AFTER city";
        if (!in_array('zip_code', $existingColumns)) $missingColumns[] = "ADD COLUMN zip_code VARCHAR(20) DEFAULT '' AFTER state";
        if (!in_array('country', $existingColumns)) $missingColumns[] = "ADD COLUMN country VARCHAR(50) DEFAULT 'United States' AFTER zip_code";
        if (!in_array('address_line2', $existingColumns)) $missingColumns[] = "ADD COLUMN address_line2 VARCHAR(255) DEFAULT '' AFTER address";
        if (!in_array('id_type', $existingColumns)) $missingColumns[] = "ADD COLUMN id_type VARCHAR(50) DEFAULT '' AFTER country";
        if (!in_array('id_number', $existingColumns)) $missingColumns[] = "ADD COLUMN id_number VARCHAR(50) DEFAULT '' AFTER id_type";
        if (!in_array('id_document_url', $existingColumns)) $missingColumns[] = "ADD COLUMN id_document_url VARCHAR(255) DEFAULT '' AFTER id_number";
        if (!in_array('notes', $existingColumns)) $missingColumns[] = "ADD COLUMN notes TEXT AFTER id_document_url";
        if (!in_array('status', $existingColumns)) $missingColumns[] = "ADD COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'active' AFTER notes";
        if (!in_array('birthdate', $existingColumns)) $missingColumns[] = "ADD COLUMN birthdate DATE AFTER status";
        if (!in_array('gender', $existingColumns)) $missingColumns[] = "ADD COLUMN gender VARCHAR(10) DEFAULT '' AFTER birthdate";
        
        // Add missing columns if needed
        if (!empty($missingColumns)) {
            $alterTableSql = "ALTER TABLE clients " . implode(", ", $missingColumns);
            $alterResult = $conn->query($alterTableSql);
            
            if ($alterResult === false) {
                throw new Exception("Failed to update table structure: " . $conn->error);
            }
        }
        
        // Use a simpler SQL statement to avoid column issues
        $sql = "INSERT INTO clients SET 
            first_name = ?,
            last_name = ?,
            email = ?,
            phone = ?,
            address = ?,
            city = ?,
            registration_date = NOW()";
        
        // Add optional columns if they exist
        $params = [$first_name, $last_name, $email, $phone, $address, $city];
        $types = "ssssss"; // s for string
        
        if (in_array('address_line2', $existingColumns)) {
            $sql .= ", address_line2 = ?";
            $params[] = $address_line2;
            $types .= "s";
        }
        
        if (in_array('state', $existingColumns)) {
            $sql .= ", state = ?";
            $params[] = $state;
            $types .= "s";
        }
        
        if (in_array('zip_code', $existingColumns)) {
            $sql .= ", zip_code = ?";
            $params[] = $zip_code;
            $types .= "s";
        }
        
        if (in_array('country', $existingColumns)) {
            $sql .= ", country = ?";
            $params[] = $country;
            $types .= "s";
        }
        
        if (in_array('id_type', $existingColumns)) {
            $sql .= ", id_type = ?";
            $params[] = $id_type;
            $types .= "s";
        }
        
        if (in_array('id_number', $existingColumns)) {
            $sql .= ", id_number = ?";
            $params[] = $id_number;
            $types .= "s";
        }
        
        if (in_array('id_document_url', $existingColumns)) {
            $sql .= ", id_document_url = ?";
            $params[] = $id_document_path;
            $types .= "s";
        }
        
        if (in_array('notes', $existingColumns)) {
            $sql .= ", notes = ?";
            $params[] = $notes;
            $types .= "s";
        }
        
        if (in_array('birthdate', $existingColumns) && !empty($birthdate)) {
            $sql .= ", birthdate = ?";
            $params[] = $birthdate;
            $types .= "s";
        }
        
        if (in_array('gender', $existingColumns)) {
            $sql .= ", gender = ?";
            $params[] = $gender;
            $types .= "s";
        }
        
        // Create prepared statement
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param($types, ...$params);
        
        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Failed to add client: " . $stmt->error);
        }
        
        // Get the last inserted ID
        $client_id = $conn->insert_id;
        
        // Close statement
        $stmt->close();
        
        // Store success message
        $_SESSION['flash']['success'] = "Client $first_name $last_name has been added successfully!";
        
        // Redirect to client list
        header('Location: index.php?page=clients');
        exit;
        
    } catch (Exception $e) {
        // Log the error (in a real application)
        error_log($e->getMessage());
        
        // Store error message
        $_SESSION['flash']['error'] = "Error adding client: " . $e->getMessage();
        
        // Redirect back to the form
        header('Location: index.php?page=clients/create');
        exit;
    }
} else {
    // If not POST request, redirect to create form
    header('Location: index.php?page=clients/create');
    exit;
} 