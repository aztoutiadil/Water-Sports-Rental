-- Water Sports Rental Management System Database Schema

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS maintenance_records;
DROP TABLE IF EXISTS boats;
DROP TABLE IF EXISTS jet_skis;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;

-- Users table for system authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clients table
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(50) NOT NULL DEFAULT 'United States',
    id_type VARCHAR(50) NOT NULL,
    id_number VARCHAR(50) NOT NULL,
    id_document_url VARCHAR(255),
    notes TEXT,
    status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
    birthdate DATE,
    registration_date DATE NOT NULL,
    total_rentals INT DEFAULT 0,
    total_spent DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Jet Skis table
CREATE TABLE jet_skis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    hourly_rate DECIMAL(10, 2) NOT NULL,
    daily_rate DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'maintenance', 'rented', 'unavailable') NOT NULL DEFAULT 'available',
    image_url VARCHAR(255),
    notes TEXT,
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tourist Boats table
CREATE TABLE boats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    year INT NOT NULL,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    hourly_rate DECIMAL(10, 2) NOT NULL,
    daily_rate DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'maintenance', 'rented', 'unavailable') NOT NULL DEFAULT 'available',
    image_url VARCHAR(255),
    features TEXT,
    notes TEXT,
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Maintenance Records table
CREATE TABLE maintenance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_type ENUM('jet_ski', 'boat') NOT NULL,
    equipment_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    performed_by VARCHAR(100) NOT NULL,
    notes TEXT NOT NULL,
    next_maintenance_date DATE NOT NULL,
    cost DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT CHECK (equipment_id > 0)
);

-- Reservations table
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    equipment_type ENUM('jet_ski', 'boat') NOT NULL,
    equipment_id INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    deposit_amount DECIMAL(10, 2) DEFAULT 0.00,
    deposit_paid BOOLEAN DEFAULT FALSE,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Invoices table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) NOT NULL UNIQUE,
    reservation_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    deposit_paid DECIMAL(10, 2) DEFAULT 0.00,
    balance_due DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid', 'partially_paid', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_date DATE,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'bank_transfer', 'paypal', 'other') NOT NULL,
    transaction_reference VARCHAR(100),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Indexes for better performance
CREATE INDEX idx_clients_email ON clients(email);
CREATE INDEX idx_clients_status ON clients(status);
CREATE INDEX idx_jet_skis_status ON jet_skis(status);
CREATE INDEX idx_boats_status ON boats(status);
CREATE INDEX idx_reservations_dates ON reservations(start_date, end_date);
CREATE INDEX idx_reservations_status ON reservations(status);
CREATE INDEX idx_reservations_client ON reservations(client_id);
CREATE INDEX idx_reservations_equipment ON reservations(equipment_type, equipment_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_reservation ON invoices(reservation_id);
CREATE INDEX idx_payments_invoice ON payments(invoice_id);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, first_name, last_name, role) 
VALUES ('admin', '$2y$10$GnZYn4lKCAx19wxPsAWHqePzLe2qCMoL/j4tuHbrwkX7hqIvg4fOi', 'admin@watersports.com', 'Admin', 'User', 'admin');

-- Insert some sample data for demonstration

-- Sample Clients
INSERT INTO clients (first_name, last_name, email, phone, address, city, state, zip_code, country, id_type, id_number, registration_date, total_rentals, total_spent) VALUES
('John', 'Smith', 'john.smith@example.com', '+1234567890', '123 Main St', 'Miami', 'FL', '33101', 'United States', 'Driver License', 'DL12345678', CURDATE(), 5, 1250.00),
('Jane', 'Doe', 'jane.doe@example.com', '+1987654321', '456 Oak Ave', 'Orlando', 'FL', '32801', 'United States', 'Passport', 'P98765432', CURDATE(), 2, 900.00),
('Robert', 'Johnson', 'robert.johnson@example.com', '+1122334455', '789 Pine Blvd', 'Tampa', 'FL', '33602', 'United States', 'Driver License', 'DL87654321', CURDATE(), 8, 2200.00),
('Sarah', 'Williams', 'sarah.williams@example.com', '+1567890123', '321 Maple Dr', 'Jacksonville', 'FL', '32202', 'United States', 'Passport', 'P12345678', CURDATE(), 1, 350.00),
('Michael', 'Brown', 'michael.brown@example.com', '+1654321789', '654 Beach Rd', 'Miami Beach', 'FL', '33139', 'United States', 'Driver License', 'DL56789012', CURDATE(), 12, 4500.00);

