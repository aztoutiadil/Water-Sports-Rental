<?php
// Check if previous steps are completed
if (empty($_SESSION['booking']['equipment_type']) || empty($_SESSION['booking']['equipment_id']) || 
    empty($_SESSION['booking']['start_date']) || empty($_SESSION['booking']['end_date'])) {
    header('Location: booking.php?step=1');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'])) {
    // Validate required fields
    $requiredFields = [
        'first_name', 'last_name', 'email', 'phone', 
        'address', 'city', 'state', 'zip_code', 
        'id_type', 'id_number'
    ];
    
    $errors = [];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Check if the client already exists (by email)
    if (empty($errors) && !empty($_POST['email'])) {
        $sql = "SELECT id FROM clients WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $_POST['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Client exists, store client ID
            $client = $result->fetch_assoc();
            $_SESSION['booking']['client_id'] = $client['id'];
        }
    }
    
    // If no errors, save client information
    if (empty($errors)) {
        $_SESSION['booking']['client'] = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'zip_code' => $_POST['zip_code'],
            'country' => $_POST['country'],
            'id_type' => $_POST['id_type'],
            'id_number' => $_POST['id_number'],
            'notes' => $_POST['notes'] ?? ''
        ];
        
        // Upload ID document if provided
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../uploads/client-documents/";
            
            // Create directory if it doesn't exist
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            // Generate a unique filename
            $fileExtension = pathinfo($_FILES['id_document']['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('id_doc_') . '.' . $fileExtension;
            $targetFile = $targetDir . $newFilename;
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['id_document']['tmp_name'], $targetFile)) {
                $_SESSION['booking']['client']['id_document_url'] = 'uploads/client-documents/' . $newFilename;
            } else {
                $errors[] = 'Failed to upload ID document. Please try again.';
            }
        }
        
        if (empty($errors)) {
            // Redirect to the next step
            header('Location: booking.php?step=5');
            exit;
        }
    }
}

