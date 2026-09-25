<?php
/**
 * Factura de las compras en línea:
 *   cargarPedidoWeb()        -> datos de un pedido (solo si pertenece al cliente)
 *   generarFacturaPDF()      -> PDF con FPDF, devuelto como texto binario
 *   enviarFacturaPorCorreo() -> manda el PDF al correo del cliente con PHPMailer
 */
require_once __DIR__ . '/config_tienda.php';

use PHPMailer\PHPMailer\PHPMailer;

/** Trae el pedido web, sus productos y su pago. Devuelve null si no existe o no es de ese cliente. */
function cargarPedidoWeb(PDO $conn, string $orden, int $idCliente): ?array
{
    $st = $conn->prepare(
        'SELECT w.*, c.nombre AS cliente_nombre, c.numero_documento, td.tipo_documento
           FROM pedido_web w
           JOIN clientes c ON c.id_cliente = w.id_cliente
           LEFT JOIN tipo_documento td ON td.id_tipo_documento = c.id_tipo_documento
          WHERE w.numero_orden = ? AND w.id_cliente = ? AND w.estado = 1'
    );
    $st->execute([$orden, $idCliente]);
    $pedido = $st->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) {
        return null;
    }

    $st = $conn->prepare(
        'SELECT pc.cantidad, pc.precio_unitario, pc.subtotal, pr.nombre_producto, pr.imagen
           FROM pedido_completo pc
           LEFT JOIN productos pr ON pr.id_producto = pc.id_producto
          WHERE pc.id_pedido_web = ? AND pc.estado = 1
          ORDER BY pc.id_pedido'
    );
    $st->execute([$pedido['id_pedido_web']]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    $st = $conn->prepare(
        'SELECT mp.metodo_pago, ep.estado_pago
           FROM pedido_completo pc
           JOIN pagos pg ON pg.id_pedido = pc.id_pedido
           JOIN metodos_pago mp ON mp.id_metodos_pago = pg.id_metodo_pago
           JOIN estado_pagos ep ON ep.id_estado_pago = pg.id_estado_pago
          WHERE pc.id_pedido_web = ?
          ORDER BY pg.id_pagos LIMIT 1'
    );
    $st->execute([$pedido['id_pedido_web']]);
    $pago = $st->fetch(PDO::FETCH_ASSOC) ?: ['metodo_pago' => '—', 'estado_pago' => '—'];

    return ['pedido' => $pedido, 'items' => $items, 'pago' => $pago];
}

/** FPDF trabaja en ISO-8859-1: se convierte el texto para que salgan bien tildes y ñ. */
function textoPDF(string $s): string
{
    return mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
}

