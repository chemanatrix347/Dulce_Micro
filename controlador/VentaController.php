<?php
// controlador/VentaController.php

session_name("LOGIN");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}

require_once __DIR__ . "/../modelo/clientes.php";
require_once __DIR__ . "/../modelo/pedidocompleto.php";
require_once __DIR__ . "/../modelo/pagos.php";
require_once __DIR__ . "/../modelo/inventario.php";
require_once __DIR__ . "/../config/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $conn = (new conexion())->conn;
    $cliente = new Cliente();
    $pedido = new PedidoCompleto();

    // ---- 1) Resolver el cliente: uno existente o crear uno nuevo de una vez ----
    if (($_POST['cliente_tipo'] ?? '') === 'nuevo') {

        $nombre = trim($_POST['nuevo_nombre'] ?? '');
        $idTipoDocumento = trim($_POST['nuevo_id_tipo_documento'] ?? '');
        $numeroDocumento = trim($_POST['nuevo_numero_documento'] ?? '');
        $correo = trim($_POST['nuevo_correo'] ?? '');
        $telefono = trim($_POST['nuevo_telefono'] ?? '');

        if ($nombre === '' || $idTipoDocumento === '' || $numeroDocumento === '') {
            die("Faltan datos del cliente nuevo (nombre, tipo y numero de documento son obligatorios).");
        }

        $cliente->crear_cliente($nombre, $idTipoDocumento, $numeroDocumento, $correo, $telefono);

        // El modelo no devuelve el id, asi que lo recuperamos por su documento
        // (recien insertado, tomamos el mas reciente por si el numero se repitiera)
        $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE numero_documento = :doc ORDER BY id_cliente DESC LIMIT 1");
        $stmt->bindParam(':doc', $numeroDocumento);
        $stmt->execute();
        $idCliente = $stmt->fetchColumn();

        if (!$idCliente) {
            die("No se pudo registrar el cliente nuevo.");
        }
    } else {
        $idCliente = $_POST['id_cliente'] ?? '';
        if ($idCliente === '') {
            die("Selecciona un cliente.");
        }
    }

    // ---- 2) Registrar la venta (el pedido ya guarda metodo y fecha de pago) ----
    $idProducto = $_POST['id_producto'] ?? '';
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
    $idEstadoPedido = $_POST['id_estado_pedido'] ?? '';
    $idMetodoPago = $_POST['id_metodo_pago'] ?? '';
    $idEstadoPago = $_POST['id_estado_pago'] ?? '';
    $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');
    $fechaPedido = date('Y-m-d');
    $fechaEstimadaEntrega = date('Y-m-d', strtotime('+3 days'));

    if ($idProducto === '' || $idEstadoPedido === '' || $idMetodoPago === '' || $idEstadoPago === '') {
        die("Faltan datos de la venta (producto, estado, metodo de pago o estado del pago).");
    }

    $ok = $pedido->crear_pedido(
        $idCliente,
        $fechaPedido,
        $fechaEstimadaEntrega,
        $idProducto,
        $cantidad,
        $idEstadoPedido,
        $idMetodoPago,
        $fechaPago,
        null // id_repostera_asignada: se asigna despues desde el modulo de Pedidos
    );

    if (!$ok) {
        die("No se pudo registrar la venta. Verifica que el producto exista y este activo.");
    }

    // ---- 3) Capturar el id del pedido recien creado, para la factura ----
    $idPedido = $pedido->ultimo_id();

    // ---- 3.5) Descontar inventario (producto terminado) ----
    $inventarioModelo = new Inventario();
    $resultadoInventario = $inventarioModelo->descontar($idProducto, $cantidad);

    if (!$resultadoInventario['ok']) {
        die("No se pudo completar la venta: " . $resultadoInventario['mensaje']);
    }

    if ($resultadoInventario['agotado']) {
        $_SESSION['aviso_stock'] = "El producto quedó sin stock (0 unidades disponibles)";
    }

    // ---- 4) Registrar el pago asociado a este pedido ----
    $stmtPrecio = $conn->prepare("SELECT precio_base FROM productos WHERE id_producto = :id");
    $stmtPrecio->bindParam(':id', $idProducto);
    $stmtPrecio->execute();
    $precioBase = (float) $stmtPrecio->fetchColumn();
    $monto = $precioBase * $cantidad;

    $pago = new Pago();
    $pago->crear_pago($idPedido, $idMetodoPago, $monto, $idEstadoPago, $fechaPago);

    header("Location: ../vista/nuevaventa.php?exito=1&id_pedido=" . $idPedido);
    exit();
}

header("Location: ../vista/nuevaventa.php");
exit();
