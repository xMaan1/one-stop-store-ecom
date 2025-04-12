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
    $plasmaCategory = new ProductCategory();
    $plasmaCategory->name = 'Plasma TVs';
    $plasmaCategory->parent_id = 1; // Television has ID 1
    $plasmaCategory->description = 'Plasma Television subcategory';
    $plasmaCategory->status = 'published';
    $plasmaCategory->order = 1;
    $plasmaCategory->is_featured = 0;
    $plasmaCategory->save();

    echo "Successfully created child category 'Plasma TVs' with ID: " . $plasmaCategory->id . "\n";

    // Create a grandchild category under LED TVs (ID 36)
    $smartLedCategory = new ProductCategory();
    $smartLedCategory->name = 'Smart LED TVs';
    $smartLedCategory->parent_id = 36; // LED TVs has ID 36
    $smartLedCategory->description = 'Smart LED Television subcategory';
    $smartLedCategory->status = 'published';
    $smartLedCategory->order = 0;
    $smartLedCategory->is_featured = 1;
    $smartLedCategory->save();

    echo "Successfully created grandchild category 'Smart LED TVs' with ID: " . $smartLedCategory->id . "\n";

    // Query to verify the multi-level tree structure
    $categories = ProductCategory::with('children')
        ->where('id', 1)
        ->orWhere('parent_id', 1)
        ->orWhere('parent_id', 36)
        ->get();

    echo "\nMulti-level Category tree structure:\n";
    
    foreach ($categories as $category) {
        if ($category->id == 1) {
            echo "- " . $category->name . " (ID: " . $category->id . ")\n";
            
            // Find direct children
            $children = $categories->where('parent_id', $category->id);
            foreach ($children as $child) {
                echo "  |- " . $child->name . " (ID: " . $child->id . ")\n";
                
                // Find grandchildren
                $grandchildren = $categories->where('parent_id', $child->id);
                foreach ($grandchildren as $grandchild) {
                    echo "     |- " . $grandchild->name . " (ID: " . $grandchild->id . ")\n";
                }
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 