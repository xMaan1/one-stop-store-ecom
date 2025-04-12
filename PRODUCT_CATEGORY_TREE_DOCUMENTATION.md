# Product Category Tree Structure Documentation

This document provides a comprehensive guide to the product category tree structure implementation in the Laravel E-Commerce System.

## Overview

The product category tree structure is a hierarchical representation of product categories, allowing for parent-child relationships between categories. This structure is essential for organizing products in an intuitive and navigable way.

## Database Structure

The product categories are stored in the `ec_product_categories` table with the following key fields:

| Field       | Type         | Description                                    |
|-------------|--------------|------------------------------------------------|
| id          | int          | Primary key                                    |
| name        | varchar(191) | Category name                                  |
| parent_id   | int          | Reference to parent category (0 for root level) |
| description | mediumtext   | Description of the category                    |
| status      | varchar(60)  | Category status (published, draft, pending)    |
| order       | int          | Order for sorting categories                   |
| image       | varchar(255) | Image path for category                        |
| is_featured | tinyint      | Flag to mark featured categories               |

## Model Relationships

The `ProductCategory` model (`platform/plugins/ecommerce/src/Models/ProductCategory.php`) defines the following relationships:

1. **Parent-Child Relationships**:
   - `parent()`: BelongsTo relationship to the parent category
   - `children()`: HasMany relationship to child categories

2. **Products Relationship**:
   - `products()`: BelongsToMany relationship to associate products with categories

## Key Components

### 1. Models

The `ProductCategory` model includes methods for:
- Managing parent-child relationships
- Handling badges with product counts
- Automatically detaching products when a category is deleted

### 2. Controllers

The `ProductCategoryController` manages:
- Displaying categories in a tree structure
- Creating new categories
- Editing existing categories
- Deleting categories
- Moving categories within the tree

### 3. Repositories

The `ProductCategoryRepository` provides methods for:
- Retrieving categories with various conditions
- Getting product categories with their relationships
- Managing featured categories

### 4. Helpers

Two key helper functions support the tree structure:
- `get_product_categories()`: Retrieves and sorts categories
- `sort_item_with_children()`: Recursively builds the tree structure

### 5. Views

Tree views are implemented using:
- Recursive Blade templates
- Interactive JavaScript for expand/collapse

## How to Use

### 1. Retrieving the Category Tree

```php
// Get all product categories with their relationships
$productCategoryRepo = app(\Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface::class);
$categories = $productCategoryRepo->getProductCategories([], ['slugable'], ['products']);

// Sort them into a tree structure
$sortHelper = app(\Botble\Base\Supports\SortItemsWithChildrenHelper::class);
$sortHelper->setChildrenProperty('children')->setItems($categories);
$categoriesTree = $sortHelper->sort();
```

### 2. Creating Categories

```php
// Create a parent category
$parentCategory = new \Botble\Ecommerce\Models\ProductCategory();
$parentCategory->name = 'Parent Category';
$parentCategory->parent_id = 0; // Root level
$parentCategory->status = 'published';
$parentCategory->save();

// Create a child category
$childCategory = new \Botble\Ecommerce\Models\ProductCategory();
$childCategory->name = 'Child Category';
$childCategory->parent_id = $parentCategory->id;
$childCategory->status = 'published';
$childCategory->save();
```

### 3. Editing Categories

```php
$category = \Botble\Ecommerce\Models\ProductCategory::find($id);
$category->name = 'Updated Name';
$category->save();
```

### 4. Moving Categories in the Tree

```php
// Move a category to a different parent
$category = \Botble\Ecommerce\Models\ProductCategory::find($id);
$category->parent_id = $newParentId;
$category->save();
```

### 5. Deleting Categories

```php
$categoryRepo = app(\Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface::class);
$category = \Botble\Ecommerce\Models\ProductCategory::find($id);
$categoryRepo->delete($category);
```

## Tree Rendering

The tree structure is rendered using recursive Blade templates:

1. `categories-tree.blade.php`: Sets up permissions and includes the category tree
2. `category-tree.blade.php`: Recursively renders the tree nodes

The JavaScript file `tree-category.js` handles:
- Expanding/collapsing nodes
- AJAX loading of category data
- Interactive UI elements

## Frontend Usage

To display categories in the frontend:

```php
{!! get_product_categories(['status' => \Botble\Base\Enums\BaseStatusEnum::PUBLISHED]) !!}
```

## Best Practices

1. **Adding New Categories**:
   - Always set a valid parent_id (0 for root level)
   - Provide a meaningful name and description
   - Set the appropriate status

2. **Moving Categories**:
   - Be cautious when moving categories with children
   - All children move with their parent

3. **Deleting Categories**:
   - Consider reassigning products before deletion
   - Remember that deleting a parent will not delete its children

## Conclusion

The product category tree structure provides a powerful and flexible way to organize products in the e-commerce system. By using the proper models, repositories, and helper functions, you can easily manage complex category hierarchies. 