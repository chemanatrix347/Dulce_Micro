<?php
require_once __DIR__ . "/../config/conexion.php";

class Tortas {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_torta($tipo_torta) {
        $sql = "INSERT INTO tortas (tipo_torta, estado) VALUES (:tipo_torta, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tipo_torta', $tipo_torta);
        return $stmt->execute();
    }

    public function actualizar_torta($id, $tipo_torta) {
        $sql = "UPDATE tortas SET tipo_torta = :tipo_torta WHERE id_tortas = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tipo_torta', $tipo_torta);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_torta($id) {
        $sql = "UPDATE tortas SET estado = 0 WHERE id_tortas = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_tortas() {
        $sql = "SELECT * FROM tortas WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_torta($id) {
        $sql = "SELECT * FROM tortas WHERE id_tortas = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
