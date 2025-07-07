<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class connect_db
{
    private $conn;
    private $host = "localhost";
    private $user = "hceeab2b55_chung9atm";
    private $pass = "Chung2002!";
    private $db = "hceeab2b55_restaurant";

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
        $this->conn->query("SET NAMES 'utf8mb4'");
        $this->conn->query("SET CHARACTER SET utf8mb4");
        $this->conn->query("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
    }

    public function beginTransaction()
    {
        $this->conn->begin_transaction();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function rollback()
    {
        $this->conn->rollback();
    }

    private function connect()
    {
        $this->conn = new mysqli("localhost", "hceeab2b55_chung9atm", "Chung2002!", "hceeab2b55_restaurant");
        if ($this->conn->connect_errno) {
            throw new Exception("Kết nối database thất bại: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
        $this->conn->query("SET NAMES 'utf8mb4'");
        $this->conn->query("SET CHARACTER SET utf8mb4");
        $this->conn->query("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
        return $this->conn;
    }
    
    public function xuatdulieu($sql)
    {
        $arr = [];
        $link = $this->connect();
        $result = $link->query($sql);
        if ($result === false) {
            throw new Exception("Lỗi query: " . $link->error);
        }
        if ($result->num_rows) {
            while ($row = $result->fetch_assoc()) {
                $arr[] = $row;
            }
        }
        return $arr;
    }
    
    public function xuatdulieu_prepared($sql, $params = [])
    {
        $arr = [];
        $conn = $this->connect();
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Lỗi prepare: " . $conn->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Lỗi execute: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $arr[] = $row;
            }
        }
        $stmt->close();
        return $arr;
    }

    public function tuychinh($sql, $params = [])
    {
        $conn = $this->connect();
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Lỗi prepare: " . $conn->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Lỗi execute: " . $stmt->error);
        }
        $stmt->close();
        return 1;
    }

    public function getLastInsertId()
    {
        // Make sure we have a valid connection
        if (!$this->conn || $this->conn->connect_error) {
            $this->connect();
        }
        
        // Get the last insert ID
        $lastId = $this->conn->insert_id;
        
        // Log for debugging
        error_log("Last Insert ID: " . $lastId);
        
        return $lastId;
    }
}
?>