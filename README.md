# One Stop Store E-Commerce

This repository contains a full-featured Laravel e-commerce system that provides everything needed for an online shopping platform.

## Features

- Product management with categories, attributes, and variations
- Order management and checkout process
- Customer accounts and authentication
- Payment gateway integrations (PayPal, Stripe, others)
- Shipping methods and calculations
- Discounts and promotions
- Reviews and ratings
- Wishlists and product comparisons
- Responsive theme based on Bootstrap

## Installation

### Requirements

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Node.js and NPM

### Setup Instructions

1. Clone the repository:
   ```
   git clone https://github.com/xMaan1/one-stop-store-ecom.git
   cd one-stop-store-ecom
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Copy the environment file:
   ```
   cp .env.example .env
   ```

4. Generate application key:
   ```
   php artisan key:generate
   ```

5. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```

6. Run migrations and seeders:
   ```
   php artisan migrate
   php artisan db:seed
   ```

7. Create storage link:
   ```
   php artisan storage:link
   ```

8. Install frontend dependencies:
   ```
   npm install
   npm run dev
   ```

9. Start the development server:
   ```
   php artisan serve
   ```

Visit `http://localhost:8000` to see your store in action.

## Admin Access

After running the seeders, you can access the admin panel with:

- URL: `http://localhost:8000/admin`
- Email: `admin@example.com`
- Password: `password`

## Documentation

For detailed documentation on the product category tree structure, see [Product Category Tree Documentation](PRODUCT_CATEGORY_TREE_DOCUMENTATION.md).

## License

This project is licensed under the MIT License. 