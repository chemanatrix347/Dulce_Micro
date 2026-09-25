<?php
require_once __DIR__ . "/../config/conexion.php";

class EstadoPago {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_estado_pago($estado_pago) {
        $sql = "INSERT INTO estado_pagos (estado_pago, estado) VALUES (:estado_pago, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado_pago', $estado_pago);
        return $stmt->execute();
    }

    public function actualizar_estado_pago($id, $estado_pago) {
        $sql = "UPDATE estado_pagos SET estado_pago = :estado_pago WHERE id_estado_pago = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado_pago', $estado_pago);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_estado_pago($id) {
        $sql = "UPDATE estado_pagos SET estado = 0 WHERE id_estado_pago = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_estados_pago() {
        $sql = "SELECT * FROM estado_pagos WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_estado_pago($id) {
        $sql = "SELECT * FROM estado_pagos WHERE id_estado_pago = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
