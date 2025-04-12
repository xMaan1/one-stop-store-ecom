<?php
/**
 * Fix for "Call to undefined function get_featured_products()" error
 * 
 * This is a comprehensive solution that addresses the issue where helper functions
 * aren't being loaded properly in the hosting environment.
 */

// Option 1: Load the helper file directly
if (!function_exists('get_featured_products')) {
    $helpersPath = __DIR__ . '/platform/plugins/ecommerce/helpers/products.php';
    
    if (file_exists($helpersPath)) {
        require_once $helpersPath;
        error_log('FIX 1: Loaded helper file directly');
    } else {
        error_log('FIX 1 FAILED: Helper file not found at: ' . $helpersPath);
    }
}

// Option 2: Use the Helper class to load all helpers
if (class_exists('\Botble\Base\Supports\Helper') && !function_exists('get_featured_products')) {
    \Botble\Base\Supports\Helper::autoload(__DIR__ . '/platform/plugins/ecommerce/helpers');
    error_log('FIX 2: Used Helper class to load all helper files');
}

// Option 3: Define the function directly as a last resort
if (!function_exists('get_featured_products')) {
    function get_featured_products(array $params = []) {
        if (!class_exists('Botble\Ecommerce\Repositories\Interfaces\ProductInterface')) {
            return collect([]);
        }
        
        $params = array_merge([
            'condition' => [
                'ec_products.status'       => 'published',
                'ec_products.is_variation' => 0,
                'ec_products.is_featured'  => 1,
            ],
            'take'      => 10,
            'withCount' => [],
        ], $params);
        
        return app('Botble\Ecommerce\Repositories\Interfaces\ProductInterface')->getProducts($params);
    }
    
    error_log('FIX 3: Defined get_featured_products function directly');
}

// Additional helper functions that might be missing
if (!function_exists('get_trending_products')) {
    function get_trending_products(array $params = []) {
        if (!class_exists('Botble\Ecommerce\Repositories\Interfaces\ProductInterface')) {
            return collect([]);
        }
        
        $params = array_merge([
            'condition' => [
                'ec_products.status'       => 'published',
                'ec_products.is_variation' => 0,
            ],
            'take'      => 10,
            'order_by'  => [
                'ec_products.views' => 'DESC',
            ],
            'withCount' => [],
        ], $params);
        
        return app('Botble\Ecommerce\Repositories\Interfaces\ProductInterface')->getProducts($params);
    }
}

if (!function_exists('get_featured_product_categories')) {
    function get_featured_product_categories() {
        if (!class_exists('Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface')) {
            return collect([]);
        }
        
        return app('Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface')->getFeaturedCategories([
            'condition' => [
                'ec_product_categories.status' => 'published',
            ],
            'take'      => null,
        ]);
    }
}

if (!function_exists('get_product_collections')) {
    function get_product_collections(array $conditions = [], $take = null, array $with = []) {
        if (!class_exists('Botble\Ecommerce\Repositories\Interfaces\ProductCollectionInterface')) {
            return collect([]);
        }
        
        return app('Botble\Ecommerce\Repositories\Interfaces\ProductCollectionInterface')->advancedGet([
            'condition' => $conditions,
            'with'      => $with,
            'take'      => $take,
        ]);
    }
}

// Debug information
error_log('Ecommerce helper fix loaded and processed');
echo '<!-- Ecommerce helper fix loaded -->';
?> 