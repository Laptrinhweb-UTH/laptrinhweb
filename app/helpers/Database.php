<?php
// File: app/helpers/Database.php
// CHUYÊN chứa công cụ kết nối PDO

require_once __DIR__ . '/../../config/config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Đoạn kết nối PDO y hệt code cũ của bạn
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            die("Kết nối Database thất bại: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>