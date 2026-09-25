<?php
require_once __DIR__ . "/../config/conexion.php";

class Pago {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_pago($id_pedido, $id_metodo_pago, $monto, $id_estado_pago, $fecha_pago) {
        $sql = "INSERT INTO pagos (id_pedido, id_metodo_pago, monto, id_estado_pago, fecha_pago, estado) VALUES (:id_pedido, :id_metodo_pago, :monto, :id_estado_pago, :fecha_pago, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_pedido', $id_pedido);
        $stmt->bindParam(':id_metodo_pago', $id_metodo_pago);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':id_estado_pago', $id_estado_pago);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        return $stmt->execute();
    }

    public function actualizar_pago($id, $id_pedido, $id_metodo_pago, $monto, $id_estado_pago, $fecha_pago) {
        $sql = "UPDATE pagos SET id_pedido = :id_pedido, id_metodo_pago = :id_metodo_pago, monto = :monto, id_estado_pago = :id_estado_pago, fecha_pago = :fecha_pago WHERE id_pagos = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_pedido', $id_pedido);
        $stmt->bindParam(':id_metodo_pago', $id_metodo_pago);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':id_estado_pago', $id_estado_pago);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_pago($id) {
        $sql = "UPDATE pagos SET estado = 0 WHERE id_pagos = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_pagos() {
        $sql = "SELECT * FROM pagos WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_pago($id) {
        $sql = "SELECT * FROM pagos WHERE id_pagos = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
