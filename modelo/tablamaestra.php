<?php
require_once __DIR__ . "/../config/conexion.php";

class TablaMaestra {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_maestro($fecha_transaccion, $id_cliente, $id_materia_prima, $id_tortas, $id_postre_individual, $cantidad_pedida, $id_pago, $id_contabilidad, $id_estado_pedido) {
        $sql = "INSERT INTO tabla_maestra (fecha_transaccion, id_cliente, id_materia_prima, id_tortas, id_postre_individual, cantidad_pedida, id_pago, id_contabilidad, id_estado_pedido, estado) VALUES (:fecha_transaccion, :id_cliente, :id_materia_prima, :id_tortas, :id_postre_individual, :cantidad_pedida, :id_pago, :id_contabilidad, :id_estado_pedido, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fecha_transaccion', $fecha_transaccion);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_materia_prima', $id_materia_prima);
        $stmt->bindParam(':id_tortas', $id_tortas);
        $stmt->bindParam(':id_postre_individual', $id_postre_individual);
        $stmt->bindParam(':cantidad_pedida', $cantidad_pedida);
        $stmt->bindParam(':id_pago', $id_pago);
        $stmt->bindParam(':id_contabilidad', $id_contabilidad);
        $stmt->bindParam(':id_estado_pedido', $id_estado_pedido);
        return $stmt->execute();
    }

    public function actualizar_maestro($id, $fecha_transaccion, $id_cliente, $id_materia_prima, $id_tortas, $id_postre_individual, $cantidad_pedida, $id_pago, $id_contabilidad, $id_estado_pedido) {
        $sql = "UPDATE tabla_maestra SET fecha_transaccion = :fecha_transaccion, id_cliente = :id_cliente, id_materia_prima = :id_materia_prima, id_tortas = :id_tortas, id_postre_individual = :id_postre_individual, cantidad_pedida = :cantidad_pedida, id_pago = :id_pago, id_contabilidad = :id_contabilidad, id_estado_pedido = :id_estado_pedido WHERE id_maestro = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fecha_transaccion', $fecha_transaccion);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_materia_prima', $id_materia_prima);
        $stmt->bindParam(':id_tortas', $id_tortas);
        $stmt->bindParam(':id_postre_individual', $id_postre_individual);
        $stmt->bindParam(':cantidad_pedida', $cantidad_pedida);
        $stmt->bindParam(':id_pago', $id_pago);
        $stmt->bindParam(':id_contabilidad', $id_contabilidad);
        $stmt->bindParam(':id_estado_pedido', $id_estado_pedido);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_maestro($id) {
        $sql = "UPDATE tabla_maestra SET estado = 0 WHERE id_maestro = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_maestros() {
        $sql = "SELECT * FROM tabla_maestra WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_maestro($id) {
        $sql = "SELECT * FROM tabla_maestra WHERE id_maestro = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
