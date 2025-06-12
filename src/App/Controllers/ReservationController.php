<?php

namespace App\Controllers;

class ReservationController extends BaseController
{
    public function __construct()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        // Get search and filter parameters
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get reservations (using dummy data for now)
        $result = $this->getReservations($search, $status, $type, $date_from, $date_to, $page);
        $reservations = $result['reservations'];
        $total_pages = $result['total_pages'];
        
        $this->render('reservations/index', [
            'title' => 'Reservations',
            'reservations' => $reservations,
            'search' => $search,
            'status' => $status,
            'type' => $type,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    public function view()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get reservation details
        $reservation = $this->getReservationById($id);
        
        if (!$reservation) {
            $this->setFlash('error', 'Reservation not found');
            $this->redirect('reservations');
        }
        
        $this->render('reservations/view', [
            'title' => 'Reservation Details',
            'reservation' => $reservation
        ]);
    }

    public function create()
    {
        // Get client ID if coming from client page
        $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
        $equipment_type = isset($_GET['type']) ? $_GET['type'] : '';
        $equipment_id = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : 0;
        
        // Get available clients
        $clients = $this->getAllClients();
        
        // Get available equipment
        $jetSkis = $this->getAvailableJetSkis();
        $boats = $this->getAvailableBoats();
        
        // Default values for new reservation
        $reservation = [
            'client_id' => $client_id,
            'equipment_type' => $equipment_type,
            'equipment_id' => $equipment_id,
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+3 hours')),
            'status' => 'pending',
            'deposit_amount' => 0,
            'total_amount' => 0,
            'notes' => ''
        ];
        
        $this->render('reservations/form', [
            'title' => 'Create Reservation',
            'reservation' => $reservation,
            'clients' => $clients,
            'jetSkis' => $jetSkis,
            'boats' => $boats,
            'isEdit' => false
        ]);
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get reservation details
        $reservation = $this->getReservationById($id);
        
        if (!$reservation) {
            $this->setFlash('error', 'Reservation not found');
            $this->redirect('reservations');
        }
        
        // Get available clients
        $clients = $this->getAllClients();
        
        // Get available equipment
        $jetSkis = $this->getAvailableJetSkis();
        $boats = $this->getAvailableBoats();
        
        $this->render('reservations/form', [
            'title' => 'Edit Reservation',
            'reservation' => $reservation,
            'clients' => $clients,
            'jetSkis' => $jetSkis,
            'boats' => $boats,
            'isEdit' => true
        ]);
    }

    public function store()
    {
        // Process form submission for creating a new reservation
        if (!$this->isPost()) {
            $this->redirect('reservations');
        }
        
        // Validate input
        $errors = $this->validateReservationInput();
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('reservations/create');
        }
        
        // Save reservation (simulation for now)
        $this->setFlash('success', 'Reservation created successfully');
        $this->redirect('reservations');
    }

    public function update()
    {
        // Process form submission for updating a reservation
        if (!$this->isPost()) {
            $this->redirect('reservations');
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Validate input
        $errors = $this->validateReservationInput();
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('reservations/edit&id=' . $id);
        }
        
        // Update reservation (simulation for now)
        $this->setFlash('success', 'Reservation updated successfully');
        $this->redirect('reservations');
    }

    public function changeStatus()
    {
        if (!$this->isPost()) {
            $this->redirect('reservations');
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        
        // Validate status
        $validStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('reservations/view&id=' . $id);
        }
        
        // Update reservation status (simulation for now)
        $this->setFlash('success', 'Reservation status updated to ' . ucfirst($status));
        $this->redirect('reservations/view&id=' . $id);
    }

