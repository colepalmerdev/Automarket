<?php
// Database Configuration using PDO (consistent with rest of code)
class Database {
    private $host = 'localhost';
    private $port = '3307';
    private $db_name = 'car_marketplace';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->db_name . ';charset=utf8mb4';
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch(PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            $this->conn = null;
        }

        return $this->conn;
    }

    public function createTables() {
        $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
        try {
            $this->getConnection()->exec($sql);
            return true;
        } catch(PDOException $e) {
            echo 'Error creating tables: ' . $e->getMessage();
            return false;
        }
    }
}
?>