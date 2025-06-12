<?php

namespace App\Controllers;

class DashboardController extends BaseController {
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('index.php?page=login');
        }
    }

    public function index() {
        // In a real application, these would be fetched from the database
        $stats = $this->getStats();
        $recentBookings = $this->getRecentBookings();
        $upcomingMaintenance = $this->getUpcomingMaintenance();
        
        $data = [
            'title' => 'Dashboard',
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'stats' => $stats,
            'recent_bookings' => $recentBookings,
            'upcoming_maintenance' => $upcomingMaintenance
        ];

        echo $this->render('dashboard/index', $data);
    }

    public function profile() {
        if ($this->isPost()) {
            // Handle profile update
            $name = $this->input('name');
            $email = $this->input('email');
            $username = $this->input('username');
            
            // Validate input
            $rules = [
                'name' => 'required|min:3',
                'email' => 'required|email',
                'username' => 'required|min:3'
            ];
            
            $errors = $this->validate($this->input(), $rules);
            
            if (empty($errors)) {
                // Update user in database
                // ...
                
                // Check if password change was requested
                $currentPassword = $this->input('current_password');
                $newPassword = $this->input('new_password');
                $confirmPassword = $this->input('confirm_password');
                
                if ($currentPassword && $newPassword && $confirmPassword) {
                    if ($newPassword !== $confirmPassword) {
                        $this->setFlash('error', 'New password and confirmation do not match');
                        $this->redirect('index.php?page=dashboard/profile');
                        return;
                    }
                    
                    // Update password in database
                    // ...
                }
                
                $this->setFlash('success', 'Profile updated successfully');
            } else {
                // Set errors as flash messages
                foreach ($errors as $field => $error) {
                    $this->setFlash('error', $error);
                }
            }
            
            $this->redirect('index.php?page=dashboard/profile');
        }

        // Fetch user data
        $userData = [
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'role' => $_SESSION['user_role'] ?? 'admin'
        ];
        
        $data = [
            'title' => 'My Profile',
            'user' => $userData
        ];

        echo $this->render('dashboard/profile', $data);
    }

    public function settings() {
        if ($this->isPost()) {
            // Handle settings update
            $settings = $this->input('settings');
            
            // Save settings to database or configuration file
            // ...
            
            $this->setFlash('success', 'Settings updated successfully');
            $this->redirect('index.php?page=dashboard/settings');
        }

        // Fetch current settings
        $settings = [
            'company_name' => 'Water Sports Rental',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '+1234567890',
            'email_notifications' => true,
            'sms_notifications' => false,
            'currency' => 'USD',
            'tax_rate' => 20,
            'booking_time_slot' => 1,
            'maintenance_interval' => 30,
            'maintenance_alerts' => true
        ];
        
        $data = [
            'title' => 'Settings',
            'settings' => $settings,
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'User',
                'role' => $_SESSION['user_role'] ?? 'admin'
            ]
        ];

        echo $this->render('dashboard/settings', $data);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getStats() {
        // In a real application, these would be fetched from the database
        return [
            'total_jet_skis' => 12,
            'available_jet_skis' => 8,
            'total_boats' => 6,
            'available_boats' => 4,
            'total_clients' => 45,
            'active_rentals' => 5,
            'today_revenue' => 1250,
            'month_revenue' => 24680,
            'pending_reservations' => 3,
            'completed_reservations' => 127
        ];
    }
    
    /**
     * Get recent bookings for dashboard
     */
    private function getRecentBookings() {
        // In a real application, these would be fetched from the database
        return [
            [
                'id' => 1,
                'client_name' => 'John Doe',
                'equipment' => 'Jet Ski - Yamaha Wave Runner',
                'start_date' => '2023-06-15',
                'end_date' => '2023-06-16',
                'status' => 'active',
                'amount' => 250
            ],
            [
                'id' => 2,
                'client_name' => 'Jane Smith',
                'equipment' => 'Tourist Boat - Sea Explorer',
                'start_date' => '2023-06-14',
                'end_date' => '2023-06-14',
                'status' => 'completed',
                'amount' => 350
            ],
            [
                'id' => 3,
                'client_name' => 'Robert Johnson',
                'equipment' => 'Jet Ski - Kawasaki Ultra',
                'start_date' => '2023-06-16',
                'end_date' => '2023-06-17',
                'status' => 'pending',
                'amount' => 300
            ],
            [
                'id' => 4,
                'client_name' => 'Emily Brown',
                'equipment' => 'Tourist Boat - Ocean Voyager',
                'start_date' => '2023-06-18',
                'end_date' => '2023-06-19',
                'status' => 'pending',
                'amount' => 500
            ],
            [
                'id' => 5,
                'client_name' => 'Michael Wilson',
                'equipment' => 'Jet Ski - Sea-Doo Spark',
                'start_date' => '2023-06-13',
                'end_date' => '2023-06-13',
                'status' => 'completed',
                'amount' => 200
            ]
        ];
    }
    
    /**
     * Get upcoming maintenance for dashboard
     */
    private function getUpcomingMaintenance() {
        // In a real application, these would be fetched from the database
        return [
            [
                'id' => 1,
                'equipment' => 'Jet Ski - Yamaha Wave Runner',
                'last_maintenance' => '2023-05-15',
                'next_maintenance' => '2023-06-15',
                'status' => 'due'
            ],
            [
                'id' => 2,
                'equipment' => 'Tourist Boat - Sea Explorer',
                'last_maintenance' => '2023-05-20',
                'next_maintenance' => '2023-06-20',
                'status' => 'upcoming'
            ],
            [
                'id' => 3,
                'equipment' => 'Jet Ski - Kawasaki Ultra',
                'last_maintenance' => '2023-05-10',
                'next_maintenance' => '2023-06-10',
                'status' => 'overdue'
            ]
        ];
    }
} 