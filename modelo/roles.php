<?php
require_once __DIR__ . "/../config/conexion.php";

class Roles {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_rol($nombre_rol) {
        $sql = "INSERT INTO roles (nombre_rol, estado) VALUES (:nombre_rol, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_rol', $nombre_rol);
        return $stmt->execute();
    }

    public function actualizar_rol($id, $nombre_rol) {
        $sql = "UPDATE roles SET nombre_rol = :nombre_rol WHERE id_rol = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_rol', $nombre_rol);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_rol($id) {
        $sql = "UPDATE roles SET estado = 0 WHERE id_rol = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_roles() {
        $sql = "SELECT * FROM roles WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_rol($id) {
        $sql = "SELECT * FROM roles WHERE id_rol = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
