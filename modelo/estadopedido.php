<?php
require_once __DIR__ . "/../config/conexion.php";

class EstadoPedido {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_estado_pedido($estado_pedido) {
        $sql = "INSERT INTO estado_pedido (estado_pedido, estado) VALUES (:estado_pedido, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado_pedido', $estado_pedido);
        return $stmt->execute();
    }

    public function actualizar_estado_pedido($id, $estado_pedido) {
        $sql = "UPDATE estado_pedido SET estado_pedido = :estado_pedido WHERE id_estado_pedido = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado_pedido', $estado_pedido);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_estado_pedido($id) {
        $sql = "UPDATE estado_pedido SET estado = 0 WHERE id_estado_pedido = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_estados_pedido() {
        $sql = "SELECT * FROM estado_pedido WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_estado_pedido($id) {
        $sql = "SELECT * FROM estado_pedido WHERE id_estado_pedido = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
