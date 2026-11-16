<?php
// El mismo PDF adjunto al correo, disponible para imprimir.
session_name("LOGIN");
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/factura_pos_lib.php';
$idPedido = filter_input(INPUT_GET, 'id_pedido', FILTER_VALIDATE_INT);
if (!$idPedido || $idPedido < 1) {
    http_response_code(400);
    exit('Número de pedido inválido.');
}
try {
    $datos = cargarFacturaPOS((new conexion())->conn, $idPedido);
    $pdf = generarFacturaPOS($datos);
} catch (\Throwable $e) {
    error_log('Factura POS: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo generar la factura. Revisa el registro de errores de PHP.');
}
$nombre = preg_replace('/[^A-Za-z0-9_-]/', '_', $datos['numero']);
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="factura_' . $nombre . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit();
