<?php
// Simple web-based installer for the Laravel application on StackCP
session_start();

// Set unlimited execution time to prevent timeout
set_time_limit(0);
ini_set('memory_limit', '512M');

// Deployment steps
$steps = [
    'check_requirements' => 'Check Server Requirements',
    'create_database' => 'Create Database',
    'import_database' => 'Import Database',
    'configure_env' => 'Configure Environment',
    'set_permissions' => 'Set Directory Permissions',
    'clean_up' => 'Clean Up & Finalize'
];

// Get current step from URL or default to first step
$current_step = isset($_GET['step']) ? $_GET['step'] : 'check_requirements';
$step_index = array_search($current_step, array_keys($steps));
$next_step = ($step_index !== false && $step_index < count($steps) - 1) ? array_keys($steps)[$step_index + 1] : null;

// Process form submissions
$message = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($current_step) {
        case 'check_requirements':
            $success = true;
            $message = 'Server requirements check passed!';
            header("Location: install.php?step=$next_step");
            exit;
            break;
            
        case 'create_database':
            $db_host = $_POST['db_host'] ?? '127.0.0.1';
            $db_name = $_POST['db_name'] ?? 'username_onestop';
            $db_user = $_POST['db_user'] ?? 'username_onestop';
            $db_pass = $_POST['db_pass'] ?? '0|8|5.>w(A9e';
            
            try {
                // Connect to MySQL without selecting a database
                $dsn = "mysql:host=$db_host";
                $pdo = new PDO($dsn, $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Check if database exists
                $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
                $db_exists = $stmt->fetchColumn();
                
                if (!$db_exists) {
                    // Create the database
                    $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $message = "Database '$db_name' created successfully!";
                } else {
                    $message = "Database '$db_name' already exists.";
                }
                
                // Store DB credentials in session
                $_SESSION['db_host'] = $db_host;
                $_SESSION['db_name'] = $db_name;
                $_SESSION['db_user'] = $db_user;
                $_SESSION['db_pass'] = $db_pass;
                
                $success = true;
                header("Location: install.php?step=$next_step");
                exit;
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            }
            break;
            
        case 'import_database':
            $db_host = $_SESSION['db_host'] ?? '127.0.0.1';
            $db_name = $_SESSION['db_name'] ?? 'username_onestop';
            $db_user = $_SESSION['db_user'] ?? 'username_onestop';
            $db_pass = $_SESSION['db_pass'] ?? '0|8|5.>w(A9e';
            $sql_file = $_POST['sql_file'] ?? __DIR__ . '/database.sql';
            
            if (!file_exists($sql_file)) {
                $error = "SQL file not found: $sql_file";
                break;
            }
            
            try {
                // Read the SQL file
                $sql = file_get_contents($sql_file);
                
                if (!$sql) {
                    $error = "Error: Could not read SQL file.";
                    break;
                }
                
                // Connect to the database
                $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                $pdo = new PDO($dsn, $db_user, $db_pass, $options);
                
                // Execute the SQL
                $pdo->exec($sql);
                
                $message = "Database import completed successfully!";
                $success = true;
                header("Location: install.php?step=$next_step");
                exit;
            } catch (PDOException $e) {
                $error = "Database Error: " . $e->getMessage();
            } catch (Exception $e) {
                $error = "General Error: " . $e->getMessage();
            }
            break;
            
        case 'configure_env':
            $app_name = $_POST['app_name'] ?? 'Botble CMS';
            $app_url = $_POST['app_url'] ?? 'https://onestop-store.stackstaging.com';
            $admin_dir = $_POST['admin_dir'] ?? 'admin';
            
            $env_file = __DIR__ . '/.env';
            
            if (!is_file($env_file) && is_file(__DIR__ . '/.env.example')) {
                copy(__DIR__ . '/.env.example', $env_file);
            }
            
            if (!is_file($env_file)) {
                $error = "Could not find .env file or .env.example";
                break;
            }
            
            $env_content = file_get_contents($env_file);
            
            // Update env values
            $env_content = preg_replace('/APP_NAME=.*/', "APP_NAME=\"$app_name\"", $env_content);
            $env_content = preg_replace('/APP_URL=.*/', "APP_URL=$app_url", $env_content);
            $env_content = preg_replace('/ADMIN_DIR=.*/', "ADMIN_DIR=$admin_dir", $env_content);
            $env_content = preg_replace('/APP_DEBUG=.*/', "APP_DEBUG=false", $env_content);
            $env_content = preg_replace('/APP_ENV=.*/', "APP_ENV=production", $env_content);
            
            // Update database credentials
            $db_host = $_SESSION['db_host'] ?? '127.0.0.1';
            $db_name = $_SESSION['db_name'] ?? 'username_onestop';
            $db_user = $_SESSION['db_user'] ?? 'username_onestop';
            $db_pass = $_SESSION['db_pass'] ?? '0|8|5.>w(A9e';
            
            $env_content = preg_replace('/DB_HOST=.*/', "DB_HOST=$db_host", $env_content);
            $env_content = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE=$db_name", $env_content);
            $env_content = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME=$db_user", $env_content);
            $env_content = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=$db_pass", $env_content);
            
            file_put_contents($env_file, $env_content);
            
            $message = "Environment configuration updated successfully!";
            $success = true;
            header("Location: install.php?step=$next_step");
            exit;
            break;
            
        case 'set_permissions':
            $directories = [
                'storage' => 0755,
                'storage/app' => 0755,
                'storage/app/public' => 0755,
                'storage/framework' => 0755,
                'storage/framework/cache' => 0755,
                'storage/framework/sessions' => 0755,
                'storage/framework/views' => 0755,
                'storage/logs' => 0755,
                'bootstrap/cache' => 0755,
            ];
            
            foreach ($directories as $dir => $permission) {
                if (is_dir($dir)) {
                    chmod($dir, $permission);
                } else {
                    @mkdir($dir, $permission, true);
                }
            }
            
            $message = "Directory permissions have been set!";
            $success = true;
            header("Location: install.php?step=$next_step");
            exit;
            break;
            
        case 'clean_up':
            // Create a symbolic link from public/storage to storage/app/public
            if (!is_dir('public/storage') && is_dir('storage/app/public')) {
                if (function_exists('symlink')) {
                    @symlink(__DIR__ . '/storage/app/public', __DIR__ . '/public/storage');
                }
            }
            
            $message = "Installation completed successfully!";
            $success = true;
            // Don't redirect, this is the final step
            break;
    }
}

