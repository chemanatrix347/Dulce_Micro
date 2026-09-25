<?php
require_once __DIR__ . "/../config/conexion.php";

class PostreIndividual {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_postre_individual($nombre_producto, $descripcion) {
        $sql = "INSERT INTO postres_individuales (nombre_producto, descripcion, estado) VALUES (:nombre_producto, :descripcion, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    public function actualizar_postre_individual($id, $nombre_producto, $descripcion) {
        $sql = "UPDATE postres_individuales SET nombre_producto = :nombre_producto, descripcion = :descripcion WHERE id_postre_individual = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_postre_individual($id) {
        $sql = "UPDATE postres_individuales SET estado = 0 WHERE id_postre_individual = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_postres_individuales() {
        $sql = "SELECT * FROM postres_individuales WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_postre_individual($id) {
        $sql = "SELECT * FROM postres_individuales WHERE id_postre_individual = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
