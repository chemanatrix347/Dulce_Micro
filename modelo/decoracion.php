<?php
require_once __DIR__ . "/../config/conexion.php";

class Decoracion {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_decoracion($precio) {
        $sql = "INSERT INTO decoracion (precio, estado) VALUES (:precio, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':precio', $precio);
        return $stmt->execute();
    }

    public function actualizar_decoracion($id, $precio) {
        $sql = "UPDATE decoracion SET precio = :precio WHERE id_decoracion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_decoracion($id) {
        $sql = "UPDATE decoracion SET estado = 0 WHERE id_decoracion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_decoraciones() {
        $sql = "SELECT * FROM decoracion WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_decoracion($id) {
        $sql = "SELECT * FROM decoracion WHERE id_decoracion = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
