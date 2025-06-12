<?php

namespace App\Controllers;

class ClientController extends BaseController {
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('index.php?page=login');
        }
    }

    /**
     * Display list of all clients
     */
    public function index() {
        // Get search parameters
        $search = $this->input('search', '');
        $status = $this->input('status', '');
        $page = max(1, (int)$this->input('page', 1));
        $perPage = 10;
        
        // Get clients (in a real app, this would query the database)
        $clients = $this->getClients($search, $status, $page, $perPage);
        $totalClients = count($this->getAllClients()); // This would be a count from the database
        
        $data = [
            'title' => 'Clients',
            'clients' => $clients,
            'search' => $search,
            'status' => $status,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_items' => $totalClients,
            'total_pages' => ceil($totalClients / $perPage)
        ];
        
        echo $this->render('clients/index', $data);
    }
    
    /**
     * Display client details
     */
    public function view() {
        $id = (int)$this->input('id');
        
        // Get client details (in a real app, this would query the database)
        $client = $this->getClientById($id);
        
        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('index.php?page=clients');
            return;
        }
        
        // Get client's reservation history
        $reservationHistory = $this->getClientReservations($id);
        
        $data = [
            'title' => 'Client Details',
            'client' => $client,
            'reservation_history' => $reservationHistory
        ];
        
        echo $this->render('clients/view', $data);
    }
    
    /**
     * Show form to create a new client
     */
    public function create() {
        $data = [
            'title' => 'Add New Client',
            'client' => [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
                'id_type' => '',
                'id_number' => '',
                'notes' => '',
                'status' => 'active'
            ]
        ];
        
        echo $this->render('clients/form', $data);
    }
    
    /**
     * Show form to edit a client
     */
    public function edit() {
        $id = (int)$this->input('id');
        
        // Get client details (in a real app, this would query the database)
        $client = $this->getClientById($id);
        
        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('index.php?page=clients');
            return;
        }
        
        $data = [
            'title' => 'Edit Client',
            'client' => $client,
            'is_edit' => true
        ];
        
        echo $this->render('clients/form', $data);
    }
    
    /**
     * Process form submission to store a new client
     */
    public function store() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=clients');
            return;
        }
        
        // Validate input
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'id_type' => 'required',
            'id_number' => 'required'
        ];
        
        $errors = $this->validate($this->input(), $rules);
        
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $this->setFlash('error', $error);
            }
            
            // Redirect back with input data
            $this->redirect('index.php?page=clients/create');
            return;
        }
        
        // Process ID document upload if exists
        $idDocumentUrl = '';
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadFile('id_document', 'uploads/client-documents', ['jpg', 'jpeg', 'png', 'pdf']);
            
            if ($uploadResult['success']) {
                $idDocumentUrl = $uploadResult['path'];
            } else {
                $this->setFlash('error', $uploadResult['error']);
                $this->redirect('index.php?page=clients/create');
                return;
            }
        }
        
        // Save client to database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Client added successfully');
        $this->redirect('index.php?page=clients');
    }
    
    /**
     * Process form submission to update a client
     */
    public function update() {
        if (!$this->isPost()) {
            $this->redirect('index.php?page=clients');
            return;
        }
        
        $id = (int)$this->input('id');
        
        // Validate input
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'id_type' => 'required',
            'id_number' => 'required'
        ];
        
        $errors = $this->validate($this->input(), $rules);
        
        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $this->setFlash('error', $error);
            }
            
            // Redirect back with input data
            $this->redirect("index.php?page=clients/edit&id={$id}");
            return;
        }
        
        // Process ID document upload if exists
        if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadFile('id_document', 'uploads/client-documents', ['jpg', 'jpeg', 'png', 'pdf']);
            
            if ($uploadResult['success']) {
                // Update document URL in the database
            } else {
                $this->setFlash('error', $uploadResult['error']);
                $this->redirect("index.php?page=clients/edit&id={$id}");
                return;
            }
        }
        
        // Update client in database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Client updated successfully');
        $this->redirect('index.php?page=clients');
    }
    
    /**
     * Delete a client
     */
    public function delete() {
        $id = (int)$this->input('id');
        
        // Check if client exists (in a real app, this would query the database)
        $client = $this->getClientById($id);
        
        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('index.php?page=clients');
            return;
        }
        
        // Check if client has active reservations
        $clientReservations = $this->getClientReservations($id);
        $hasActiveReservations = false;
        
        foreach ($clientReservations as $reservation) {
            if ($reservation['status'] === 'active' || $reservation['status'] === 'upcoming') {
                $hasActiveReservations = true;
                break;
            }
        }
        
        if ($hasActiveReservations) {
            $this->setFlash('error', 'Cannot delete client with active or upcoming reservations');
            $this->redirect("index.php?page=clients/view&id={$id}");
            return;
        }
        
        // Delete client from database (in a real app)
        // For now, we'll just simulate a success
        $this->setFlash('success', 'Client deleted successfully');
        $this->redirect('index.php?page=clients');
    }
    
    /**
     * Create a new reservation for the client
     */
    public function createReservation() {
        $id = (int)$this->input('id');
        
        // Get client details (in a real app, this would query the database)
        $client = $this->getClientById($id);
        
        if (!$client) {
            $this->setFlash('error', 'Client not found');
            $this->redirect('index.php?page=clients');
            return;
        }
        
        // Redirect to reservation creation page with client ID pre-filled
        $this->redirect("index.php?page=reservations/create&client_id={$id}");
    }
    
    /**
     * Get clients with filtering and pagination
     * In a real app, this would query the database
     */
    private function getClients($search = '', $status = '', $page = 1, $perPage = 10) {
        $clients = $this->getAllClients();
        
        // Apply search filter
        if (!empty($search)) {
            $filteredClients = [];
            foreach ($clients as $client) {
                $fullName = $client['first_name'] . ' ' . $client['last_name'];
                if (stripos($fullName, $search) !== false || 
                    stripos($client['email'], $search) !== false || 
                    stripos($client['phone'], $search) !== false || 
                    stripos($client['id_number'], $search) !== false) {
                    $filteredClients[] = $client;
                }
            }
            $clients = $filteredClients;
        }
        
        // Apply status filter
        if (!empty($status)) {
            $filteredClients = [];
            foreach ($clients as $client) {
                if ($client['status'] === $status) {
                    $filteredClients[] = $client;
                }
            }
            $clients = $filteredClients;
        }
        
        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $paginatedClients = array_slice($clients, $offset, $perPage);
        
        return $paginatedClients;
    }
    
    /**
     * Get all clients
     * In a real app, this would query the database
     */
    private function getAllClients() {
        // Dummy data for demonstration
        return [
            [
                'id' => 1,
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St',
                'city' => 'Miami',
                'state' => 'FL',
                'zip_code' => '33101',
                'id_type' => 'Driver License',
                'id_number' => 'DL12345678',
                'id_document_url' => 'uploads/client-documents/john-id.jpg',
                'registration_date' => '2023-01-15',
                'notes' => 'Regular customer, prefers jet skis',
                'status' => 'active',
                'total_rentals' => 5,
                'total_spent' => 1250
            ],
            [
                'id' => 2,
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane.doe@example.com',
                'phone' => '+1987654321',
                'address' => '456 Oak Ave',
                'city' => 'Orlando',
                'state' => 'FL',
                'zip_code' => '32801',
                'id_type' => 'Passport',
                'id_number' => 'P98765432',
                'id_document_url' => 'uploads/client-documents/jane-id.jpg',
                'registration_date' => '2023-02-20',
                'notes' => 'New customer, prefers tourist boats',
                'status' => 'active',
                'total_rentals' => 2,
                'total_spent' => 900
            ],
            [
                'id' => 3,
                'first_name' => 'Robert',
                'last_name' => 'Johnson',
                'email' => 'robert.johnson@example.com',
                'phone' => '+1122334455',
                'address' => '789 Pine Blvd',
                'city' => 'Tampa',
                'state' => 'FL',
                'zip_code' => '33602',
                'id_type' => 'Driver License',
                'id_number' => 'DL87654321',
                'id_document_url' => 'uploads/client-documents/robert-id.jpg',
                'registration_date' => '2023-03-10',
                'notes' => 'Regular customer, rents both jet skis and boats',
                'status' => 'active',
                'total_rentals' => 8,
                'total_spent' => 2200
            ],
            [
                'id' => 4,
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@example.com',
                'phone' => '+1567890123',
                'address' => '321 Maple Dr',
                'city' => 'Jacksonville',
                'state' => 'FL',
                'zip_code' => '32202',
                'id_type' => 'Passport',
                'id_number' => 'P12345678',
                'id_document_url' => 'uploads/client-documents/sarah-id.jpg',
                'registration_date' => '2023-04-05',
                'notes' => 'Tourist, only made one reservation',
                'status' => 'inactive',
                'total_rentals' => 1,
                'total_spent' => 350
            ],
            [
                'id' => 5,
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '+1654321789',
                'address' => '654 Beach Rd',
                'city' => 'Miami Beach',
                'state' => 'FL',
                'zip_code' => '33139',
                'id_type' => 'Driver License',
                'id_number' => 'DL56789012',
                'id_document_url' => 'uploads/client-documents/michael-id.jpg',
                'registration_date' => '2023-05-12',
                'notes' => 'VIP client, spends a lot on premium rentals',
                'status' => 'active',
                'total_rentals' => 12,
                'total_spent' => 4500
            ]
        ];
    }
    
    /**
     * Get client by ID
     * In a real app, this would query the database
     */
    private function getClientById($id) {
        $clients = $this->getAllClients();
        
        foreach ($clients as $client) {
            if ($client['id'] === $id) {
                return $client;
            }
        }
        
        return null;
    }
    
    /**
     * Get reservations for a client
     * In a real app, this would query the database
     */
    private function getClientReservations($clientId) {
        // Dummy data for demonstration
        if ($clientId === 1) {
            return [
                [
                    'id' => 1,
                    'equipment_type' => 'Jet Ski',
                    'equipment_name' => 'Yamaha Wave Runner VX',
                    'start_date' => '2023-06-10 09:00:00',
                    'end_date' => '2023-06-10 16:00:00',
                    'status' => 'completed',
                    'amount' => 350,
                    'payment_status' => 'paid',
                    'invoice_id' => 'INV-2023-001'
                ],
                [
                    'id' => 2,
                    'equipment_type' => 'Jet Ski',
                    'equipment_name' => 'Sea-Doo Spark',
                    'start_date' => '2023-06-15 10:00:00',
                    'end_date' => '2023-06-15 15:00:00',
                    'status' => 'completed',
                    'amount' => 250,
                    'payment_status' => 'paid',
                    'invoice_id' => 'INV-2023-005'
                ],
                [
                    'id' => 3,
                    'equipment_type' => 'Tourist Boat',
                    'equipment_name' => 'Sea Explorer',
                    'start_date' => '2023-06-20 09:00:00',
                    'end_date' => '2023-06-20 17:00:00',
                    'status' => 'upcoming',
                    'amount' => 600,
                    'payment_status' => 'deposit',
                    'invoice_id' => 'INV-2023-010'
                ],
                [
                    'id' => 4,
                    'equipment_type' => 'Jet Ski',
                    'equipment_name' => 'Kawasaki Ultra 310LX',
                    'start_date' => '2023-07-05 11:00:00',
                    'end_date' => '2023-07-05 16:00:00',
                    'status' => 'upcoming',
                    'amount' => 400,
                    'payment_status' => 'pending',
                    'invoice_id' => null
                ]
            ];
        }
        
        return [];
    }
} 