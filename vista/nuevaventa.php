<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}

// ---- GUARDIA: sin usuario logueado no se entra ----
if (empty($_SESSION['id_usuario'])) {
    header("Location: /Dulce_Micro/vista/login.php");
    exit();
}

$tiempo_limite = 900;
if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > $tiempo_limite)) {
    session_unset();
    session_destroy();
    header("Location: /Dulce_Micro/vista/login.php?expirada=1");
    exit();
}
$_SESSION['ultima_actividad'] = time();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/../modelo/producto.php";
require_once __DIR__ . "/../modelo/clientes.php";
require_once __DIR__ . "/../modelo/metodospago.php";
require_once __DIR__ . "/../modelo/estadopedido.php";
require_once __DIR__ . "/../modelo/tipodocumento.php";
require_once __DIR__ . "/../modelo/estadopago.php";

$idPedidoResultado = (int)($_GET['id_pedido'] ?? 0);
$avisoCorreoPOS = $_SESSION['pos_factura_resultados'][$idPedidoResultado] ?? null;
unset($_SESSION['pos_factura_resultados'][$idPedidoResultado]);

$productoModel = new Producto();
$clienteModel = new Cliente();
$metodoPagoModel = new MetodoPago();
$estadoPedidoModel = new EstadoPedido();
$tipoDocumentoModel = new TipoDocumento();
$estadoPagoModel = new EstadoPago();

$listaProductos = $productoModel->leer_productos();
$listaClientes = $clienteModel->leer_clientes();
$listaMetodosPago = $metodoPagoModel->leer_metodos_pago();
$listaEstadosPedido = $estadoPedidoModel->leer_estados_pedido();
$listaTiposDocumento = $tipoDocumentoModel->leer_tipos_documento();
$listaEstadosPago = $estadoPagoModel->leer_estados_pago();

