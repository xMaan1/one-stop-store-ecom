# Product Category Tree Structure Documentation

## Overview

This document provides an overview of how product categories are structured and managed in our Laravel e-commerce system. The product category system is implemented as a hierarchical tree structure, allowing for parent-child relationships between categories.

## Technical Implementation

The product category tree structure is implemented using the following components:

### Database Schema

The `ec_product_categories` table stores all product categories with the following key columns:
- `id`: Primary key
- `name`: Category name
- `parent_id`: Reference to parent category (null for root categories)
- `order`: Position in the tree for sorting
- `status`: Whether the category is active or inactive
- `image`: Category image path
- `is_featured`: Whether to show in featured sections

### Models

The `ProductCategory` model in `platform/plugins/ecommerce/src/Models/ProductCategory.php` includes relationships:

```php
// Parent-child relationships
public function parent()
{
    return $this->belongsTo(ProductCategory::class, 'parent_id')->withDefault();
}

public function children()
{
    return $this->hasMany(ProductCategory::class, 'parent_id');
}

// Products in this category
public function products()
{
    return $this->hasMany(Product::class, 'category_id');
}
```

## Controller Logic

The `ProductCategoryController` manages the tree structure:

```php
// Getting categories in tree structure
public function index(Request $request, BaseHttpResponse $response)
{
    $this->pageTitle(trans('plugins/ecommerce::product-categories.name'));

    $categories = $this->productCategoryRepository->getProductCategories([], ['children'], ['id', 'name', 'parent_id']);

    if ($request->ajax()) {
        $data = view('plugins/ecommerce::product-categories.partials.categories-tree', compact('categories'))->render();
        return $response->setData($data);
    }

    return view('plugins/ecommerce::product-categories.index', compact('categories'));
}

// Create a parent category
public function create(FormBuilder $formBuilder)
{
    $this->pageTitle(trans('plugins/ecommerce::product-categories.create'));

    return $formBuilder->create(ProductCategoryForm::class)->renderForm();
}
```

## Views

The category tree is rendered using recursive Blade templates:

- `category-tree.blade.php`: Renders the hierarchical structure as an unordered list
- `categories-tree.blade.php`: Wrapper for rendering the entire tree with permissions

## JavaScript Functionality

The tree structure is enhanced with JavaScript for:
- Drag and drop reordering
- Expanding/collapsing nodes
- AJAX loading of children
- Inline editing of category properties

## How to Use

### Creating Categories

1. Navigate to Products → Categories in the admin panel
2. Click "Create" button to add a new category
3. Fill in the category details including:
   - Name
   - Parent category (if any)
   - Description
   - Image
   - Status

### Managing the Hierarchy

1. Use drag and drop on the category list to rearrange categories
2. Categories can be nested up to 3 levels deep
3. Click on the arrow next to a parent category to expand/collapse its children

### Best Practices

1. Keep the category structure shallow (max 3 levels)
2. Use clear, concise names for categories
3. Consider SEO implications when naming categories
4. Use consistent naming conventions
5. Consider adding category thumbnails for better user experience

## API Endpoints

The category tree can be accessed via these API endpoints:

- `GET /api/v1/product-categories`: List all categories
- `GET /api/v1/product-categories/{id}`: Get a specific category with its children

## Troubleshooting

Common issues:
- Categories not appearing: Check the 'status' field is set to active
- Tree not updating: Clear cache after making structural changes
- Performance issues: Too many categories in a single level can slow down page loads 