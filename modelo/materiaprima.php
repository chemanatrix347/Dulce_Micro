<?php
require_once __DIR__ . "/../config/conexion.php";

class MateriaPrima {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_materia_prima($nombre_insumo, $descripcion, $stock_disponible, $id_unidad_medida) {
        $sql = "INSERT INTO materia_prima (nombre_insumo, descripcion, stock_disponible, id_unidad_medida, estado) VALUES (:nombre_insumo, :descripcion, :stock_disponible, :id_unidad_medida, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_insumo', $nombre_insumo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':stock_disponible', $stock_disponible);
        $stmt->bindParam(':id_unidad_medida', $id_unidad_medida);
        return $stmt->execute();
    }

    public function actualizar_materia_prima($id, $nombre_insumo, $descripcion, $stock_disponible, $id_unidad_medida) {
        $sql = "UPDATE materia_prima SET nombre_insumo = :nombre_insumo, descripcion = :descripcion, stock_disponible = :stock_disponible, id_unidad_medida = :id_unidad_medida WHERE id_materia_prima = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_insumo', $nombre_insumo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':stock_disponible', $stock_disponible);
        $stmt->bindParam(':id_unidad_medida', $id_unidad_medida);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_materia_prima($id) {
        $sql = "UPDATE materia_prima SET estado = 0 WHERE id_materia_prima = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_materias_primas() {
        $sql = "SELECT * FROM materia_prima WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_materia_prima($id) {
        $sql = "SELECT * FROM materia_prima WHERE id_materia_prima = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
