# Fix for "Call to undefined function get_featured_products()" Error

This document provides steps to fix the error `Call to undefined function get_featured_products()` that occurs when running your Laravel e-commerce site on StackCP hosting.

## Option 1: Quick Fix - Modify the index.php file

1. Upload the `fix_ecommerce_helper.php` file to your site's root directory on StackCP.

2. Edit your root `index.php` file and add the following line immediately after the autoload line:

```php
// After the line: require __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/fix_ecommerce_helper.php';
```

3. Save the modified index.php file and refresh your site.

## Option 2: Create a custom Service Provider

If Option 1 doesn't work, you can create a custom service provider:

1. Create a file called `CustomHelperServiceProvider.php` in the `app/Providers` directory:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomHelperServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Manually include the helper files
        $helperPath = base_path('platform/plugins/ecommerce/helpers');
        
        if (is_dir($helperPath)) {
            foreach (scandir($helperPath) as $helperFile) {
                if ($helperFile != '.' && $helperFile != '..' && file_exists($path = $helperPath . '/' . $helperFile)) {
                    require_once $path;
                }
            }
        }
    }
}
```

2. Register this service provider in `config/app.php` by adding it to the providers array:

```php
'providers' => [
    // Other providers...
    App\Providers\CustomHelperServiceProvider::class,
],
```

3. Clear the configuration cache:
```
php artisan config:clear
```

## Option 3: Copy Helper Function Directly

If neither Option 1 nor Option 2 works, you can directly edit the template file:

1. Edit the file `/platform/themes/shopwise/views/index.blade.php` to check for the function existence before using it:

```php
@if(function_exists('get_featured_products'))
    {!! get_featured_products() !!}
@else
    <!-- Fallback content -->
    <div class="alert alert-warning">Featured products are currently unavailable.</div>
@endif
```

## Option 4: Last Resort - Define the Function in the Template

As a last resort, you can define the function directly in your template file:

1. Add this to the very top of `/platform/themes/shopwise/views/index.blade.php`:

```php
@php
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
}
@endphp
```

## Troubleshooting

If you're still experiencing issues after trying these solutions:

1. Check the error logs on your hosting to identify the exact cause.
2. Verify that all files are uploaded correctly, especially the helper files.
3. Make sure you have the correct permissions set on all directories.
4. Try to clear all caches:
   ```
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

If none of these solutions work, you may need to contact StackCP support to check if there are any server configurations that might be preventing the helpers from loading properly. 