    public function delete()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Delete reservation (simulation for now)
        $this->setFlash('success', 'Reservation deleted successfully');
        $this->redirect('reservations');
    }

    public function createInvoice()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Generate invoice for reservation (simulation for now)
        $this->setFlash('success', 'Invoice generated successfully');
        $this->redirect('reservations/view&id=' . $id);
    }

    // Helper methods
    private function getReservations($search, $status, $type, $date_from, $date_to, $page)
    {
        // Dummy data for demonstration
        $reservations = [];
        
        // Generate dummy data
        for ($i = 1; $i <= 20; $i++) {
            $equipment_type = $i % 2 === 0 ? 'Jet Ski' : 'Boat';
            $equipment_name = $equipment_type === 'Jet Ski' ? 'Yamaha FX Cruiser ' . $i : 'Sea Ray Sundancer ' . $i;
            
            $statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
            $status_index = $i % 5;
            
            $start_date = date('Y-m-d H:i:s', strtotime('+' . $i . ' days'));
            $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +' . ($i % 5 + 1) . ' hours'));
            
            $reservation = [
                'id' => $i,
                'client_id' => $i,
                'client_name' => 'Client ' . $i,
                'equipment_type' => $equipment_type,
                'equipment_id' => $i,
                'equipment_name' => $equipment_name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'duration' => ($i % 5 + 1) . ' hours',
                'status' => $statuses[$status_index],
                'deposit_amount' => ($i * 25),
                'total_amount' => ($i * 100),
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . $i . ' days')),
                'notes' => 'Sample notes for reservation ' . $i
            ];
            
            // Apply filters
            if (!empty($search) && 
                strpos(strtolower($reservation['client_name']), strtolower($search)) === false && 
                strpos(strtolower($reservation['equipment_name']), strtolower($search)) === false) {
                continue;
            }
            
            if (!empty($status) && $reservation['status'] !== $status) {
                continue;
            }
            
            if (!empty($type) && $reservation['equipment_type'] !== $type) {
                continue;
            }
            
            if (!empty($date_from) && strtotime($reservation['start_date']) < strtotime($date_from)) {
                continue;
            }
            
            if (!empty($date_to) && strtotime($reservation['end_date']) > strtotime($date_to . ' 23:59:59')) {
                continue;
            }
            
            $reservations[] = $reservation;
        }
        
        // Pagination
        $items_per_page = 10;
        $total_items = count($reservations);
        $total_pages = ceil($total_items / $items_per_page);
        
        // Adjust page number
        if ($page < 1) $page = 1;
        if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
        
        // Slice the array for the current page
        $offset = ($page - 1) * $items_per_page;
        $reservations = array_slice($reservations, $offset, $items_per_page);
        
        return [
            'reservations' => $reservations,
            'total_pages' => $total_pages
        ];
    }

    private function getReservationById($id)
    {
        // Dummy data for demonstration
        if ($id <= 0) {
            return null;
        }
        
        $equipment_type = $id % 2 === 0 ? 'Jet Ski' : 'Boat';
        $equipment_name = $equipment_type === 'Jet Ski' ? 'Yamaha FX Cruiser ' . $id : 'Sea Ray Sundancer ' . $id;
        
        $statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $status_index = $id % 5;
        
        $start_date = date('Y-m-d H:i:s', strtotime('+' . $id . ' days'));
        $end_date = date('Y-m-d H:i:s', strtotime($start_date . ' +' . ($id % 5 + 1) . ' hours'));
        
        return [
            'id' => $id,
            'client_id' => $id,
            'client_name' => 'Client ' . $id,
            'client_email' => 'client' . $id . '@example.com',
            'client_phone' => '555-123-' . str_pad($id, 4, '0', STR_PAD_LEFT),
            'equipment_type' => $equipment_type,
            'equipment_id' => $id,
            'equipment_name' => $equipment_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'duration' => ($id % 5 + 1) . ' hours',
            'status' => $statuses[$status_index],
            'deposit_amount' => ($id * 25),
            'total_amount' => ($id * 100),
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . $id . ' days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-' . ($id - 1) . ' days')),
            'notes' => 'Sample notes for reservation ' . $id,
            'has_invoice' => ($id % 3 === 0),
            'invoice_id' => ($id % 3 === 0) ? $id : null
        ];
    }

    private function getAllClients()
    {
        // Dummy data for demonstration
        $clients = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $clients[] = [
                'id' => $i,
                'name' => 'Client ' . $i,
                'email' => 'client' . $i . '@example.com',
                'phone' => '555-123-' . str_pad($i, 4, '0', STR_PAD_LEFT)
            ];
        }
        
        return $clients;
    }

    private function getAvailableJetSkis()
    {
        // Dummy data for demonstration
        $jetSkis = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $jetSkis[] = [
                'id' => $i,
                'model' => 'Yamaha FX Cruiser ' . $i,
                'registration' => 'JS' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'hourly_rate' => 50 + ($i * 5)
            ];
        }
        
        return $jetSkis;
    }

    private function getAvailableBoats()
    {
        // Dummy data for demonstration
        $boats = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $boats[] = [
                'id' => $i,
                'name' => 'Sea Ray Sundancer ' . $i,
                'registration' => 'BT' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'hourly_rate' => 100 + ($i * 10),
                'capacity' => 6 + $i
            ];
        }
        
        return $boats;
    }

    private function validateReservationInput()
    {
        $errors = [];
        
        // Check required fields
        if (empty($_POST['client_id'])) {
            $errors[] = 'Client is required';
        }
        
        if (empty($_POST['equipment_type'])) {
            $errors[] = 'Equipment type is required';
        }
        
        if (empty($_POST['equipment_id'])) {
            $errors[] = 'Equipment is required';
        }
        
        if (empty($_POST['start_date'])) {
            $errors[] = 'Start date is required';
        }
        
        if (empty($_POST['end_date'])) {
            $errors[] = 'End date is required';
        }
        
        // Validate dates
        if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
            $start_date = strtotime($_POST['start_date']);
            $end_date = strtotime($_POST['end_date']);
            
            if ($start_date >= $end_date) {
                $errors[] = 'End date must be after start date';
            }
            
            if ($start_date < strtotime('now')) {
                $errors[] = 'Start date cannot be in the past';
            }
        }
        
        return $errors;
    }
} 