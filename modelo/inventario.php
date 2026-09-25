<?php

require_once __DIR__ . "/../config/conexion.php";

class Inventario {

    private $db;

    public function __construct() {

        $conexion = new conexion();
        $this->db = $conexion->conn;

    }

    // =========================================================
    // CREAR INVENTARIO
    // =========================================================

    public function crear_inventario($id_producto, $cantidad) {

        $sql = "INSERT INTO inventario
                (
                    id_producto,
                    cantidad
                )
                VALUES
                (
                    :id_producto,
                    :cantidad
                )";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id_producto',
            $id_producto,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':cantidad',
            $cantidad
        );

        return $stmt->execute();
    }


    // =========================================================
    // ACTUALIZAR INVENTARIO
    // =========================================================

    public function actualizar_inventario(
        $id,
        $id_producto,
        $cantidad
    ) {

        $sql = "UPDATE inventario
                SET
                    id_producto = :id_producto,
                    cantidad = :cantidad
                WHERE id_inventario = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id_producto',
            $id_producto,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':cantidad',
            $cantidad
        );

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }


    // =========================================================
    // ELIMINAR INVENTARIO
    // =========================================================

    public function eliminar_inventario($id) {

        $sql = "UPDATE inventario
                SET estado = 0
                WHERE id_inventario = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }


    // =========================================================
    // LISTAR INVENTARIO
    // =========================================================

    public function leer_inventarios() {

        $sql = "SELECT
                    i.id_inventario,
                    i.id_producto,
                    i.cantidad,
                    i.estado,

                    p.nombre_producto,

                    p.precio_base AS precio_unitario_base,

                    c.categoria_postre,
                    s.sabor,
                    t.tamano

                FROM inventario i

                INNER JOIN productos p
                    ON i.id_producto = p.id_producto

                LEFT JOIN categoria_postre c
                    ON p.id_categoria_postre = c.id_categoria_postre

                LEFT JOIN sabor s
                    ON p.id_sabor = s.id_sabor

                LEFT JOIN tamano t
                    ON p.id_tamano = t.id_tamano

                WHERE i.estado = 1

                ORDER BY i.id_inventario ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // =========================================================
    // BUSCAR INVENTARIO
    // =========================================================

    public function buscar_inventario($id) {

        $sql = "SELECT
                    i.id_inventario,
                    i.id_producto,
                    i.cantidad,
                    i.estado,

                    p.nombre_producto,

                    p.precio_base AS precio_unitario_base,

                    c.categoria_postre,
                    s.sabor,
                    t.tamano

                FROM inventario i

                INNER JOIN productos p
                    ON i.id_producto = p.id_producto

                LEFT JOIN categoria_postre c
                    ON p.id_categoria_postre = c.id_categoria_postre

                LEFT JOIN sabor s
                    ON p.id_sabor = s.id_sabor

                LEFT JOIN tamano t
                    ON p.id_tamano = t.id_tamano

                WHERE i.id_inventario = :id
                  AND i.estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // =========================================================
    // OBTENER INVENTARIO POR PRODUCTO (para venta y producción)
    // =========================================================

    public function obtener_por_producto($id_producto) {

        $sql = "SELECT * FROM inventario
                WHERE id_producto = :id_producto
                  AND estado = 1";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(
            ':id_producto',
            $id_producto,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // =========================================================
    // DESCONTAR INVENTARIO (al confirmar una venta)
    // =========================================================

    public function descontar($id_producto, $cantidad_vendida) {

        $fila = $this->obtener_por_producto($id_producto);

        if (!$fila) {
            return ['ok' => false, 'mensaje' => 'El producto no tiene registro de inventario'];
        }

        if ($fila['cantidad'] < $cantidad_vendida) {
            return ['ok' => false, 'mensaje' => 'No hay suficiente stock disponible'];
        }

        $nueva_cantidad = $fila['cantidad'] - $cantidad_vendida;

        $sql = "UPDATE inventario
                SET cantidad = :cantidad
                WHERE id_producto = :id_producto";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':cantidad', $nueva_cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'ok' => true,
            'nueva_cantidad' => $nueva_cantidad,
            'agotado' => $nueva_cantidad <= 0
        ];
    }


    // =========================================================
    // AUMENTAR INVENTARIO (al preparar/producir tortas)
    // =========================================================

    public function aumentar($id_producto, $cantidad_producida) {

        $fila = $this->obtener_por_producto($id_producto);

        if (!$fila) {
            // Si el producto nunca tuvo fila de inventario, se crea
            return $this->crear_inventario($id_producto, $cantidad_producida);
        }

        $nueva_cantidad = $fila['cantidad'] + $cantidad_producida;

        $sql = "UPDATE inventario
                SET cantidad = :cantidad
                WHERE id_producto = :id_producto";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':cantidad', $nueva_cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':id_producto', $id_producto, PDO::PARAM_INT);

        return $stmt->execute();
    }

}

?>
