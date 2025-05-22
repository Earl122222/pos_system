-- Create pos_order table if it doesn't exist
CREATE TABLE IF NOT EXISTS pos_order (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL,
    order_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') NOT NULL DEFAULT 'Dine-in',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
    order_status VARCHAR(20) NOT NULL DEFAULT 'Completed',
    order_created_by INT NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_created_by) REFERENCES pos_user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create pos_order_item table if it doesn't exist
CREATE TABLE IF NOT EXISTS pos_order_item (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (order_id) REFERENCES pos_order(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES pos_product(product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 