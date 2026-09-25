<?php
require_once __DIR__ . "/../config/conexion.php";

class Contabilidad {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_movimiento($tipo_movimiento, $monto_transaccion, $descripcion_registro, $fecha_registro) {
        $sql = "INSERT INTO contabilidad (tipo_movimiento, monto_transaccion, descripcion_registro, fecha_registro, estado) VALUES (:tipo_movimiento, :monto_transaccion, :descripcion_registro, :fecha_registro, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tipo_movimiento', $tipo_movimiento);
        $stmt->bindParam(':monto_transaccion', $monto_transaccion);
        $stmt->bindParam(':descripcion_registro', $descripcion_registro);
        $stmt->bindParam(':fecha_registro', $fecha_registro);
        return $stmt->execute();
    }

    public function actualizar_movimiento($id, $tipo_movimiento, $monto_transaccion, $descripcion_registro, $fecha_registro) {
        $sql = "UPDATE contabilidad SET tipo_movimiento = :tipo_movimiento, monto_transaccion = :monto_transaccion, descripcion_registro = :descripcion_registro, fecha_registro = :fecha_registro WHERE id_contabilidad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tipo_movimiento', $tipo_movimiento);
        $stmt->bindParam(':monto_transaccion', $monto_transaccion);
        $stmt->bindParam(':descripcion_registro', $descripcion_registro);
        $stmt->bindParam(':fecha_registro', $fecha_registro);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_movimiento($id) {
        $sql = "UPDATE contabilidad SET estado = 0 WHERE id_contabilidad = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_movimientos() {
        $sql = "SELECT * FROM contabilidad WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_movimiento($id) {
        $sql = "SELECT * FROM contabilidad WHERE id_contabilidad = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
