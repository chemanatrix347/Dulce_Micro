<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
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
    :root {
        --dm-rosa: #f7a8c4;
        --dm-morado: #c9a0dc;
        --dm-rosa-fuerte: #e685a8;
        --dm-morado-fuerte: #a879c9;

        --dm-fondo: #e4c3e9;
        --dm-fondo-2: #d3a9df;
        --dm-texto: #4d3b56;
        --dm-texto-suave: #7b6b83;

        --dm-blanco: #ffffff;
        --dm-borde: #eadced;

        --dm-exito: #35a56a;
        --dm-error: #c0392b;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background:
            radial-gradient(circle at 10% 10%, rgba(247,168,196,.30), transparent 22%),
            radial-gradient(circle at 90% 85%, rgba(168,121,201,.28), transparent 25%),
            linear-gradient(135deg, var(--dm-fondo), var(--dm-fondo-2));

        margin: 0;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        color: var(--dm-texto);
        min-height: 100vh;
    }

    /* =========================================================
       TOPBAR
    ========================================================= */

    .pos-topbar {
        background: linear-gradient(
            90deg,
            var(--dm-rosa) 0%,
            var(--dm-morado) 100%
        );

        color: #fff;
        padding: .9rem 1.5rem;

        display: flex;
        justify-content: space-between;
        align-items: center;

        flex-wrap: wrap;
        gap: .8rem;

        box-shadow: 0 4px 18px rgba(80, 50, 90, .15);

        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .pos-topbar h4 {
        margin: 0;
        font-weight: 800;
        letter-spacing: -.3px;
    }

    .pos-topbar h4 i {
        margin-right: .45rem;
    }

    .pos-topbar .btn {
        border-radius: 20px;
        padding: .4rem .9rem;
        font-weight: 600;
    }

    /* =========================================================
       PASOS
    ========================================================= */

    .pos-steps {
        max-width: 1100px;
        margin: 0 auto;

        display: flex;
        justify-content: center;
        align-items: center;

        gap: .7rem;

        padding: 1.4rem 1.5rem 1.1rem;

        flex-wrap: wrap;
    }

    .pos-step-pill {
        position: relative;

        padding: .65rem 1.25rem;

        border-radius: 30px;

        background: rgba(255,255,255,.72);
        color: #8b7c92;

        font-weight: 700;
        font-size: .85rem;

        border: 2px solid rgba(255,255,255,.7);

        box-shadow: 0 3px 10px rgba(80,50,90,.08);

        transition: all .2s ease;
    }

    .pos-step-pill.active {
        background: var(--dm-morado-fuerte);
        color: #fff;
        border-color: var(--dm-morado-fuerte);

        transform: translateY(-2px);

        box-shadow: 0 6px 16px rgba(168,121,201,.35);
    }

    .pos-step-pill.done {
        background: var(--dm-rosa);
        color: #fff;
        border-color: var(--dm-rosa);

        box-shadow: 0 4px 12px rgba(230,133,168,.25);
    }

    /* =========================================================
       CONTENEDOR PRINCIPAL
    ========================================================= */

    .pos-panel {
        max-width: 1100px;
        margin: 0 auto;
        padding: .5rem 1.5rem 4rem;
    }

    .pos-step {
        display: none;

        background: rgba(255,255,255,.94);

        border-radius: 24px;

        padding: 2rem;

        box-shadow:
            0 12px 35px rgba(70,45,80,.13);

        border: 1px solid rgba(255,255,255,.8);

        animation: aparecer .25s ease;
    }

    .pos-step.active {
        display: block;
    }

    @keyframes aparecer {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* =========================================================
       TITULOS
    ========================================================= */

    .pos-step h5 {
        font-size: 1.35rem;
        font-weight: 800;

        color: var(--dm-texto);

        margin-bottom: 1.4rem;

        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .pos-step h5::before {
        content: "";
        display: inline-block;

        width: 6px;
        height: 25px;

        border-radius: 10px;

        background: linear-gradient(
            var(--dm-rosa-fuerte),
            var(--dm-morado-fuerte)
        );
    }

    .pos-step h6 {
        font-weight: 800;
        color: var(--dm-texto);
    }

    /* =========================================================
       TARJETAS DE PRODUCTOS / OPCIONES
    ========================================================= */

    .grid-productos {
        display: grid;

        grid-template-columns:
            repeat(auto-fill, minmax(180px, 1fr));

        gap: 1rem;

        margin-top: 1rem;
    }

    .btn-pos {
        background: #fff;

        border: 2px solid var(--dm-borde);

        border-radius: 18px;

        padding: 1.25rem 1rem;

        text-align: center;

        font-weight: 700;

        color: var(--dm-texto);

        width: 100%;
        min-height: 95px;

        transition:
            transform .18s ease,
            box-shadow .18s ease,
            border-color .18s ease,
            background .18s ease;

        cursor: pointer;

        box-shadow: 0 3px 9px rgba(70,45,80,.05);
    }

    .btn-pos:hover {
        border-color: var(--dm-morado);

        transform: translateY(-4px);

        box-shadow:
            0 10px 22px rgba(120,80,140,.14);
    }

    .btn-pos.selected {
        background:
            linear-gradient(
                135deg,
                var(--dm-morado),
                var(--dm-morado-fuerte)
            );

        border-color: var(--dm-morado-fuerte);

        color: #fff;

        box-shadow:
            0 8px 20px rgba(168,121,201,.32);

        transform: translateY(-2px);
    }

    .btn-pos small {
        display: block;

        font-weight: 500;

        margin-top: .45rem;

        opacity: .8;
    }

    .btn-pos.selected small {
        opacity: .95;
    }

    /* NUEVO: botones de producto/variante sin stock, deshabilitados */
    .btn-pos:disabled,
    .btn-pos.agotado {
        opacity: .5;
        cursor: not-allowed;
        background: #f2eef4;
        color: #a99cae;
    }

    .btn-pos:disabled:hover,
    .btn-pos.agotado:hover {
        transform: none;
        border-color: var(--dm-borde);
        box-shadow: 0 3px 9px rgba(70,45,80,.05);
    }

    .btn-pos small.agotado-label {
        color: var(--dm-error);
        font-weight: 800;
        opacity: 1;
    }

    /* =========================================================
       CANTIDAD
    ========================================================= */

    #bloqueCantidad {
        margin-top: 1.8rem;

        padding: 1.2rem;

        background: #faf5fc;

        border: 1px solid var(--dm-borde);

        border-radius: 18px;

        text-align: center;
    }

    #bloqueCantidad::before {
        content: "Cantidad";

        display: block;

        font-size: .85rem;

        font-weight: 700;

        color: var(--dm-texto-suave);

        margin-bottom: .6rem;
    }

    .qty-control {
        display: flex;

        align-items: center;
        justify-content: center;

        gap: 1rem;

        margin: .5rem 0;
    }

    .qty-control button {
        width: 48px;
        height: 48px;

        border-radius: 50%;

        border: none;

        background: var(--dm-morado-fuerte);

        color: #fff;

        font-size: 1.4rem;
        font-weight: 800;

        box-shadow: 0 4px 12px rgba(168,121,201,.25);

        transition: .15s ease;
    }

    .qty-control button:hover {
        transform: scale(1.08);
        background: var(--dm-rosa-fuerte);
    }

    .qty-control span {
        font-size: 1.7rem;

        font-weight: 800;

        min-width: 50px;

        text-align: center;

        color: var(--dm-texto);
    }

    /* =========================================================
       CLIENTES
    ========================================================= */

    #btnClienteExistente,
    #btnClienteNuevo {
        min-height: 80px;
    }

    .form-control {
        border: 2px solid #eadced;

        border-radius: 14px;

        padding: .8rem 1rem;

        color: var(--dm-texto);

        box-shadow: none;

        transition: .15s ease;
    }

    .form-control:focus {
        border-color: var(--dm-morado);

        box-shadow:
            0 0 0 4px rgba(201,160,220,.20);
    }

    .form-label {
        font-weight: 700;

        color: var(--dm-texto);

        margin-bottom: .4rem;
    }

    .cliente-list {
        max-height: 320px;

        overflow-y: auto;

        padding-right: .3rem;

        margin-top: .8rem;
    }

    .cliente-item {
        padding: .9rem 1rem;

        border-radius: 14px;

        border: 2px solid #eee5f0;

        background: #fff;

        margin-bottom: .6rem;

        cursor: pointer;

        font-weight: 600;

        transition: .15s ease;
    }

    .cliente-item:hover {
        border-color: var(--dm-morado);

        background: #faf4fc;

        transform: translateX(3px);
    }

    .cliente-item.selected {
        background:
            linear-gradient(
                135deg,
                var(--dm-morado),
                var(--dm-morado-fuerte)
            );

        color: #fff;

        border-color: var(--dm-morado-fuerte);

        box-shadow: 0 6px 16px rgba(168,121,201,.25);
    }

    /* =========================================================
       RESUMEN DE VENTA
    ========================================================= */

    .resumen-box {
        background:
            linear-gradient(
                135deg,
                #fff,
                #fbf4fd
            );

        border-radius: 20px;

        padding: 1.5rem;

        box-shadow: 0 7px 20px rgba(70,45,80,.08);

        margin-bottom: 1.5rem;

        border: 1px solid var(--dm-borde);

        position: relative;
    }

    .resumen-box::before {
        content: "RESUMEN DE LA VENTA";

        display: block;

        font-size: .75rem;

        font-weight: 800;

        letter-spacing: .08em;

        color: var(--dm-morado-fuerte);

        margin-bottom: .7rem;
    }

    #resumenProducto {
        font-size: 1.1rem;

        font-weight: 800;

        color: var(--dm-texto);
    }

    #resumenCliente {
        display: inline-block;

        margin-top: .25rem;

        color: var(--dm-texto-suave);
    }

    .monto-display {
        text-align: center;

        font-size: 2.3rem;

        font-weight: 900;

        color: var(--dm-morado-fuerte);

        margin: .7rem 0;
    }

    #resumenTotal {
        border-top: 1px dashed #dccde2;

        padding-top: .8rem;

        margin-top: .8rem;
    }

    /* =========================================================
       MÉTODOS DE PAGO
    ========================================================= */

    #gridMetodosPago .btn-pos {
        min-height: 80px;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: .5rem;
    }

    /* =========================================================
       EFECTIVO
    ========================================================= */

    #bloqueEfectivo {
        background: #faf7fc;

        border: 1px solid var(--dm-borde);

        border-radius: 20px;

        padding: 1.3rem;

        margin-top: 1rem;
    }

    .billetes {
        display: grid;

        grid-template-columns:
            repeat(auto-fit, minmax(105px, 1fr));

        gap: .7rem;

        margin: 1rem 0;
    }

    .billetes button {
        padding: .8rem .4rem;

        border-radius: 13px;

        border: 2px solid #b7e4c7;

        background: #eafaf0;

        color: #1e7e46;

        font-weight: 800;

        transition: .15s ease;
    }

    .billetes button:hover {
        transform: translateY(-2px);

        background: #dff6e8;
    }

    /* =========================================================
       TECLADO
    ========================================================= */

    .keypad {
        display: grid;

        grid-template-columns: repeat(3, 1fr);

        gap: .65rem;

        max-width: 300px;

        margin: 1.2rem auto;
    }

    .keypad button {
        padding: 1rem;

        font-size: 1.2rem;

        border-radius: 14px;

        border: 2px solid var(--dm-borde);

        background: #fff;

        color: var(--dm-texto);

        font-weight: 800;

        transition: .15s ease;

        box-shadow: 0 2px 6px rgba(70,45,80,.04);
    }

    .keypad button:hover {
        border-color: var(--dm-morado);

        background: #faf4fc;

        transform: translateY(-2px);
    }

    .keypad button:active {
        background: var(--dm-morado-fuerte);

        color: #fff;

        transform: scale(.97);
    }

    #cambioDisplay {
        padding: .8rem;

        border-radius: 12px;

        background: #fff;

        min-height: 20px;
    }

    /* =========================================================
       ESTADO DEL PEDIDO / PAGO
    ========================================================= */

    #gridEstadosPedido .btn-pos,
    #gridEstadosPago .btn-pos {
        min-height: 60px;
    }

    /* =========================================================
       BOTONES DE NAVEGACIÓN
    ========================================================= */

    .nav-buttons {
        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 1rem;

        margin-top: 2rem;

        padding-top: 1.5rem;

        border-top: 1px solid #eee4f0;
    }

    .btn-continuar,
    .btn-volver {
        border-radius: 30px;

        padding: .85rem 1.7rem;

        font-weight: 800;

        transition: .18s ease;
    }

    .btn-continuar {
        background: var(--dm-morado-fuerte);

        border: none;

        color: #fff;

        box-shadow: 0 5px 15px rgba(168,121,201,.25);
    }

    .btn-continuar:hover {
        background: var(--dm-rosa-fuerte);

        transform: translateY(-2px);

        color: #fff;
    }

    .btn-volver {
        background: #fff;

        border: 2px solid #ded4e2;

        color: #706476;
    }

    .btn-volver:hover {
        border-color: var(--dm-morado);

        color: var(--dm-morado-fuerte);

        background: #faf5fc;
    }

    /* =========================================================
       CONFIRMAR VENTA
    ========================================================= */

    .btn-continuar[onclick="confirmarVenta()"] {
        background: var(--dm-exito) !important;

        padding: 1rem 2rem;

        box-shadow: 0 6px 18px rgba(53,165,106,.25);

        font-size: 1rem;
    }

    .btn-continuar[onclick="confirmarVenta()"]:hover {
        background: #278653 !important;

        transform: translateY(-2px);
    }

    /* =========================================================
       ALERTA DE VENTA EXITOSA
    ========================================================= */

    .alert-success {
        border: none;

        border-left: 5px solid var(--dm-exito);

        border-radius: 14px;

        background: #eaf8f0;

        color: #236b46;

        box-shadow: 0 5px 15px rgba(50,100,70,.08);

        padding: 1rem 1.2rem;
    }

    /* =========================================================
       SCROLLBAR
    ========================================================= */

    .cliente-list::-webkit-scrollbar {
        width: 7px;
    }

    .cliente-list::-webkit-scrollbar-track {
        background: #f4edf6;

        border-radius: 10px;
    }

    .cliente-list::-webkit-scrollbar-thumb {
        background: var(--dm-morado);

        border-radius: 10px;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .pos-topbar {
            padding: .8rem 1rem;
        }

        .pos-topbar h4 {
            font-size: 1rem;
        }

        .pos-topbar > div {
            width: 100%;

            display: flex;

            align-items: center;

            justify-content: space-between;
        }

        .pos-panel {
            padding: .5rem .8rem 2rem;
        }

        .pos-step {
            padding: 1.3rem;

            border-radius: 18px;
        }

        .pos-steps {
            padding: 1rem .7rem;

            gap: .4rem;
        }

        .pos-step-pill {
            padding: .5rem .8rem;

            font-size: .75rem;
        }

        .grid-productos {
            grid-template-columns:
                repeat(2, 1fr);

            gap: .7rem;
        }

        .btn-pos {
            min-height: 85px;

            padding: .9rem .6rem;
        }

        .nav-buttons {
            flex-direction: column-reverse;
        }

        .nav-buttons button {
            width: 100%;
        }

        .monto-display {
            font-size: 2rem;
        }
    }

    @media (max-width: 430px) {

        .grid-productos {
            grid-template-columns: 1fr;
        }

        .pos-step-pill {
            flex: 1 1 45%;

            text-align: center;
        }

        .billetes {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
</head>
<body>

<div class="pos-topbar">
    <h4><i class="fa-solid fa-cash-register"></i> Punto de Venta — Dulce Micro</h4>
    <div>
        <span class="me-3">Vendedor: <?= htmlspecialchars($_SESSION['Nombre'] ?? '') ?></span>
        <a href="/Dulce_Micro/index.php" class="btn btn-sm btn-outline-light me-2"><i class="fa-solid fa-gauge"></i> Menú</a>
        <a href="/Dulce_Micro/vista/logout.php" class="btn btn-sm btn-outline-light"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
    </div>
</div>

<div class="pos-steps">
    <div class="pos-step-pill" data-step-pill="1">1. Producto</div>
    <div class="pos-step-pill" data-step-pill="2">2. Sabor</div>
    <div class="pos-step-pill" data-step-pill="3">3. Cliente</div>
    <div class="pos-step-pill" data-step-pill="4">4. Pago</div>
</div>

<div class="pos-panel">

<?php if (isset($_GET['exito']) && isset($_GET['id_pedido'])): ?>
    <div class="alert alert-success d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fa-solid fa-circle-check"></i> Venta registrada correctamente (Pedido #<?= (int) $_GET['id_pedido'] ?>).</span>
        <a href="/Dulce_Micro/controlador/FacturaPDF.php?id_pedido=<?= (int) $_GET['id_pedido'] ?>" target="_blank" class="btn btn-sm btn-success">
            <i class="fa-solid fa-print"></i> Imprimir factura
        </a>
    </div>
<?php endif; ?>

<?php if (isset($_GET['sin_stock'])): ?>
    <div class="alert alert-success" style="border-left-color: var(--dm-error); background:#fdeceb; color:#8a2c22;">
        <i class="fa-solid fa-triangle-exclamation"></i> No hay suficiente stock disponible para completar esa venta.
    </div>
<?php endif; ?>

    <!-- PASO 1: PRODUCTO -->
    <div class="pos-step" data-step="1">
        <h5 class="mb-3">Selecciona un producto</h5>
        <div class="grid-productos" id="gridProductosBase"></div>
    </div>

    <!-- PASO 2: SABOR -->
    <div class="pos-step" data-step="2">
        <h5 class="mb-3">Selecciona el sabor</h5>
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
        <h5 class="mb-3">Cliente</h5>
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
                <label class="form-label">Nombre completo</label>
                <input type="text" class="form-control form-control-lg" id="nuevoNombre">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de documento</label>
                <div class="d-flex gap-2 flex-wrap" id="gridTipoDocumento"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Número de documento</label>
                <input type="text" class="form-control form-control-lg" id="nuevoNumeroDocumento">
            </div>
            <div class="col-md-4">
                <label class="form-label">Correo (opcional)</label>
                <input type="text" class="form-control form-control-lg" id="nuevoCorreo">
            </div>
            <div class="col-md-4">
                <label class="form-label">Teléfono (opcional)</label>
                <input type="text" class="form-control form-control-lg" id="nuevoTelefono">
            </div>
        </div>

        <div class="nav-buttons">
            <button type="button" class="btn-volver" onclick="irPaso(2)"><i class="fa-solid fa-arrow-left"></i> Volver</button>
            <button type="button" class="btn-continuar" onclick="validarClienteYContinuar()">Continuar <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>

    <!-- PASO 4: PAGO -->
    <div class="pos-step" data-step="4">
        <div class="resumen-box">
            <strong id="resumenProducto">-</strong><br>
            <span class="text-muted" id="resumenCliente">-</span>
            <div class="monto-display" id="resumenTotal">$0</div>
        </div>

        <h5 class="mb-2">Método de pago</h5>
        <div class="d-flex gap-2 flex-wrap mb-3" id="gridMetodosPago"></div>

        <div id="bloqueEfectivo" style="display:none;">
            <h6 class="mb-2">Billetes rápidos</h6>
            <div class="billetes" id="gridBilletes"></div>
            <div class="monto-display" id="montoRecibido">$0</div>
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

        <h5 class="mt-3 mb-2">Estado del pago</h5>
        <div class="d-flex gap-2 flex-wrap mb-4" id="gridEstadosPago"></div>

        <h5 class="mt-3 mb-2">Estado del pedido</h5>
        <div class="d-flex gap-2 flex-wrap mb-4" id="gridEstadosPedido"></div>

        <div class="nav-buttons">
            <button type="button" class="btn-volver" onclick="irPaso(3)"><i class="fa-solid fa-arrow-left"></i> Volver</button>
            <button type="button" class="btn-continuar" style="background:#28a745" onclick="confirmarVenta()"><i class="fa-solid fa-check"></i> Confirmar venta</button>
        </div>
    </div>

</div>

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
</form>

<script>
const productosAgrupados = <?= json_encode($productosAgrupados, JSON_UNESCAPED_UNICODE) ?>;
const listaClientes = <?= json_encode($listaClientes, JSON_UNESCAPED_UNICODE) ?>;
const listaMetodosPago = <?= json_encode($listaMetodosPago, JSON_UNESCAPED_UNICODE) ?>;
const listaEstadosPedido = <?= json_encode($listaEstadosPedido, JSON_UNESCAPED_UNICODE) ?>;
const listaTiposDocumento = <?= json_encode($listaTiposDocumento, JSON_UNESCAPED_UNICODE) ?>;
const listaEstadosPago = <?= json_encode($listaEstadosPago, JSON_UNESCAPED_UNICODE) ?>;

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
    if (n === 4) actualizarResumenPago();
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
        btn.innerHTML = nombre + (
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
        btn.innerHTML = v.sabor + (
            agotado
                ? '<small class="agotado-label">Agotado</small>'
                : '<small>' + v.tamano + ' — ' + formatoMoneda(v.precio_base) + '</small>'
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
            const div = document.createElement('div');
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
    el.style.color = cambio >= 0 ? '#1e7e46' : '#c0392b';
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
        btn.onclick = () => {
            document.querySelectorAll('#gridEstadosPedido .btn-pos').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            estado.estadoPedido = e;
        };
        cont.appendChild(btn);
    });
}

function actualizarResumenPago() {
    const v = estado.producto;
    document.getElementById('resumenProducto').textContent =
        estado.grupoActual + ' — ' + v.sabor + ' (' + v.tamano + ') x' + estado.cantidad;
    document.getElementById('resumenCliente').textContent =
        estado.clienteTipo === 'existente'
            ? (estado.cliente ? 'Cliente: ' + estado.cliente.nombre : 'Cliente no seleccionado')
            : 'Cliente nuevo: ' + document.getElementById('nuevoNombre').value;
    document.getElementById('resumenTotal').textContent = formatoMoneda(calcularTotal());
}

function confirmarVenta() {
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

    document.getElementById('formVenta').submit();
}

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

</body>
</html>
