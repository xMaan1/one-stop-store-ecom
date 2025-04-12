-- Set all product brand_id values to NULL
UPDATE ec_products SET brand_id = NULL;

-- Delete all entries from the brands table (optional)
-- TRUNCATE TABLE ec_brands;

-- Set brand_id column to NULL in the ec_products table (ensure it's nullable)
-- ALTER TABLE ec_products MODIFY COLUMN brand_id INT UNSIGNED NULL; 