<?php
// Set unlimited execution time to prevent timeout
set_time_limit(0);
ini_set('memory_limit', '512M');

// FTP connection details
$ftp_server = "https://onestop-store.stackstaging.com/";
$ftp_username = "onestop.store";
$ftp_password = "0|8|5.>w(A9e";
$remote_dir = "/"; // or the specific directory on your StackCP hosting

// Connect to FTP server
echo "Connecting to FTP server...\n";
$conn_id = ftp_connect($ftp_server);

if (!$conn_id) {
    die("Could not connect to the FTP server!");
}

// Login with username and password
echo "Logging in...\n";
$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);

if (!$login_result) {
    ftp_close($conn_id);
    die("Login failed!");
}

// Turn on passive mode
ftp_pasv($conn_id, true);

// Get current directory
$current_dir = ftp_pwd($conn_id);
echo "Current directory: " . $current_dir . "\n";

// Function to recursively upload directory
function upload_directory($conn_id, $local_dir, $remote_dir) {
    // Create remote directory if it doesn't exist
    if (!@ftp_chdir($conn_id, $remote_dir)) {
        ftp_mkdir($conn_id, $remote_dir);
        echo "Created directory: $remote_dir\n";
    }
    ftp_chdir($conn_id, $remote_dir);
    
    // Get local directory contents
    $files = scandir($local_dir);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..' || $file == '.git') {
            continue; // Skip these directories
        }
        
        $local_path = $local_dir . '/' . $file;
        $remote_path = $file;
        
        if (is_dir($local_path)) {
            // It's a directory, recursively upload
            upload_directory($conn_id, $local_path, $remote_path);
            ftp_chdir($conn_id, '..'); // Go back up after recursion
        } else {
            // It's a file, upload it
            $upload = ftp_put($conn_id, $remote_path, $local_path, FTP_BINARY);
            if ($upload) {
                echo "Uploaded: $local_path to $remote_dir/$remote_path\n";
            } else {
                echo "Failed to upload: $local_path\n";
            }
        }
    }
}

// Start the upload
echo "Starting file upload...\n";

// Clean up any existing files (optional)
// Delete existing files in the destination directory
$files = ftp_nlist($conn_id, ".");
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        if (@ftp_size($conn_id, $file) == -1) {
            // It's a directory
            echo "Skipping directory: $file\n";
        } else {
            // It's a file
            if (ftp_delete($conn_id, $file)) {
                echo "Deleted file: $file\n";
            } else {
                echo "Failed to delete file: $file\n";
            }
        }
    }
}

// First, upload public directory contents to public_html
echo "Uploading public directory content...\n";
$public_dir = __DIR__ . '/public';
$files = scandir($public_dir);

foreach ($files as $file) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    
    $local_path = $public_dir . '/' . $file;
    $remote_path = $file;
    
    if (is_dir($local_path)) {
        upload_directory($conn_id, $local_path, $remote_path);
        ftp_chdir($conn_id, $remote_dir); // Return to base directory
    } else {
        $upload = ftp_put($conn_id, $remote_path, $local_path, FTP_BINARY);
        if ($upload) {
            echo "Uploaded: $local_path to $remote_path\n";
        } else {
            echo "Failed to upload: $local_path\n";
        }
    }
}

// Create a directory for the Laravel app outside public_html
echo "Creating Laravel app directory...\n";
if (!@ftp_chdir($conn_id, "laravel")) {
    ftp_mkdir($conn_id, "laravel");
    echo "Created directory: laravel\n";
}
ftp_chdir($conn_id, "laravel");

// Upload Laravel core files and directories (excluding public)
$exclude_dirs = array('.', '..', '.git', 'public', 'node_modules', 'vendor', 'storage');
$files = scandir(__DIR__);

foreach ($files as $file) {
    if (in_array($file, $exclude_dirs)) {
        continue;
    }
    
    $local_path = __DIR__ . '/' . $file;
    $remote_path = $file;
    
    if (is_dir($local_path)) {
        upload_directory($conn_id, $local_path, $remote_path);
        ftp_chdir($conn_id, "../laravel"); // Return to laravel directory
    } else {
        $upload = ftp_put($conn_id, $remote_path, $local_path, FTP_BINARY);
        if ($upload) {
            echo "Uploaded: $local_path to laravel/$remote_path\n";
        } else {
            echo "Failed to upload: $local_path\n";
        }
    }
}

// Special handling for storage directory
echo "Uploading storage directory with special handling...\n";
upload_directory($conn_id, __DIR__ . '/storage', 'storage');

// Create .env file with proper configuration
echo "Creating .env file...\n";
$env_content = "APP_NAME=\"Botble CMS\"
APP_DEBUG=false
APP_ENV=production
APP_URL=https://onestop-store.stackstaging.com
APP_KEY=base64:gvnyrpg1kBqWFyGSsCtB/+rhC4hLBs/E5wjjE8IFMxY=
LOG_CHANNEL=daily

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_onestop
DB_USERNAME=username_onestop
DB_PASSWORD=0|8|5.>w(A9e
DB_STRICT=false

ADMIN_DIR=admin";

// Save .env content to a temporary file
$temp_env = tempnam(sys_get_temp_dir(), 'env');
file_put_contents($temp_env, $env_content);

// Upload the .env file
$upload = ftp_put($conn_id, '.env', $temp_env, FTP_ASCII);
if ($upload) {
    echo "Uploaded: .env file with proper configuration\n";
} else {
    echo "Failed to upload: .env file\n";
}
unlink($temp_env); // Delete the temporary file

// Create a special index.php file in the public directory to point to the Laravel installation
echo "Creating index.php file to connect to Laravel installation...\n";
$index_content = "<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on.
|
*/

require __DIR__.'/../laravel/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

\$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

\$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);

\$response = \$kernel->handle(
    \$request = Illuminate\Http\Request::capture()
);

\$response->send();

\$kernel->terminate(\$request, \$response);";

// Save index.php content to a temporary file
$temp_index = tempnam(sys_get_temp_dir(), 'index');
file_put_contents($temp_index, $index_content);

// Navigate back to public_html
ftp_chdir($conn_id, "/");

// Upload the index.php file
$upload = ftp_put($conn_id, 'index.php', $temp_index, FTP_ASCII);
if ($upload) {
    echo "Uploaded: index.php file to connect to Laravel installation\n";
} else {
    echo "Failed to upload: index.php file\n";
}
unlink($temp_index); // Delete the temporary file

// Close the FTP connection
ftp_close($conn_id);
echo "FTP connection closed.\n";

echo "Deployment completed!\n";
echo "Your Laravel application should now be accessible at: https://onestop-store.stackstaging.com\n";
?> 