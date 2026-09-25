<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/config_tienda.php';

exigirCliente('checkout');

if (!csrfValido()) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$conn      = (new conexion())->conn;
$idCliente = (int)$_SESSION['id_cliente'];

/** Vuelve a checkout.php mostrando los errores y lo que el cliente ya había escrito. */
function volverAlCheckout(array $errores, array $old): void
{
    $_SESSION['checkout_flash'] = ['errores' => $errores, 'old' => $old];
    header('Location: checkout.php');
    exit;
}

/* ---------- Se vuelve a validar todo en el servidor: nada del formulario es confiable ---------- */
$carga = cargarItemsCarrito($conn, $_SESSION['carrito'] ?? []);
if (!$carga['items']) {
    header('Location: carrito.php?error=vacio');
    exit;
}

$entrega = $_SESSION['entrega'] ?? [];
$fechasOk = array_column(diasDisponibles(), 'valor');
if (!in_array($entrega['fecha'] ?? '', $fechasOk, true) || !isset(FRANJAS[$entrega['franja'] ?? ''])) {
    header('Location: carrito.php?error=fecha');
    exit;
}

$telefono     = preg_replace('/\D/', '', $_POST['telefono'] ?? '');
$ciudad       = trim($_POST['ciudad'] ?? '');
$direccion    = trim($_POST['direccion'] ?? '');
$barrio       = trim($_POST['barrio'] ?? '');
$indicaciones = mb_substr(trim($_POST['indicaciones'] ?? ''), 0, 300);
$idMetodoPago = (int)($_POST['id_metodo_pago'] ?? 0);

$old = compact('telefono', 'ciudad', 'direccion', 'barrio', 'indicaciones') + ['id_metodo_pago' => $idMetodoPago];

$errores = [];
if (strlen($telefono) < 7 || strlen($telefono) > 15) {
    $errores[] = 'Escribe un teléfono de contacto válido.';
}
if (!in_array($ciudad, CIUDADES_ENTREGA, true)) {
    $errores[] = 'Elige una ciudad de entrega válida.';
}
if (mb_strlen($direccion) < 5 || mb_strlen($direccion) > 255) {
    $errores[] = 'Escribe la dirección completa de entrega.';
}

$st = $conn->prepare('SELECT metodo_pago FROM metodos_pago WHERE id_metodos_pago = ? AND estado = 1');
$st->execute([$idMetodoPago]);
$nombreMetodo = $st->fetchColumn();
if (!$nombreMetodo) {
    $errores[] = 'Elige un método de pago válido.';
}
if ($errores) {
    volverAlCheckout($errores, $old);
}

/* ---------- Cliente: correo para la factura ---------- */
$st = $conn->prepare('SELECT correo FROM clientes WHERE id_cliente = ? AND estado = 1');
$st->execute([$idCliente]);
$correoCliente = $st->fetchColumn();
if (!$correoCliente) {
    header('Location: logout.php');
    exit;
}

$items    = $carga['items'];
$subtotal = $carga['subtotal'];
$envio    = costoEnvio($subtotal);
$total    = $subtotal + $envio;

// Efectivo (pago contra entrega) siempre queda pendiente; los demás dependen del modo de prueba
$esEfectivo   = mb_stripos($nombreMetodo, 'efectivo') !== false;
$idEstadoPago = ($esEfectivo || !MODO_PAGO_SIMULADO) ? ESTADO_PAGO_PENDIENTE : ESTADO_PAGO_COMPLETADO;
$fechaPago    = $idEstadoPago === ESTADO_PAGO_COMPLETADO ? date('Y-m-d') : null;

try {
    $conn->beginTransaction();

    // Vuelve a revisar el stock/estado de cada producto justo antes de guardar, por si cambió
    $stCheck = $conn->prepare('SELECT 1 FROM productos WHERE id_producto = ? AND estado = 1');
    foreach ($items as $it) {
        $stCheck->execute([$it['id']]);
        if (!$stCheck->fetchColumn()) {
            throw new RuntimeException('producto_no_disponible');
        }
    }

    // Número de orden legible y único: DM- + fecha + 4 caracteres al azar
    do {
        $numeroOrden = 'DM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $st = $conn->prepare('SELECT 1 FROM pedido_web WHERE numero_orden = ?');
        $st->execute([$numeroOrden]);
    } while ($st->fetchColumn());

    $st = $conn->prepare(
        'INSERT INTO pedido_web
            (numero_orden, id_cliente, fecha_entrega, franja, ciudad, direccion, barrio,
             indicaciones, dedicatoria, telefono_contacto, subtotal, costo_envio, total,
             correo_factura, factura_enviada, estado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1)'
    );
    $st->execute([
        $numeroOrden, $idCliente, $entrega['fecha'], $entrega['franja'], $ciudad, $direccion, $barrio,
        $indicaciones, $entrega['dedicatoria'] ?? '', $telefono, $subtotal, $envio, $total, $correoCliente,
    ]);
    $idPedidoWeb = (int)$conn->lastInsertId();

    // Un producto = una fila en pedido_completo, igual que en el POS del panel
    $stItem = $conn->prepare(
        'INSERT INTO pedido_completo
            (id_producto, id_cliente, fecha_pedido, fecha_estimada_entrega, id_categoria_postre,
             cantidad, id_tamano, id_sabor, precio_unitario, subtotal, id_estado_pedido,
             id_metodo_pago, fecha_pago, id_pedido_web, estado)
         VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
    );
    $stPago = $conn->prepare(
        'INSERT INTO pagos (id_pedido, id_metodo_pago, monto, id_estado_pago, fecha_pago, estado)
         VALUES (?, ?, ?, ?, ?, 1)'
    );

    foreach ($items as $it) {
        $stItem->execute([
            $it['id'], $idCliente, $entrega['fecha'], $it['categoria'],
            $it['cantidad'], $it['tamano'], $it['sabor'], $it['precio'], $it['subtotal'],
            ESTADO_PEDIDO_PENDIENTE, $idMetodoPago, $fechaPago, $idPedidoWeb,
        ]);
        $idPedido = (int)$conn->lastInsertId();
        $stPago->execute([$idPedido, $idMetodoPago, $it['subtotal'], $idEstadoPago, $fechaPago ?? date('Y-m-d')]);
    }

    $conn->commit();

} catch (RuntimeException $e) {
    $conn->rollBack();
    header('Location: carrito.php?error=no_disponible');
    exit;
} catch (PDOException $e) {
    $conn->rollBack();
    error_log('procesar_pedido.php: ' . $e->getMessage());
    volverAlCheckout(['No pudimos registrar tu pedido. Intenta de nuevo en unos minutos.'], $old);
}

// Pedido guardado: se limpian el carrito y los datos de entrega de la sesión
unset($_SESSION['carrito'], $_SESSION['entrega']);

header('Location: confirmacion.php?orden=' . urlencode($numeroOrden));
exit;
