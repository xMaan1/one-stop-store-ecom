-- SQL script to delete all product categories
-- First, disable foreign key checks to avoid constraint errors
SET FOREIGN_KEY_CHECKS = 0;

-- Delete all product category relationships
DELETE FROM ec_product_category_product WHERE category_id IN (SELECT id FROM ec_product_categories);

-- Delete all product categories
TRUNCATE TABLE ec_product_categories;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1; 