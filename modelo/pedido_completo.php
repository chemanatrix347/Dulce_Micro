<?php

require_once __DIR__ . "/../config/conexion.php";
require_once __DIR__ . "/inventario.php"; // NUEVO: para validar y descontar stock

class PedidoCompleto
{
    private $db;

    // NUEVO: información que queda disponible después de llamar a crear_pedido()
    public $agotado = false;       // true si el producto quedó en 0 tras esta venta
    public $ultimo_error = null;   // 'producto_invalido' | 'stock_insuficiente' | null

    public function __construct()
    {
        $conexion = new conexion();
        $this->db = $conexion->conn;
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR PEDIDO
    |--------------------------------------------------------------------------
    | El usuario selecciona un producto.
    | El sistema obtiene automáticamente:
    | - categoría
    | - sabor
    | - tamaño
    | - precio
    | Ademas genera un codigo de pedido legible tipo DM-20260922-A6D0,
    | igual al que usa la tienda en linea (pedido_web.numero_orden).
    |
    | NUEVO: ahora valida stock ANTES de insertar el pedido y descuenta
    | el inventario justo DESPUÉS de insertarlo (tanto para POS como para
    | la tienda en línea, porque ambos pasan por este mismo método).
    |--------------------------------------------------------------------------
    */
    public function crear_pedido(
        $id_cliente,
        $fecha_pedido,
        $fecha_estimada_entrega,
        $id_producto,
        $cantidad,
        $id_estado_pedido,
        $id_metodo_pago,
        $fecha_pago,
        $id_repostera_asignada
    ) {
        $this->agotado = false;
        $this->ultimo_error = null;

        // Obtener información actual del producto
        $sqlProducto = "
            SELECT
                id_producto,
                id_categoria_postre,
                id_tamano,
                id_sabor,
                precio_base
            FROM productos
            WHERE id_producto = :id_producto
            AND estado = 1
        ";

        $stmtProducto = $this->db->prepare($sqlProducto);
        $stmtProducto->bindParam(':id_producto', $id_producto);
        $stmtProducto->execute();

        $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            $this->ultimo_error = 'producto_invalido';
            return false;
        }

        // ---------------------------------------------------------------
        // NUEVO: validar que haya stock suficiente ANTES de crear el pedido
        // ---------------------------------------------------------------
        $inventario = new Inventario();
        $fila_inventario = $inventario->obtener_por_producto($id_producto);

        if (!$fila_inventario || $fila_inventario['cantidad'] < $cantidad) {
            $this->ultimo_error = 'stock_insuficiente';
            return false;
        }
        // ---------------------------------------------------------------

        // Precio actual del producto
        $precio_unitario = $producto['precio_base'];

        // Calcular subtotal
        $subtotal = $precio_unitario * $cantidad;

        // Codigo de pedido legible y unico: DM- + fecha + 4 caracteres al azar
        do {
            $codigo_pedido = 'DM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
            $stCod = $this->db->prepare("SELECT 1 FROM pedido_completo WHERE codigo_pedido = :codigo");
            $stCod->bindParam(':codigo', $codigo_pedido);
            $stCod->execute();
        } while ($stCod->fetchColumn());

        // Guardamos también categoría, tamaño y sabor
        // para conservar compatibilidad con los datos anteriores.
        $sql = "
            INSERT INTO pedido_completo (
                codigo_pedido,
                id_producto,
                id_cliente,
                fecha_pedido,
                fecha_estimada_entrega,
                id_categoria_postre,
                cantidad,
                id_tamano,
                id_sabor,
                precio_unitario,
                subtotal,
                id_estado_pedido,
                id_metodo_pago,
                fecha_pago,
                id_repostera_asignada,
                estado
            )
            VALUES (
                :codigo_pedido,
                :id_producto,
                :id_cliente,
                :fecha_pedido,
                :fecha_estimada_entrega,
                :id_categoria_postre,
                :cantidad,
                :id_tamano,
                :id_sabor,
                :precio_unitario,
                :subtotal,
                :id_estado_pedido,
                :id_metodo_pago,
                :fecha_pago,
                :id_repostera_asignada,
                1
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':codigo_pedido', $codigo_pedido);
        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':fecha_pedido', $fecha_pedido);
        $stmt->bindParam(':fecha_estimada_entrega', $fecha_estimada_entrega);
        $stmt->bindParam(':id_categoria_postre', $producto['id_categoria_postre']);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id_tamano', $producto['id_tamano']);
        $stmt->bindParam(':id_sabor', $producto['id_sabor']);
        $stmt->bindParam(':precio_unitario', $precio_unitario);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':id_estado_pedido', $id_estado_pedido);
        $stmt->bindParam(':id_metodo_pago', $id_metodo_pago);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        $stmt->bindParam(':id_repostera_asignada', $id_repostera_asignada);

        if (!$stmt->execute()) {
            return false;
        }

        // ---------------------------------------------------------------
        // NUEVO: descontar el inventario ahora que el pedido quedó registrado
        // ---------------------------------------------------------------
        $resultado_inventario = $inventario->descontar($id_producto, $cantidad);
        $this->agotado = $resultado_inventario['agotado'] ?? false;
        // ---------------------------------------------------------------

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR PEDIDO
    |--------------------------------------------------------------------------
    | No modifica codigo_pedido: el codigo se genera una sola vez al crear
    | el pedido y se mantiene igual aunque se edite el resto de datos.
    |
    | NOTA: este método NO ajusta el inventario. Si en el futuro necesitas
    | permitir cambiar la cantidad o el producto de un pedido ya creado,
    | avísame para sumar/restar la diferencia de stock aquí también.
    |--------------------------------------------------------------------------
    */
    public function actualizar_pedido(
        $id,
        $id_cliente,
        $fecha_pedido,
        $fecha_estimada_entrega,
        $id_producto,
        $cantidad,
        $id_estado_pedido,
        $id_metodo_pago,
        $fecha_pago,
        $id_repostera_asignada
    ) {

        // Buscar el pedido actual
        $sqlActual = "
            SELECT
                id_producto,
                precio_unitario
            FROM pedido_completo
            WHERE id_pedido = :id
        ";

        $stmtActual = $this->db->prepare($sqlActual);
        $stmtActual->bindParam(':id', $id);
        $stmtActual->execute();

        $pedidoActual = $stmtActual->fetch(PDO::FETCH_ASSOC);

        if (!$pedidoActual) {
            return false;
        }


        // Obtener información del nuevo producto
        $sqlProducto = "
            SELECT
                id_producto,
                id_categoria_postre,
                id_tamano,
                id_sabor,
                precio_base
            FROM productos
            WHERE id_producto = :id_producto
            AND estado = 1
        ";

        $stmtProducto = $this->db->prepare($sqlProducto);
        $stmtProducto->bindParam(':id_producto', $id_producto);
        $stmtProducto->execute();

        $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            return false;
        }


        /*
        Si se cambia el producto:
        usamos el precio actual del nuevo producto.

        Si se está editando un pedido histórico
        sin cambiar el producto:
        conservamos su precio histórico.
        */
        if (
            $pedidoActual['id_producto'] !== null &&
            (int)$pedidoActual['id_producto'] === (int)$id_producto
        ) {
            $precio_unitario = $pedidoActual['precio_unitario'];
        } else {
            $precio_unitario = $producto['precio_base'];
        }

        // Recalcular subtotal
        $subtotal = $precio_unitario * $cantidad;


        $sql = "
            UPDATE pedido_completo
            SET
                id_producto = :id_producto,
                id_cliente = :id_cliente,
                fecha_pedido = :fecha_pedido,
                fecha_estimada_entrega = :fecha_estimada_entrega,
                id_categoria_postre = :id_categoria_postre,
                cantidad = :cantidad,
                id_tamano = :id_tamano,
                id_sabor = :id_sabor,
                precio_unitario = :precio_unitario,
                subtotal = :subtotal,
                id_estado_pedido = :id_estado_pedido,
                id_metodo_pago = :id_metodo_pago,
                fecha_pago = :fecha_pago,
                id_repostera_asignada = :id_repostera_asignada
            WHERE id_pedido = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':fecha_pedido', $fecha_pedido);
        $stmt->bindParam(':fecha_estimada_entrega', $fecha_estimada_entrega);
        $stmt->bindParam(':id_categoria_postre', $producto['id_categoria_postre']);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id_tamano', $producto['id_tamano']);
        $stmt->bindParam(':id_sabor', $producto['id_sabor']);
        $stmt->bindParam(':precio_unitario', $precio_unitario);
        $stmt->bindParam(':subtotal', $subtotal);
        $stmt->bindParam(':id_estado_pedido', $id_estado_pedido);
        $stmt->bindParam(':id_metodo_pago', $id_metodo_pago);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        $stmt->bindParam(':id_repostera_asignada', $id_repostera_asignada);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR PEDIDO
    |--------------------------------------------------------------------------
    | Eliminación lógica.
    |--------------------------------------------------------------------------
    */
    public function eliminar_pedido($id)
    {
        $sql = "
            UPDATE pedido_completo
            SET estado = 0
            WHERE id_pedido = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }


    /*
    |--------------------------------------------------------------------------
    | LEER PEDIDOS
    |--------------------------------------------------------------------------
    */
    public function leer_pedidos()
    {
        $sql = "
            SELECT
                pc.id_pedido,
                pc.codigo_pedido,
                pc.id_producto,
                pc.id_cliente,
                pc.fecha_pedido,
                pc.fecha_estimada_entrega,

                pc.id_categoria_postre,
                pc.cantidad,
                pc.id_tamano,
                pc.id_sabor,

                pc.precio_unitario,
                pc.subtotal,

                pc.id_estado_pedido,
                pc.id_metodo_pago,
                pc.fecha_pago,
                pc.id_repostera_asignada,
                pc.estado,

                p.nombre_producto,
                p.descripcion,
                p.precio_base,

                c.categoria_postre,
                s.sabor,
                t.tamano

            FROM pedido_completo pc

            LEFT JOIN productos p
                ON p.id_producto = pc.id_producto

            LEFT JOIN categoria_postre c
                ON c.id_categoria_postre = pc.id_categoria_postre

            LEFT JOIN sabor s
                ON s.id_sabor = pc.id_sabor

            LEFT JOIN tamano t
                ON t.id_tamano = pc.id_tamano

            WHERE pc.estado = 1

            ORDER BY pc.id_pedido ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR PEDIDO
    |--------------------------------------------------------------------------
    */
    public function buscar_pedido($id)
    {
        $sql = "
            SELECT
                pc.id_pedido,
                pc.codigo_pedido,
                pc.id_producto,
                pc.id_cliente,
                pc.fecha_pedido,
                pc.fecha_estimada_entrega,

                pc.id_categoria_postre,
                pc.cantidad,
                pc.id_tamano,
                pc.id_sabor,

                pc.precio_unitario,
                pc.subtotal,

                pc.id_estado_pedido,
                pc.id_metodo_pago,
                pc.fecha_pago,
                pc.id_repostera_asignada,
                pc.estado,

                p.nombre_producto,
                p.descripcion,
                p.precio_base

            FROM pedido_completo pc

            LEFT JOIN productos p
                ON p.id_producto = pc.id_producto

            WHERE pc.id_pedido = :id
            AND pc.estado = 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | ULTIMO ID (NUEVO)
    |--------------------------------------------------------------------------
    | Devuelve el id_pedido recien insertado. Se usa justo despues de
    | llamar a crear_pedido() para saber que pedido se acaba de crear.
    |--------------------------------------------------------------------------
    */
    public function ultimo_id()
    {
        return $this->db->lastInsertId();
    }


    /*
    |--------------------------------------------------------------------------
    | DATOS FACTURA (NUEVO)
    |--------------------------------------------------------------------------
    | Trae el pedido con nombre de producto, datos del cliente y nombre
    | del metodo de pago, todo junto, para imprimir la factura en PDF.
    |--------------------------------------------------------------------------
    */
    public function datos_factura($id_pedido)
    {
        $sql = "
            SELECT
                pc.*,
                p.nombre_producto,
                cl.nombre AS nombre_cliente,
                cl.numero_documento,
                cl.telefono,
                mp.metodo_pago
            FROM pedido_completo pc
            INNER JOIN productos p ON pc.id_producto = p.id_producto
            INNER JOIN clientes cl ON pc.id_cliente = cl.id_cliente
            INNER JOIN metodos_pago mp ON pc.id_metodo_pago = mp.id_metodos_pago
            WHERE pc.id_pedido = :id
            AND pc.estado = 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id_pedido);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
