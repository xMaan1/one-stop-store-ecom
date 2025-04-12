<?php
// FTP connection details
$ftp_server = "onestop.store";
$ftp_username = "username";
$ftp_password = "0|8|5.>w(A9e";

// Connect to FTP server
$conn_id = ftp_connect($ftp_server);

// Check connection
if ($conn_id) {
    echo "Connected to FTP server successfully\n";
    
    // Try to login
    $login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
    
    if ($login_result) {
        echo "Logged in successfully\n";
        
        // Get current directory
        $current_dir = ftp_pwd($conn_id);
        echo "Current directory: " . $current_dir . "\n";
        
        // List files in the current directory
        $files = ftp_nlist($conn_id, ".");
        echo "Files in current directory: \n";
        print_r($files);
        
    } else {
        echo "Login failed\n";
    }
    
    // Close the connection
    ftp_close($conn_id);
} else {
    echo "Could not connect to FTP server\n";
}
?> 