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
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .form-label {
            font-weight: 500;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .btn-primary:hover {
            background: #003585;
        }
        .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            color: #495057;
        }
        .preview-image {
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 5px;
            background-color: #f8f9fa;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .form-section-title {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
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
            <li><a href="index.php?page=jet-skis" class="active"><i class="bi bi-water"></i> Jet Skis</a></li>
            <li><a href="index.php?page=boats"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
            <li><a href="index.php?page=clients"><i class="bi bi-people"></i> Clients</a></li>
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
            <h1 class="h4 mb-0"><?php echo htmlspecialchars($title); ?></h1>
            <a href="index.php?page=jet-skis" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Jet Skis
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
            <form action="index.php?page=jet-skis/<?php echo isset($is_edit) && $is_edit ? 'update' : 'store'; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php if (isset($is_edit) && $is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($jet_ski['id']); ?>">
                <?php endif; ?>
                
                <div class="form-section">
                    <h5 class="form-section-title">Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label required-field">Brand</label>
                            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($jet_ski['brand']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the brand name.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="model" class="form-label required-field">Model</label>
                            <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($jet_ski['model']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the model name.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label required-field">Year</label>
                            <input type="number" class="form-control" id="year" name="year" min="2000" max="<?php echo date('Y') + 1; ?>" value="<?php echo htmlspecialchars($jet_ski['year']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid year.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="registration_number" class="form-label required-field">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($jet_ski['registration_number']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the registration number.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label required-field">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="available" <?php echo $jet_ski['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="maintenance" <?php echo $jet_ski['status'] === 'maintenance' ? 'selected' : ''; ?>>In Maintenance</option>
                                <option value="rented" <?php echo $jet_ski['status'] === 'rented' ? 'selected' : ''; ?>>Currently Rented</option>
                                <option value="unavailable" <?php echo $jet_ski['status'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a status.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Pricing Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hourly_rate" class="form-label required-field">Hourly Rate ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" min="0" step="0.01" value="<?php echo htmlspecialchars($jet_ski['hourly_rate']); ?>" required>
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid hourly rate.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="daily_rate" class="form-label required-field">Daily Rate ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="daily_rate" name="daily_rate" min="0" step="0.01" value="<?php echo htmlspecialchars($jet_ski['daily_rate']); ?>" required>
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid daily rate.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Maintenance Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="last_maintenance_date" class="form-label">Last Maintenance Date</label>
                            <input type="date" class="form-control" id="last_maintenance_date" name="last_maintenance_date" value="<?php echo htmlspecialchars($jet_ski['last_maintenance_date']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                            <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date" value="<?php echo htmlspecialchars($jet_ski['next_maintenance_date']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Additional Information</h5>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($jet_ski['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="image" class="form-label">Jet Ski Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                        <div class="form-text">Upload a photo of the jet ski (JPEG, PNG, max 5MB)</div>
                        
                        <?php if (!empty($jet_ski['image_url'])): ?>
                            <div class="mt-2">
                                <p class="mb-1">Current Image:</p>
                                <img src="<?php echo htmlspecialchars($jet_ski['image_url']); ?>" alt="Current jet ski image" class="preview-image">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php?page=jet-skis" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> <?php echo isset($is_edit) && $is_edit ? 'Update' : 'Save'; ?> Jet Ski
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Check if preview image exists, if not create it
                    let previewContainer = document.querySelector('.preview-image');
                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.classList.add('mt-2');
                        previewContainer.innerHTML = `
                            <p class="mb-1">New Image Preview:</p>
                            <img src="${event.target.result}" alt="Jet ski image preview" class="preview-image">
                        `;
                        document.getElementById('image').parentNode.appendChild(previewContainer);
                    } else {
                        // Update existing preview
                        previewContainer.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Auto-calculate daily rate based on hourly rate
        document.getElementById('hourly_rate').addEventListener('input', function() {
            const hourlyRate = parseFloat(this.value) || 0;
            const dailyRate = hourlyRate * 8; // Assuming 8 hours per day
            document.getElementById('daily_rate').value = dailyRate.toFixed(2);
        });
    </script>
</body>
</html> 