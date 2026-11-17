<?php
// Funciones del POS: consultar la factura y generar el mismo PDF para imprimir y enviar.
require_once __DIR__ . '/../modelo/pedidocompleto.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

function cargarFacturaPOS(PDO $conn, int $idPedido): array
{
    $factura = (new PedidoCompleto())->datos_factura($idPedido);
    if (!$factura) {
        throw new \RuntimeException('No se encontró el pedido para generar la factura.');
    }
    $st = $conn->prepare('SELECT * FROM clientes WHERE id_cliente = :id');
    $st->execute([':id' => $factura['id_cliente']]);
    $cliente = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    $correo = trim((string)($cliente['correo'] ?? ''));
    if ($correo === '') {
        $correo = trim((string)($cliente['correo_electronico'] ?? ''));
    }
    $numero = !empty($factura['codigo_pedido'])
        ? (string)$factura['codigo_pedido']
        : str_pad((string)$factura['id_pedido'], 6, '0', STR_PAD_LEFT);
    return ['factura' => $factura, 'correo' => $correo, 'numero' => $numero];
}

function dmPosTexto($s): string
{
    return (string)iconv('UTF-8', 'windows-1252//TRANSLIT', (string)$s);
}

function dmPosCOP($n): string
{
    return '$ ' . number_format((float)$n, 0, ',', '.');
}

function generarFacturaPOS(array $datos): string
{
    $factura = $datos['factura'];
    $correo = $datos['correo'];
    $numero = $datos['numero'];
    $mostrarEntrega = false;
    $logoPath = __DIR__ . '/../img_logos/logo principal.png';

    $envio = (float) ($factura['costo_envio'] ?? 0);
    $total = (float) $factura['subtotal'] + $envio;

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();

    // ---- Encabezado: logo + marca a la izquierda, datos de factura a la derecha ----
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 10, 24);
}

$pdf->SetXY(44, 15);
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(168, 121, 201);
$pdf->Cell(70, 9, 'Dulce Micro', 0, 1, 'L');

$pdf->SetX(44);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(120, 100, 140);
$pdf->Cell(70, 5, dmPosTexto('Repostería artesanal'), 0, 1, 'L');

$pdf->SetXY(110, 13);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(60, 40, 80);
$pdf->Cell(85, 7, 'FACTURA DE VENTA', 0, 2, 'R');
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(168, 121, 201);
$pdf->Cell(85, 6, $numero, 0, 2, 'R');
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(60, 40, 80);
$pdf->Cell(85, 5, 'Fecha: ' . date('d/m/Y', strtotime($factura['fecha_pedido'])), 0, 0, 'R');

$pdf->SetDrawColor(201, 160, 220);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 42, 195, 42);

// ---- Facturado a (izquierda) / Entrega (derecha, opcional) ----
$y = 50;
$pdf->SetXY(15, $y);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(168, 121, 201);
$pdf->Cell(90, 5, 'FACTURADO A', 0, 1);

$pdf->SetX(15);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(60, 40, 80);
$pdf->Cell(90, 6, dmPosTexto($factura['nombre_cliente']), 0, 1);

$pdf->SetFont('Arial', '', 9);
$pdf->SetX(15);
$pdf->Cell(90, 5, 'CC ' . dmPosTexto($factura['numero_documento']), 0, 1);
if ($correo !== '') {
    $pdf->SetX(15);
    $pdf->Cell(90, 5, dmPosTexto($correo), 0, 1);
}
if (!empty($factura['telefono'])) {
    $pdf->SetX(15);
    $pdf->Cell(90, 5, 'Tel. ' . dmPosTexto($factura['telefono']), 0, 1);
}

if ($mostrarEntrega && !empty($factura['fecha_estimada_entrega'])) {
    $pdf->SetXY(110, $y);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(168, 121, 201);
    $pdf->Cell(85, 5, 'ENTREGA', 0, 1);
    $pdf->SetX(110);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(60, 40, 80);
    $pdf->Cell(85, 6, date('d/m/Y', strtotime($factura['fecha_estimada_entrega'])), 0, 1);
}

