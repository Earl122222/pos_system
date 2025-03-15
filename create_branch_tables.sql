USE pos_db;

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

-- Create pos_cashier_sessions table
CREATE TABLE IF NOT EXISTS pos_cashier_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    branch_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES pos_user(user_id),
    FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id)
);

-- Create pos_stock_movement table
CREATE TABLE IF NOT EXISTS pos_stock_movement (
    movement_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT NOT NULL,
    inventory_id INT NOT NULL,
    user_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT', 'ADJUST') NOT NULL,
    quantity INT NOT NULL,
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    reference_type VARCHAR(50) NOT NULL,
    reference_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES pos_branch(branch_id),
    FOREIGN KEY (inventory_id) REFERENCES pos_inventory(inventory_id),
    FOREIGN KEY (user_id) REFERENCES pos_user(user_id)
); 