// Agrupar variantes (producto+sabor+tamaño) por nombre base, para el paso 1
// $listaProductos ya trae el campo "stock" (ver modelo/producto.php modificado)
$productosAgrupados = [];
foreach ($listaProductos as $p) {
    $productosAgrupados[$p['nombre_producto']][] = $p;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Punto de Venta - Dulce Micro</title>
<link rel="icon" type="image/png" href="/Dulce_Micro/img_logos/favicon.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<script src="https://kit.fontawesome.com/223d4f4ee1.js" crossorigin="anonymous"></script>
<style>
:root{--dm-morado-fuerte:#79509b;--dm-rosa-fuerte:#b95e88;--dm-error:#ae3543;--ink:#30283a;--muted:#766d80;--border:#e8e1ec;--soft:#f5eff9}
*{box-sizing:border-box}body{margin:0;background:#f7f5f9;color:var(--ink);font-family:system-ui,-apple-system,"Segoe UI",sans-serif;min-height:100vh}
button,input,a{touch-action:manipulation}button{font:inherit}button,a,input{outline-offset:4px}button:focus-visible,a:focus-visible{outline:3px solid #b98bd3}button:disabled{cursor:not-allowed;opacity:.5}
.pos-topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 32px;background:white;border-bottom:1px solid var(--border)}
.brand{display:flex;align-items:center;gap:12px}.brand img{width:44px;height:44px;object-fit:contain}.brand h4{font-size:18px;font-weight:800;margin:0}.brand small{color:var(--muted);font-size:12px}.top-actions{display:flex;gap:10px;align-items:center;font-size:13px}.top-actions .btn{border:1px solid var(--border);color:var(--ink);border-radius:10px;padding:9px 14px}
.workspace{max-width:1440px;padding:30px 32px 48px;margin:auto}.page-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}.page-title h1{font-size:28px;font-weight:800;margin:0 0 5px;letter-spacing:-1px}.page-title p{color:var(--muted);margin:0}.badge-pos{background:#efe6f5;color:#79509b;border-radius:30px;padding:8px 14px;font-size:12px;font-weight:700}
.pos-layout{display:grid;grid-template-columns:minmax(0,1fr) 310px;gap:24px;align-items:start}.pos-steps{display:flex;gap:8px;margin-bottom:20px}.pos-step-pill{flex:1;padding:12px 8px;border-radius:12px;border:1px solid var(--border);background:white;color:var(--muted);font-size:12px;text-align:center;font-weight:650}.pos-step-pill b{display:block;font-size:15px;margin-bottom:2px}.pos-step-pill.active{background:var(--dm-morado-fuerte);color:white;border-color:var(--dm-morado-fuerte)}.pos-step-pill.done{background:#eee5f5;color:#79509b;border-color:#ddcce9}.pos-step{display:none;background:white;border:1px solid var(--border);border-radius:20px;padding:28px;min-height:400px}.pos-step.active{display:block}.pos-step h5{font-size:21px;font-weight:750;margin:0 0 8px}.step-caption{color:var(--muted);font-size:14px;margin-bottom:24px}.section-label{font-size:11px;text-transform:uppercase;letter-spacing:1.7px;font-weight:800;color:var(--dm-morado-fuerte);margin-bottom:12px}.grid-productos{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-top:16px}.btn-pos{border:1.5px solid var(--border);background:white;border-radius:14px;padding:18px 14px;width:100%;min-height:88px;text-align:left;color:var(--ink);font-weight:650;transition:border-color .15s,background .15s}.btn-pos:hover{border-color:#b592cd;background:#faf7fc}.btn-pos.selected{border-color:var(--dm-morado-fuerte);background:#f0e6f7;box-shadow:inset 0 0 0 1px var(--dm-morado-fuerte);color:#633882}.btn-pos small{display:block;font-size:12px;color:var(--muted);font-weight:500;margin-top:8px}.btn-pos.agotado{background:#f4f3f5;opacity:.6}.agotado-label{color:var(--dm-error)!important}
.form-control{border:1.5px solid var(--border);border-radius:11px;padding:12px 14px;font-size:15px;background:#fdfcfe}.form-control:focus{border-color:#a77ac0;box-shadow:0 0 0 3px #f1e7f8}.form-label{font-weight:650;font-size:13px}.cliente-list{max-height:320px;overflow:auto;margin-top:12px}.cliente-item{padding:15px;border:1px solid var(--border);border-radius:12px;margin-bottom:8px;background:white;width:100%;text-align:left}.cliente-item.selected{border-color:#79509b;background:#f0e6f7}.cliente-item:hover{background:#faf6fc}
.nav-buttons{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:28px;padding-top:22px;border-top:1px solid var(--border)}.btn-volver,.btn-continuar{border-radius:11px;padding:13px 20px;font-weight:700;min-height:48px}.btn-volver{border:1px solid var(--border);background:white;color:#62576c}.btn-continuar{border:0;background:var(--dm-morado-fuerte);color:white}.btn-continuar:hover{background:#633882}.btn-continuar:disabled{opacity:.65}
#bloqueCantidad{margin-top:24px;border-radius:14px;background:var(--soft);padding:20px}#bloqueCantidad:before{content:'Cantidad de unidades';font-weight:700;font-size:14px}.qty-control{display:flex;gap:22px;align-items:center;margin-top:14px}.qty-control button{width:46px;height:46px;border-radius:12px;border:1px solid #d9c8e4;background:white;font-size:24px;color:#79509b}.qty-control span{font-size:25px;font-weight:800;min-width:28px;text-align:center}
.payment-section{border:1px solid var(--border);border-radius:15px;padding:20px;margin-top:20px}#bloqueEfectivo{background:#f9f6fc;border-radius:15px;padding:20px;margin:20px 0}#bloqueEfectivo h6{font-size:13px;font-weight:750}.billetes{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}.billetes button{border:1px solid #d8c7e5;background:white;border-radius:8px;padding:10px 12px;font-size:13px;font-weight:650;color:#79509b}.monto-display{font-size:32px;font-weight:800;margin:12px 0;color:#79509b}.keypad{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;max-width:300px;margin:14px auto}.keypad button{padding:12px;background:white;border:1px solid var(--border);border-radius:10px;font-size:19px}.cash-label{text-align:center;color:var(--muted);font-size:12px;margin:18px 0 0}#montoRecibido{text-align:center}#cambioDisplay{padding:12px;background:white;border-radius:10px}
.ticket{position:sticky;top:24px;background:white;border:1px solid var(--border);border-radius:20px;overflow:hidden}.ticket-head{padding:22px;background:#f0e8f6;border-bottom:1px solid var(--border)}.ticket-head h2{font-size:17px;font-weight:800;margin:0}.ticket-head small{color:var(--muted)}.ticket-body{padding:22px}.ticket-label{font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:10px}.ticket-block{padding:18px 0;border-bottom:1px dashed var(--border);font-size:14px}.ticket-block:first-child{padding-top:0}.ticket-total{padding-top:20px}.ticket-total .monto-display{margin:4px 0}.ticket-note{font-size:12px;line-height:1.6;color:var(--muted);background:#faf8fc;padding:16px 22px;margin:0}#resumenProducto{font-size:15px;line-height:1.6}#resumenCliente{line-height:1.6}#correoResumen{overflow-wrap:anywhere}.alert{border-radius:12px;font-size:14px}.review-box{background:#f7f1fb;border:1px solid #e4d5ee;border-radius:14px;padding:20px;line-height:1.8;margin-bottom:22px}.review-box strong{color:#633882}.empty-note{color:var(--muted);padding:20px;text-align:center}.search-label{display:block;color:var(--muted);font-size:12px;margin-bottom:8px}
@media(min-width:1100px){#bloqueEfectivo .keypad{max-width:240px}.cash-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:center}}
@media(max-width:1000px){.pos-layout{grid-template-columns:minmax(0,1fr) 260px}.workspace{padding:24px 18px}.pos-step{padding:22px}.pos-step-pill{padding:10px 4px;font-size:11px}.pos-topbar{padding:16px 20px}}
@media(max-width:760px){.pos-layout{grid-template-columns:1fr}.ticket{position:static}.pos-topbar{align-items:flex-start;flex-wrap:wrap}.top-actions{flex-wrap:wrap}.workspace{padding:22px 14px}.page-title h1{font-size:25px}.badge-pos{display:none}.pos-step{padding:20px;min-height:0}.pos-steps{gap:4px}.pos-step-pill{font-size:10px;border-radius:9px;padding:9px 2px}.grid-productos{grid-template-columns:repeat(2,minmax(0,1fr))}.btn-continuar,.btn-volver{padding:12px 14px;font-size:14px}.ticket-head{padding:18px}.ticket-body{padding:18px}.top-actions span{width:100%}}

/* Identidad Dulce Micro: rosa y lila en todas las secciones. */
:root{--dm-morado-fuerte:#79509b;--dm-rosa-fuerte:#a34673;--dm-error:#a34673;--ink:#49314f;--muted:#796780;--border:#e9dcec;--soft:#f8eef8}
body{background:radial-gradient(ellipse at top left,#fce1ed,transparent 50%),radial-gradient(ellipse at bottom right,#eadcf7,transparent 50%),#faf5fb}
.pos-topbar{background:linear-gradient(110deg,#f7a8c4,#dfadd8 55%,#c9a0dc);color:#49314f;border-bottom:1px solid #dcb4df;box-shadow:0 5px 20px #79509b15}.brand small{color:#57365f}.brand img{background:#fff;border-radius:12px;padding:5px}.top-actions .btn{color:#49314f;background:#ffffff75;border-color:#ffffffb3}.top-actions .btn:hover{background:#fff}.badge-pos{background:#eddcf5;color:#6b3f87}.page-title h1{color:#654177}
[data-step="1"],[data-step-pill="1"]{--section:#d887ac;--section-dark:#8d3e67;--tint:#fff0f6;--line:#efc5d9}
[data-step="2"],[data-step-pill="2"]{--section:#b58ace;--section-dark:#6f438d;--tint:#f4eafa;--line:#dbc3e9}
[data-step="3"],[data-step-pill="3"]{--section:#d997bc;--section-dark:#91446d;--tint:#fceef7;--line:#edc7df}
[data-step="4"],[data-step-pill="4"]{--section:#aa80c4;--section-dark:#70458d;--tint:#f1e9f8;--line:#d6bee7}
[data-step="5"],[data-step-pill="5"]{--section:#bf8cc6;--section-dark:#7b4385;--tint:#f7eaf8;--line:#e4c5e8}
.pos-step-pill{background:var(--tint);color:var(--section-dark);border-color:var(--line);border-top:4px solid var(--section);box-shadow:0 3px 10px #79509b08}.pos-step-pill b{margin:auto auto 6px;background:white;border-radius:9px;width:30px;line-height:30px}.pos-step-pill.active{background:var(--section-dark);border-color:var(--section-dark);color:#fff;box-shadow:0 5px 14px #79509b22;outline:2px solid var(--section);outline-offset:2px}.pos-step-pill.active b{color:var(--section-dark)}.pos-step-pill.done{background:var(--tint);color:var(--section-dark);border-color:var(--line);border-top-color:var(--section)}
.pos-step{border-top:5px solid var(--section);background:linear-gradient(180deg,var(--tint),#fff 155px);box-shadow:0 8px 28px #79509b10}.section-label{display:inline-block;padding:6px 10px;border-radius:7px;background:var(--tint);color:var(--section-dark);border:1px solid var(--line)}.pos-step h5{color:var(--section-dark)}.pos-step .btn-continuar{background:var(--section-dark);color:#fff;box-shadow:0 4px 12px #79509b18}.pos-step .btn-continuar:hover{filter:brightness(.92)}.pos-step .btn-pos:hover{border-color:var(--section);background:var(--tint)}.pos-step .btn-pos.selected{border-color:var(--section-dark);box-shadow:inset 0 0 0 1px var(--section-dark);background:var(--tint);color:var(--section-dark)}
#gridProductosBase .btn-pos{border-left:5px solid #dc91b3;background:#fff2f8}#gridProductosBase .btn-pos:nth-child(even){border-left-color:#b58ace;background:#f5eefb}#gridProductosBase .btn-pos.agotado{background:#f3eff4;border-left-color:#b3a6b8}
#bloqueCantidad{background:#f2eafa;border:1px solid #dbc3e9}.qty-control button{color:#70458d;border-color:#dbc3e9}.cliente-item.selected{background:#fceef7;border-color:#a85784;box-shadow:inset 3px 0 #a85784}.cliente-item:hover{background:#fff4fa}#btnClienteExistente{border-top:4px solid #dc91b3}#btnClienteNuevo{border-top:4px solid #b58ace}
#bloqueEfectivo{background:#f8eff9;border:1px solid #dfc5e7}#bloqueEfectivo h6{color:#70458d}.billetes button{background:#ecdaf4;color:#643b7d;border-color:#d5b6e3}.billetes button:hover{background:#dfc3ed}.keypad button:hover{background:#fce7f2;border-color:#dba5c1}#montoRecibido{color:#70458d}.payment-section{background:#fff4f9;border-color:#efc5d9}
.ticket{box-shadow:0 10px 30px #79509b12}.ticket-head{background:linear-gradient(110deg,#f7a8c4,#c9a0dc);color:#49314f;border:0}.ticket-head small{color:#57365f}.ticket-block{padding-left:12px;border-left:3px solid #dc91b3;margin-bottom:14px}.ticket-block:nth-child(2){border-left-color:#b58ace}.ticket-block:nth-child(3){border-left-color:#d6a1cf}.ticket-total{padding:18px;border-radius:14px;background:linear-gradient(120deg,#92517f,#70458d);color:#fff}.ticket-total .ticket-label{color:#fff0fa}.ticket-total .monto-display{color:#fff}.ticket-note{background:#f9edf6;color:#735379}.review-box{background:#f5eaf9;border-color:#dbc3e9}.review-box strong{color:#70458d}
#gridEstadosPedido .btn-pos,#gridEstadosPago .btn-pos{background:#f5eefb;color:#6f438d;border-color:#dbc3e9;border-left:5px solid #b58ace}#gridEstadosPedido .btn-pos:nth-child(odd),#gridEstadosPago .btn-pos:nth-child(odd){background:#fff0f6;color:#8d3e67;border-color:#efc5d9;border-left-color:#dc91b3}#gridEstadosPedido .btn-pos.selected,#gridEstadosPago .btn-pos.selected{box-shadow:inset 0 0 0 2px #70458d;border-color:#70458d;background:#edddf5;color:#59366f}
.alert-success{background:#f1e5f8;color:#633b7d;border-color:#d9bce8}.alert-warning{background:#fce9f2;color:#8a4167;border-color:#ebbad3}.btn-success{background:#79509b;border-color:#79509b}.btn-success:hover{background:#653f84;border-color:#653f84}#btnConfirmarVenta{background:linear-gradient(110deg,#a34673,#79509b);color:white}
@media(max-width:760px){.pos-step-pill.active{outline-offset:0;outline-width:1px}.pos-step-pill b{width:25px;line-height:25px}.ticket-block{padding-left:10px}}
</style>
</head>
<body>

<header class="pos-topbar">
 <div class="brand"><img src="/Dulce_Micro/img_logos/favicon.png" alt=""><div><h4>Dulce Micro</h4><small>Repostería artesanal · Punto de venta</small></div></div>
 <div class="top-actions"><span>Vendedor: <?= htmlspecialchars($_SESSION['Nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></span><a href="/Dulce_Micro/index.php" class="btn">Menú principal</a><a href="/Dulce_Micro/vista/logout.php" class="btn">Salir</a></div>
</header>
<main class="workspace">
 <div class="page-title"><div><h1>Nueva venta</h1><p>Una compra, paso a paso.</p></div><span class="badge-pos">DULCE MICRO / CAJA</span></div>
 <div class="pos-layout"><div>
 <nav class="pos-steps" aria-label="Pasos de la venta">
<div class="pos-step-pill" data-step-pill="1"><b>01</b>Producto</div><div class="pos-step-pill" data-step-pill="2"><b>02</b>Presentación</div><div class="pos-step-pill" data-step-pill="3"><b>03</b>Cliente</div><div class="pos-step-pill" data-step-pill="4"><b>04</b>Pago</div><div class="pos-step-pill" data-step-pill="5"><b>05</b>Confirmación</div></nav>
<div class="pos-panel">

<?php if (isset($_GET['exito']) && isset($_GET['id_pedido'])): ?>
    <div class="alert alert-success d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa-solid fa-circle-check"></i> Venta registrada correctamente (Pedido #<?= htmlspecialchars((string)($avisoCorreoPOS['numero'] ?? $idPedidoResultado), ENT_QUOTES, 'UTF-8') ?>).</span>
        <a href="/Dulce_Micro/controlador/FacturaPDF.php?id_pedido=<?= (int) $_GET['id_pedido'] ?>" target="_blank" class="btn btn-sm btn-success">
            <i class="fa-solid fa-print"></i> Imprimir factura
        </a>
        <a
    href="/Dulce_Micro/controlador/FacturaTicket.php?id_pedido=<?= (int) $_GET['id_pedido'] ?>"
    target="_blank"
    rel="noopener"
    class="btn btn-sm btn-success"
>
    <i class="fa-solid fa-print"></i>
    Imprimir tirilla
</a>
    </div>
<?php endif; ?>

<?php if (isset($_GET['exito']) && $avisoCorreoPOS): ?>
    <?php
    $mensajesCorreo = [
        'enviada' => 'La factura fue enviada al correo del cliente.',
        'sin_correo' => 'No se envió la factura: el cliente no tiene un correo válido registrado. Puedes imprimirla.',
        'error' => 'Venta registrada, pero no se pudo enviar la factura por correo. Puedes imprimirla.',
    ];
    $estadoCorreo = $avisoCorreoPOS['estado'];
    ?>
    <div role="status" class="alert <?= $estadoCorreo === 'enviada' ? 'alert-success' : 'alert-warning' ?>">
        <?= htmlspecialchars($mensajesCorreo[$estadoCorreo] ?? $mensajesCorreo['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['sin_stock'])): ?>
    <div class="alert alert-success" style="border-left-color: var(--dm-error); background:#fdeceb; color:#8a2c22;">
        <i class="fa-solid fa-triangle-exclamation"></i> No hay suficiente stock disponible para completar esa venta.
    </div>
<?php endif; ?>

    <!-- PASO 1: PRODUCTO -->
    <div class="pos-step" data-step="1">
        <div class="section-label">Paso 01 / Catálogo</div><h5>¿Qué desea el cliente?</h5><p class="step-caption">Selecciona un producto para ver sus presentaciones disponibles.</p><label class="search-label" for="buscarProducto">Buscar producto</label><input id="buscarProducto" class="form-control" placeholder="Escribe el nombre del producto…" oninput="filtrarProductos()">
        <div class="grid-productos" id="gridProductosBase"></div>
    </div>

    <!-- PASO 2: SABOR -->
    <div class="pos-step" data-step="2">
        <div class="section-label">Paso 02 / Presentación</div><h5>Elige sabor y cantidad</h5><p class="step-caption" id="grupoElegido">Selecciona una presentación disponible.</p>
        <div class="grid-productos" id="gridSabores"></div>

        <div id="bloqueCantidad" style="display:none;">
            <div class="qty-control">
                <button type="button" onclick="cambiarCantidad(-1)">−</button>
                <span id="cantidadValor">1</span>
                <button type="button" onclick="cambiarCantidad(1)">+</button>
            </div>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn-volver" onclick="irPaso(1)"><i class="fa-solid fa-arrow-left"></i> Volver</button>
            <button type="button" class="btn-continuar" id="btnContinuarPaso2" style="display:none" onclick="irPaso(3)">Continuar <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- PASO 3: CLIENTE -->
    <div class="pos-step" data-step="3">
        <div class="section-label">Paso 03 / Cliente</div><h5>¿Para quién es la compra?</h5><p class="step-caption">Selecciona un cliente o registra uno nuevo. Su correo se usará para enviar la factura.</p>
        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn-pos" style="width:auto; flex:1" id="btnClienteExistente" onclick="modoCliente('existente')">
                <i class="fa-solid fa-magnifying-glass"></i> Cliente existente
            </button>
            <button type="button" class="btn-pos" style="width:auto; flex:1" id="btnClienteNuevo" onclick="modoCliente('nuevo')">
                <i class="fa-solid fa-user-plus"></i> Cliente nuevo
            </button>
        </div>

        <div id="bloqueClienteExistente">
            <input type="text" class="form-control form-control-lg mb-2" placeholder="Buscar por nombre o documento..." id="buscarCliente" oninput="filtrarClientes()">
            <div class="cliente-list" id="listaClientesPos"></div>
        </div>

        <div id="bloqueClienteNuevo" class="row g-3" style="display:none;">
            <div class="col-md-6">
                <label class="form-label" for="nuevoNombre">Nombre completo</label>
                <input type="text" class="form-control form-control-lg" id="nuevoNombre">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de documento</label>
                <div class="d-flex gap-2 flex-wrap" id="gridTipoDocumento"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="nuevoNumeroDocumento">Número de documento</label>
                <input type="text" class="form-control form-control-lg" id="nuevoNumeroDocumento">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="nuevoCorreo">Correo (opcional)</label>
                <input type="email" class="form-control form-control-lg" id="nuevoCorreo" autocomplete="email" placeholder="cliente@correo.com">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="nuevoTelefono">Teléfono (opcional)</label>
                <input type="tel" class="form-control form-control-lg" id="nuevoTelefono" autocomplete="tel">
            </div>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn-volver" onclick="irPaso(2)"><i class="fa-solid fa-arrow-left"></i> Volver</button>
            <button type="button" class="btn-continuar" onclick="validarClienteYContinuar()">Continuar <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- PASO 4: PAGO -->
    <div class="pos-step" data-step="4">
        <div class="section-label">Paso 04 / Cobro</div><h5>Recibe el pago</h5><p class="step-caption">Elige el método y registra el estado del pago.</p>
        <h5 class="mb-2">Método de pago</h5>
        <div class="d-flex gap-2 flex-wrap mb-3" id="gridMetodosPago"></div>

        <div id="bloqueEfectivo" style="display:none;">
            <h6 class="mb-2">Billetes rápidos</h6>
            <div class="billetes" id="gridBilletes"></div>
            <p class="cash-label">Dinero recibido</p><div class="monto-display" id="montoRecibido">$0</div>
            <div class="keypad">
                <button type="button" onclick="tecla('1')">1</button>
                <button type="button" onclick="tecla('2')">2</button>
                <button type="button" onclick="tecla('3')">3</button>
                <button type="button" onclick="tecla('4')">4</button>
                <button type="button" onclick="tecla('5')">5</button>
                <button type="button" onclick="tecla('6')">6</button>
                <button type="button" onclick="tecla('7')">7</button>
                <button type="button" onclick="tecla('8')">8</button>
                <button type="button" onclick="tecla('9')">9</button>
                <button type="button" onclick="tecla('000')">000</button>
                <button type="button" onclick="tecla('0')">0</button>
                <button type="button" onclick="borrarTecla()"><i class="fa-solid fa-delete-left"></i></button>
            </div>
            <div class="text-center fw-bold" id="cambioDisplay" style="font-size:1.3rem;"></div>
        </div>

        <div class="payment-section"><h5 class="mb-2">Estado del pago</h5>
        <div class="d-flex gap-2 flex-wrap mb-4" id="gridEstadosPago"></div>

        </div><div class="nav-buttons"><button type="button" class="btn-volver" onclick="irPaso(3)">Volver a cliente</button><button type="button" class="btn-continuar" onclick="validarPagoYContinuar()">Revisar venta →</button></div>
    </div>
    <div class="pos-step" data-step="5">
      <div class="section-label">Paso 05 / Confirmación</div><h5>Revisa y finaliza</h5><p class="step-caption">Comprueba el resumen antes de registrar la venta.</p>
      <div class="review-box" id="revisionVenta"></div>
      <h5 class="mb-2">Estado del pedido</h5>
        <div class="d-flex gap-2 flex-wrap mb-4" id="gridEstadosPedido"></div>

        <div class="nav-buttons">
            <button type="button" class="btn-volver" onclick="irPaso(4)"><i class="fa-solid fa-arrow-left"></i> Volver al pago</button>
            <button type="button" class="btn-continuar" id="btnConfirmarVenta" onclick="confirmarVenta()"><i class="fa-solid fa-check"></i> Registrar venta</button>
        </div>
    </div>

</div>

</div>
<aside class="ticket" aria-label="Resumen de la venta"><div class="ticket-head"><h2>Tu venta actual</h2><small>El resumen se actualiza al elegir</small></div><div class="ticket-body">
 <div class="ticket-block"><div class="ticket-label">Producto</div><strong id="resumenProducto">Sin producto seleccionado</strong></div>
 <div class="ticket-block"><div class="ticket-label">Cliente</div><span id="resumenCliente">Por seleccionar</span></div>
 <div class="ticket-block"><div class="ticket-label">Pago</div><span id="pagoResumen">Por definir</span></div>
 <div class="ticket-total"><div class="ticket-label">Total de la venta</div><div class="monto-display" id="resumenTotal">$0</div></div>
 </div><p class="ticket-note" id="correoResumen">La factura se enviará al correo registrado del cliente.</p></aside>
</div></main>
<form id="formVenta" action="../controlador/VentaController.php" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="id_producto" id="inputIdProducto">
    <input type="hidden" name="cantidad" id="inputCantidad">
    <input type="hidden" name="cliente_tipo" id="inputClienteTipo">
    <input type="hidden" name="id_cliente" id="inputIdCliente">
    <input type="hidden" name="nuevo_nombre" id="inputNuevoNombre">
    <input type="hidden" name="nuevo_id_tipo_documento" id="inputNuevoTipoDocumento">
    <input type="hidden" name="nuevo_numero_documento" id="inputNuevoNumeroDocumento">
    <input type="hidden" name="nuevo_correo" id="inputNuevoCorreo">
    <input type="hidden" name="nuevo_telefono" id="inputNuevoTelefono">
    <input type="hidden" name="id_metodo_pago" id="inputIdMetodoPago">
    <input type="hidden" name="fecha_pago" value="<?= date('Y-m-d') ?>">
    <input type="hidden" name="id_estado_pedido" id="inputIdEstadoPedido">
    <input type="hidden" name="id_estado_pago" id="inputIdEstadoPago">
    <input type="hidden" name="efectivo_recibido" id="inputEfectivoRecibido">
</form>

<script>
const productosAgrupados = <?= json_encode($productosAgrupados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const listaClientes = <?= json_encode($listaClientes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const listaMetodosPago = <?= json_encode($listaMetodosPago, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const listaEstadosPedido = <?= json_encode($listaEstadosPedido, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const listaTiposDocumento = <?= json_encode($listaTiposDocumento, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const listaEstadosPago = <?= json_encode($listaEstadosPago, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

let estado = {
    grupoActual: null,
    producto: null,
    cantidad: 1,
    clienteTipo: 'existente',
    cliente: null,
    nuevoTipoDocumento: null,
    metodoPago: null,
    estadoPedido: null,
    estadoPago: null,
    montoRecibido: 0
};

function irPaso(n) {
    document.querySelectorAll('.pos-step').forEach(el => el.classList.remove('active'));
    document.querySelector('.pos-step[data-step="' + n + '"]').classList.add('active');
    document.querySelectorAll('.pos-step-pill').forEach(el => {
        const p = parseInt(el.dataset.stepPill);
        el.classList.toggle('active', p === n);
        el.classList.toggle('done', p < n);
    });
    actualizarResumenPago();
    document.querySelectorAll('.pos-step-pill').forEach(el => {
        if (Number(el.dataset.stepPill) === n) el.setAttribute('aria-current','step');
        else el.removeAttribute('aria-current');
    });
    const active = document.querySelector('.pos-step.active');
    active.setAttribute('tabindex', '-1');
    active.focus({preventScroll:true});
    if (window.innerWidth < 761) window.scrollTo({top:0, behavior:'smooth'});
}

function formatoMoneda(valor) {
    return '$' + Number(valor).toLocaleString('es-CO');
}

// ---- Paso 1: productos base ----
// NUEVO: si NINGUNA variante del producto tiene stock, el botón del grupo
// se muestra deshabilitado con la etiqueta "Agotado".
function renderProductosBase() {
    const cont = document.getElementById('gridProductosBase');
    cont.innerHTML = '';
    Object.keys(productosAgrupados).forEach(nombre => {
        const variantes = productosAgrupados[nombre];
        const disponibles = variantes.filter(v => Number(v.stock) > 0);
        const agotadoTotal = disponibles.length === 0;
        const precioMin = Math.min(...variantes.map(v => parseFloat(v.precio_base)));

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-pos' + (agotadoTotal ? ' agotado' : '');
        btn.disabled = agotadoTotal;
        btn.innerHTML = escaparHTML(nombre) + (
            agotadoTotal
                ? '<small class="agotado-label">Agotado</small>'
                : '<small>Desde ' + formatoMoneda(precioMin) + '</small>'
        );
        if (!agotadoTotal) {
            btn.onclick = () => seleccionarGrupo(nombre);
        }
        cont.appendChild(btn);
    });
}

function seleccionarGrupo(nombre) {
    estado.grupoActual = nombre;
    document.getElementById('grupoElegido').textContent = nombre + ' · Elige sabor, tamaño y unidades.';
    estado.producto = null;
    estado.cantidad = 1;
    document.getElementById('cantidadValor').textContent = '1';
    document.getElementById('bloqueCantidad').style.display = 'none';
    document.getElementById('btnContinuarPaso2').style.display = 'none';
    renderSabores();
    irPaso(2);
}

// ---- Paso 2: sabores/variantes ----
// NUEVO: cada variante sin stock se muestra deshabilitada, con "Agotado"
// en vez del precio, y no se puede seleccionar.
function renderSabores() {
    const cont = document.getElementById('gridSabores');
    cont.innerHTML = '';
    const variantes = productosAgrupados[estado.grupoActual] || [];
    variantes.forEach(v => {
        const agotado = Number(v.stock) <= 0;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-pos' + (agotado ? ' agotado' : '');
        btn.disabled = agotado;
        btn.innerHTML = escaparHTML(v.sabor) + (
            agotado
                ? '<small class="agotado-label">Agotado</small>'
                : '<small>' + escaparHTML(v.tamano) + ' — ' + formatoMoneda(v.precio_base) + '</small>'
        );
        if (!agotado) {
            btn.onclick = () => seleccionarVariante(v, btn);
        }
        cont.appendChild(btn);
    });
}

function seleccionarVariante(v, btnEl) {
    document.querySelectorAll('#gridSabores .btn-pos').forEach(b => b.classList.remove('selected'));
    btnEl.classList.add('selected');
    estado.producto = v;
    estado.cantidad = 1;
    document.getElementById('cantidadValor').textContent = '1';
    document.getElementById('bloqueCantidad').style.display = 'block';
    document.getElementById('btnContinuarPaso2').style.display = 'inline-block';
}

// NUEVO: la cantidad ya no puede subir por encima del stock disponible de la variante elegida
function cambiarCantidad(delta) {
    const maximo = estado.producto ? Number(estado.producto.stock) : 999;
    estado.cantidad = Math.max(1, Math.min(maximo, estado.cantidad + delta));
    document.getElementById('cantidadValor').textContent = estado.cantidad;
}

// ---- Paso 3: cliente ----
function modoCliente(tipo) {
    estado.clienteTipo = tipo;
    document.getElementById('bloqueClienteExistente').style.display = tipo === 'existente' ? 'block' : 'none';
    document.getElementById('bloqueClienteNuevo').style.display = tipo === 'nuevo' ? 'flex' : 'none';
    document.getElementById('btnClienteExistente').classList.toggle('selected', tipo === 'existente');
    document.getElementById('btnClienteNuevo').classList.toggle('selected', tipo === 'nuevo');
}

function renderClientes(filtro = '') {
    const cont = document.getElementById('listaClientesPos');
    cont.innerHTML = '';
    const f = filtro.toLowerCase();
    listaClientes
        .filter(c => !f || c.nombre.toLowerCase().includes(f) || String(c.numero_documento || '').includes(f))
        .forEach(c => {
            const div = document.createElement('button');
            div.type = 'button';
            div.className = 'cliente-item';
            div.textContent = c.nombre + ' — ' + c.numero_documento;
            div.onclick = () => seleccionarCliente(c, div);
            cont.appendChild(div);
        });
}

function seleccionarCliente(c, divEl) {
    document.querySelectorAll('.cliente-item').forEach(d => d.classList.remove('selected'));
    divEl.classList.add('selected');
    estado.cliente = c;
}

function filtrarClientes() {
    renderClientes(document.getElementById('buscarCliente').value);
}

function renderTiposDocumento() {
    const cont = document.getElementById('gridTipoDocumento');
    cont.innerHTML = '';
    listaTiposDocumento.forEach(t => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-pos';
        btn.style.width = 'auto';
        btn.style.padding = '.6rem 1rem';
        btn.textContent = t.tipo_documento;
        btn.onclick = () => {
            document.querySelectorAll('#gridTipoDocumento .btn-pos').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            estado.nuevoTipoDocumento = t.id_tipo_documento;
        };
        cont.appendChild(btn);
    });
}

function validarClienteYContinuar() {
    if (estado.clienteTipo === 'existente') {
        if (!estado.cliente) { alert('Selecciona un cliente.'); return; }
    } else {
        const nombre = document.getElementById('nuevoNombre').value.trim();
        const numDoc = document.getElementById('nuevoNumeroDocumento').value.trim();
        if (!nombre || !estado.nuevoTipoDocumento || !numDoc) {
            alert('Completa nombre, tipo y número de documento del cliente nuevo.');
            return;
        }
    }
    const correo = document.getElementById('nuevoCorreo');
    if (estado.clienteTipo === 'nuevo' && !correo.checkValidity()) { correo.reportValidity(); return; }
    irPaso(4);
}

// ---- Paso 4: pago ----
function renderMetodosPago() {
    const cont = document.getElementById('gridMetodosPago');
    cont.innerHTML = '';
    listaMetodosPago.forEach(m => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-pos';
        btn.style.width = 'auto';
        btn.style.flex = '1 1 140px';
        btn.textContent = m.metodo_pago;
        btn.onclick = () => seleccionarMetodoPago(m, btn);
        cont.appendChild(btn);
    });
}

function seleccionarMetodoPago(m, btnEl) {
    document.querySelectorAll('#gridMetodosPago .btn-pos').forEach(b => b.classList.remove('selected'));
    btnEl.classList.add('selected');
    estado.metodoPago = m;
    const esEfectivo = m.metodo_pago.toLowerCase().includes('efectivo');
    document.getElementById('bloqueEfectivo').style.display = esEfectivo ? 'block' : 'none';
    if (esEfectivo) {
        estado.montoRecibido = 0;
        document.getElementById('montoRecibido').textContent = formatoMoneda(0);
        actualizarCambio();
    }
}

const billetesCOP = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
function renderBilletes() {
    const cont = document.getElementById('gridBilletes');
    cont.innerHTML = '';
    billetesCOP.forEach(b => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = formatoMoneda(b);
        btn.onclick = () => {
            estado.montoRecibido += b;
            document.getElementById('montoRecibido').textContent = formatoMoneda(estado.montoRecibido);
            actualizarCambio();
        };
        cont.appendChild(btn);
    });
}

function tecla(n) {
    estado.montoRecibido = parseInt(String(estado.montoRecibido) + n);
    document.getElementById('montoRecibido').textContent = formatoMoneda(estado.montoRecibido);
    actualizarCambio();
}

function borrarTecla() {
    let s = String(estado.montoRecibido);
    s = s.slice(0, -1);
    estado.montoRecibido = s ? parseInt(s) : 0;
    document.getElementById('montoRecibido').textContent = formatoMoneda(estado.montoRecibido);
    actualizarCambio();
}

function calcularTotal() {
    if (!estado.producto) return 0;
    return parseFloat(estado.producto.precio_base) * estado.cantidad;
}

function actualizarCambio() {
    const total = calcularTotal();
    const cambio = estado.montoRecibido - total;
    const el = document.getElementById('cambioDisplay');
    if (estado.montoRecibido === 0) { el.textContent = ''; return; }
    el.textContent = cambio >= 0 ? 'Cambio: ' + formatoMoneda(cambio) : 'Faltan: ' + formatoMoneda(Math.abs(cambio));
    el.style.color = cambio >= 0 ? '#70458d' : '#a34673';
}

function colorEstado(texto) {
    const clave = String(texto ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
    const tonos = {
        'pendiente': 'red', 'pendiente de pago': 'red', 'no pagado': 'red',
        'rechazado': 'red', 'cancelado': 'red', 'anulado': 'red',
        'en preparacion': 'orange', 'preparacion': 'orange',
        'parcial': 'orange', 'pago parcial': 'orange', 'abonado': 'orange',
        'completado': 'blue', 'enviado': 'purple',
        'entregado': 'green', 'pagado': 'green', 'aprobado': 'green'
    };
    return tonos[clave] ?? 'neutral';
}

function renderEstadosPago() {
    const cont = document.getElementById('gridEstadosPago');
    cont.innerHTML = '';
    listaEstadosPago.forEach(e => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-pos';
        btn.style.width = 'auto';
        btn.style.padding = '.6rem 1.2rem';
        btn.textContent = e.estado_pago;
        btn.dataset.tone = colorEstado(e.estado_pago);
        btn.onclick = () => {
            document.querySelectorAll('#gridEstadosPago .btn-pos').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            estado.estadoPago = e;
        };
        cont.appendChild(btn);
    });
}

function renderEstadosPedido() {
    const cont = document.getElementById('gridEstadosPedido');
    cont.innerHTML = '';
    listaEstadosPedido.forEach(e => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-pos';
        btn.style.width = 'auto';
        btn.style.padding = '.6rem 1.2rem';
        btn.textContent = e.estado_pedido;
        btn.dataset.tone = colorEstado(e.estado_pedido);
        btn.onclick = () => {
            document.querySelectorAll('#gridEstadosPedido .btn-pos').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            estado.estadoPedido = e;
        };
        cont.appendChild(btn);
    });
}

function escaparHTML(valor) {
    const el = document.createElement('span'); el.textContent = String(valor ?? ''); return el.innerHTML;
}
function filtrarProductos() {
    const filtro = document.getElementById('buscarProducto').value.toLocaleLowerCase('es');
    let visibles = 0;
    document.querySelectorAll('#gridProductosBase .btn-pos').forEach(btn => {
        btn.hidden = !btn.textContent.toLocaleLowerCase('es').includes(filtro);
        if (!btn.hidden) visibles++;
    });
    let aviso = document.getElementById('sinProductos');
    if (!aviso) { aviso = document.createElement('p'); aviso.id = 'sinProductos'; aviso.className = 'empty-note'; document.getElementById('gridProductosBase').after(aviso); }
    aviso.textContent = visibles ? '' : 'No hay productos que coincidan con tu búsqueda.';
}
function actualizarResumenPago() {
    const v = estado.producto;
    const nombre = estado.clienteTipo === 'existente'
        ? (estado.cliente?.nombre || 'Por seleccionar')
        : (document.getElementById('nuevoNombre').value.trim() || 'Cliente nuevo');
    const correo = estado.clienteTipo === 'existente'
        ? (estado.cliente?.correo || estado.cliente?.correo_electronico || '')
        : document.getElementById('nuevoCorreo').value.trim();
    document.getElementById('resumenProducto').textContent = v
        ? estado.grupoActual + ' · ' + v.sabor + ' (' + v.tamano + ') × ' + estado.cantidad
        : (estado.grupoActual || 'Sin producto seleccionado');
    document.getElementById('resumenCliente').textContent = nombre;
    document.getElementById('resumenTotal').textContent = formatoMoneda(calcularTotal());
    const pagoTexto = estado.metodoPago ? estado.metodoPago.metodo_pago + (estado.estadoPago ? ' · ' + estado.estadoPago.estado_pago : '') : 'Por definir';
    document.getElementById('pagoResumen').textContent = pagoTexto;
    document.getElementById('correoResumen').textContent = correo
        ? 'Factura por correo: ' + correo
        : 'El envío usa el correo guardado del cliente. Si no tiene uno válido, podrás imprimir la factura.';
    const revision = document.getElementById('revisionVenta');
    revision.innerHTML = '<strong>' + escaparHTML(nombre) + '</strong><br>'
        + escaparHTML(document.getElementById('resumenProducto').textContent) + '<br>'
        + escaparHTML(pagoTexto) + '<br><strong>Total: ' + escaparHTML(formatoMoneda(calcularTotal())) + '</strong>'
        + (estado.metodoPago?.metodo_pago.toLowerCase().includes('efectivo') ? '<br>Recibido: ' + escaparHTML(formatoMoneda(estado.montoRecibido)) + ' · Cambio: ' + escaparHTML(formatoMoneda(Math.max(0,estado.montoRecibido-calcularTotal()))) : '');
}
function validarPagoYContinuar() {
    if (!estado.metodoPago) { alert('Selecciona un método de pago.'); return; }
    if (!estado.estadoPago) { alert('Selecciona el estado del pago.'); return; }
    if (estado.metodoPago.metodo_pago.toLowerCase().includes('efectivo') && estado.montoRecibido < calcularTotal()) {
        alert('El monto recibido es menor al total de la venta.'); return;
    }
    irPaso(5);
}
let enviandoVenta = false;
function confirmarVenta() {
    if (enviandoVenta) return;
    if (!estado.metodoPago) { alert('Selecciona un método de pago.'); return; }
    if (!estado.estadoPedido) { alert('Selecciona el estado del pedido.'); return; }
    if (!estado.estadoPago) { alert('Selecciona el estado del pago.'); return; }
    if (estado.metodoPago.metodo_pago.toLowerCase().includes('efectivo') && estado.montoRecibido < calcularTotal()) {
        alert('El monto recibido es menor al total de la venta.');
        return;
    }
    // NUEVO: última barrera de seguridad, por si el stock cambió mientras el vendedor navegaba los pasos
    if (Number(estado.producto.stock) < estado.cantidad) {
        alert('El stock de este producto cambió y ya no alcanza para la cantidad seleccionada.');
        return;
    }

    document.getElementById('inputIdProducto').value = estado.producto.id_producto;
    document.getElementById('inputCantidad').value = estado.cantidad;
    document.getElementById('inputClienteTipo').value = estado.clienteTipo;

    if (estado.clienteTipo === 'existente') {
        document.getElementById('inputIdCliente').value = estado.cliente.id_cliente;
    } else {
        document.getElementById('inputNuevoNombre').value = document.getElementById('nuevoNombre').value.trim();
        document.getElementById('inputNuevoTipoDocumento').value = estado.nuevoTipoDocumento;
        document.getElementById('inputNuevoNumeroDocumento').value = document.getElementById('nuevoNumeroDocumento').value.trim();
        document.getElementById('inputNuevoCorreo').value = document.getElementById('nuevoCorreo').value.trim();
        document.getElementById('inputNuevoTelefono').value = document.getElementById('nuevoTelefono').value.trim();
    }

    document.getElementById('inputIdMetodoPago').value = estado.metodoPago.id_metodos_pago;
    document.getElementById('inputIdEstadoPedido').value = estado.estadoPedido.id_estado_pedido;
    document.getElementById('inputIdEstadoPago').value = estado.estadoPago.id_estado_pago;

        document.getElementById('inputEfectivoRecibido').value =
        estado.metodoPago.metodo_pago.toLowerCase().includes('efectivo')
            ? String(estado.montoRecibido)
            : '';

    enviandoVenta = true;
    const boton = document.getElementById('btnConfirmarVenta');
    boton.disabled = true;
    boton.textContent = 'Registrando venta…';

    document.getElementById('formVenta').submit();
}

// Mantener el resumen sincronizado con todas las selecciones y campos.
document.addEventListener('click', () => actualizarResumenPago());
document.addEventListener('input', () => actualizarResumenPago());
window.addEventListener('pageshow', () => {
    enviandoVenta = false;
    document.getElementById('btnConfirmarVenta').disabled = false;
    document.getElementById('btnConfirmarVenta').textContent = 'Registrar venta';
});
// Inicializar
renderProductosBase();
renderClientes();
renderTiposDocumento();
renderMetodosPago();
renderBilletes();
renderEstadosPago();
renderEstadosPedido();
modoCliente('existente');
irPaso(1);
</script>
<?php
$idTicketPOS = filter_input(INPUT_GET, 'id_pedido', FILTER_VALIDATE_INT);
?>

<?php if (isset($_GET['exito']) && $idTicketPOS && $idTicketPOS > 0): ?>
<script>
(() => {
    const idPedido = <?= (int) $idTicketPOS ?>;
    const clave = 'dulce_micro_ticket_' + idPedido;

    try {
        if (sessionStorage.getItem(clave)) return;
    } catch (error) {}

    const marco = document.createElement('iframe');
    marco.title = 'Impresión de tirilla POS';
    marco.style.cssText =
        'position:fixed;left:-9999px;top:0;width:57mm;height:1px;border:0;';

    marco.onload = () => {
        try {
            const ventana = marco.contentWindow;

            if (!ventana.document.getElementById('tirillaPOS')) return;

            ventana.focus();
            ventana.print();

            try {
                sessionStorage.setItem(clave, '1');
            } catch (error) {}
        } catch (error) {
            console.error('No se pudo abrir la impresión:', error);
        }
    };

    marco.src =
        '/Dulce_Micro/controlador/FacturaTicket.php?id_pedido=' +
        encodeURIComponent(idPedido);

    document.body.appendChild(marco);
})();
</script>
<?php endif; ?>
</body>
</html>