<?php
//config/conexion.php
class conexion {
    private $host = "localhost";
    private $dbname = "login_db";
    private $user = "root";
    private $password = "";
    public $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4", $this->user,$this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error en la conexion: " . $e->getMessage());
        }
    }
}
?>