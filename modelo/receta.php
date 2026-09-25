<?php

require_once __DIR__ . "/../config/conexion.php";

class Receta
{
    private $db;

    public function __construct()
    {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    // Crear una receta
    public function crear_receta($id_producto, $id_materia_prima, $cantidad)
    {
        $sql = "INSERT INTO recetas
                (id_producto, id_materia_prima, cantidad, estado)
                VALUES
                (:id_producto, :id_materia_prima, :cantidad, 1)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->bindValue(':id_materia_prima', $id_materia_prima, PDO::PARAM_INT);
        $stmt->bindValue(':cantidad', $cantidad);

        return $stmt->execute();
    }

    // Actualizar una receta
    public function actualizar_receta($id, $id_producto, $id_materia_prima, $cantidad)
    {
        $sql = "UPDATE recetas
                SET
                    id_producto = :id_producto,
                    id_materia_prima = :id_materia_prima,
                    cantidad = :cantidad
                WHERE id_receta = :id
                AND estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->bindValue(':id_materia_prima', $id_materia_prima, PDO::PARAM_INT);
        $stmt->bindValue(':cantidad', $cantidad);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Eliminar lógicamente una receta
    public function eliminar_receta($id)
    {
        $sql = "UPDATE recetas
                SET estado = 0
                WHERE id_receta = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Leer todas las recetas
    public function leer_recetas()
    {
        $sql = "SELECT
                    r.id_receta,
                    r.id_producto,
                    r.id_materia_prima,
                    r.cantidad,

                    p.nombre_producto,

                    mp.nombre_insumo,
                    mp.id_unidad_medida,

                    um.unidad_medida

                FROM recetas r

                INNER JOIN productos p
                    ON p.id_producto = r.id_producto

                INNER JOIN materia_prima mp
                    ON mp.id_materia_prima = r.id_materia_prima

                LEFT JOIN unidad_medida um
                    ON um.id_unidad_medida = mp.id_unidad_medida

                WHERE r.estado = 1

                ORDER BY r.id_receta ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar una receta por ID
    public function buscar_receta($id)
    {
        $sql = "SELECT
                    r.id_receta,
                    r.id_producto,
                    r.id_materia_prima,
                    r.cantidad
                FROM recetas r
                WHERE r.id_receta = :id
                AND r.estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>