<?php
require_once __DIR__ . "/../config/conexion.php";

class Usuario {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    public function crear_usuario($nombre_completo, $correo_electronico, $id_roles, $contrasena_hash, $fecha_registro, $estado) {
        $sql = "INSERT INTO usuarios 
                (nombre_completo, correo_electronico, id_roles, contrasena_hash, fecha_registro, estado) 
                VALUES 
                (:nombre_completo, :correo_electronico, :id_roles, :contrasena_hash, :fecha_registro, :estado)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':correo_electronico', $correo_electronico);
        $stmt->bindParam(':id_roles', $id_roles);
        $stmt->bindParam(':contrasena_hash', $contrasena_hash);
        $stmt->bindParam(':fecha_registro', $fecha_registro);
        $stmt->bindParam(':estado', $estado);

        return $stmt->execute();
    }

    public function actualizar_usuario($id, $nombre_completo, $correo_electronico, $id_roles, $contrasena_hash, $fecha_registro, $estado) {
        $sql = "UPDATE usuarios 
                SET nombre_completo = :nombre_completo,
                    correo_electronico = :correo_electronico,
                    id_roles = :id_roles,
                    contrasena_hash = :contrasena_hash,
                    fecha_registro = :fecha_registro,
                    estado = :estado 
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre_completo', $nombre_completo);
        $stmt->bindParam(':correo_electronico', $correo_electronico);
        $stmt->bindParam(':id_roles', $id_roles);
        $stmt->bindParam(':contrasena_hash', $contrasena_hash);
        $stmt->bindParam(':fecha_registro', $fecha_registro);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function eliminar_usuario($id) {
        $sql = "UPDATE usuarios 
                SET estado = 'INACTIVO' 
                WHERE id_usuario = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function leer_usuarios() {
        $sql = "SELECT 
                    u.id_usuario,
                    u.nombre_completo,
                    u.correo_electronico,
                    u.id_roles,
                    u.contrasena_hash,
                    u.fecha_registro,
                    u.estado,
                    r.nombre_rol
                FROM usuarios u
                LEFT JOIN roles r 
                    ON r.id_rol = u.id_roles
                WHERE u.estado != 'INACTIVO'";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar_usuario($id) {
        $sql = "SELECT * 
                FROM usuarios 
                WHERE id_usuario = :id 
                AND estado != 'INACTIVO'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscar_por_correo($correo) {
        $sql = "SELECT * 
                FROM usuarios 
                WHERE correo_electronico = :correo 
                AND estado != 'INACTIVO'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Leer los roles activos para el desplegable de usuarios
    public function leer_roles()
    {
        $sql = "SELECT id_rol, nombre_rol
                FROM roles
                WHERE estado = 1
                ORDER BY nombre_rol ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>