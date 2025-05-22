-- Add order_type column if it doesn't exist
ALTER TABLE pos_order 
ADD COLUMN IF NOT EXISTS order_type ENUM('Dine-in', 'Takeout', 'Delivery', 'Grab Food') NOT NULL DEFAULT 'Dine-in';

-- Update existing orders to have a default type
UPDATE pos_order SET order_type = 'Dine-in' WHERE order_type IS NULL; 