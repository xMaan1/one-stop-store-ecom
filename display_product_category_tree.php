<?php

// Bootstrap the Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import the necessary classes
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface;
use Botble\Base\Supports\SortItemsWithChildrenHelper;

/**
 * Function to display the tree structure recursively
 */
function displayTreeNode($category, $level = 0)
{
    $indent = str_repeat('  ', $level);
    $folderIcon = count($category->children) > 0 ? '📁' : '📄';
    
    // Badge for product count
    $badge = '';
    if (isset($category->badge_with_count)) {
        $badge = ' [' . strip_tags($category->badge_with_count) . ' products]';
    }
    
    echo $indent . $folderIcon . " " . $category->name . $badge . "\n";
    
    // Process children recursively
    foreach ($category->children as $child) {
        displayTreeNode($child, $level + 1);
    }
}

try {
    // 1. Get all categories with their relationships
    $productCategoryRepo = app(ProductCategoryInterface::class);
    $categories = $productCategoryRepo->getProductCategories([], ['slugable'], ['products']);
    
    // 2. Sort the categories into a tree structure
    $sortHelper = app(SortItemsWithChildrenHelper::class);
    $sortHelper->setChildrenProperty('children')->setItems($categories);
    $categoriesTree = $sortHelper->sort();
    
    echo "==============================================\n";
    echo "      PRODUCT CATEGORY TREE STRUCTURE        \n";
    echo "==============================================\n\n";
    
    // Display the sorted tree
    foreach ($categoriesTree as $rootCategory) {
        displayTreeNode($rootCategory);
    }
    
    echo "\n==============================================\n";
    echo "Total Categories: " . count($categories) . "\n";
    
    // Demonstrate how to find a specific category and its descendants
    echo "\nExample: Finding a specific category (ID 1 - Television) and its descendants:\n";
    
    $television = $categories->where('id', 1)->first();
    if ($television) {
        $subcategories = $categories->where('parent_id', $television->id);
        
        echo "Television subcategories (" . $subcategories->count() . "):\n";
        foreach ($subcategories as $subcategory) {
            echo "- " . $subcategory->name . "\n";
            
            // Show grandchildren
            $grandchildren = $categories->where('parent_id', $subcategory->id);
            foreach ($grandchildren as $grandchild) {
                echo "  └─ " . $grandchild->name . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 