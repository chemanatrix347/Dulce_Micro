<?php
require_once __DIR__ . "/../config/conexion.php";

class GastoDecoracion {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_gasto_decoracion($id_decoracion, $numero_diseno, $nombre_diseno, $fondant_inicial, $gasto_fondant, $colorante_inicial, $gasto_colorante) {
        $sql = "INSERT INTO gasto_decoracion (id_decoracion, numero_diseno, nombre_diseno, fondant_inicial, gasto_fondant, colorante_inicial, gasto_colorante, estado) VALUES (:id_decoracion, :numero_diseno, :nombre_diseno, :fondant_inicial, :gasto_fondant, :colorante_inicial, :gasto_colorante, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_decoracion', $id_decoracion);
        $stmt->bindParam(':numero_diseno', $numero_diseno);
        $stmt->bindParam(':nombre_diseno', $nombre_diseno);
        $stmt->bindParam(':fondant_inicial', $fondant_inicial);
        $stmt->bindParam(':gasto_fondant', $gasto_fondant);
        $stmt->bindParam(':colorante_inicial', $colorante_inicial);
        $stmt->bindParam(':gasto_colorante', $gasto_colorante);
        return $stmt->execute();
    }

    public function actualizar_gasto_decoracion($id, $id_decoracion, $numero_diseno, $nombre_diseno, $fondant_inicial, $gasto_fondant, $colorante_inicial, $gasto_colorante) {
        $sql = "UPDATE gasto_decoracion SET id_decoracion = :id_decoracion, numero_diseno = :numero_diseno, nombre_diseno = :nombre_diseno, fondant_inicial = :fondant_inicial, gasto_fondant = :gasto_fondant, colorante_inicial = :colorante_inicial, gasto_colorante = :gasto_colorante WHERE id_gasto_decoracion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_decoracion', $id_decoracion);
        $stmt->bindParam(':numero_diseno', $numero_diseno);
        $stmt->bindParam(':nombre_diseno', $nombre_diseno);
        $stmt->bindParam(':fondant_inicial', $fondant_inicial);
        $stmt->bindParam(':gasto_fondant', $gasto_fondant);
        $stmt->bindParam(':colorante_inicial', $colorante_inicial);
        $stmt->bindParam(':gasto_colorante', $gasto_colorante);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_gasto_decoracion($id) {
        $sql = "UPDATE gasto_decoracion SET estado = 0 WHERE id_gasto_decoracion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_gastos_decoracion() {
        $sql = "SELECT * FROM gasto_decoracion WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_gasto_decoracion($id) {
        $sql = "SELECT * FROM gasto_decoracion WHERE id_gasto_decoracion = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