/** Genera la factura en PDF y la devuelve como string. */
function generarFacturaPDF(array $d): string
{
    require_once __DIR__ . '/../lib/fpdf/fpdf.php';

    $p = $d['pedido'];
    $items = $d['items'];
    $pago = $d['pago'];
    $cop = fn(float $v): string => '$ ' . number_format($v, 0, ',', '.');

    $pdf = new FPDF('P', 'mm', 'Letter');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    $logo = __DIR__ . '/../img_logos/favicon.png';
    if (is_file($logo)) {
        try {
            $pdf->Image($logo, 15, 12, 24);
        } catch (\Throwable $e) {
        }
    }

    $pdf->SetXY(43, 15);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(138, 90, 174);
    $pdf->Cell(90, 8, textoPDF(NEGOCIO_NOMBRE), 0, 2);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(110, 90, 122);
    $pdf->Cell(90, 5, textoPDF(NEGOCIO_SLOGAN), 0, 2);
    if (NEGOCIO_NIT !== '') {
        $pdf->Cell(90, 5, 'NIT ' . textoPDF(NEGOCIO_NIT), 0, 2);
    }
    if (NEGOCIO_CONTACTO !== '') {
        $pdf->Cell(90, 5, textoPDF(NEGOCIO_CONTACTO), 0, 2);
    }

    $pdf->SetXY(130, 15);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->SetTextColor(58, 37, 69);
    $pdf->Cell(70.9, 7, 'FACTURA DE VENTA', 0, 2, 'R');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(138, 90, 174);
    $pdf->Cell(70.9, 6, $p['numero_orden'], 0, 2, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(58, 37, 69);
    $pdf->Cell(70.9, 5, 'Fecha: ' . date('d/m/Y H:i', strtotime($p['fecha_creacion'])), 0, 2, 'R');

    $pdf->SetDrawColor(201, 160, 220);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, 44, 200.9, 44);

    $y0 = 50;
    $pdf->SetXY(15, $y0);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(138, 90, 174);
    $pdf->Cell(90, 5, 'FACTURADO A', 0, 2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(58, 37, 69);
    $pdf->Cell(90, 5.5, textoPDF($p['cliente_nombre']), 0, 2);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(90, 5, textoPDF(trim(($p['tipo_documento'] ?? '') . ' ' . ($p['numero_documento'] ?? ''))), 0, 2);
    $pdf->Cell(90, 5, textoPDF($p['correo_factura']), 0, 2);
    $pdf->Cell(90, 5, 'Tel. ' . textoPDF((string)$p['telefono_contacto']), 0, 2);
    $yIzq = $pdf->GetY();

    $pdf->SetXY(110, $y0);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(138, 90, 174);
    $pdf->Cell(90.9, 5, 'ENTREGA', 0, 2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(58, 37, 69);
    $franja = FRANJAS[$p['franja']] ?? ['', ''];
    $pdf->MultiCell(90.9, 5.5, textoPDF(fechaLarga($p['fecha_entrega'])), 0, 'L');
    $pdf->SetX(110);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(90.9, 5, textoPDF($franja[0] . ' (' . $franja[1] . ')'), 0, 2);
    $pdf->MultiCell(90.9, 5, textoPDF($p['direccion'] . ($p['barrio'] ? ', ' . $p['barrio'] : '') . ' - ' . $p['ciudad']), 0, 'L');
    $yDer = $pdf->GetY();

    $pdf->SetY(max($yIzq, $yDer) + 8);

    $pdf->SetFillColor(241, 226, 246);
    $pdf->SetTextColor(58, 37, 69);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(95, 8, ' Producto', 0, 0, 'L', true);
    $pdf->Cell(20, 8, 'Cant.', 0, 0, 'C', true);
    $pdf->Cell(35, 8, 'Vr. unitario', 0, 0, 'R', true);
    $pdf->Cell(35.9, 8, 'Subtotal ', 0, 1, 'R', true);

    $pdf->SetFont('Arial', '', 9);
    foreach ($items as $i => $it) {
        $pdf->SetFillColor(...($i % 2 ? [250, 240, 247] : [255, 255, 255]));
        $pdf->Cell(95, 8, ' ' . textoPDF(mb_strimwidth((string)$it['nombre_producto'], 0, 52, '...')), 0, 0, 'L', true);
        $pdf->Cell(20, 8, (string)$it['cantidad'], 0, 0, 'C', true);
        $pdf->Cell(35, 8, $cop((float)$it['precio_unitario']), 0, 0, 'R', true);
        $pdf->Cell(35.9, 8, $cop((float)$it['subtotal']) . ' ', 0, 1, 'R', true);
    }
    $pdf->SetDrawColor(235, 211, 230);
    $pdf->Line(15, $pdf->GetY(), 200.9, $pdf->GetY());

    $pdf->Ln(4);
    $fila = function (string $etiqueta, string $valor, bool $fuerte = false) use ($pdf): void {
        $pdf->SetX(120);
        $pdf->SetFont('Arial', $fuerte ? 'B' : '', $fuerte ? 12 : 10);
        $pdf->SetTextColor($fuerte ? 138 : 58, $fuerte ? 90 : 37, $fuerte ? 174 : 69);
        $pdf->Cell(40, $fuerte ? 9 : 6.5, textoPDF($etiqueta), 0, 0, 'L');
        $pdf->Cell(40.9, $fuerte ? 9 : 6.5, textoPDF($valor), 0, 1, 'R');
    };
    $fila('Subtotal', $cop((float)$p['subtotal']));
    $fila('Envío', (float)$p['costo_envio'] > 0 ? $cop((float)$p['costo_envio']) : 'Gratis');
    $fila('TOTAL COP', $cop((float)$p['total']), true);

    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(138, 90, 174);
    $pdf->Cell(0, 5, 'PAGO', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(58, 37, 69);
    $pdf->Cell(0, 5, textoPDF('Método: ' . $pago['metodo_pago'] . '   |   Estado: ' . $pago['estado_pago']), 0, 1);

    if (!empty($p['dedicatoria'])) {
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(138, 90, 174);
        $pdf->Cell(0, 5, 'DEDICATORIA / NOTAS', 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(58, 37, 69);
        $pdf->MultiCell(0, 5, textoPDF($p['dedicatoria']), 0, 'L');
    }

    if (!empty($p['indicaciones'])) {
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(138, 90, 174);
        $pdf->Cell(0, 5, 'INDICACIONES DE ENTREGA', 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(58, 37, 69);
        $pdf->MultiCell(0, 5, textoPDF($p['indicaciones']), 0, 'L');
    }

    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(110, 90, 122);
    $pdf->Cell(0, 5, textoPDF('¡Gracias por tu compra! Comprobante de venta de ' . NEGOCIO_NOMBRE . '.'), 0, 1, 'C');

    return $pdf->Output('S');
}

/** Cuerpo HTML del correo que acompaña la factura. */
function cuerpoCorreoFactura(array $d): string
{
    $p = $d['pedido'];
    $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $franja = FRANJAS[$p['franja']] ?? ['', ''];

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
        : 'Gratis';

    $nombreCompleto = trim((string)$p['cliente_nombre']);
    $partesNombre = preg_split('/\s+/', $nombreCompleto);
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
    Hemos recibido correctamente tu pedido.
</div>

</td>
</tr>

<tr>
<td style="padding:10px 35px 25px 35px;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F8EFFB;border-radius:15px;">
<tr>
<td style="padding:18px;text-align:center;">

<div style="font-size:12px;color:#8D7693;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">
    Número de pedido
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

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background:#FFF3F8;border:1px solid #F7D8E7;border-radius:15px;">

<tr>
<td style="padding:20px;">

<div style="font-size:16px;font-weight:bold;color:#A85B80;margin-bottom:10px;">
    📦 Información de entrega
</div>

<div style="font-size:14px;line-height:1.7;color:#654B5F;">

<strong>Fecha:</strong>
' . $esc(fechaLarga($p['fecha_entrega'])) . '

<br>

<strong>Horario:</strong>
' . $esc($franja[0] . ' (' . $franja[1] . ')') . '

<br>

<strong>Dirección:</strong>
' . $esc($p['direccion']) . '

' . (!empty($p['barrio']) ? '<br><strong>Barrio:</strong> ' . $esc($p['barrio']) : '') . '

<br>

<strong>Ciudad:</strong>
' . $esc($p['ciudad']) . '

</div>

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

/**
 * Envía la factura al correo registrado en el pedido.
 * Devuelve [bool $ok, string $mensaje, string $detalleTecnico].
 */
function enviarFacturaPorCorreo(PDO $conn, string $orden, int $idCliente): array
{
    $d = cargarPedidoWeb($conn, $orden, $idCliente);

    if (!$d) {
        return [false, 'No encontramos ese pedido.', ''];
    }

    $cfg = is_file(__DIR__ . '/mail_config.php')
        ? require __DIR__ . '/mail_config.php'
        : [];

    if (empty($cfg['usuario']) || empty($cfg['clave'])) {
        return [
            false,
            'No pudimos enviar la factura por correo.',
            'El correo de la tienda no está configurado: completa tienda/mail_config.php'
        ];
    }

    $lib = __DIR__ . '/../lib/PHPMailer/src/';

    foreach (['Exception', 'PHPMailer', 'SMTP'] as $archivo) {

        if (!is_file($lib . $archivo . '.php')) {
            return [
                false,
                'No pudimos enviar la factura por correo.',
                'Falta la librería PHPMailer en lib/PHPMailer/src/'
            ];
        }

        require_once $lib . $archivo . '.php';
    }

    try {

        $pdf = generarFacturaPDF($d);
        $puerto = (int)($cfg['puerto'] ?? 587);

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $cfg['host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['usuario'];
        $mail->Password = $cfg['clave'];

        $mail->SMTPSecure = $puerto === 465
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = $puerto;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 15;

        $mail->setFrom(
            $cfg['remitente'] ?: $cfg['usuario'],
            $cfg['nombre'] ?? NEGOCIO_NOMBRE
        );

        $mail->addAddress(
            $d['pedido']['correo_factura'],
            $d['pedido']['cliente_nombre']
        );

        $mail->isHTML(true);

        $mail->Subject =
            'Tu factura de ' . NEGOCIO_NOMBRE .
            ' - Pedido ' . $orden;

        $mail->Body = cuerpoCorreoFactura($d);

        $mail->AltBody =
            'Gracias por tu compra en ' . NEGOCIO_NOMBRE . '. ' .
            'Tu pedido ' . $orden .
            ' fue recibido. Adjuntamos tu factura en PDF.';

        /*
         * ========================================================
         * LOGO INCRUSTADO EN EL CORREO
         * ========================================================
         *
         * Se utiliza CID para que Gmail, Outlook y otros clientes
         * puedan mostrar el logo sin necesitar acceso a localhost.
         */
        $logoPath = __DIR__ . '/../img_logos/favicon.png';

        if (is_file($logoPath)) {

            $mail->addEmbeddedImage(
                $logoPath,
                'dulcemicro_logo',
                'logo-principal.png',
                PHPMailer::ENCODING_BASE64,
                'image/png'
            );
        }

        /*
         * ========================================================
         * FACTURA PDF ADJUNTA
         * ========================================================
         */

        $mail->addStringAttachment(
            $pdf,
            'Factura-' . $orden . '.pdf',
            PHPMailer::ENCODING_BASE64,
            'application/pdf'
        );

        $mail->send();

    } catch (\Throwable $e) {

        error_log(
            'Factura por correo (' . $orden . '): ' .
            $e->getMessage()
        );

        return [
            false,
            'No pudimos enviar la factura por correo.',
            $e->getMessage()
        ];
    }

    $conn->prepare(
        'UPDATE pedido_web
            SET factura_enviada = 1
          WHERE numero_orden = ?'
    )->execute([$orden]);

    return [true, 'Factura enviada.', ''];
}
