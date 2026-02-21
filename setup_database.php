<?php
// setup_database.php
echo "<h2>Smart Retail System - Database Setup</h2>";

$host = "localhost";
$username = "root";
$password = "Tshegofatso13#";
$dbname = "smart_retail_system";

try {
    // Create connection without selecting database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);
    echo "Database created successfully<br>";
    
    // Select the database
    $conn->exec("USE $dbname");
    
    // Read and execute SQL file
    $sql_file = "database/smart_retail.sql";
    if (file_exists($sql_file)) {
        $queries = file_get_contents($sql_file);
        
        // Split by semicolon, but be careful with triggers and procedures
        $queries = explode(';', $queries);
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && strlen($query) > 10) {
                try {
                    $conn->exec($query);
                    echo "Query executed successfully<br>";
                } catch (PDOException $e) {
                    // Skip duplicate key errors and other non-critical errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "Error: " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
        echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
        echo "<p><a href='index.php'>Go to Homepage</a></p>";
    } else {
        echo "<h3 style='color: red;'>SQL file not found: $sql_file</h3>";
    }
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Connection failed: " . $e->getMessage() . "</h3>";
    echo "<p>Please check your MySQL configuration in config/database.php</p>";
}
?>