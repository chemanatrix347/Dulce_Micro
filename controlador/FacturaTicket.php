<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}

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
} catch (\Throwable $e) {
    error_log('Tirilla POS: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo cargar la tirilla.');
}

$f = $datos['factura'];
$subtotal = (float) $f['subtotal'];
$envio = (float) ($f['costo_envio'] ?? 0);
$total = $subtotal + $envio;

$esc = static function ($valor): string {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$moneda = static function ($valor): string {
    return '$ ' . number_format((float) $valor, 0, ',', '.');
};

$fechaOriginal = (string) ($f['fecha_pedido'] ?? '');
$fechaTimestamp = $fechaOriginal !== '' ? strtotime($fechaOriginal) : false;
$fecha = $fechaTimestamp !== false
    ? date('d/m/Y', $fechaTimestamp)
    : $fechaOriginal;

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tirilla <?= $esc($datos['numero']) ?> - Dulce Micro</title>

<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 20px 0;
        background: #faf0f8;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
    }

    .acciones {
        text-align: center;
        margin-bottom: 20px;
    }

    .acciones button {
        padding: 12px 20px;
        border: 0;
        border-radius: 10px;
        background: #79509b;
        color: white;
        font-weight: bold;
        cursor: pointer;
    }

    .papel {
        width: 57mm;
        margin: 0 auto;
        padding: 3mm 4.5mm;
        background: white;
    }

    .ticket {
        width: 48mm;
        font-size: 9pt;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    h1 {
        margin: 0 0 2mm;
        font-size: 14pt;
    }

    p { margin: 0 0 2mm; }
    .centrado { text-align: center; }
    .derecha { text-align: right; }
    .pequeno { font-size: 8pt; }
    .total { font-size: 12pt; font-weight: bold; }

    hr {
        border: 0;
        border-top: 1px dashed black;
        margin: 3mm 0;
    }

    .bloque-total, footer { break-inside: avoid; }

    @page {
        size: auto;
        margin: 0;
    }

    @media print {
        html, body {
            width: 57mm;
            padding: 0;
            margin: 0;
            background: white;
        }

        .acciones { display: none !important; }
        .papel { margin: 0; }
    }
</style>
</head>

<body>
<div class="acciones">
    <button type="button" onclick="window.print()">
        Imprimir tirilla
    </button>
</div>

<main class="papel">
<article class="ticket" id="tirillaPOS">
    <header class="centrado">
        <h1>DULCE MICRO</h1>
        <p>Repostería artesanal</p>
        <p class="pequeno">COMPROBANTE DE VENTA</p>
        <p><strong>N.º <?= $esc($datos['numero']) ?></strong></p>
    </header>

    <hr>

    <p><strong>Fecha:</strong> <?= $esc($fecha) ?></p>
    <p><strong>Cliente:</strong> <?= $esc($f['nombre_cliente']) ?></p>

    <?php if (!empty($f['numero_documento'])): ?>
        <p>
            <strong>Documento:</strong>
            <?= $esc($f['numero_documento']) ?>
        </p>
    <?php endif; ?>

    <hr>

    <p><strong>DETALLE DE COMPRA</strong></p>
    <p><?= $esc($f['nombre_producto']) ?></p>
    <p>
        <?= $esc($f['cantidad']) ?> ×
        <?= $esc($moneda($f['precio_unitario'])) ?>
    </p>
    <p class="derecha">
        <strong><?= $esc($moneda($subtotal)) ?></strong>
    </p>

    <hr>

    <div class="bloque-total">
        <p>Subtotal</p>
        <p class="derecha"><?= $esc($moneda($subtotal)) ?></p>

        <?php if ($envio > 0): ?>
            <p>Envío</p>
            <p class="derecha"><?= $esc($moneda($envio)) ?></p>
        <?php endif; ?>

        <p class="total">TOTAL COP</p>
        <p class="total derecha"><?= $esc($moneda($total)) ?></p>
    </div>

    <hr>

    <p>
        <strong>Método de pago:</strong>
        <?= $esc($f['metodo_pago']) ?>
    </p>

    <?php if (
    stripos((string) ($f['metodo_pago'] ?? ''), 'efectivo') !== false
    && isset($f['efectivo_recibido'], $f['cambio'])
): ?>
    <p><strong>Efectivo recibido:</strong></p>
    <p class="derecha">
        <?= $esc($moneda($f['efectivo_recibido'])) ?>
    </p>

    <p><strong>Vueltos:</strong></p>
    <p class="derecha">
        <strong><?= $esc($moneda($f['cambio'])) ?></strong>
    </p>
<?php endif; ?>

    <?php if (!empty($f['estado_pago'])): ?>
        <p>
            <strong>Estado:</strong>
            <?= $esc($f['estado_pago']) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($f['notas'])): ?>
        <hr>
        <p><strong>DEDICATORIA / NOTAS</strong></p>
        <p><?= nl2br($esc($f['notas'])) ?></p>
    <?php endif; ?>

    <footer class="centrado">
        <hr>
        <p><strong>¡Gracias por tu compra!</strong></p>
        <p class="pequeno">Hecho con mucho cariño.</p>
        <p class="pequeno">¡Esperamos verte pronto!</p>
    </footer>
</article>
</main>
</body>
</html>