// Get equipment and booking details for display
$equipmentType = $_SESSION['booking']['equipment_type'];
$equipmentId = $_SESSION['booking']['equipment_id'];
$equipment = getEquipmentDetails($equipmentType, $equipmentId, $conn);
$startDate = new DateTime($_SESSION['booking']['start_date']);
$endDate = new DateTime($_SESSION['booking']['end_date']);
$totalAmount = $_SESSION['booking']['total_amount'];
$depositAmount = $_SESSION['booking']['deposit_amount'];
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <h3 class="mb-4">Your Information</h3>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Rental Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Equipment:</strong> 
                                <?php echo htmlspecialchars($equipment['display_name']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Type:</strong> 
                                <?php echo $equipmentType === 'jet_ski' ? 'Jet Ski' : 'Tourist Boat'; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Start Date:</strong> 
                                <?php echo $startDate->format('M d, Y h:i A'); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>End Date:</strong> 
                                <?php echo $endDate->format('M d, Y h:i A'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Amount:</strong> 
                                <span class="text-primary">$<?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Required Deposit (25%):</strong> 
                                <span class="text-success">$<?php echo number_format($depositAmount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <?php if (!empty($equipment['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($equipment['image_url']); ?>" alt="Equipment" class="img-fluid rounded mb-3" style="max-height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="height: 120px;">
                                <i class="bi bi-<?php echo $equipmentType === 'jet_ski' ? 'water' : 'tsunami'; ?> text-secondary" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <h5><?php echo htmlspecialchars($equipment['display_name']); ?></h5>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label required">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                value="<?php echo isset($_SESSION['booking']['client']['first_name']) ? htmlspecialchars($_SESSION['booking']['client']['first_name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label required">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                value="<?php echo isset($_SESSION['booking']['client']['last_name']) ? htmlspecialchars($_SESSION['booking']['client']['last_name']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label required">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?php echo isset($_SESSION['booking']['client']['email']) ? htmlspecialchars($_SESSION['booking']['client']['email']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                value="<?php echo isset($_SESSION['booking']['client']['phone']) ? htmlspecialchars($_SESSION['booking']['client']['phone']) : ''; ?>" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Address Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="address" class="form-label required">Street Address</label>
                        <input type="text" class="form-control" id="address" name="address" 
                            value="<?php echo isset($_SESSION['booking']['client']['address']) ? htmlspecialchars($_SESSION['booking']['client']['address']) : ''; ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label required">City</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                value="<?php echo isset($_SESSION['booking']['client']['city']) ? htmlspecialchars($_SESSION['booking']['client']['city']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label required">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state" 
                                value="<?php echo isset($_SESSION['booking']['client']['state']) ? htmlspecialchars($_SESSION['booking']['client']['state']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="zip_code" class="form-label required">Zip/Postal Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                value="<?php echo isset($_SESSION['booking']['client']['zip_code']) ? htmlspecialchars($_SESSION['booking']['client']['zip_code']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label required">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="United States" <?php echo (isset($_SESSION['booking']['client']['country']) && $_SESSION['booking']['client']['country'] === 'United States') ? 'selected' : ''; ?>>United States</option>
                                <option value="Canada" <?php echo (isset($_SESSION['booking']['client']['country']) && $_SESSION['booking']['client']['country'] === 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                <option value="Mexico" <?php echo (isset($_SESSION['booking']['client']['country']) && $_SESSION['booking']['client']['country'] === 'Mexico') ? 'selected' : ''; ?>>Mexico</option>
                                <option value="United Kingdom" <?php echo (isset($_SESSION['booking']['client']['country']) && $_SESSION['booking']['client']['country'] === 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="Australia" <?php echo (isset($_SESSION['booking']['client']['country']) && $_SESSION['booking']['client']['country'] === 'Australia') ? 'selected' : ''; ?>>Australia</option>
                                <option value="Other" <?php echo (isset($_SESSION['booking']['client']['country']) && !in_array($_SESSION['booking']['client']['country'], ['United States', 'Canada', 'Mexico', 'United Kingdom', 'Australia'])) ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Identification</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_type" class="form-label required">ID Type</label>
                            <select class="form-select" id="id_type" name="id_type" required>
                                <option value="">Select ID Type</option>
                                <option value="Driver's License" <?php echo (isset($_SESSION['booking']['client']['id_type']) && $_SESSION['booking']['client']['id_type'] === "Driver's License") ? 'selected' : ''; ?>>Driver's License</option>
                                <option value="Passport" <?php echo (isset($_SESSION['booking']['client']['id_type']) && $_SESSION['booking']['client']['id_type'] === 'Passport') ? 'selected' : ''; ?>>Passport</option>
                                <option value="National ID" <?php echo (isset($_SESSION['booking']['client']['id_type']) && $_SESSION['booking']['client']['id_type'] === 'National ID') ? 'selected' : ''; ?>>National ID</option>
                                <option value="Residence Permit" <?php echo (isset($_SESSION['booking']['client']['id_type']) && $_SESSION['booking']['client']['id_type'] === 'Residence Permit') ? 'selected' : ''; ?>>Residence Permit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_number" class="form-label required">ID Number</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" 
                                value="<?php echo isset($_SESSION['booking']['client']['id_number']) ? htmlspecialchars($_SESSION['booking']['client']['id_number']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="id_document" class="form-label">Upload ID Document (Optional)</label>
                        <input type="file" class="form-control" id="id_document" name="id_document" accept="image/*,.pdf">
                        <div class="form-text">Please upload a scan or photo of your ID document (max 5MB, JPG/PNG/PDF)</div>
                        
                        <?php if (isset($_SESSION['booking']['client']['id_document_url'])): ?>
                            <div class="mt-2">
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> ID Document already uploaded</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Special Requests or Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_SESSION['booking']['client']['notes']) ? htmlspecialchars($_SESSION['booking']['client']['notes']) : ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="booking.php?step=3" class="btn btn-outline-secondary me-2">Back</a>
                <button type="submit" class="btn btn-primary">Continue to Payment</button>
            </div>
        </form>
    </div>
</div>

<style>
.required::after {
    content: ' *';
    color: red;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File size validation
    const idDocument = document.getElementById('id_document');
    
    idDocument.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const fileSize = this.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 5) {
                alert('File size exceeds 5MB. Please choose a smaller file.');
                this.value = '';
            }
        }
    });
});</script> 