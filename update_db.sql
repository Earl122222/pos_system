ALTER TABLE pos_product 
ADD COLUMN IF NOT EXISTS description TEXT AFTER product_image,
ADD COLUMN IF NOT EXISTS ingredients TEXT AFTER description; 