// ---- Tabla de producto ----
$pdf->SetY(88);
$pdf->SetFillColor(240, 224, 244);
$pdf->SetTextColor(60, 40, 80);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(90, 8, ' Producto', 0, 0, 'L', true);
$pdf->Cell(20, 8, 'Cant.', 0, 0, 'C', true);
$pdf->Cell(35, 8, 'Vr. unitario', 0, 0, 'R', true);
$pdf->Cell(35, 8, 'Subtotal ', 0, 1, 'R', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetDrawColor(235, 215, 240);
$pdf->SetLineWidth(0.3);
$pdf->Cell(90, 9, ' ' . dmPosTexto($factura['nombre_producto']), 'B', 0, 'L');
$pdf->Cell(20, 9, $factura['cantidad'], 'B', 0, 'C');
$pdf->Cell(35, 9, dmPosCOP($factura['precio_unitario']), 'B', 0, 'R');
$pdf->Cell(35, 9, dmPosCOP($factura['subtotal']) . ' ', 'B', 1, 'R');
$pdf->Ln(4);

// ---- Totales ----
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(110, 7, '', 0, 0);
$pdf->Cell(35, 7, 'Subtotal', 0, 0, 'L');
$pdf->Cell(35, 7, dmPosCOP($factura['subtotal']) . ' ', 0, 1, 'R');

if ($envio > 0) {
    $pdf->Cell(110, 7, '', 0, 0);
    $pdf->Cell(35, 7, dmPosTexto('Envío'), 0, 0, 'L');
    $pdf->Cell(35, 7, dmPosCOP($envio) . ' ', 0, 1, 'R');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(168, 121, 201);
$pdf->Cell(110, 9, '', 0, 0);
$pdf->Cell(35, 9, 'TOTAL COP', 0, 0, 'L');
$pdf->Cell(35, 9, dmPosCOP($total) . ' ', 0, 1, 'R');
$pdf->Ln(4);

// ---- Pago ----
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, 'PAGO', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(60, 40, 80);
$pagoTxt = dmPosTexto('Método: ') . dmPosTexto($factura['metodo_pago']);
if (!empty($factura['estado_pago'])) {
    $pagoTxt .= '   |   Estado: ' . dmPosTexto($factura['estado_pago']);
}
$pdf->Cell(0, 6, $pagoTxt, 0, 1);

// Mostrar efectivo recibido y vueltos en el PDF.
if (
    stripos((string) ($factura['metodo_pago'] ?? ''), 'efectivo') !== false
    && isset($factura['efectivo_recibido'], $factura['cambio'])
) {
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(60, 40, 80);

    $pdf->Cell(
        0,
        6,
        dmPosTexto('Efectivo recibido: ')
            . dmPosCOP($factura['efectivo_recibido']),
        0,
        1
    );

    $pdf->SetFont('Arial', 'B', 9);

    $pdf->Cell(
        0,
        6,
        dmPosTexto('Vueltos: ')
            . dmPosCOP($factura['cambio']),
        0,
        1
    );

    $pdf->SetFont('Arial', '', 9);
}

// ---- Dedicatoria / notas (solo si existen) ----
if (!empty($factura['notas'])) {
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(168, 121, 201);
    $pdf->Cell(0, 5, 'DEDICATORIA / NOTAS', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(60, 40, 80);
    $pdf->MultiCell(0, 5, dmPosTexto($factura['notas']));
}

// ---- Pie ----
$pdf->Ln(8);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(120, 100, 140);
$pdf->Cell(0, 6, dmPosTexto('¡Gracias por tu compra! Comprobante de venta de Dulce Micro.'), 0, 1, 'C');

return $pdf->Output('S');
}

function enviarFacturaPOS(PDO $conn, int $idPedido): array
{
    $datos = cargarFacturaPOS($conn, $idPedido);
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        return ['estado' => 'sin_correo', 'numero' => $datos['numero']];
    }
    require_once __DIR__ . '/../tienda/factura_lib.php';
    $f = $datos['factura'];
    $subtotal = (float)$f['subtotal'];
    $envio = (float)($f['costo_envio'] ?? 0);
    $cuerpo = cuerpoCorreoFacturaPOS([
        'pedido' => [
            'numero_orden' => $datos['numero'],
            'cliente_nombre' => $f['nombre_cliente'],
            'subtotal' => $subtotal,
            'costo_envio' => $envio,
            'total' => $subtotal + $envio,
        ],
        'items' => [[
            'nombre_producto' => $f['nombre_producto'],
            'cantidad' => $f['cantidad'],
            'subtotal' => $subtotal,
        ]],
        'pago' => [
            'metodo_pago' => $f['metodo_pago'],
            'estado_pago' => $f['estado_pago'] ?? '',
        ],
    ]);
    [$ok] = enviarFacturaPreparada(['pedido' => [
        'numero_orden' => $datos['numero'],
        'correo_factura' => $datos['correo'],
        'cliente_nombre' => $f['nombre_cliente'],
    ]], generarFacturaPOS($datos), $cuerpo);
    return ['estado' => $ok ? 'enviada' : 'error', 'numero' => $datos['numero']];
}

/** Correo del POS con el estilo visual de la tienda web. */
function cuerpoCorreoFacturaPOS(array $d): string

{

    $p = $d['pedido'];

    $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$logoHtml = '';



    if (is_file(__DIR__ . '/../img_logos/favicon.png')) {

        $logoHtml = '

            <img src="cid:dulcemicro_logo"

                 alt="' . $esc(NEGOCIO_NOMBRE) . '"

                 style="display:block;width:110px;max-width:110px;height:auto;margin:0 auto 10px auto;border:0;">

        ';

    }



    $filas = '';



    foreach ($d['items'] as $it) {

        $filas .= '

            <tr>

                <td style="padding:14px 8px;border-bottom:1px solid #F3E5F5;color:#49314F;font-size:14px;">

                    <strong>' . $esc($it['nombre_producto']) . '</strong>

                    <br>

                    <span style="color:#8D7693;font-size:13px;">

                        Cantidad: ' . (int)$it['cantidad'] . '

                    </span>

                </td>

                <td style="padding:14px 8px;border-bottom:1px solid #F3E5F5;text-align:right;white-space:nowrap;color:#6B4B73;font-weight:bold;font-size:14px;">

                    ' . $esc(formatoCOP((float)$it['subtotal'])) . '

                </td>

            </tr>';

    }



    $envio = (float)$p['costo_envio'] > 0

        ? $esc(formatoCOP((float)$p['costo_envio']))

        : 'No aplica';



    $nombreCompleto = trim((string)$p['cliente_nombre']);

    $partesNombre = preg_split('/**\s**+/', $nombreCompleto);

    $nombreCorto = $partesNombre[0] ?? $nombreCompleto;



    return '<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Factura - ' . $esc(NEGOCIO_NOMBRE) . '</title>

</head>



<body style="margin:0;padding:0;background:#FFF5FA;font-family:Arial,Helvetica,sans-serif;color:#49314F;">



<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF5FA;padding:30px 10px;">

<tr>

<td align="center">



<table width="600" cellpadding="0" cellspacing="0" border="0"

       style="width:100%;max-width:600px;background:#FFFFFF;border-radius:22px;overflow:hidden;">



<tr>

<td style="background:#C9A0DC;padding:30px 25px;text-align:center;">



' . $logoHtml . '



<div style="font-size:27px;font-weight:bold;color:#FFFFFF;margin-bottom:5px;">

    ' . $esc(NEGOCIO_NOMBRE) . '

</div>



<div style="font-size:14px;color:#FFFFFF;">

    ' . $esc(NEGOCIO_SLOGAN) . '

</div>



</td>

</tr>



<tr>

<td style="padding:35px 35px 15px 35px;">



<div style="text-align:center;font-size:25px;font-weight:bold;color:#49314F;margin-bottom:10px;">

    ¡Gracias por tu compra! 💕

</div>



<div style="text-align:center;font-size:15px;line-height:1.6;color:#806B87;">

    Hola <strong style="color:#8A5AAE;">' . $esc($nombreCorto) . '</strong>,

    <br>

    Registramos tu compra en nuestro punto de venta.

</div>



</td>

</tr>



<tr>

<td style="padding:10px 35px 25px 35px;">



<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F8EFFB;border-radius:15px;">

<tr>

<td style="padding:18px;text-align:center;">



<div style="font-size:12px;color:#8D7693;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">

    Número de pedido · POS

</div>



<div style="font-size:22px;font-weight:bold;color:#8A5AAE;">

    ' . $esc($p['numero_orden']) . '

</div>



</td>

</tr>

</table>



</td>

</tr>



<tr>

<td style="padding:0 35px 25px 35px;">



<div style="font-size:17px;font-weight:bold;color:#49314F;margin-bottom:12px;">

    🧁 Resumen de tu pedido

</div>



<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #F1E2F6;border-radius:12px;">



<tr>

<td style="padding:11px 8px;background:#F7EEF9;color:#76537F;font-size:12px;font-weight:bold;text-transform:uppercase;">

    Producto

</td>



<td style="padding:11px 8px;background:#F7EEF9;color:#76537F;font-size:12px;font-weight:bold;text-transform:uppercase;text-align:right;">

    Total

</td>

</tr>



' . $filas . '



</table>



</td>

</tr>



<tr>

<td style="padding:0 35px 25px 35px;">



<table width="100%" cellpadding="0" cellspacing="0" border="0">



<tr>

<td style="padding:6px 0;color:#806B87;font-size:14px;">Subtotal</td>

<td style="padding:6px 0;text-align:right;font-size:14px;color:#49314F;">

    ' . $esc(formatoCOP((float)$p['subtotal'])) . '

</td>

</tr>



<tr>

<td style="padding:6px 0;color:#806B87;font-size:14px;">Envío</td>

<td style="padding:6px 0;text-align:right;font-size:14px;color:#49314F;">

    ' . $envio . '

</td>

</tr>



<tr>

<td style="padding:8px 0;"></td>

<td style="padding:8px 0;"></td>

</tr>



<tr>

<td style="padding:18px 15px;background:#F7EEF9;border-radius:12px 0 0 12px;font-size:17px;font-weight:bold;color:#49314F;">

    TOTAL

</td>



<td style="padding:18px 15px;background:#F7EEF9;border-radius:0 12px 12px 0;text-align:right;font-size:21px;font-weight:bold;color:#8A5AAE;">

    ' . $esc(formatoCOP((float)$p['total'])) . '

</td>

</tr>



</table>



</td>

</tr>



<tr>

<td style="padding:0 35px 25px 35px;">



<div style="font-size:16px;font-weight:bold;color:#49314F;margin-bottom:10px;">

    💳 Información de pago

</div>



<div style="background:#F4F9FF;border-radius:12px;padding:15px;font-size:14px;color:#5F6878;">



<strong>Método:</strong>

' . $esc($d['pago']['metodo_pago']) . '



&nbsp;&nbsp; | &nbsp;&nbsp;



<strong>Estado:</strong>

' . $esc($d['pago']['estado_pago']) . '



</div>



</td>

</tr>



<tr>

<td style="padding:0 35px 30px 35px;text-align:center;">



<div style="background:#F8EFFB;border-radius:15px;padding:20px;">



<div style="font-size:28px;margin-bottom:8px;">

    📄

</div>



<div style="font-size:15px;font-weight:bold;color:#49314F;margin-bottom:5px;">

    Tu factura está adjunta

</div>



<div style="font-size:13px;color:#806B87;">

    Encontrarás el comprobante de tu compra

    en formato PDF adjunto a este correo.

</div>



</div>



</td>

</tr>



<tr>

<td style="padding:5px 35px 30px 35px;text-align:center;">



<div style="font-size:19px;font-weight:bold;color:#8A5AAE;margin-bottom:8px;">

    🍰 ¡Esperamos verte pronto!

</div>



<div style="font-size:13px;line-height:1.6;color:#806B87;">

    Gracias por confiar en

    <strong>' . $esc(NEGOCIO_NOMBRE) . '</strong>.

    <br>

    Preparamos cada pedido con mucho cariño.

</div>



</td>

</tr>



<tr>

<td style="background:#49314F;padding:25px;text-align:center;">



<div style="color:#FFFFFF;font-size:15px;font-weight:bold;margin-bottom:7px;">

    ' . $esc(NEGOCIO_NOMBRE) . '

</div>



<div style="color:#EBDDF0;font-size:12px;line-height:1.6;">



' . $esc(NEGOCIO_SLOGAN) . '



<br>



' . $esc(NEGOCIO_CONTACTO) . '



<br><br>



Este correo fue enviado automáticamente.



</div>



</td>

</tr>



</table>



</td>

</tr>

</table>



</body>

</html>';

}
