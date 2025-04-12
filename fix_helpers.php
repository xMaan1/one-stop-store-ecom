<?php

/**
 * Helper Fix Script
 * 
 * This script checks if the get_featured_products function exists
 * and loads the helper file manually if it doesn't.
 * 
 * Place this file in your project root and require it from index.php
 */

// Check if the function exists
if (!function_exists('get_featured_products')) {
    // Define the path to the products helper file
    $helpersPath = __DIR__ . '/platform/plugins/ecommerce/helpers/products.php';
    
    // Check if the file exists
    if (file_exists($helpersPath)) {
        // Load the file
        require_once $helpersPath;
        error_log('Manually loaded products helper file');
    } else {
        error_log('ERROR: Could not find products helper file at: ' . $helpersPath);
    }
}

// Check if other key helper functions exist and load them if needed
$helperFiles = [
    'products.php',
    'common.php',
    'currencies.php',
    'prices.php',
    'product-categories.php',
    'discounts.php',
    'product-variations.php',
    'order.php',
    'shipping.php',
    'constants.php',
    'brands.php',
    'product-attributes.php',
    'customer.php'
];

foreach ($helperFiles as $file) {
    $helpersPath = __DIR__ . '/platform/plugins/ecommerce/helpers/' . $file;
    if (file_exists($helpersPath)) {
        require_once $helpersPath;
        error_log('Manually loaded helper file: ' . $file);
    }
}

// Debug info
echo '<!-- Helper fix script executed -->';
?> 