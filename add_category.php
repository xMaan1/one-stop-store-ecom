<?php

// Bootstrap the Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import the necessary classes
use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Support\Facades\DB;

try {
    // Create a new child category for Television
    $childCategory = new ProductCategory();
    $childCategory->name = 'LED TVs';
    $childCategory->parent_id = 1; // Television has ID 1
    $childCategory->description = 'LED Television subcategory';
    $childCategory->status = 'published';
    $childCategory->order = 0;
    $childCategory->is_featured = 0;
    $childCategory->save();

    echo "Successfully created child category 'LED TVs' with ID: " . $childCategory->id . "\n";

    // Query to verify the parent-child relationship
    $tree = DB::table('ec_product_categories')
        ->select('id', 'name', 'parent_id')
        ->where('parent_id', 1)
        ->orWhere('id', 1)
        ->get();

    echo "\nCategory tree structure:\n";
    echo "ID\tName\t\tParent ID\n";
    echo "------------------------------\n";
    foreach ($tree as $category) {
        echo $category->id . "\t" . $category->name . "\t\t" . $category->parent_id . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 