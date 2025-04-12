<?php

// Bootstrap the Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import the necessary classes
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface;
use Illuminate\Support\Facades\DB;

try {
    // =============== EDIT A CATEGORY ===============
    // Get the Smart LED TVs category (ID 38) and update it
    $smartLedCategory = ProductCategory::find(38);
    
    if ($smartLedCategory) {
        echo "Updating category: " . $smartLedCategory->name . " (ID: " . $smartLedCategory->id . ")\n";
        echo "Current values:\n";
        echo "- Name: " . $smartLedCategory->name . "\n";
        echo "- Description: " . $smartLedCategory->description . "\n";
        echo "- Is Featured: " . ($smartLedCategory->is_featured ? 'Yes' : 'No') . "\n";
        
        // Update the category
        $smartLedCategory->name = 'Smart 4K Ultra HD TVs';
        $smartLedCategory->description = 'High-end Smart 4K Ultra HD Television subcategory';
        $smartLedCategory->is_featured = 1;
        $smartLedCategory->save();
        
        echo "\nCategory updated successfully!\n";
        echo "New values:\n";
        echo "- Name: " . $smartLedCategory->name . "\n";
        echo "- Description: " . $smartLedCategory->description . "\n";
        echo "- Is Featured: " . ($smartLedCategory->is_featured ? 'Yes' : 'No') . "\n";
    } else {
        echo "Smart LED TVs category (ID 38) not found.\n";
    }
    
    // =============== CREATE AND DELETE A CATEGORY ===============
    // Create a temporary category for deletion demo
    $tempCategory = new ProductCategory();
    $tempCategory->name = 'Temporary Category';
    $tempCategory->parent_id = 1; // Television has ID 1
    $tempCategory->description = 'This category will be deleted';
    $tempCategory->status = 'published';
    $tempCategory->order = 10;
    $tempCategory->is_featured = 0;
    $tempCategory->save();
    
    echo "\nCreated temporary category with ID: " . $tempCategory->id . "\n";
    
    // Delete the temporary category
    $categoryRepo = app(ProductCategoryInterface::class);
    $categoryRepo->delete($tempCategory);
    
    echo "Temporary category deleted successfully!\n";
    
    // Verify deletion
    $deletedCategory = ProductCategory::find($tempCategory->id);
    if (!$deletedCategory) {
        echo "Verified: Category ID " . $tempCategory->id . " no longer exists.\n";
    } else {
        echo "Error: Category still exists.\n";
    }
    
    // =============== MOVING CATEGORIES IN THE TREE ===============
    // Move a category to a different parent
    $plasmaCategory = ProductCategory::find(37); // Plasma TVs
    
    if ($plasmaCategory) {
        echo "\nMoving category: " . $plasmaCategory->name . " (ID: " . $plasmaCategory->id . ")\n";
        echo "Current parent ID: " . $plasmaCategory->parent_id . "\n";
        
        // Move to Mobile category (ID 2)
        $plasmaCategory->parent_id = 2;
        $plasmaCategory->save();
        
        echo "New parent ID: " . $plasmaCategory->parent_id . "\n";
        echo "Category moved successfully!\n";
    }
    
    echo "\nProduct Category Tree Management operations completed successfully!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 