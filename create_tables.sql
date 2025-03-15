USE pos_db;

-- Create pos_user table if it doesn't exist
CREATE TABLE IF NOT EXISTS pos_user (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    user_type ENUM('Admin', 'Cashier') NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    profile_image VARCHAR(255),
    user_status ENUM('Active', 'Inactive') DEFAULT 'Active',
    branch_id INT,
    employee_id VARCHAR(50),
    shift_schedule VARCHAR(50),
    date_hired DATE,
    emergency_contact VARCHAR(100),
    emergency_number VARCHAR(20),
    address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
);

-- Create pos_branch table
CREATE TABLE IF NOT EXISTS pos_branch (
    branch_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_name VARCHAR(100) NOT NULL,
    branch_code VARCHAR(20) NOT NULL UNIQUE,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    street_address TEXT NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    complete_address TEXT NOT NULL,
    manager_name VARCHAR(100) NOT NULL,
    opening_date DATE NOT NULL,
    operating_hours VARCHAR(50) NOT NULL,
    seating_capacity INT,
    notes TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create pos_cashier_details table
CREATE TABLE IF NOT EXISTS pos_cashier_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    branch_id INT NOT NULL,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    shift_schedule VARCHAR(50) NOT NULL,
    date_hired DATE NOT NULL,
    emergency_contact VARCHAR(100) NOT NULL,
    emergency_number VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
    FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
); 