<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/factura_lib.php';

exigirCliente();

$conn  = (new conexion())->conn;
$orden = $_GET['orden'] ?? '';

$d = cargarPedidoWeb($conn, $orden, (int)$_SESSION['id_cliente']);
if (!$d) {
    http_response_code(404);
    exit('Pedido no encontrado.');
}

if (!is_file(__DIR__ . '/../lib/fpdf/fpdf.php')) {
    http_response_code(503);
    exit('La generación de facturas en PDF todavía no está instalada en el servidor (falta la librería FPDF en Dulce_Micro/lib/fpdf/).');
}

$pdf = generarFacturaPDF($d);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Factura-' . $orden . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
