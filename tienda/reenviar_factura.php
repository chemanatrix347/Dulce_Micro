<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/factura_lib.php';

exigirCliente();

if (!csrfValido()) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$conn  = (new conexion())->conn;
$orden = $_POST['orden'] ?? '';

[$ok, $mensaje] = enviarFacturaPorCorreo($conn, $orden, (int)$_SESSION['id_cliente']);

$_SESSION['flash_factura'] = ['ok' => $ok, 'mensaje' => $mensaje, 'orden' => $orden];

$volver = ($_POST['volver'] ?? '') === 'pedidos' ? 'mis_pedidos.php' : ('confirmacion.php?orden=' . urlencode($orden));
header('Location: ' . $volver);
exit;
