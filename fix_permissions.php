<?php
// Fix Laravel directory permissions
// This script ensures the right permissions for the Laravel app after deployment

// Set unlimited execution time to prevent timeout
set_time_limit(0);

echo "Starting permission fix for Laravel directories...\n";

// Fix permissions for storage directory
if (is_dir('storage')) {
    echo "Setting permissions for storage directory...\n";
    
    // Make storage dir writable by web server
    chmod('storage', 0755);
    
    // Make all subdirectories writable
    $dirs = [
        'storage/app',
        'storage/app/public',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs'
    ];
    
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            chmod($dir, 0755);
            echo "Fixed permissions for: $dir\n";
        } else {
            mkdir($dir, 0755, true);
            echo "Created directory with proper permissions: $dir\n";
        }
    }
    
    // Set all files to be writable
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('storage'));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            chmod($file->getPathname(), 0644);
            echo "Fixed permissions for file: " . $file->getPathname() . "\n";
        }
    }
} else {
    echo "Warning: storage directory not found!\n";
}

// Bootstrap cache directory
if (is_dir('bootstrap/cache')) {
    echo "Setting permissions for bootstrap/cache directory...\n";
    chmod('bootstrap/cache', 0755);
    
    // Set all files to be writable
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('bootstrap/cache'));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            chmod($file->getPathname(), 0644);
            echo "Fixed permissions for file: " . $file->getPathname() . "\n";
        }
    }
} else {
    echo "Warning: bootstrap/cache directory not found!\n";
}

// Fix permissions for the public directory
if (is_dir('public')) {
    echo "Setting permissions for public directory...\n";
    chmod('public', 0755);
    
    // Set all files to be readable
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('public'));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            chmod($file->getPathname(), 0644);
            echo "Fixed permissions for file: " . $file->getPathname() . "\n";
        } elseif ($file->isDir() && !in_array($file->getBasename(), ['.', '..'])) {
            chmod($file->getPathname(), 0755);
            echo "Fixed permissions for directory: " . $file->getPathname() . "\n";
        }
    }
} else {
    echo "Warning: public directory not found!\n";
}

echo "Permission fixes completed!\n";
?> 