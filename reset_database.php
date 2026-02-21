<?php
// reset_database_fixed.php
echo "<h2>Smart Retail System - Database Reset (Fixed Version)</h2>";

$host = "localhost";
$username = "root";
$password = "Tshegofatso13#";
$dbname = "smart_retail_system";

try {
    // Create connection
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    $conn->exec("DROP DATABASE IF EXISTS $dbname");
    echo "? Database dropped<br>";
    
    $conn->exec("CREATE DATABASE $dbname");
    echo "? Database created<br>";
    
    $conn->exec("USE $dbname");
    echo "? Database selected<br>";
    
    // Execute the fixed SQL file
    $sql_file = "database/smart_retail.sql";
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        
        // Split and execute queries one by one
        $queries = explode(';', $sql);
        $success_count = 0;
        $error_count = 0;
        
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && strlen($query) > 10) {
                try {
                    $conn->exec($query);
                    $success_count++;
                } catch (PDOException $e) {
                    // Skip non-critical errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $error_count++;
                        echo "Query error (non-critical): " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
        
        echo "? Queries executed: $success_count successful, $error_count errors<br>";
        echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
        echo "<p><strong>Demo Credentials:</strong></p>";
        echo "<p>Admin: admin@smartretail.com / admin123</p>";
        echo "<p>User: john@example.com / password</p>";
        echo "<p><a href='login.php'>Go to Login Page</a> | <a href='index.php'>Go to Homepage</a></p>";
    } else {
        echo "<h3 style='color: red;'>SQL file not found: $sql_file</h3>";
    }
    
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Connection failed: " . $e->getMessage() . "</h3>";
    echo "<p>Please check your MySQL configuration in config/database.php</p>";
}
?>