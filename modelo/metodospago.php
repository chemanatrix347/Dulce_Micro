<?php
require_once __DIR__ . "/../config/conexion.php";

class MetodoPago {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_metodo_pago($metodo_pago) {
        $sql = "INSERT INTO metodos_pago (metodo_pago, estado) VALUES (:metodo_pago, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':metodo_pago', $metodo_pago);
        return $stmt->execute();
    }

    public function actualizar_metodo_pago($id, $metodo_pago) {
        $sql = "UPDATE metodos_pago SET metodo_pago = :metodo_pago WHERE id_metodos_pago = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':metodo_pago', $metodo_pago);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_metodo_pago($id) {
        $sql = "UPDATE metodos_pago SET estado = 0 WHERE id_metodos_pago = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_metodos_pago() {
        $sql = "SELECT * FROM metodos_pago WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_metodo_pago($id) {
        $sql = "SELECT * FROM metodos_pago WHERE id_metodos_pago = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
