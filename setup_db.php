<?php
// Set unlimited execution time to prevent timeout
set_time_limit(0);
ini_set('memory_limit', '512M');

// Database connection details
$db_host = "127.0.0.1";
$db_name = "username_onestop";
$db_user = "username_onestop";
$db_pass = "0|8|5.>w(A9e";

// Path to your SQL file
$sql_file = __DIR__ . '/database.sql';

// Function to execute SQL commands
function execute_sql($sql_file, $db_host, $db_name, $db_user, $db_pass) {
    echo "Importing database from SQL file: $sql_file\n";
    
    try {
        // Read the SQL file
        $sql = file_get_contents($sql_file);
        
        if (!$sql) {
            return "Error: Could not read SQL file.";
        }
        
        // Connect to the database
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        echo "Connecting to database...\n";
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        
        // Execute the SQL
        echo "Executing SQL statements...\n";
        $pdo->exec($sql);
        
        echo "Database import completed successfully!\n";
        return true;
    } catch (PDOException $e) {
        return "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        return "General Error: " . $e->getMessage();
    }
}

// Check if database already exists, if not create it
function create_database_if_not_exists($db_host, $db_name, $db_user, $db_pass) {
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
            echo "Database '$db_name' does not exist. Creating it...\n";
            $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Database created successfully!\n";
        } else {
            echo "Database '$db_name' already exists.\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// First, try to create the database if it doesn't exist
if (create_database_if_not_exists($db_host, $db_name, $db_user, $db_pass)) {
    // Then import the SQL file
    $result = execute_sql($sql_file, $db_host, $db_name, $db_user, $db_pass);
    if ($result !== true) {
        echo $result . "\n";
    }
}

// Update .env file with correct database credentials
echo "Updating .env file with database credentials...\n";

// Read existing .env file
$env_file = __DIR__ . '/.env';
$env_content = file_get_contents($env_file);

// Update database credentials
$env_content = preg_replace('/DB_HOST=.*/', "DB_HOST=$db_host", $env_content);
$env_content = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE=$db_name", $env_content);
$env_content = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME=$db_user", $env_content);
$env_content = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=$db_pass", $env_content);
$env_content = preg_replace('/APP_URL=.*/', "APP_URL=https://onestop-store.stackstaging.com", $env_content);
$env_content = preg_replace('/APP_DEBUG=.*/', "APP_DEBUG=false", $env_content);
$env_content = preg_replace('/APP_ENV=.*/', "APP_ENV=production", $env_content);

// Write the updated .env file
file_put_contents($env_file, $env_content);
echo "Updated .env file with database credentials.\n";

echo "Database setup completed!\n";
?> 