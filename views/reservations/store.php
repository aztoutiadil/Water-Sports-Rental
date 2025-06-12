<?php
// Initialize variables to prevent undefined variable errors
$title = "Process Reservation";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $client_id = $_POST['client_id'] ?? '';
    $equipment_type = $_POST['equipment_type'] ?? '';
    $jet_ski_id = $_POST['jet_ski_id'] ?? null;
    $boat_id = $_POST['boat_id'] ?? null;
    $equipment_id = ($equipment_type === 'jet_ski') ? $jet_ski_id : $boat_id;
    
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $deposit_amount = $_POST['deposit_amount'] ?? 0;
    $total_amount = $_POST['total_amount'] ?? 0;
    
    // Additional services
    $add_instructor = isset($_POST['add_instructor']) ? 1 : 0;
    $add_equipment = isset($_POST['add_equipment']) ? 1 : 0;
    $add_insurance = isset($_POST['add_insurance']) ? 1 : 0;
    
    // Validate required fields
    $errors = [];
    
    if (empty($client_id)) {
        $errors[] = "Client is required";
    }
    
    if (empty($equipment_type)) {
        $errors[] = "Equipment type is required";
    } else {
        if ($equipment_type === 'jet_ski' && empty($jet_ski_id)) {
            $errors[] = "Please select a jet ski";
        } else if ($equipment_type === 'boat' && empty($boat_id)) {
            $errors[] = "Please select a boat";
        }
    }
    
    if (empty($start_date)) {
        $errors[] = "Start date is required";
    }
    
    if (empty($end_date)) {
        $errors[] = "End date is required";
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        if ($start > $end) {
            $errors[] = "End date cannot be before start date";
        }
    }
    
    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }
    
    // If there are validation errors
    if (!empty($errors)) {
        $_SESSION['flash']['error'] = "Please fix the following errors: " . implode(", ", $errors);
        
        // Redirect back to the form
        header('Location: index.php?page=reservations/create');
        exit;
    }
    
    // Save to database
    if (isset($conn)) {
        try {
            // First, check if the necessary columns exist in the reservations table
            // Get table structure
            $columnsQuery = "DESCRIBE reservations";
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
            if (!in_array('add_instructor', $existingColumns)) $missingColumns[] = "ADD COLUMN add_instructor TINYINT(1) DEFAULT 0 AFTER end_date";
            if (!in_array('add_equipment', $existingColumns)) $missingColumns[] = "ADD COLUMN add_equipment TINYINT(1) DEFAULT 0 AFTER add_instructor";
            if (!in_array('add_insurance', $existingColumns)) $missingColumns[] = "ADD COLUMN add_insurance TINYINT(1) DEFAULT 0 AFTER add_equipment";
            if (!in_array('total_amount', $existingColumns)) $missingColumns[] = "ADD COLUMN total_amount DECIMAL(10,2) NOT NULL AFTER add_insurance";
            if (!in_array('deposit_amount', $existingColumns)) $missingColumns[] = "ADD COLUMN deposit_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount";
            if (!in_array('deposit_paid', $existingColumns)) $missingColumns[] = "ADD COLUMN deposit_paid TINYINT(1) DEFAULT 0 AFTER deposit_amount";
            if (!in_array('payment_method', $existingColumns)) $missingColumns[] = "ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER deposit_paid";
            
            // Add missing columns if needed
            if (!empty($missingColumns)) {
                $alterTableSql = "ALTER TABLE reservations " . implode(", ", $missingColumns);
                $alterResult = $conn->query($alterTableSql);
                
                if ($alterResult === false) {
                    throw new Exception("Failed to update table structure: " . $conn->error);
                }
            }
            
            // Calculate if deposit is paid based on payment method
            $deposit_paid = ($payment_method !== 'cash') ? 1 : 0;
            
            // Use a simpler SQL statement to avoid column issues
            $sql = "INSERT INTO reservations SET 
                client_id = ?,
                equipment_type = ?,
                equipment_id = ?,
                start_date = ?,
                end_date = ?,
                total_amount = ?,
                deposit_amount = ?,
                deposit_paid = ?,
                status = 'pending',
                created_at = NOW()";
            
            // Add optional columns if they exist
            $params = [
                $client_id, $equipment_type, $equipment_id, $start_date, $end_date,
                $total_amount, $deposit_amount, $deposit_paid
            ];
            $types = "issssdd" . "i"; // i for integer, s for string, d for double
            
            // Add additional services and payment method if columns exist
            if (in_array('add_instructor', $existingColumns)) {
                $sql .= ", add_instructor = ?";
                $params[] = $add_instructor;
                $types .= "i";
            }
            
            if (in_array('add_equipment', $existingColumns)) {
                $sql .= ", add_equipment = ?";
                $params[] = $add_equipment;
                $types .= "i";
            }
            
            if (in_array('add_insurance', $existingColumns)) {
                $sql .= ", add_insurance = ?";
                $params[] = $add_insurance;
                $types .= "i";
            }
            
            if (in_array('payment_method', $existingColumns)) {
                $sql .= ", payment_method = ?";
                $params[] = $payment_method;
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
                throw new Exception("Failed to add reservation: " . $stmt->error);
            }
            
            // Get the last inserted ID
            $reservation_id = $conn->insert_id;
            
            // Close statement
            $stmt->close();
            
            // Update equipment status to 'booked' for the reservation period
            $equipmentTable = ($equipment_type === 'jet_ski') ? 'jet_skis' : 'boats';
            $updateSql = "UPDATE $equipmentTable SET status = 'booked' WHERE id = ?";
            
            $updateStmt = $conn->prepare($updateSql);
            if ($updateStmt !== false) {
                $updateStmt->bind_param("i", $equipment_id);
                $updateStmt->execute();
                $updateStmt->close();
            }
            
            // Store success message
            $_SESSION['flash']['success'] = "Reservation has been created successfully! Reservation ID: #$reservation_id";
            
            // Redirect to reservations list
            header('Location: index.php?page=reservations');
            exit;
            
        } catch (Exception $e) {
            // Log the error (in a real application)
            error_log($e->getMessage());
            
            // Store error message
            $_SESSION['flash']['error'] = "Error creating reservation: " . $e->getMessage();
            
            // Redirect back to the form
            header('Location: index.php?page=reservations/create');
            exit;
        }
    } else {
        // If no database connection, simulate success for demo purposes
        $_SESSION['flash']['success'] = "Reservation has been created successfully! (Dummy mode - no database connection)";
        
        // Redirect to reservations list
        header('Location: index.php?page=reservations');
        exit;
    }
} else {
    // If not POST request, redirect to create form
    header('Location: index.php?page=reservations/create');
    exit;
} 