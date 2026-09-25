<?php
require_once __DIR__ . "/../config/conexion.php";

class Cliente {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_cliente($nombre, $id_tipo_documento, $numero_documento, $correo, $telefono) {
        $sql = "INSERT INTO clientes (nombre, id_tipo_documento, numero_documento, correo, telefono, estado) VALUES (:nombre, :id_tipo_documento, :numero_documento, :correo, :telefono, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_tipo_documento', $id_tipo_documento);
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);
        return $stmt->execute();
    }

    public function actualizar_cliente($id, $nombre, $id_tipo_documento, $numero_documento, $correo, $telefono) {
        $sql = "UPDATE clientes SET nombre = :nombre, id_tipo_documento = :id_tipo_documento, numero_documento = :numero_documento, correo = :correo, telefono = :telefono WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_tipo_documento', $id_tipo_documento);
        $stmt->bindParam(':numero_documento', $numero_documento);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Soft delete: marca el registro como inactivo en vez de borrarlo fisicamente
    public function eliminar_cliente($id) {
        $sql = "UPDATE clientes SET estado = 0 WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function leer_clientes() {
        $sql = "SELECT * FROM clientes WHERE estado = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_cliente($id) {
        $sql = "SELECT * FROM clientes WHERE id_cliente = :id AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

        // NUEVO: busca un cliente por su número de documento, para no crear duplicados
    public function buscar_por_documento($numero_documento) {
        $sql = "SELECT * FROM clientes WHERE numero_documento = :doc AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':doc', $numero_documento);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
