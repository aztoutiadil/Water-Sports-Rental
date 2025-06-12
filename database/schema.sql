-- Create database
CREATE DATABASE IF NOT EXISTS water_sports_rental;
USE water_sports_rental;

-- Create admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create clients table
CREATE TABLE IF NOT EXISTS clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create jet_skis table
CREATE TABLE IF NOT EXISTS jet_skis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model VARCHAR(100) NOT NULL,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'maintenance', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create tourist_boats table
CREATE TABLE IF NOT EXISTS tourist_boats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'maintenance', 'reserved') DEFAULT 'available',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    equipment_type ENUM('jet_ski', 'tourist_boat') NOT NULL,
    equipment_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    INDEX idx_equipment (equipment_type, equipment_id),
    INDEX idx_dates (start_time, end_time)
);

-- Create invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT NOT NULL UNIQUE,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pdf_path VARCHAR(255),
    FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$8K1p/bMXYLwJ4q1s8XNGx.XQZ0nHq7yN0kvIHxUwHZrLqkGDJX.Hy', 'admin@example.com'); 