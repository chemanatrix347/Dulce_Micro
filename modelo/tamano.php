<?php
require_once __DIR__ . "/../config/conexion.php";

class Tamano {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_tamano($tamano) {
        $sql = "INSERT INTO tamano (tamano, estado) VALUES (:tamano, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tamano', $tamano);
        return $stmt->execute();
    }

    public function actualizar_tamano($id, $tamano) {
        $sql = "UPDATE tamano SET tamano = :tamano WHERE id_tamano = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tamano', $tamano);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_tamano($id) {
        $sql = "UPDATE tamano SET estado = 0 WHERE id_tamano = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_tamanos() {
        $sql = "SELECT * FROM tamano WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_tamano($id) {
        $sql = "SELECT * FROM tamano WHERE id_tamano = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