// Functions to check server requirements
function check_php_version($required = '7.3.0') {
    return version_compare(PHP_VERSION, $required, '>=');
}

function check_extension($name) {
    return extension_loaded($name);
}

function is_writable_recursive($directory) {
    if (!is_dir($directory)) {
        return false;
    }
    
    if (!is_writable($directory)) {
        return false;
    }
    
    return true;
}

function display_check_result($condition, $text) {
    if ($condition) {
        return "<div class='check-item check-pass'><span class='icon'>✅</span> $text</div>";
    } else {
        return "<div class='check-item check-fail'><span class='icon'>❌</span> $text</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Application Installer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4a90e2;
            margin-top: 0;
        }
        .step-list {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .step-item {
            padding: 10px 15px;
            margin-right: 5px;
            border-radius: 5px;
            cursor: pointer;
        }
        .step-active {
            background-color: #4a90e2;
            color: #fff;
        }
        .step-complete {
            background-color: #5cb85c;
            color: #fff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 15px;
            background-color: #4a90e2;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #3a80d2;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .check-item {
            padding: 5px 0;
        }
        .check-pass {
            color: #3c763d;
        }
        .check-fail {
            color: #a94442;
        }
        .icon {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel Application Installer</h1>
        
        <!-- Step Navigation -->
        <div class="step-list">
            <?php foreach ($steps as $key => $title): ?>
                <div class="step-item <?php 
                    if ($key === $current_step) echo 'step-active';
                    elseif (array_search($key, array_keys($steps)) < array_search($current_step, array_keys($steps))) 
                        echo 'step-complete';
                ?>">
                    <?php echo $title; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Step Content -->
        <form method="post">
            <?php switch ($current_step): ?>
                <?php case 'check_requirements': ?>
                    <h2>Server Requirements</h2>
                    <div>
                        <?php echo display_check_result(check_php_version('7.3.0'), 'PHP Version >= 7.3.0 (' . PHP_VERSION . ')'); ?>
                        <?php echo display_check_result(check_extension('PDO'), 'PDO Extension'); ?>
                        <?php echo display_check_result(check_extension('mysqli'), 'MySQLi Extension'); ?>
                        <?php echo display_check_result(check_extension('openssl'), 'OpenSSL Extension'); ?>
                        <?php echo display_check_result(check_extension('mbstring'), 'Mbstring Extension'); ?>
                        <?php echo display_check_result(check_extension('tokenizer'), 'Tokenizer Extension'); ?>
                        <?php echo display_check_result(check_extension('JSON'), 'JSON Extension'); ?>
                        <?php echo display_check_result(check_extension('cURL'), 'cURL Extension'); ?>
                        <?php echo display_check_result(check_extension('fileinfo'), 'Fileinfo Extension'); ?>
                        <?php echo display_check_result(check_extension('zip'), 'ZIP Extension'); ?>
                        <?php echo display_check_result(is_writable_recursive(__DIR__ . '/storage'), 'Storage Directory Writable'); ?>
                        <?php echo display_check_result(is_writable_recursive(__DIR__ . '/bootstrap/cache'), 'Bootstrap/Cache Directory Writable'); ?>
                    </div>
                    <button type="submit" class="btn">Continue</button>
                    <?php break; ?>
                
                <?php case 'create_database': ?>
                    <h2>Database Configuration</h2>
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" name="db_host" id="db_host" value="127.0.0.1" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" name="db_name" id="db_name" value="username_onestop" required>
                    </div>
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" name="db_user" id="db_user" value="username_onestop" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" name="db_pass" id="db_pass" value="0|8|5.>w(A9e" required>
                    </div>
                    <button type="submit" class="btn">Create Database</button>
                    <?php break; ?>
                
                <?php case 'import_database': ?>
                    <h2>Import Database</h2>
                    <div class="form-group">
                        <label for="sql_file">SQL File Path</label>
                        <input type="text" name="sql_file" id="sql_file" value="<?php echo __DIR__ . '/database.sql'; ?>" required>
                    </div>
                    <button type="submit" class="btn">Import Database</button>
                    <?php break; ?>
                
                <?php case 'configure_env': ?>
                    <h2>Configure Environment</h2>
                    <div class="form-group">
                        <label for="app_name">Application Name</label>
                        <input type="text" name="app_name" id="app_name" value="Botble CMS" required>
                    </div>
                    <div class="form-group">
                        <label for="app_url">Application URL</label>
                        <input type="text" name="app_url" id="app_url" value="https://onestop-store.stackstaging.com" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_dir">Admin Directory</label>
                        <input type="text" name="admin_dir" id="admin_dir" value="admin" required>
                    </div>
                    <button type="submit" class="btn">Configure Environment</button>
                    <?php break; ?>
                
                <?php case 'set_permissions': ?>
                    <h2>Directory Permissions</h2>
                    <p>We'll set the correct permissions for the following directories:</p>
                    <ul>
                        <li>storage/ (755)</li>
                        <li>storage/app/ (755)</li>
                        <li>storage/app/public/ (755)</li>
                        <li>storage/framework/ (755)</li>
                        <li>storage/framework/cache/ (755)</li>
                        <li>storage/framework/sessions/ (755)</li>
                        <li>storage/framework/views/ (755)</li>
                        <li>storage/logs/ (755)</li>
                        <li>bootstrap/cache/ (755)</li>
                    </ul>
                    <button type="submit" class="btn">Set Permissions</button>
                    <?php break; ?>
                
                <?php case 'clean_up': ?>
                    <h2>Installation Complete!</h2>
                    <p>Your Laravel application has been successfully installed.</p>
                    <p>You can now access your website at: <a href="https://onestop-store.stackstaging.com" target="_blank">https://onestop-store.stackstaging.com</a></p>
                    <p>Admin dashboard: <a href="https://onestop-store.stackstaging.com/admin" target="_blank">https://onestop-store.stackstaging.com/admin</a></p>
                    <p>Admin credentials:</p>
                    <ul>
                        <li>Username: <strong>botble</strong></li>
                        <li>Password: <strong>159357</strong></li>
                    </ul>
                    <p>For security reasons, please delete this installer file after completing the installation.</p>
                    <button type="submit" class="btn">Finish Installation</button>
                    <?php break; ?>
                
                <?php default: ?>
                    <h2>Unknown Step</h2>
                    <p>Please go back to the beginning of the installation.</p>
                    <a href="install.php" class="btn">Start Over</a>
            <?php endswitch; ?>
        </form>
    </div>
</body>
</html> 