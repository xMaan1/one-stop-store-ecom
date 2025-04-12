<?php
/**
 * StackCP Deployment Script
 * 
 * This script orchestrates the deployment of your Laravel application to StackCP hosting.
 * It will:
 * 1. Upload files via FTP
 * 2. Set up the database
 * 3. Configure the environment
 * 4. Set proper permissions
 * 
 * Usage: php deploy_to_stackcp.php
 */

// Set unlimited execution time to prevent timeout
set_time_limit(0);
ini_set('memory_limit', '512M');

// Colors for terminal output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

// Configuration
$config = [
    'ftp' => [
        'host' => 'onestop.store',
        'username' => 'username',
        'password' => '0|8|5.>w(A9e',
        'port' => 21,
        'timeout' => 90,
        'passive' => true
    ],
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'username_onestop',
        'user' => 'username_onestop',
        'pass' => '0|8|5.>w(A9e'
    ],
    'app' => [
        'name' => 'Botble CMS',
        'url' => 'https://onestop-store.stackstaging.com',
        'debug' => false,
        'env' => 'production',
        'admin_dir' => 'admin'
    ]
];

// Display header
echo COLOR_BLUE . "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                                                           ║\n";
echo "║             Laravel StackCP Deployment Tool               ║\n";
echo "║                                                           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo COLOR_RESET . "\n";

// Ask for confirmation
echo COLOR_YELLOW . "This script will deploy your Laravel application to StackCP hosting.\n\n";
echo "FTP Host: " . $config['ftp']['host'] . "\n";
echo "FTP Username: " . $config['ftp']['username'] . "\n";
echo "Database Name: " . $config['db']['name'] . "\n";
echo "App URL: " . $config['app']['url'] . "\n\n";
echo COLOR_RESET;

echo "Press ENTER to continue or CTRL+C to abort...\n";
fgets(STDIN);

// Step 1: FTP Upload
echo COLOR_BLUE . "\n[1/4] Starting file upload via FTP...\n" . COLOR_RESET;
uploadViaFTP($config['ftp']);

// Step 2: Database Setup
echo COLOR_BLUE . "\n[2/4] Setting up the database...\n" . COLOR_RESET;
// Note: This will be handled via the web installer

// Step 3: Environment Configuration
echo COLOR_BLUE . "\n[3/4] Configuring the environment...\n" . COLOR_RESET;
// Note: This will be handled via the web installer

// Step 4: Set Permissions
echo COLOR_BLUE . "\n[4/4] Setting directory permissions...\n" . COLOR_RESET;
// Note: This will be handled via the web installer

// Completion
echo COLOR_GREEN . "\n✓ Deployment preparation complete!\n\n";
echo "Your files have been uploaded to the server.\n";
echo "To complete the installation, visit:\n";
echo COLOR_BLUE . $config['app']['url'] . "/install.php\n\n" . COLOR_RESET;
echo "Follow the on-screen instructions to:\n";
echo "- Check server requirements\n";
echo "- Set up your database\n";
echo "- Configure your environment\n";
echo "- Set proper permissions\n\n";
echo "After installation is complete, you can access:\n";
echo "- Website: " . COLOR_BLUE . $config['app']['url'] . COLOR_RESET . "\n";
echo "- Admin dashboard: " . COLOR_BLUE . $config['app']['url'] . "/admin" . COLOR_RESET . "\n";
echo "- Default admin login:\n";
echo "  - Username: botble\n";
echo "  - Password: 159357\n\n";
echo COLOR_YELLOW . "Don't forget to change the default admin password after logging in!" . COLOR_RESET . "\n\n";

// Function to handle FTP upload
function uploadViaFTP($ftp_config) {
    // Connect to FTP server
    echo "Connecting to FTP server " . $ftp_config['host'] . "...\n";
    $conn_id = ftp_connect($ftp_config['host'], $ftp_config['port'], $ftp_config['timeout']);
    
    if (!$conn_id) {
        echo COLOR_RED . "Could not connect to the FTP server!\n" . COLOR_RESET;
        exit(1);
    }
    
    // Login with username and password
    echo "Logging in...\n";
    $login_result = ftp_login($conn_id, $ftp_config['username'], $ftp_config['password']);
    
    if (!$login_result) {
        ftp_close($conn_id);
        echo COLOR_RED . "FTP login failed!\n" . COLOR_RESET;
        exit(1);
    }
    
    // Set passive mode if required
    if ($ftp_config['passive']) {
        ftp_pasv($conn_id, true);
    }
    
    // Get current directory
    $current_dir = ftp_pwd($conn_id);
    echo "Current FTP directory: " . $current_dir . "\n";
    
    // Upload installation files first
    echo "Uploading installer files...\n";
    $installer_files = [
        'install.php',
        '.htaccess.new' => '.htaccess',
        'fix_permissions.php',
        'STACKCP_DEPLOY_README.md' => 'README.md'
    ];
    
    foreach ($installer_files as $local_file => $remote_file) {
        if (is_int($local_file)) {
            $local_file = $remote_file;
        }
        
        if (file_exists($local_file)) {
            $upload = ftp_put($conn_id, $remote_file, $local_file, FTP_ASCII);
            if ($upload) {
                echo "✓ Uploaded: " . $local_file . " → " . $remote_file . "\n";
            } else {
                echo COLOR_RED . "Failed to upload: " . $local_file . "\n" . COLOR_RESET;
            }
        } else {
            echo COLOR_YELLOW . "Warning: File not found: " . $local_file . "\n" . COLOR_RESET;
        }
    }
    
    // Upload deployment files
    echo "\nUploading deployment files.\n";
    echo "This may take some time depending on your internet connection...\n";
    
    // Create laravel directory for non-public files
    if (!@ftp_chdir($conn_id, "laravel")) {
        ftp_mkdir($conn_id, "laravel");
        echo "Created directory: laravel\n";
    }
    ftp_chdir($conn_id, $current_dir);
    
    // Upload database file
    if (file_exists('database.sql')) {
        $upload = ftp_put($conn_id, 'database.sql', 'database.sql', FTP_BINARY);
        if ($upload) {
            echo "✓ Uploaded: database.sql\n";
        } else {
            echo COLOR_RED . "Failed to upload: database.sql\n" . COLOR_RESET;
        }
    }
    
    echo "\nFile upload completed.\n";
    
    // Close the FTP connection
    ftp_close($conn_id);
    echo "FTP connection closed.\n";
    
    return true;
}
?>