<?php
require_once __DIR__ . "/../config/conexion.php";

class Producto {
    private $db;

    public function __construct() {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    // Crear producto
    public function crear_producto(
        $nombre_producto,
        $descripcion,
        $id_categoria_postre,
        $id_tortas,
        $id_postre_individual,
        $id_sabor,
        $id_tamano,
        $precio_base
    ) {
        $sql = "INSERT INTO productos
                (
                    nombre_producto,
                    descripcion,
                    id_categoria_postre,
                    id_tortas,
                    id_sabor,
                    id_tamano,
                    precio_base,
                    estado
                )
                VALUES
                (
                    :nombre_producto,
                    :descripcion,
                    :id_categoria_postre,
                    :id_tortas,
                    :id_sabor,
                    :id_tamano,
                    :precio_base,
                    1
                )";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':nombre_producto', $nombre_producto);
        $stmt->bindValue(':descripcion', $descripcion);
        $stmt->bindValue(
            ':id_categoria_postre',
            $id_categoria_postre,
            PDO::PARAM_INT
        );

        if ($id_tortas === null || $id_tortas === '') {
            $stmt->bindValue(':id_tortas', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(
                ':id_tortas',
                $id_tortas,
                PDO::PARAM_INT
            );
        }

        $stmt->bindValue(
            ':id_sabor',
            $id_sabor,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_tamano',
            $id_tamano,
            PDO::PARAM_INT
        );

        $stmt->bindValue(':precio_base', $precio_base);

        return $stmt->execute();
    }


    // Actualizar producto
    public function actualizar_producto(
        $id,
        $nombre_producto,
        $descripcion,
        $id_categoria_postre,
        $id_tortas,
        $id_postre_individual,
        $id_sabor,
        $id_tamano,
        $precio_base
    ) {
        $sql = "UPDATE productos SET
                    nombre_producto = :nombre_producto,
                    descripcion = :descripcion,
                    id_categoria_postre = :id_categoria_postre,
                    id_tortas = :id_tortas,
                    id_sabor = :id_sabor,
                    id_tamano = :id_tamano,
                    precio_base = :precio_base
                WHERE id_producto = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':nombre_producto', $nombre_producto);
        $stmt->bindValue(':descripcion', $descripcion);

        $stmt->bindValue(
            ':id_categoria_postre',
            $id_categoria_postre,
            PDO::PARAM_INT
        );

        if ($id_tortas === null || $id_tortas === '') {
            $stmt->bindValue(':id_tortas', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(
                ':id_tortas',
                $id_tortas,
                PDO::PARAM_INT
            );
        }

        $stmt->bindValue(
            ':id_sabor',
            $id_sabor,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':id_tamano',
            $id_tamano,
            PDO::PARAM_INT
        );

        $stmt->bindValue(':precio_base', $precio_base);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }


    // Eliminación lógica
    public function eliminar_producto($id) {
        $sql = "UPDATE productos
                SET estado = 0
                WHERE id_producto = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }


    // Listar productos activos
    // NUEVO: se agrega COALESCE(i.cantidad, 0) AS stock via LEFT JOIN inventario,
    // para que el POS y cualquier otra vista sepan cuánto stock queda por variante.
    public function leer_productos() {

        $sql = "SELECT
                    p.id_producto,
                    p.nombre_producto,
                    p.descripcion,

                    p.id_categoria_postre,
                    c.categoria_postre,

                    p.id_tortas,
                    tr.tipo_torta,

                    p.id_sabor,
                    s.sabor,

                    p.id_tamano,
                    t.tamano,

                    p.precio_base,
                    p.estado,
                    p.fecha_registro,

                    COALESCE(i.cantidad, 0) AS stock

                FROM productos p

                INNER JOIN categoria_postre c
                    ON p.id_categoria_postre = c.id_categoria_postre

                LEFT JOIN tortas tr
                    ON p.id_tortas = tr.id_tortas

                INNER JOIN sabor s
                    ON p.id_sabor = s.id_sabor

                INNER JOIN tamano t
                    ON p.id_tamano = t.id_tamano

                LEFT JOIN inventario i
                    ON i.id_producto = p.id_producto
                    AND i.estado = 1

                WHERE p.estado = 1

                ORDER BY p.id_producto ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Buscar un producto por ID
    // NUEVO: también trae el stock actual (misma lógica que leer_productos).
    public function buscar_producto($id) {

        $sql = "SELECT
                    p.id_producto,
                    p.nombre_producto,
                    p.descripcion,

                    p.id_categoria_postre,
                    c.categoria_postre,

                    p.id_tortas,
                    tr.tipo_torta,

                    p.id_sabor,
                    s.sabor,

                    p.id_tamano,
                    t.tamano,

                    p.precio_base,
                    p.estado,
                    p.fecha_registro,

                    COALESCE(i.cantidad, 0) AS stock

                FROM productos p

                INNER JOIN categoria_postre c
                    ON p.id_categoria_postre = c.id_categoria_postre

                LEFT JOIN tortas tr
                    ON p.id_tortas = tr.id_tortas

                INNER JOIN sabor s
                    ON p.id_sabor = s.id_sabor

                INNER JOIN tamano t
                    ON p.id_tamano = t.id_tamano

                LEFT JOIN inventario i
                    ON i.id_producto = p.id_producto
                    AND i.estado = 1

                WHERE p.id_producto = :id
                  AND p.estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Obtener productos activos para usar en otros módulos
    public function listar_para_select() {

        $sql = "SELECT
                    p.id_producto,
                    p.nombre_producto,
                    p.precio_base
                FROM productos p
                WHERE p.estado = 1
                ORDER BY p.nombre_producto ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
