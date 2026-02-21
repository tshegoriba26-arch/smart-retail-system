<?php
class Database {
    private $host = "localhost";
    private $db_name = "smart_retail_system";
    private $username = "root";
    private $password = "Tshegofatso13#";
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            // First try to connect with database
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // If database doesn't exist, try to create it
            try {
                $this->conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
                $this->conn->exec("CREATE DATABASE IF NOT EXISTS `$this->db_name`");
                $this->conn->exec("USE `$this->db_name`");
                $this->conn->exec("set names utf8");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Log that database was created
                error_log("Database $this->db_name was created automatically");
            } catch(PDOException $create_exception) {
                echo "Database connection and creation failed: " . $create_exception->getMessage();
                return null;
            }
        }
        
        return $this->conn;
    }
}

// Global database connection with error handling
$database = new Database();
$db = $database->getConnection();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if essential tables exist, if not run setup
if ($db) {
    try {
        $result = $db->query("SELECT 1 FROM products LIMIT 1");
    } catch (PDOException $e) {
        // Tables don't exist, redirect to setup
        if (strpos($e->getMessage(), 'products') !== false && !isset($_GET['setup'])) {
            header('Location: setup_database.php');
            exit;
        }
    }
}
?>