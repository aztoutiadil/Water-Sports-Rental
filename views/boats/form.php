<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Water Sports Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            <li><a href="index.php?page=jet-skis"><i class="bi bi-water"></i> Jet Skis</a></li>
            <li><a href="index.php?page=boats" class="active"><i class="bi bi-tsunami"></i> Tourist Boats</a></li>
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
            <a href="index.php?page=boats" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Tourist Boats
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
            <form action="index.php?page=boats/<?php echo isset($is_edit) && $is_edit ? 'update' : 'store'; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php if (isset($is_edit) && $is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($boat['id']); ?>">
                <?php endif; ?>
                
                <div class="form-section">
                    <h5 class="form-section-title">Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label required-field">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($boat['name']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the boat name.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label required-field">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="" disabled <?php echo empty($boat['type']) ? 'selected' : ''; ?>>Select boat type</option>
                                <option value="Pontoon Boat" <?php echo $boat['type'] === 'Pontoon Boat' ? 'selected' : ''; ?>>Pontoon Boat</option>
                                <option value="Cabin Cruiser" <?php echo $boat['type'] === 'Cabin Cruiser' ? 'selected' : ''; ?>>Cabin Cruiser</option>
                                <option value="Speedboat" <?php echo $boat['type'] === 'Speedboat' ? 'selected' : ''; ?>>Speedboat</option>
                                <option value="Sailboat" <?php echo $boat['type'] === 'Sailboat' ? 'selected' : ''; ?>>Sailboat</option>
                                <option value="Yacht" <?php echo $boat['type'] === 'Yacht' ? 'selected' : ''; ?>>Yacht</option>
                                <option value="Fishing Boat" <?php echo $boat['type'] === 'Fishing Boat' ? 'selected' : ''; ?>>Fishing Boat</option>
                                <option value="Other" <?php echo $boat['type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select the boat type.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label required-field">Year</label>
                            <input type="number" class="form-control" id="year" name="year" min="2000" max="<?php echo date('Y') + 1; ?>" value="<?php echo htmlspecialchars($boat['year']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid year.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="capacity" class="form-label required-field">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" max="100" value="<?php echo htmlspecialchars($boat['capacity']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the boat capacity.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="registration_number" class="form-label required-field">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($boat['registration_number']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the registration number.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Pricing & Availability</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="hourly_rate" class="form-label required-field">Hourly Rate ($)</label>
                            <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" min="0" step="0.01" value="<?php echo htmlspecialchars($boat['hourly_rate']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the hourly rate.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="daily_rate" class="form-label required-field">Daily Rate ($)</label>
                            <input type="number" class="form-control" id="daily_rate" name="daily_rate" min="0" step="0.01" value="<?php echo htmlspecialchars($boat['daily_rate']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the daily rate.
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label required-field">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="available" <?php echo $boat['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="booked" <?php echo $boat['status'] === 'booked' ? 'selected' : ''; ?>>Booked</option>
                                <option value="maintenance" <?php echo $boat['status'] === 'maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                                <option value="out_of_service" <?php echo $boat['status'] === 'out_of_service' ? 'selected' : ''; ?>>Out of Service</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select the boat status.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Features & Maintenance</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="features" class="form-label">Features</label>
                            <textarea class="form-control" id="features" name="features" rows="3" placeholder="GPS, Refrigerator, Bathroom, Sunshade, etc."><?php echo htmlspecialchars($boat['features']); ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="last_maintenance_date" class="form-label">Last Maintenance Date</label>
                            <input type="date" class="form-control" id="last_maintenance_date" name="last_maintenance_date" value="<?php echo htmlspecialchars($boat['last_maintenance_date']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                            <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date" value="<?php echo htmlspecialchars($boat['next_maintenance_date']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="form-section-title">Image & Additional Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Upload Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
                            <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, JPEG, PNG.</div>
                            
                            <?php if (!empty($boat['image_url'])): ?>
                                <div class="mt-2">
                                    <p class="mb-1">Current Image:</p>
                                    <img src="<?php echo htmlspecialchars($boat['image_url']); ?>" alt="Boat image" class="preview-image">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($boat['notes']); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="index.php?page=boats" class="btn btn-outline-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> 
                        <?php echo isset($is_edit) && $is_edit ? 'Update Tourist Boat' : 'Add Tourist Boat'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                            <img src="${event.target.result}" alt="Boat image preview" class="preview-image">
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
        
        // Initialize date pickers
        flatpickr("#last_maintenance_date", {
            dateFormat: "Y-m-d",
        });
        
        flatpickr("#next_maintenance_date", {
            dateFormat: "Y-m-d",
        });
    </script>
</body>
</html> 