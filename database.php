<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "sdn_barambai";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Log error internally, do not expose to user
            error_log("Connection error: " . $exception->getMessage());
            die(json_encode(["success" => false, "message" => "Database connection failed."]));
        }
        return $this->conn;
    }
}