-- Sample Jet Skis
INSERT INTO jet_skis (model, brand, year, registration_number, hourly_rate, daily_rate, status, last_maintenance_date, next_maintenance_date) VALUES
('Wave Runner VX', 'Yamaha', 2022, 'YJK-2022-001', 75.00, 350.00, 'available', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY)),
('Sea-Doo Spark', 'Sea-Doo', 2021, 'SDS-2021-002', 65.00, 300.00, 'maintenance', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY)),
('Ultra 310LX', 'Kawasaki', 2023, 'KUS-2023-003', 85.00, 400.00, 'rented', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('RXP-X 300', 'Sea-Doo', 2022, 'SDR-2022-004', 80.00, 380.00, 'available', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
('FX Cruiser SVHO', 'Yamaha', 2021, 'YFC-2021-005', 90.00, 420.00, 'available', DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY));

-- Sample Tourist Boats
INSERT INTO boats (name, type, capacity, year, registration_number, hourly_rate, daily_rate, status, features, last_maintenance_date, next_maintenance_date) VALUES
('Sea Explorer', 'Pontoon Boat', 12, 2021, 'TB-2021-001', 150.00, 750.00, 'available', 'GPS, Refrigerator, Bathroom, Sunshade', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY)),
('Ocean Voyager', 'Cabin Cruiser', 8, 2020, 'TB-2020-002', 200.00, 900.00, 'maintenance', 'GPS, Air Conditioning, Kitchen, Shower, Sleeping Quarters', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY)),
('Coastal Cruiser', 'Bowrider', 6, 2022, 'TB-2022-003', 120.00, 600.00, 'rented', 'GPS, Bluetooth Stereo, Sunshade', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('Island Hopper', 'Deck Boat', 10, 2019, 'TB-2019-004', 140.00, 680.00, 'available', 'GPS, Bluetooth Stereo, Refrigerator, Sunshade', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY));

-- Sample Maintenance Records
INSERT INTO maintenance_records (equipment_type, equipment_id, maintenance_date, performed_by, notes, next_maintenance_date, cost) VALUES
('jet_ski', 1, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'John Doe', 'Regular maintenance - Oil change, filter replacement', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 120.00),
('jet_ski', 1, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'Mike Smith', 'Engine inspection and tuning', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 85.00),
('jet_ski', 1, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 'John Doe', 'Hull repair and polishing', DATE_SUB(CURDATE(), INTERVAL 45 DAY), 150.00),
('boat', 1, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'John Doe', 'Regular maintenance - Engine tuning, hull cleaning, safety equipment check', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 250.00),
('boat', 1, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'Mike Smith', 'Electrical system repairs, navigation equipment update', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 320.00),
('boat', 1, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 'John Doe', 'Winter storage maintenance, propeller replacement', DATE_SUB(CURDATE(), INTERVAL 45 DAY), 180.00);

-- Sample Reservations (past and upcoming)
INSERT INTO reservations (client_id, equipment_type, equipment_id, start_date, end_date, total_amount, deposit_amount, deposit_paid, status, created_by) VALUES
-- Completed reservations
(1, 'jet_ski', 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 350.00, 100.00, TRUE, 'completed', 1),
(2, 'jet_ski', 4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 380.00, 100.00, TRUE, 'completed', 1),
(3, 'boat', 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 750.00, 200.00, TRUE, 'completed', 1),
-- Current rentals
(3, 'jet_ski', 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 800.00, 200.00, TRUE, 'confirmed', 1),
(5, 'boat', 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 0 DAY), 600.00, 150.00, TRUE, 'confirmed', 1),
-- Upcoming reservations
(1, 'jet_ski', 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 350.00, 100.00, TRUE, 'confirmed', 1),
(4, 'boat', 4, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 680.00, 170.00, TRUE, 'confirmed', 1),
(2, 'jet_ski', 5, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 420.00, 105.00, FALSE, 'pending', 1);

-- Sample Invoices
INSERT INTO invoices (invoice_number, reservation_id, issue_date, due_date, subtotal, tax_rate, tax_amount, total_amount, deposit_paid, balance_due, status, created_by) VALUES
('INV-2023-001', 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 350.00, 7.00, 24.50, 374.50, 100.00, 0.00, 'paid', 1),
('INV-2023-002', 2, DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 380.00, 7.00, 26.60, 406.60, 100.00, 0.00, 'paid', 1),
('INV-2023-003', 3, DATE_SUB(CURDATE(), INTERVAL 11 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 750.00, 7.00, 52.50, 802.50, 200.00, 0.00, 'paid', 1),
('INV-2023-004', 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 800.00, 7.00, 56.00, 856.00, 200.00, 656.00, 'partially_paid', 1),
('INV-2023-005', 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 0 DAY), 600.00, 7.00, 42.00, 642.00, 150.00, 492.00, 'partially_paid', 1),
('INV-2023-006', 6, DATE_ADD(CURDATE(), INTERVAL 4 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 350.00, 7.00, 24.50, 374.50, 100.00, 274.50, 'partially_paid', 1),
('INV-2023-007', 7, DATE_ADD(CURDATE(), INTERVAL 6 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 680.00, 7.00, 47.60, 727.60, 170.00, 557.60, 'partially_paid', 1);

-- Sample Payments
INSERT INTO payments (invoice_id, amount, payment_date, payment_method, transaction_reference, created_by) VALUES
(1, 100.00, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'credit_card', 'TXREF123456', 1),
(1, 274.50, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'credit_card', 'TXREF123457', 1),
(2, 100.00, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 'credit_card', 'TXREF123458', 1),
(2, 306.60, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'cash', NULL, 1),
(3, 200.00, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'credit_card', 'TXREF123459', 1),
(3, 602.50, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'bank_transfer', 'TXREF123460', 1),
(4, 200.00, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'credit_card', 'TXREF123461', 1),
(5, 150.00, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'credit_card', 'TXREF123462', 1),
(6, 100.00, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'paypal', 'TXREF123463', 1),
(7, 170.00, DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'debit_card', 'TXREF123464', 1); 