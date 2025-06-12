<?php
// Initialize variables to prevent undefined variable errors
$title = "Add Client";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}
?>
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
            --accent-color: #00c8ff;
            --bg-light: #f8f9fa;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --transition-speed: 0.3s;
        }
        
        body {
            min-height: 100vh;
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), #003080);
            padding: 1rem;
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all var(--transition-speed);
        }
        
        .sidebar-logo {
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-logo img {
            width: 50px;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }
        
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all var(--transition-speed);
            font-weight: 500;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-menu i {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin var(--transition-speed);
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background: #003585;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-section-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .custom-file-upload {
            border: 1px dashed #ddd;
            border-radius: 10px;
            padding: 2rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all var(--transition-speed);
        }
        
        .custom-file-upload:hover {
            border-color: var(--secondary-color);
            background-color: rgba(0, 153, 255, 0.05);
        }
        
        .custom-file-upload i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                padding: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Add New Client</h1>
            <a href="index.php?page=clients" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Clients
            </a>
        </div>
        
        <div class="form-card">
            <form action="index.php?page=clients/store" method="POST" enctype="multipart/form-data">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-person"></i> Personal Information</h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="birthdate" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">-- Select Gender --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-geo-alt"></i> Contact Information</h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="address_line1" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" id="address_line1" name="address" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="address_line2" name="address_line2">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Postal/Zip Code</label>
                            <input type="text" class="form-control" id="postal_code" name="zip_code" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="">-- Select Country --</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="UK">United Kingdom</option>
                                <option value="AU">Australia</option>
                                <option value="FR">France</option>
                                <option value="DE">Germany</option>
                                <option value="ES">Spain</option>
                                <!-- Add more countries as needed -->
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact">
                        </div>
                    </div>
                </div>
                
                <!-- Identification -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-card-text"></i> Identification</h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_type" class="form-label">ID Type</label>
                            <select class="form-select" id="id_type" name="id_type" required>
                                <option value="">-- Select ID Type --</option>
                                <option value="passport">Passport</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="national_id">National ID</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="id_number" class="form-label">ID Number</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="id_expiry" class="form-label">ID Expiry Date</label>
                            <input type="date" class="form-control" id="id_expiry" name="id_expiry" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="id_document" class="form-label">ID Document (Photo/Scan)</label>
                            <div class="custom-file-upload" onclick="document.getElementById('id_document').click()">
                                <input type="file" id="id_document" name="id_document" style="display:none" accept="image/*,.pdf">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <p class="mb-0">Click to upload or drag and drop</p>
                                <p class="text-muted small">JPG, PNG or PDF (max. 5MB)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-info-circle"></i> Additional Information</h3>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="how_heard" class="form-label">How did you hear about us?</label>
                            <select class="form-select" id="how_heard" name="how_heard">
                                <option value="">-- Select Option --</option>
                                <option value="search_engine">Search Engine</option>
                                <option value="social_media">Social Media</option>
                                <option value="referral">Friend/Family Referral</option>
                                <option value="advertisement">Advertisement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="preferred_contact" class="form-label">Preferred Contact Method</label>
                            <select class="form-select" id="preferred_contact" name="preferred_contact">
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="special_requirements" class="form-label">Special Requirements or Notes</label>
                            <textarea class="form-control" id="special_requirements" name="special_requirements" rows="3"></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="marketing_consent" name="marketing_consent" value="1">
                                <label class="form-check-label" for="marketing_consent">
                                    I agree to receive marketing communications from Water Sports Rental
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php?page=clients" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Display filename when file is selected
        document.getElementById('id_document').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file selected';
            const fileUpload = this.closest('.custom-file-upload');
            const fileInfo = fileUpload.querySelector('.text-muted');
            
            if (this.files[0]) {
                fileInfo.textContent = fileName;
                fileUpload.style.borderColor = '#0099ff';
                fileUpload.style.backgroundColor = 'rgba(0, 153, 255, 0.05)';
            } else {
                fileInfo.textContent = 'JPG, PNG or PDF (max. 5MB)';
                fileUpload.style.borderColor = '#ddd';
                fileUpload.style.backgroundColor = '';
            }
        });
    </script>
</body>
</html> 