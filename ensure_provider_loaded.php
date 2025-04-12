<?php

/**
 * Provider Loading Check
 * 
 * This script ensures the EcommerceServiceProvider is properly loaded
 * and manually loads helper files if needed.
 */

// Check if the EcommerceServiceProvider is loaded
// We'll do this by checking if a core ecommerce function exists
if (!function_exists('get_featured_products')) {
    // If it doesn't exist, we'll manually load the provider class
    $providerClass = 'Botble\Ecommerce\Providers\EcommerceServiceProvider';
    
    if (class_exists($providerClass)) {
        // If the class exists, create an instance and call the boot method
        $provider = new $providerClass(app());
        
        if (method_exists($provider, 'boot')) {
            try {
                $provider->boot();
                error_log('Manually booted EcommerceServiceProvider');
            } catch (\Exception $e) {
                error_log('Error booting EcommerceServiceProvider: ' . $e->getMessage());
            }
        }
    } else {
        error_log('EcommerceServiceProvider class does not exist');
    }
    
    // As a fallback, directly load the helpers
    $helpersPath = __DIR__ . '/platform/plugins/ecommerce/helpers/products.php';
    
    if (file_exists($helpersPath)) {
        require_once $helpersPath;
        error_log('Manually loaded products helper file');
    } else {
        error_log('Could not find products helper file at: ' . $helpersPath);
    }
}

// Check if Helper class exists and use it to load helpers
if (class_exists('\Botble\Base\Supports\Helper')) {
    \Botble\Base\Supports\Helper::autoload(__DIR__ . '/platform/plugins/ecommerce/helpers');
    error_log('Used Helper class to load ecommerce helpers');
}

// Define the function directly as a last resort
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
    
    error_log('Defined get_featured_products function directly');
}

// Debug info
echo '<!-- Provider loading check executed -->';
?> 