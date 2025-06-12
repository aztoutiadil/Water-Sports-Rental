<?php

namespace App\Controllers;

class JetSkiController extends BaseController {
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('index.php?page=login');
        }
    }

    /**
     * Display list of all jet skis
     */
    public function index() {
        // Get search parameters
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 10;
        
        // Get jet skis (in a real app, this would query the database)
        $jetSkis = $this->getJetSkis($search, $status, $page, $perPage);
        $totalJetSkis = 12; // This would be a count from the database
        
        $data = [
            'title' => 'Jet Skis',
            'jet_skis' => $jetSkis,
            'search' => $search,
            'status' => $status,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalJetSkis,
            'total_pages' => ceil($totalJetSkis / $perPage)
        ];
        
        echo $this->render('jet-skis/index', $data);
    }
    
    /**
     * Display jet ski details
     */
    public function view() {
        $id = (int)$this->input('id');
        
        // Get jet ski details (in a real app, this would query the database)
        $jetSki = $this->getJetSkiById($id);
        
        if (!$jetSki) {
            $this->setFlash('error', 'Jet ski not found');
            $this->redirect('index.php?page=jet-skis');
            return;
        }
        
        // Get maintenance history
        $maintenanceHistory = $this->getMaintenanceHistory($id);
        
        // Get reservation history
        $reservationHistory = $this->getReservationHistory($id);
        
        $data = [
            'title' => 'Jet Ski Details',
            'jet_ski' => $jetSki,
            'maintenance_history' => $maintenanceHistory,
            'reservation_history' => $reservationHistory
        ];
        
        echo $this->render('jet-skis/view', $data);
    }
    
    /**
     * Show form to create a new jet ski
     */
    public function create() {
        $data = [
            'title' => 'Add New Jet Ski',
            'jet_ski' => [
                'model' => '',
                'brand' => '',
                'year' => date('Y'),
                'registration_number' => '',
                'hourly_rate' => '',
                'daily_rate' => '',
                'status' => 'available',
                'notes' => '',
                'last_maintenance_date' => date('Y-m-d'),
                'next_maintenance_date' => date('Y-m-d', strtotime('+30 days'))
            ]
        ];
        
        echo $this->render('jet-skis/form', $data);
    }
    
    /**
     * Show form to edit a jet ski
     */
    public function edit() {
        $id = (int)$this->input('id');
        
        // Get jet ski details (in a real app, this would query the database)
        $jetSki = $this->getJetSkiById($id);
        
        if (!$jetSki) {
            $this->setFlash('error', 'Jet ski not found');
            $this->redirect('index.php?page=jet-skis');
            return;
        }
        
        $data = [
            'title' => 'Edit Jet Ski',
            'jet_ski' => $jetSki,
            'is_edit' => true
        ];
        
        echo $this->render('jet-skis/form', $data);
    }
    
    /**
     * Process form submission to store a new jet ski
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=jet-skis');
            return;
        }
        
        // Validate input
        $rules = [
            'model' => 'required',
            'brand' => 'required',
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
            $this->redirect('index.php?page=jet-skis/create');
            return;
        }
        
        // Process file upload if exists
        $imageUrl = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadFile('image', 'uploads/jet-skis', ['jpg', 'jpeg', 'png']);
            
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['path'];
            } else {
                $this->setFlash('error', $uploadResult['error']);
                $this->redirect('index.php?page=jet-skis/create');
                return;
            }
        }
        
        // Save jet ski to database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Jet ski added successfully');
        $this->redirect('index.php?page=jet-skis');
    }
    
    /**
     * Process form submission to update a jet ski
     */
    public function update() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=jet-skis');
            return;
        }
        
        $id = (int)$this->input('id');
        
        // Validate input
        $rules = [
            'model' => 'required',
            'brand' => 'required',
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
            $this->redirect("index.php?page=jet-skis/edit&id={$id}");
            return;
        }
        
        // Process file upload if exists
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadFile('image', 'uploads/jet-skis', ['jpg', 'jpeg', 'png']);
            
            if ($uploadResult['success']) {
                // Update image URL in the database
            } else {
                $this->setFlash('error', $uploadResult['error']);
                $this->redirect("index.php?page=jet-skis/edit&id={$id}");
                return;
            }
        }
        
        // Update jet ski in database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Jet ski updated successfully');
        $this->redirect('index.php?page=jet-skis');
    }
    
    /**
     * Delete a jet ski
     */
    public function delete() {
        $id = (int)$this->input('id');
        
        // Check if jet ski exists (in a real app, this would query the database)
        $jetSki = $this->getJetSkiById($id);
        
        if (!$jetSki) {
            $this->setFlash('error', 'Jet ski not found');
            $this->redirect('index.php?page=jet-skis');
            return;
        }
        
        // Check if jet ski has active reservations
        // In a real app, this would check the database
        
        // Delete jet ski from database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Jet ski deleted successfully');
        $this->redirect('index.php?page=jet-skis');
    }
    
    /**
     * Add maintenance record for a jet ski
     */
    public function addMaintenance() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=jet-skis');
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
            
            $this->redirect("index.php?page=jet-skis/view&id={$id}");
            return;
        }
        
        // Save maintenance record to database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Maintenance record added successfully');
        $this->redirect("index.php?page=jet-skis/view&id={$id}");
    }
    
    /**
     * Get jet skis with filtering and pagination
     * In a real app, this would query the database
     */
    private function getJetSkis($search = '', $status = '', $page = 1, $perPage = 10) {
        // Dummy data for demonstration
        $jetSkis = [
            [
                'id' => 1,
                'model' => 'Wave Runner VX',
                'brand' => 'Yamaha',
                'year' => 2022,
                'registration_number' => 'YJK-2022-001',
                'hourly_rate' => 75,
                'daily_rate' => 350,
                'status' => 'available',
                'image_url' => 'uploads/jet-skis/yamaha-vx.jpg',
                'last_maintenance_date' => '2023-05-15',
                'next_maintenance_date' => '2023-06-15'
            ],
            [
                'id' => 2,
                'model' => 'Sea-Doo Spark',
                'brand' => 'Sea-Doo',
                'year' => 2021,
                'registration_number' => 'SDS-2021-002',
                'hourly_rate' => 65,
                'daily_rate' => 300,
                'status' => 'maintenance',
                'image_url' => 'uploads/jet-skis/seadoo-spark.jpg',
                'last_maintenance_date' => '2023-06-01',
                'next_maintenance_date' => '2023-07-01'
            ],
            [
                'id' => 3,
                'model' => 'Ultra 310LX',
                'brand' => 'Kawasaki',
                'year' => 2023,
                'registration_number' => 'KUS-2023-003',
                'hourly_rate' => 85,
                'daily_rate' => 400,
                'status' => 'rented',
                'image_url' => 'uploads/jet-skis/kawasaki-ultra.jpg',
                'last_maintenance_date' => '2023-05-20',
                'next_maintenance_date' => '2023-06-20'
            ],
            [
                'id' => 4,
                'model' => 'RXP-X 300',
                'brand' => 'Sea-Doo',
                'year' => 2022,
                'registration_number' => 'SDR-2022-004',
                'hourly_rate' => 80,
                'daily_rate' => 380,
                'status' => 'available',
                'image_url' => 'uploads/jet-skis/seadoo-rxp.jpg',
                'last_maintenance_date' => '2023-05-25',
                'next_maintenance_date' => '2023-06-25'
            ],
            [
                'id' => 5,
                'model' => 'FX Cruiser SVHO',
                'brand' => 'Yamaha',
                'year' => 2021,
                'registration_number' => 'YFC-2021-005',
                'hourly_rate' => 90,
                'daily_rate' => 420,
                'status' => 'available',
                'image_url' => 'uploads/jet-skis/yamaha-fx.jpg',
                'last_maintenance_date' => '2023-05-10',
                'next_maintenance_date' => '2023-06-10'
            ]
        ];
        
        // Apply search filter
        if (!empty($search)) {
            $filteredJetSkis = [];
            foreach ($jetSkis as $jetSki) {
                if (stripos($jetSki['model'], $search) !== false || 
                    stripos($jetSki['brand'], $search) !== false || 
                    stripos($jetSki['registration_number'], $search) !== false) {
                    $filteredJetSkis[] = $jetSki;
                }
            }
            $jetSkis = $filteredJetSkis;
        }
        
        // Apply status filter
        if (!empty($status)) {
            $filteredJetSkis = [];
            foreach ($jetSkis as $jetSki) {
                if ($jetSki['status'] === $status) {
                    $filteredJetSkis[] = $jetSki;
                }
            }
            $jetSkis = $filteredJetSkis;
        }
        
        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $paginatedJetSkis = array_slice($jetSkis, $offset, $perPage);
        
        return $paginatedJetSkis;
    }
    
    /**
     * Get jet ski by ID
     * In a real app, this would query the database
     */
    private function getJetSkiById($id) {
        $jetSkis = $this->getJetSkis();
        
        foreach ($jetSkis as $jetSki) {
            if ($jetSki['id'] === $id) {
                return $jetSki;
            }
        }
        
        return null;
    }
    
    /**
     * Get maintenance history for a jet ski
     * In a real app, this would query the database
     */
    private function getMaintenanceHistory($jetSkiId) {
        // Dummy data for demonstration
        if ($jetSkiId === 1) {
            return [
                [
                    'id' => 1,
                    'date' => '2023-05-15',
                    'notes' => 'Regular maintenance - Oil change, filter replacement',
                    'performed_by' => 'John Doe',
                    'next_maintenance_date' => '2023-06-15'
                ],
                [
                    'id' => 2,
                    'date' => '2023-04-10',
                    'notes' => 'Engine inspection and tuning',
                    'performed_by' => 'Mike Smith',
                    'next_maintenance_date' => '2023-05-15'
                ],
                [
                    'id' => 3,
                    'date' => '2023-03-05',
                    'notes' => 'Hull repair and polishing',
                    'performed_by' => 'John Doe',
                    'next_maintenance_date' => '2023-04-10'
                ]
            ];
        }
        
        return [];
    }
    
    /**
     * Get reservation history for a jet ski
     * In a real app, this would query the database
     */
    private function getReservationHistory($jetSkiId) {
        // Dummy data for demonstration
        if ($jetSkiId === 1) {
            return [
                [
                    'id' => 1,
                    'client_name' => 'John Smith',
                    'start_date' => '2023-06-10 09:00:00',
                    'end_date' => '2023-06-10 16:00:00',
                    'status' => 'completed',
                    'amount' => 350
                ],
                [
                    'id' => 2,
                    'client_name' => 'Jane Doe',
                    'start_date' => '2023-06-12 10:00:00',
                    'end_date' => '2023-06-12 15:00:00',
                    'status' => 'upcoming',
                    'amount' => 300
                ],
                [
                    'id' => 3,
                    'client_name' => 'Michael Johnson',
                    'start_date' => '2023-06-15 09:00:00',
                    'end_date' => '2023-06-15 17:00:00',
                    'status' => 'upcoming',
                    'amount' => 400
                ]
            ];
        }
        
        return [];
    }
} 