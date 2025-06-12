<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0043a8;
            --secondary-color: #0099ff;
        }
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-color);
            padding: 1rem;
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-logo {
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar-menu i {
            margin-right: 0.5rem;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }
        .top-bar {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: #003585;
        }
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .form-label {
            font-weight: 500;
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .form-section-title {
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .file-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }
        .file-upload-container:hover {
            border-color: var(--primary-color);
        }
        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }
        .preview-document {
            max-width: 100%;
            height: auto;
            margin-top: 1rem;
            display: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .document-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 1rem;
        }
        .document-info {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 0.5rem;
        }
        .document-info i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h4>Water Sports Rental</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php?page=dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="index.php?page=jet-skis"><i class="bi bi-water"></i> Jet Skis</a></li>
            <li><a href="index.php?page=boats"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
            <li><a href="index.php?page=clients" class="active"><i class="bi bi-people"></i> Clients</a></li>
            <li><a href="index.php?page=reservations"><i class="bi bi-calendar-check"></i> Reservations</a></li>
            <li><a href="index.php?page=invoices"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="h4 mb-0"><?php echo isset($client) ? 'Edit Client' : 'Add New Client'; ?></h1>
            <a href="index.php?page=clients" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Clients
            </a>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> mb-3">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="form-container">
            <form action="index.php?page=clients/<?php echo isset($client) ? 'update&id=' . $client['id'] : 'store'; ?>" method="POST" enctype="multipart/form-data" id="clientForm">
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Personal Information</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label required-field">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['first_name']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label required-field">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['last_name']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['email']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label required-field">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['phone']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="birthdate" class="form-label required-field">Date of Birth</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['birthdate']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo (isset($client) && $client['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($client) && $client['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending" <?php echo (isset($client) && $client['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Identification Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Identification</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_type" class="form-label required-field">ID Type</label>
                            <select class="form-select" id="id_type" name="id_type" required>
                                <option value="">Select ID Type</option>
                                <option value="National ID" <?php echo (isset($client) && $client['id_type'] === 'National ID') ? 'selected' : ''; ?>>National ID</option>
                                <option value="Passport" <?php echo (isset($client) && $client['id_type'] === 'Passport') ? 'selected' : ''; ?>>Passport</option>
                                <option value="Driver's License" <?php echo (isset($client) && $client['id_type'] === "Driver's License") ? 'selected' : ''; ?>>Driver's License</option>
                                <option value="Residence Permit" <?php echo (isset($client) && $client['id_type'] === 'Residence Permit') ? 'selected' : ''; ?>>Residence Permit</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="id_number" class="form-label required-field">ID Number</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['id_number']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="id_document" class="form-label">ID Document <?php echo isset($client) && !empty($client['id_document']) ? '(Leave empty to keep current)' : 'required-field'; ?></label>
                            <div class="file-upload-container">
                                <i class="bi bi-upload fs-3 mb-2 text-primary"></i>
                                <p>Drag and drop your ID document or <strong>click to browse</strong></p>
                                <input type="file" class="form-control visually-hidden" id="id_document" name="id_document" accept="image/*,.pdf" 
                                    <?php echo isset($client) && !empty($client['id_document']) ? '' : 'required'; ?>>
                                <small class="text-muted d-block mt-2">Acceptable formats: JPEG, PNG, PDF. Max size: 5MB</small>
                            </div>
                            
                            <?php if (isset($client) && !empty($client['id_document'])): ?>
                                <div class="document-preview">
                                    <div class="document-info">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <div>
                                            <p class="mb-0"><?php echo htmlspecialchars(basename($client['id_document'])); ?></p>
                                            <small class="text-muted">Current document</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="document-preview" id="newDocumentPreview" style="display: none;">
                                <div class="document-info">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <div>
                                        <p class="mb-0" id="documentName">No file selected</p>
                                        <small class="text-muted">New document to upload</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Address</h3>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="address" class="form-label required-field">Street Address</label>
                            <input type="text" class="form-control" id="address" name="address" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['address']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label required-field">City</label>
                            <input type="text" class="form-control" id="city" name="city" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['city']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label required-field">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['state']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="postal_code" class="form-label required-field">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required
                                value="<?php echo isset($client) ? htmlspecialchars($client['postal_code']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label required-field">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="">Select Country</option>
                                <option value="United States" <?php echo (isset($client) && $client['country'] === 'United States') ? 'selected' : ''; ?>>United States</option>
                                <option value="Canada" <?php echo (isset($client) && $client['country'] === 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                <option value="Mexico" <?php echo (isset($client) && $client['country'] === 'Mexico') ? 'selected' : ''; ?>>Mexico</option>
                                <option value="United Kingdom" <?php echo (isset($client) && $client['country'] === 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="France" <?php echo (isset($client) && $client['country'] === 'France') ? 'selected' : ''; ?>>France</option>
                                <option value="Germany" <?php echo (isset($client) && $client['country'] === 'Germany') ? 'selected' : ''; ?>>Germany</option>
                                <option value="Spain" <?php echo (isset($client) && $client['country'] === 'Spain') ? 'selected' : ''; ?>>Spain</option>
                                <option value="Italy" <?php echo (isset($client) && $client['country'] === 'Italy') ? 'selected' : ''; ?>>Italy</option>
                                <option value="Australia" <?php echo (isset($client) && $client['country'] === 'Australia') ? 'selected' : ''; ?>>Australia</option>
                                <option value="Japan" <?php echo (isset($client) && $client['country'] === 'Japan') ? 'selected' : ''; ?>>Japan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Additional Information</h3>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo isset($client) ? htmlspecialchars($client['notes']) : ''; ?></textarea>
                            <small class="text-muted">Any relevant information about the client, including preferences or special requirements.</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-outline-secondary me-md-2" onclick="window.location.href='index.php?page=clients'">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> <?php echo isset($client) ? 'Update Client' : 'Save Client'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File upload preview
            const idDocument = document.getElementById('id_document');
            const documentPreview = document.getElementById('newDocumentPreview');
            const documentName = document.getElementById('documentName');
            const fileUploadContainer = document.querySelector('.file-upload-container');
            
            fileUploadContainer.addEventListener('click', function() {
                idDocument.click();
            });
            
            idDocument.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    documentName.textContent = this.files[0].name;
                    documentPreview.style.display = 'block';
                    
                    // Add visual feedback
                    fileUploadContainer.style.borderColor = '#0043a8';
                    
                    // Check file size
                    const fileSize = this.files[0].size / 1024 / 1024; // in MB
                    if (fileSize > 5) {
                        alert('File size exceeds 5MB. Please choose a smaller file.');
                        this.value = '';
                        documentPreview.style.display = 'none';
                        fileUploadContainer.style.borderColor = '#dc3545';
                    }
                }
            });
            
            // Form validation
            const form = document.getElementById('clientForm');
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Basic validation for required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                // Email validation
                const emailField = document.getElementById('email');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailField.value && !emailPattern.test(emailField.value)) {
                    emailField.classList.add('is-invalid');
                    isValid = false;
                }
                
                // Phone validation
                const phoneField = document.getElementById('phone');
                const phonePattern = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
                if (phoneField.value && !phonePattern.test(phoneField.value)) {
                    phoneField.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    event.preventDefault();
                    alert('Please check the form for errors and try again.');
                }
            });
            
            // Real-time validation feedback
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(function(input) {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                    
                    // Email validation
                    if (this.id === 'email' && this.value) {
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(this.value)) {
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                        }
                    }
                    
                    // Phone validation
                    if (this.id === 'phone' && this.value) {
                        const phonePattern = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
                        if (!phonePattern.test(this.value)) {
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                        }
                    }
                });
            });
            
            // Prevent form submission on Enter key press
            form.addEventListener('keypress', function(event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html> 