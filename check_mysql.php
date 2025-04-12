<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$password = '';

echo "Checking MySQL connection status...\n";

$connection = @mysqli_connect($host, $user, $password, '', $port);

if ($connection) {
    echo "SUCCESS: MySQL connection is working!\n";
    mysqli_close($connection);
} else {
    echo "ERROR: Could not connect to MySQL. Error: " . mysqli_connect_error() . "\n";
}
?> 