# Deploying to StackCP Hosting

This guide explains how to deploy your Laravel e-commerce application to StackCP hosting.

## Prerequisites

- StackCP account with FTP access
- MySQL database set up in StackCP
- PHP 7.3 or higher on your hosting

## Deployment Steps

### 1. FTP Upload

Upload all files to your StackCP hosting using FTP. You can use any FTP client like FileZilla.

```
Host: onestop.store
Username: username
Password: 0|8|5.>w(A9e
Port: 21
```

### 2. Directory Structure

The recommended directory structure for hosting:

- `public_html/` - Contains all files from your `public/` directory
- `laravel/` - Contains all other Laravel files and folders

### 3. Web-based Installer

After uploading your files, navigate to the installer:

```
https://onestop-store.stackstaging.com/install.php
```

The installer will:
- Check server requirements
- Set up your database
- Configure your environment
- Set proper permissions
- Complete the installation

### 4. Alternative Manual Deployment

If the web installer doesn't work, you can follow these manual steps:

#### a. Database Setup

1. Log into your StackCP dashboard
2. Go to the MySQL section and create a new database
3. Import your database.sql file

#### b. Environment Configuration

1. Edit the `.env` file with your database credentials
2. Set the APP_URL to your domain
3. Set APP_DEBUG to false

```
APP_NAME="Botble CMS"
APP_DEBUG=false
APP_ENV=production
APP_URL=https://onestop-store.stackstaging.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
ADMIN_DIR=admin
```

#### c. Directory Permissions

Ensure these directories are writable:
- storage/
- bootstrap/cache/

#### d. .htaccess Configuration

Make sure your .htaccess file is properly set up in the public directory.

### 5. Checking the Installation

After deployment, you can:
- Visit your site at: https://onestop-store.stackstaging.com
- Access admin dashboard at: https://onestop-store.stackstaging.com/admin
- Login with the default credentials:
  - Username: botble
  - Password: 159357

### 6. Post-Installation

- Change the default admin password
- Configure your website settings through the admin dashboard
- Set up mail configurations
- Clear cache: `php artisan cache:clear`

## Troubleshooting

### 500 Internal Server Error
- Check your .env file configuration
- Check file permissions
- Check PHP version (should be 7.3+)
- Check error logs in StackCP

### Missing Assets
- Make sure storage symbolic link is created
- Verify .htaccess is properly set up
- Run `php artisan storage:link` on the server if needed

### Database Connection Issues
- Verify your database credentials
- Check that the database exists
- Ensure your database user has proper permissions

## Support

If you encounter any issues with your deployment, please contact StackCP support. 