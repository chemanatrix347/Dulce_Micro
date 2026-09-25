<?php
require_once __DIR__ . "/../config/conexion.php";

class CategoriaPostre {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_categoria_postre($categoria_postre) {
        $sql = "INSERT INTO categoria_postre (categoria_postre, estado) VALUES (:categoria_postre, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categoria_postre', $categoria_postre);
        return $stmt->execute();
    }

    public function actualizar_categoria_postre($id, $categoria_postre) {
        $sql = "UPDATE categoria_postre SET categoria_postre = :categoria_postre WHERE id_categoria_postre = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categoria_postre', $categoria_postre);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_categoria_postre($id) {
        $sql = "UPDATE categoria_postre SET estado = 0 WHERE id_categoria_postre = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_categorias_postre() {
        $sql = "SELECT * FROM categoria_postre WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_categoria_postre($id) {
        $sql = "SELECT * FROM categoria_postre WHERE id_categoria_postre = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
