<?php
class DB {
    private $host = "localhost";
    private $dbname = "specanciensdb";
    private $dbuser = "root";
    private $dbpass = "";
    private $conn;

    public function __construct() {
        if (!$this->conn) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
                $this->conn = new PDO($dsn, $this->dbuser, $this->dbpass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("<h3>Database Connection Failed</h3>" . $e->getMessage());
            }
        }
    }

    // Used for queries with values to bind
    public function simplequery($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function execute($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    // Used for queries without values
    public function simplequerywithoutcondition($query) {
        $stmt = $this->conn->query($query);
        return $stmt;
    }

    // ✅ Add this method
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>
