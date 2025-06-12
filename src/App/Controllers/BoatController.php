<?php

namespace App\Controllers;

class BoatController extends BaseController {
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('index.php?page=login');
        }
    }

    /**
     * Display list of all tourist boats
     */
    public function index() {
        // Get search parameters
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 10;
        
        // Get boats (in a real app, this would query the database)
        $boats = $this->getBoats($search, $status, $page, $perPage);
        $totalBoats = 8; // This would be a count from the database
        
        $data = [
            'title' => 'Tourist Boats',
            'boats' => $boats,
            'search' => $search,
            'status' => $status,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalBoats,
            'total_pages' => ceil($totalBoats / $perPage)
        ];
        
        echo $this->render('boats/index', $data);
    }
    
    /**
     * Display boat details
     */
    public function view() {
        $id = (int)$this->input('id');
        
        // Get boat details (in a real app, this would query the database)
        $boat = $this->getBoatById($id);
        
        if (!$boat) {
            $this->setFlash('error', 'Tourist boat not found');
            $this->redirect('index.php?page=boats');
            return;
        }
        
        // Get maintenance history
        $maintenanceHistory = $this->getMaintenanceHistory($id);
        
        // Get reservation history
        $reservationHistory = $this->getReservationHistory($id);
        
        $data = [
            'title' => 'Tourist Boat Details',
            'boat' => $boat,
            'maintenance_history' => $maintenanceHistory,
            'reservation_history' => $reservationHistory
        ];
        
        echo $this->render('boats/view', $data);
    }
    
    /**
     * Show form to create a new boat
     */
    public function create() {
        $data = [
            'title' => 'Add New Tourist Boat',
            'boat' => [
                'name' => '',
                'type' => '',
                'capacity' => 0,
                'year' => date('Y'),
                'registration_number' => '',
                'hourly_rate' => '',
                'daily_rate' => '',
                'status' => 'available',
                'notes' => '',
                'features' => '',
                'last_maintenance_date' => date('Y-m-d'),
                'next_maintenance_date' => date('Y-m-d', strtotime('+30 days'))
            ]
        ];
        
        echo $this->render('boats/form', $data);
    }
    
    /**
     * Show form to edit a boat
     */
    public function edit() {
        $id = (int)$this->input('id');
        
        // Get boat details (in a real app, this would query the database)
        $boat = $this->getBoatById($id);
        
        if (!$boat) {
            $this->setFlash('error', 'Tourist boat not found');
            $this->redirect('index.php?page=boats');
            return;
        }
        
        $data = [
            'title' => 'Edit Tourist Boat',
            'boat' => $boat,
            'is_edit' => true
        ];
        
        echo $this->render('boats/form', $data);
    }
    
    /**
     * Process form submission to store a new boat
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=boats');
            return;
        }
        
        // Validate input
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'capacity' => 'required|numeric',
            'year' => 'required|numeric',
            'registration_number' => 'required',
            'hourly_rate' => 'required|numeric',
            'daily_rate' => 'required|numeric',
            'status' => 'required'
        ];
        
        $errors = $this->validate($this->input(), $rules);
        
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $this->setFlash('error', $error);
            }
            
            // Redirect back with input data
            $this->redirect('index.php?page=boats/create');
            return;
        }
        
        // Process file upload if exists
        $imageUrl = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadFile('image', 'uploads/boats', ['jpg', 'jpeg', 'png']);
            
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['path'];
            } else {
                $this->setFlash('error', $uploadResult['error']);
                $this->redirect('index.php?page=boats/create');
                return;
            }
        }
        
        // Save boat to database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Tourist boat added successfully');
        $this->redirect('index.php?page=boats');
    }
    
    /**
     * Process form submission to update a boat
     */
    public function update() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=boats');
            return;
        }
        
        $id = (int)$this->input('id');
        
        // Validate input
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'capacity' => 'required|numeric',
            'year' => 'required|numeric',
            'registration_number' => 'required',
            'hourly_rate' => 'required|numeric',
            'daily_rate' => 'required|numeric',
            'status' => 'required'
        ];
        
        $errors = $this->validate($this->input(), $rules);
        
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $this->setFlash('error', $error);
            }
            
            // Redirect back with input data
            $this->redirect("index.php?page=boats/edit&id={$id}");
            return;
        }
        
        // Process file upload if exists
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadFile('image', 'uploads/boats', ['jpg', 'jpeg', 'png']);
            
            if ($uploadResult['success']) {
                // Update image URL in the database
            } else {
                $this->setFlash('error', $uploadResult['error']);
                $this->redirect("index.php?page=boats/edit&id={$id}");
                return;
            }
        }
        
        // Update boat in database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Tourist boat updated successfully');
        $this->redirect('index.php?page=boats');
    }
    
    /**
     * Delete a boat
     */
    public function delete() {
        $id = (int)$this->input('id');
        
        // Check if boat exists (in a real app, this would query the database)
        $boat = $this->getBoatById($id);
        
        if (!$boat) {
            $this->setFlash('error', 'Tourist boat not found');
            $this->redirect('index.php?page=boats');
            return;
        }
        
        // Check if boat has active reservations
        // In a real app, this would check the database
        
        // Delete boat from database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Tourist boat deleted successfully');
        $this->redirect('index.php?page=boats');
    }
    
    /**
     * Add maintenance record for a boat
     */
    public function addMaintenance() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=boats');
            return;
        }
        
        $id = (int)$this->input('id');
        $maintenanceDate = $this->input('maintenance_date');
        $maintenanceNotes = $this->input('maintenance_notes');
        $nextMaintenanceDate = $this->input('next_maintenance_date');
        
        // Validate input
        $rules = [
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'required|date'
        ];
        
        $errors = $this->validate([
            'maintenance_date' => $maintenanceDate,
            'next_maintenance_date' => $nextMaintenanceDate
        ], $rules);
        
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $this->setFlash('error', $error);
            }
            
            $this->redirect("index.php?page=boats/view&id={$id}");
            return;
        }
        
        // Save maintenance record to database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Maintenance record added successfully');
        $this->redirect("index.php?page=boats/view&id={$id}");
    }
    
    /**
     * Get tourist boats with filtering and pagination
     * In a real app, this would query the database
     */
    private function getBoats($search = '', $status = '', $page = 1, $perPage = 10) {
        // Dummy data for demonstration
        $boats = [
            [
                'id' => 1,
                'name' => 'Sea Explorer',
                'type' => 'Pontoon Boat',
                'capacity' => 12,
                'year' => 2021,
                'registration_number' => 'TB-2021-001',
                'hourly_rate' => 150,
                'daily_rate' => 750,
                'status' => 'available',
                'image_url' => 'uploads/boats/sea-explorer.jpg',
                'features' => 'GPS, Refrigerator, Bathroom, Sunshade',
                'last_maintenance_date' => '2023-05-10',
                'next_maintenance_date' => '2023-06-10'
            ],
            [
                'id' => 2,
                'name' => 'Ocean Voyager',
                'type' => 'Cabin Cruiser',
                'capacity' => 8,
                'year' => 2020,
                'registration_number' => 'TB-2020-002',
                'hourly_rate' => 200,
                'daily_rate' => 900,
                'status' => 'maintenance',
                'image_url' => 'uploads/boats/ocean-voyager.jpg',
                'features' => 'GPS, Air Conditioning, Kitchen, Shower, Sleeping Quarters',
                'last_maintenance_date' => '2023-06-01',
                'next_maintenance_date' => '2023-07-01'
            ],
            [
                'id' => 3,
                'name' => 'Coastal Cruiser',
                'type' => 'Bowrider',
                'capacity' => 6,
                'year' => 2022,
                'registration_number' => 'TB-2022-003',
                'hourly_rate' => 120,
                'daily_rate' => 600,
                'status' => 'rented',
                'image_url' => 'uploads/boats/coastal-cruiser.jpg',
                'features' => 'GPS, Bluetooth Stereo, Sunshade',
                'last_maintenance_date' => '2023-05-20',
                'next_maintenance_date' => '2023-06-20'
            ],
            [
                'id' => 4,
                'name' => 'Island Hopper',
                'type' => 'Deck Boat',
                'capacity' => 10,
                'year' => 2019,
                'registration_number' => 'TB-2019-004',
                'hourly_rate' => 140,
                'daily_rate' => 680,
                'status' => 'available',
                'image_url' => 'uploads/boats/island-hopper.jpg',
                'features' => 'GPS, Bluetooth Stereo, Refrigerator, Sunshade',
                'last_maintenance_date' => '2023-05-15',
                'next_maintenance_date' => '2023-06-15'
            ]
        ];
        
        // Apply search filter
        if (!empty($search)) {
            $filteredBoats = [];
            foreach ($boats as $boat) {
                if (stripos($boat['name'], $search) !== false || 
                    stripos($boat['type'], $search) !== false || 
                    stripos($boat['registration_number'], $search) !== false) {
                    $filteredBoats[] = $boat;
                }
            }
            $boats = $filteredBoats;
        }
        
        // Apply status filter
        if (!empty($status)) {
            $filteredBoats = [];
            foreach ($boats as $boat) {
                if ($boat['status'] === $status) {
                    $filteredBoats[] = $boat;
                }
            }
            $boats = $filteredBoats;
        }
        
        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $paginatedBoats = array_slice($boats, $offset, $perPage);
        
        return $paginatedBoats;
    }
    
    /**
     * Get boat by ID
     * In a real app, this would query the database
     */
    private function getBoatById($id) {
        $boats = $this->getBoats();
        
        foreach ($boats as $boat) {
            if ($boat['id'] === $id) {
                return $boat;
            }
        }
        
        return null;
    }
    
    /**
     * Get maintenance history for a boat
     * In a real app, this would query the database
     */
    private function getMaintenanceHistory($boatId) {
        // Dummy data for demonstration
        if ($boatId === 1) {
            return [
                [
                    'id' => 1,
                    'date' => '2023-05-10',
                    'notes' => 'Regular maintenance - Engine tuning, hull cleaning, safety equipment check',
                    'performed_by' => 'John Doe',
                    'next_maintenance_date' => '2023-06-10'
                ],
                [
                    'id' => 2,
                    'date' => '2023-04-05',
                    'notes' => 'Electrical system repairs, navigation equipment update',
                    'performed_by' => 'Mike Smith',
                    'next_maintenance_date' => '2023-05-10'
                ],
                [
                    'id' => 3,
                    'date' => '2023-03-01',
                    'notes' => 'Winter storage maintenance, propeller replacement',
                    'performed_by' => 'John Doe',
                    'next_maintenance_date' => '2023-04-05'
                ]
            ];
        }
        
        return [];
    }
    
    /**
     * Get reservation history for a boat
     * In a real app, this would query the database
     */
    private function getReservationHistory($boatId) {
        // Dummy data for demonstration
        if ($boatId === 1) {
            return [
                [
                    'id' => 1,
                    'client_name' => 'Sarah Johnson',
                    'start_date' => '2023-06-05 09:00:00',
                    'end_date' => '2023-06-05 16:00:00',
                    'status' => 'completed',
                    'amount' => 750
                ],
                [
                    'id' => 2,
                    'client_name' => 'Robert Brown',
                    'start_date' => '2023-06-10 10:00:00',
                    'end_date' => '2023-06-10 18:00:00',
                    'status' => 'upcoming',
                    'amount' => 900
                ],
                [
                    'id' => 3,
                    'client_name' => 'Jennifer Davis',
                    'start_date' => '2023-06-15 09:00:00',
                    'end_date' => '2023-06-16 17:00:00',
                    'status' => 'upcoming',
                    'amount' => 1200
                ]
            ];
        }
        
        return [];
    }
} 