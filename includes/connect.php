<?php
// Database Configuration using PDO (consistent with rest of code)
class Database {
    private $host = 'localhost';
    private $db_name = 'car_marketplace';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
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