<?php
require_once __DIR__ . "/../config/conexion.php";

class UnidadMedida {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_unidad_medida($unidad_medida) {
        $sql = "INSERT INTO unidad_medida (unidad_medida, estado) VALUES (:unidad_medida, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':unidad_medida', $unidad_medida);
        return $stmt->execute();
    }

    public function actualizar_unidad_medida($id, $unidad_medida) {
        $sql = "UPDATE unidad_medida SET unidad_medida = :unidad_medida WHERE id_unidad_medida = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':unidad_medida', $unidad_medida);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_unidad_medida($id) {
        $sql = "UPDATE unidad_medida SET estado = 0 WHERE id_unidad_medida = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_unidades_medida() {
        $sql = "SELECT * FROM unidad_medida WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_unidad_medida($id) {
        $sql = "SELECT * FROM unidad_medida WHERE id_unidad_medida = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
