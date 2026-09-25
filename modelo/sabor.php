<?php
require_once __DIR__ . "/../config/conexion.php";

class Sabor {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_sabor($sabor) {
        $sql = "INSERT INTO sabor (sabor, estado) VALUES (:sabor, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':sabor', $sabor);
        return $stmt->execute();
    }

    public function actualizar_sabor($id, $sabor) {
        $sql = "UPDATE sabor SET sabor = :sabor WHERE id_sabor = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':sabor', $sabor);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_sabor($id) {
        $sql = "UPDATE sabor SET estado = 0 WHERE id_sabor = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_sabores() {
        $sql = "SELECT * FROM sabor WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_sabor($id) {
        $sql = "SELECT * FROM sabor WHERE id_sabor = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
