<?php
require_once __DIR__ . "/../config/conexion.php";

class Repostera {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_repostera($nombre_repostera) {
        $sql = "INSERT INTO reposteras (nombre_repostera, estado) VALUES (:nombre_repostera, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_repostera', $nombre_repostera);
        return $stmt->execute();
    }

    public function actualizar_repostera($id, $nombre_repostera) {
        $sql = "UPDATE reposteras SET nombre_repostera = :nombre_repostera WHERE id_repostera_asignada = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_repostera', $nombre_repostera);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_repostera($id) {
        $sql = "UPDATE reposteras SET estado = 0 WHERE id_repostera_asignada = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_reposteras() {
        $sql = "SELECT * FROM reposteras WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_repostera($id) {
        $sql = "SELECT * FROM reposteras WHERE id_repostera_asignada = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
