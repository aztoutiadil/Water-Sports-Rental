<?php

namespace App\Controllers;

class InvoiceController extends BaseController
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
        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get invoices (using dummy data for now)
        $result = $this->getInvoices($search, $status, $date_from, $date_to, $page);
        $invoices = $result['invoices'];
        $total_pages = $result['total_pages'];
        
        $this->render('invoices/index', [
            'title' => 'Invoices',
            'invoices' => $invoices,
            'search' => $search,
            'status' => $status,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    public function view()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get invoice details
        $invoice = $this->getInvoiceById($id);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }
        
        $this->render('invoices/view', [
            'title' => 'Invoice Details',
            'invoice' => $invoice
        ]);
    }

    public function create()
    {
        // Get reservation ID if coming from reservation page
        $reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
        
        if ($reservation_id <= 0) {
            $this->setFlash('error', 'Reservation ID is required');
            $this->redirect('reservations');
        }
        
        // Get reservation details
        $reservation = $this->getReservationById($reservation_id);
        
        if (!$reservation) {
            $this->setFlash('error', 'Reservation not found');
            $this->redirect('reservations');
        }
        
        // Check if invoice already exists for this reservation
        if (!empty($reservation['invoice_id'])) {
            $this->setFlash('info', 'Invoice already exists for this reservation');
            $this->redirect('invoices/view&id=' . $reservation['invoice_id']);
        }
        
        // Calculate default values
        $subtotal = $reservation['total_amount'];
        $tax_rate = 0.10; // 10% tax
        $tax_amount = $subtotal * $tax_rate;
        $total = $subtotal + $tax_amount;
        $deposit_paid = $reservation['deposit_amount'];
        $balance_due = $total - $deposit_paid;
        
        // Default values for new invoice
        $invoice = [
            'id' => null,
            'invoice_number' => 'INV-' . date('Ymd') . '-' . $reservation_id,
            'reservation_id' => $reservation_id,
            'client_id' => $reservation['client_id'],
            'client_name' => $reservation['client_name'],
            'client_email' => $reservation['client_email'],
            'client_phone' => $reservation['client_phone'],
            'equipment_type' => $reservation['equipment_type'],
            'equipment_name' => $reservation['equipment_name'],
            'start_date' => $reservation['start_date'],
            'end_date' => $reservation['end_date'],
            'duration' => $reservation['duration'],
            'subtotal' => $subtotal,
            'tax_rate' => $tax_rate,
            'tax_amount' => $tax_amount,
            'total' => $total,
            'deposit_paid' => $deposit_paid,
            'balance_due' => $balance_due,
            'status' => 'pending',
            'payment_method' => '',
            'payment_date' => null,
            'notes' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->render('invoices/form', [
            'title' => 'Create Invoice',
            'invoice' => $invoice,
            'reservation' => $reservation,
            'isEdit' => false
        ]);
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get invoice details
        $invoice = $this->getInvoiceById($id);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }
        
        // Get reservation details
        $reservation = $this->getReservationById($invoice['reservation_id']);
        
        $this->render('invoices/form', [
            'title' => 'Edit Invoice',
            'invoice' => $invoice,
            'reservation' => $reservation,
            'isEdit' => true
        ]);
    }

    public function store()
    {
        // Process form submission for creating a new invoice
        if (!$this->isPost()) {
            $this->redirect('invoices');
        }
        
        // Validate input
        $errors = $this->validateInvoiceInput();
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('invoices/create&reservation_id=' . $_POST['reservation_id']);
        }
        
        // Create invoice (simulation for now)
        $this->setFlash('success', 'Invoice created successfully');
        $this->redirect('invoices');
    }

    public function update()
    {
        // Process form submission for updating an invoice
        if (!$this->isPost()) {
            $this->redirect('invoices');
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Validate input
        $errors = $this->validateInvoiceInput();
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('invoices/edit&id=' . $id);
        }
        
        // Update invoice (simulation for now)
        $this->setFlash('success', 'Invoice updated successfully');
        $this->redirect('invoices/view&id=' . $id);
    }

    public function recordPayment()
    {
        // Process form submission for recording a payment
        if (!$this->isPost()) {
            $this->redirect('invoices');
        }
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Validate input
        if (empty($_POST['payment_amount']) || !is_numeric($_POST['payment_amount']) || $_POST['payment_amount'] <= 0) {
            $this->setFlash('error', 'Valid payment amount is required');
            $this->redirect('invoices/view&id=' . $id);
        }
        
        if (empty($_POST['payment_method'])) {
            $this->setFlash('error', 'Payment method is required');
            $this->redirect('invoices/view&id=' . $id);
        }
        
        // Record payment (simulation for now)
        $this->setFlash('success', 'Payment recorded successfully');
        $this->redirect('invoices/view&id=' . $id);
    }

    public function sendEmail()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get invoice details
        $invoice = $this->getInvoiceById($id);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }
        
        // Send email (simulation for now)
        $this->setFlash('success', 'Invoice has been sent to ' . $invoice['client_email']);
        $this->redirect('invoices/view&id=' . $id);
    }

    public function download()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Get invoice details
        $invoice = $this->getInvoiceById($id);
        
        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            $this->redirect('invoices');
        }
        
        // In a real application, you would generate a PDF here
        // For now, we'll just show a message
        $this->setFlash('success', 'PDF generation would happen here in a real application');
        $this->redirect('invoices/view&id=' . $id);
    }

    // Helper methods
    private function getInvoices($search, $status, $date_from, $date_to, $page)
    {
        // Dummy data for demonstration
        $invoices = [];
        
        // Generate dummy data
        for ($i = 1; $i <= 20; $i++) {
            $statuses = ['pending', 'paid', 'partially_paid', 'cancelled'];
            $status_index = $i % 4;
            
            $created_date = date('Y-m-d H:i:s', strtotime('-' . $i . ' days'));
            $payment_date = $statuses[$status_index] === 'paid' || $statuses[$status_index] === 'partially_paid' 
                          ? date('Y-m-d H:i:s', strtotime($created_date . ' +2 days')) 
                          : null;
            
            $subtotal = $i * 100;
            $tax_rate = 0.10;
            $tax_amount = $subtotal * $tax_rate;
            $total = $subtotal + $tax_amount;
            $deposit_paid = $i * 25;
            $balance_due = $total - $deposit_paid;
            
            if ($statuses[$status_index] === 'paid') {
                $balance_due = 0;
            } elseif ($statuses[$status_index] === 'partially_paid') {
                $balance_due = $total / 2;
            }
            
            $invoice = [
                'id' => $i,
                'invoice_number' => 'INV-' . date('Ymd', strtotime($created_date)) . '-' . $i,
                'reservation_id' => $i,
                'client_id' => $i,
                'client_name' => 'Client ' . $i,
                'client_email' => 'client' . $i . '@example.com',
                'client_phone' => '555-123-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'equipment_type' => $i % 2 === 0 ? 'Jet Ski' : 'Boat',
                'equipment_name' => $i % 2 === 0 ? 'Yamaha FX Cruiser ' . $i : 'Sea Ray Sundancer ' . $i,
                'start_date' => date('Y-m-d H:i:s', strtotime('+' . $i . ' days')),
                'end_date' => date('Y-m-d H:i:s', strtotime('+' . $i . ' days +' . ($i % 5 + 1) . ' hours')),
                'duration' => ($i % 5 + 1) . ' hours',
                'subtotal' => $subtotal,
                'tax_rate' => $tax_rate,
                'tax_amount' => $tax_amount,
                'total' => $total,
                'deposit_paid' => $deposit_paid,
                'balance_due' => $balance_due,
                'status' => $statuses[$status_index],
                'payment_method' => $statuses[$status_index] === 'paid' || $statuses[$status_index] === 'partially_paid' 
                                  ? ['Credit Card', 'Cash', 'Bank Transfer', 'PayPal'][$i % 4] 
                                  : '',
                'payment_date' => $payment_date,
                'notes' => 'Sample notes for invoice ' . $i,
                'created_at' => $created_date,
                'updated_at' => $payment_date ?? $created_date
            ];
            
            // Apply filters
            if (!empty($search) && 
                strpos(strtolower($invoice['client_name']), strtolower($search)) === false && 
                strpos(strtolower($invoice['invoice_number']), strtolower($search)) === false) {
                continue;
            }
            
            if (!empty($status) && $invoice['status'] !== $status) {
                continue;
            }
            
            if (!empty($date_from) && strtotime($invoice['created_at']) < strtotime($date_from)) {
                continue;
            }
            
            if (!empty($date_to) && strtotime($invoice['created_at']) > strtotime($date_to . ' 23:59:59')) {
                continue;
            }
            
            $invoices[] = $invoice;
        }
        
        // Pagination
        $items_per_page = 10;
        $total_items = count($invoices);
        $total_pages = ceil($total_items / $items_per_page);
        
        // Adjust page number
        if ($page < 1) $page = 1;
        if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
        
        // Slice the array for the current page
        $offset = ($page - 1) * $items_per_page;
        $invoices = array_slice($invoices, $offset, $items_per_page);
        
        return [
            'invoices' => $invoices,
            'total_pages' => $total_pages
        ];
    }

    private function getInvoiceById($id)
    {
        // Dummy data for demonstration
        if ($id <= 0) {
            return null;
        }
        
        $statuses = ['pending', 'paid', 'partially_paid', 'cancelled'];
        $status_index = $id % 4;
        
        $created_date = date('Y-m-d H:i:s', strtotime('-' . $id . ' days'));
        $payment_date = $statuses[$status_index] === 'paid' || $statuses[$status_index] === 'partially_paid' 
                      ? date('Y-m-d H:i:s', strtotime($created_date . ' +2 days')) 
                      : null;
        
        $subtotal = $id * 100;
        $tax_rate = 0.10;
        $tax_amount = $subtotal * $tax_rate;
        $total = $subtotal + $tax_amount;
        $deposit_paid = $id * 25;
        $balance_due = $total - $deposit_paid;
        
        if ($statuses[$status_index] === 'paid') {
            $balance_due = 0;
        } elseif ($statuses[$status_index] === 'partially_paid') {
            $balance_due = $total / 2;
        }
        
        return [
            'id' => $id,
            'invoice_number' => 'INV-' . date('Ymd', strtotime($created_date)) . '-' . $id,
            'reservation_id' => $id,
            'client_id' => $id,
            'client_name' => 'Client ' . $id,
            'client_email' => 'client' . $id . '@example.com',
            'client_phone' => '555-123-' . str_pad($id, 4, '0', STR_PAD_LEFT),
            'client_address' => '123 Main St, Apt ' . $id . ', Anytown, State, 12345',
            'equipment_type' => $id % 2 === 0 ? 'Jet Ski' : 'Boat',
            'equipment_name' => $id % 2 === 0 ? 'Yamaha FX Cruiser ' . $id : 'Sea Ray Sundancer ' . $id,
            'start_date' => date('Y-m-d H:i:s', strtotime('+' . $id . ' days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+' . $id . ' days +' . ($id % 5 + 1) . ' hours')),
            'duration' => ($id % 5 + 1) . ' hours',
            'subtotal' => $subtotal,
            'tax_rate' => $tax_rate,
            'tax_amount' => $tax_amount,
            'total' => $total,
            'deposit_paid' => $deposit_paid,
            'balance_due' => $balance_due,
            'status' => $statuses[$status_index],
            'payment_method' => $statuses[$status_index] === 'paid' || $statuses[$status_index] === 'partially_paid' 
                              ? ['Credit Card', 'Cash', 'Bank Transfer', 'PayPal'][$id % 4] 
                              : '',
            'payment_date' => $payment_date,
            'notes' => 'Sample notes for invoice ' . $id,
            'created_at' => $created_date,
            'updated_at' => $payment_date ?? $created_date,
            'payment_history' => $this->getPaymentHistory($id)
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
            'invoice_id' => ($id % 3 === 0) ? $id : null
        ];
    }

    private function getPaymentHistory($invoice_id)
    {
        // Dummy data for demonstration
        $payments = [];
        
        $statuses = ['pending', 'paid', 'partially_paid', 'cancelled'];
        $status_index = $invoice_id % 4;
        
        if ($statuses[$status_index] === 'paid') {
            $created_date = date('Y-m-d H:i:s', strtotime('-' . $invoice_id . ' days'));
            $payment_date = date('Y-m-d H:i:s', strtotime($created_date . ' +2 days'));
            
            $payments[] = [
                'id' => 1,
                'amount' => $invoice_id * 100 * 1.1, // total with tax
                'payment_method' => ['Credit Card', 'Cash', 'Bank Transfer', 'PayPal'][$invoice_id % 4],
                'payment_date' => $payment_date,
                'notes' => 'Full payment'
            ];
        } elseif ($statuses[$status_index] === 'partially_paid') {
            $created_date = date('Y-m-d H:i:s', strtotime('-' . $invoice_id . ' days'));
            $payment_date = date('Y-m-d H:i:s', strtotime($created_date . ' +2 days'));
            
            $payments[] = [
                'id' => 1,
                'amount' => ($invoice_id * 100 * 1.1) / 2, // half of total with tax
                'payment_method' => ['Credit Card', 'Cash', 'Bank Transfer', 'PayPal'][$invoice_id % 4],
                'payment_date' => $payment_date,
                'notes' => 'Partial payment'
            ];
        }
        
        return $payments;
    }

    private function validateInvoiceInput()
    {
        $errors = [];
        
        // Check required fields
        if (empty($_POST['reservation_id'])) {
            $errors[] = 'Reservation is required';
        }
        
        if (empty($_POST['invoice_number'])) {
            $errors[] = 'Invoice number is required';
        }
        
        if (!isset($_POST['subtotal']) || !is_numeric($_POST['subtotal']) || $_POST['subtotal'] < 0) {
            $errors[] = 'Valid subtotal is required';
        }
        
        if (!isset($_POST['tax_rate']) || !is_numeric($_POST['tax_rate']) || $_POST['tax_rate'] < 0) {
            $errors[] = 'Valid tax rate is required';
        }
        
        if (!isset($_POST['tax_amount']) || !is_numeric($_POST['tax_amount']) || $_POST['tax_amount'] < 0) {
            $errors[] = 'Valid tax amount is required';
        }
        
        if (!isset($_POST['total']) || !is_numeric($_POST['total']) || $_POST['total'] < 0) {
            $errors[] = 'Valid total is required';
        }
        
        if (!isset($_POST['deposit_paid']) || !is_numeric($_POST['deposit_paid']) || $_POST['deposit_paid'] < 0) {
            $errors[] = 'Valid deposit amount is required';
        }
        
        if (!isset($_POST['balance_due']) || !is_numeric($_POST['balance_due']) || $_POST['balance_due'] < 0) {
            $errors[] = 'Valid balance due is required';
        }
        
        return $errors;
    }
} 