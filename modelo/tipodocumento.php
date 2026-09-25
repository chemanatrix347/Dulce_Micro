<?php
require_once __DIR__ . "/../config/conexion.php";

class TipoDocumento {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_tipo_documento($tipo_documento) {
        $sql = "INSERT INTO tipo_documento (tipo_documento, estado) VALUES (:tipo_documento, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tipo_documento', $tipo_documento);
        return $stmt->execute();
    }

    public function actualizar_tipo_documento($id, $tipo_documento) {
        $sql = "UPDATE tipo_documento SET tipo_documento = :tipo_documento WHERE id_tipo_documento = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tipo_documento', $tipo_documento);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_tipo_documento($id) {
        $sql = "UPDATE tipo_documento SET estado = 0 WHERE id_tipo_documento = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_tipos_documento() {
        $sql = "SELECT * FROM tipo_documento WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_tipo_documento($id) {
        $sql = "SELECT * FROM tipo_documento WHERE id_tipo_documento = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
