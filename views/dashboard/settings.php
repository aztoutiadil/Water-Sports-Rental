<?php
// Initialize variables to prevent undefined variable errors
$title = "Settings";

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Get user data
$user = $_SESSION['user'];

// Initialize settings variable
$settings = [];
// In a real application, settings would be fetched from database
// Example: $settings = fetch_settings_from_database($user['id']);
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
        .settings-card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
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
            font-weight: 500;
        }
        .btn-primary:hover {
            background: #003585;
        }
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .nav-pills .nav-link {
            color: #6c757d;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        .nav-pills .nav-link:hover:not(.active) {
            background-color: rgba(0, 67, 168, 0.1);
        }
        .settings-header {
            border-bottom: 1px solid #f1f1f1;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
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
            <li><a href="index.php?page=clients"><i class="bi bi-people"></i> Clients</a></li>
            <li><a href="index.php?page=reservations"><i class="bi bi-calendar-check"></i> Reservations</a></li>
            <li><a href="index.php?page=invoices"><i class="bi bi-receipt"></i> Invoices</a></li>
            <li><a href="index.php?page=reports"><i class="bi bi-bar-chart"></i> Reports</a></li>
            <li><a href="index.php?page=dashboard/profile"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="index.php?page=dashboard/settings" class="active"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1 class="h4 mb-0">Settings</h1>
            <div class="user-info d-flex align-items-center">
                <span class="me-2">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <div class="dropdown">
                    <a href="#" class="text-decoration-none" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="index.php?page=dashboard/profile"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="index.php?page=dashboard/settings"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?page=auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> mb-3">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="list-group settings-nav">
                    <a class="list-group-item list-group-item-action active" id="general-tab" data-bs-toggle="list" href="#general" role="tab" aria-controls="general">
                        <i class="bi bi-gear me-2"></i> General
                    </a>
                    <a class="list-group-item list-group-item-action" id="notifications-tab" data-bs-toggle="list" href="#notifications" role="tab" aria-controls="notifications">
                        <i class="bi bi-bell me-2"></i> Notifications
                    </a>
                    <a class="list-group-item list-group-item-action" id="billing-tab" data-bs-toggle="list" href="#billing" role="tab" aria-controls="billing">
                        <i class="bi bi-cash-coin me-2"></i> Billing & Payments
                    </a>
                    <a class="list-group-item list-group-item-action" id="maintenance-tab" data-bs-toggle="list" href="#maintenance" role="tab" aria-controls="maintenance">
                        <i class="bi bi-tools me-2"></i> Maintenance
                    </a>
                    <a class="list-group-item list-group-item-action" id="advanced-tab" data-bs-toggle="list" href="#advanced" role="tab" aria-controls="advanced">
                        <i class="bi bi-sliders me-2"></i> Advanced
                    </a>
                </div>
            </div>
            
            <div class="col-md-9">
                <form action="index.php?page=dashboard/settings" method="POST" class="needs-validation" novalidate>
                    <div class="tab-content">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <div class="settings-card">
                                <div class="settings-header">
                                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i> General Settings</h5>
                                </div>
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="settings[company_name]" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'Water Sports Rental'); ?>" required>
                                    <div class="invalid-feedback">Please enter your company name.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" id="contact_email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="tel" class="form-control" id="contact_phone" name="settings[contact_phone]" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Please enter a contact phone number.</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="business_address" class="form-label">Business Address</label>
                                    <textarea class="form-control" id="business_address" name="settings[business_address]" rows="3"><?php echo htmlspecialchars($settings['business_address'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="website" class="form-label">Website URL</label>
                                    <input type="url" class="form-control" id="website" name="settings[website]" value="<?php echo htmlspecialchars($settings['website'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                            <div class="settings-card">
                                <div class="settings-header">
                                    <h5 class="mb-0"><i class="bi bi-bell me-2"></i> Notification Settings</h5>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Notifications</label>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="email_new_booking" name="settings[email_new_booking]" <?php echo ($settings['email_new_booking'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_new_booking">New reservation notifications</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="email_booking_reminders" name="settings[email_booking_reminders]" <?php echo ($settings['email_booking_reminders'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_booking_reminders">Reservation reminder notifications</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="email_maintenance" name="settings[email_maintenance]" <?php echo ($settings['email_maintenance'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_maintenance">Equipment maintenance alerts</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMS Notifications</label>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="sms_notifications" name="settings[sms_notifications]" <?php echo ($settings['sms_notifications'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="sms_notifications">Enable SMS notifications</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="sms_booking_reminders" name="settings[sms_booking_reminders]" <?php echo ($settings['sms_booking_reminders'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="sms_booking_reminders">Send SMS for booking reminders</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="notification_email" class="form-label">Notification Email Address</label>
                                    <input type="email" class="form-control" id="notification_email" name="settings[notification_email]" value="<?php echo htmlspecialchars($settings['notification_email'] ?? $settings['contact_email'] ?? ''); ?>">
                                    <div class="form-text">If left empty, the contact email will be used.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing & Payment Settings -->
                        <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                            <div class="settings-card">
                                <div class="settings-header">
                                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Billing & Payment Settings</h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency" name="settings[currency]">
                                            <option value="USD" <?php echo ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                            <option value="EUR" <?php echo ($settings['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                            <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                        <input type="number" class="form-control" id="tax_rate" name="settings[tax_rate]" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '20'); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="invoice_prefix" class="form-label">Invoice Number Prefix</label>
                                    <input type="text" class="form-control" id="invoice_prefix" name="settings[invoice_prefix]" value="<?php echo htmlspecialchars($settings['invoice_prefix'] ?? 'INV-'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="payment_terms" class="form-label">Payment Terms (days)</label>
                                    <input type="number" class="form-control" id="payment_terms" name="settings[payment_terms]" min="0" value="<?php echo htmlspecialchars($settings['payment_terms'] ?? '30'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Accepted Payment Methods</label>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="accept_cash" name="settings[accept_cash]" <?php echo ($settings['accept_cash'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="accept_cash">Cash</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="accept_credit_card" name="settings[accept_credit_card]" <?php echo ($settings['accept_credit_card'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="accept_credit_card">Credit Card</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="accept_bank_transfer" name="settings[accept_bank_transfer]" <?php echo ($settings['accept_bank_transfer'] ?? true) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="accept_bank_transfer">Bank Transfer</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Settings -->
                        <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                            <div class="settings-card">
                                <div class="settings-header">
                                    <h5 class="mb-0"><i class="bi bi-tools me-2"></i> Maintenance Settings</h5>
                                </div>
                                <div class="mb-3">
                                    <label for="maintenance_interval" class="form-label">Equipment Maintenance Interval (days)</label>
                                    <input type="number" class="form-control" id="maintenance_interval" name="settings[maintenance_interval]" min="1" value="<?php echo htmlspecialchars($settings['maintenance_interval'] ?? '30'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="maintenance_reminder_days" class="form-label">Maintenance Reminder (days before due)</label>
                                    <input type="number" class="form-control" id="maintenance_reminder_days" name="settings[maintenance_reminder_days]" min="1" value="<?php echo htmlspecialchars($settings['maintenance_reminder_days'] ?? '7'); ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="maintenance_alerts" name="settings[maintenance_alerts]" <?php echo ($settings['maintenance_alerts'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maintenance_alerts">Enable Maintenance Alerts</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="block_overdue_equipment" name="settings[block_overdue_equipment]" <?php echo ($settings['block_overdue_equipment'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="block_overdue_equipment">Prevent booking of equipment with overdue maintenance</label>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">
                            <div class="settings-card">
                                <div class="settings-header">
                                    <h5 class="mb-0"><i class="bi bi-sliders me-2"></i> Advanced Settings</h5>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i> These settings should only be modified by administrators who understand their implications.
                                </div>
                                <div class="mb-3">
                                    <label for="booking_time_slot" class="form-label">Default Booking Time Slot (hours)</label>
                                    <input type="number" class="form-control" id="booking_time_slot" name="settings[booking_time_slot]" min="1" max="24" value="<?php echo htmlspecialchars($settings['booking_time_slot'] ?? '1'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="min_booking_notice" class="form-label">Minimum Booking Notice (hours)</label>
                                    <input type="number" class="form-control" id="min_booking_notice" name="settings[min_booking_notice]" min="0" value="<?php echo htmlspecialchars($settings['min_booking_notice'] ?? '2'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="max_future_booking_days" class="form-label">Maximum Future Booking Period (days)</label>
                                    <input type="number" class="form-control" id="max_future_booking_days" name="settings[max_future_booking_days]" min="1" value="<?php echo htmlspecialchars($settings['max_future_booking_days'] ?? '90'); ?>">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="require_id_card" name="settings[require_id_card]" <?php echo ($settings['require_id_card'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="require_id_card">Require ID card for all bookings</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="debug_mode" name="settings[debug_mode]" <?php echo ($settings['debug_mode'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="debug_mode">Enable Debug Mode</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
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
    </script>
</body>
</html> 