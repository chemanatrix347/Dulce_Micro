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

$productoModel = new Producto();
$clienteModel = new Cliente();
$metodoPagoModel = new MetodoPago();
$estadoPedidoModel = new EstadoPedido();
$tipoDocumentoModel = new TipoDocumento();

$listaProductos = $productoModel->leer_productos();
$listaClientes = $clienteModel->leer_clientes();
$listaMetodosPago = $metodoPagoModel->leer_metodos_pago();
$listaEstadosPedido = $estadoPedidoModel->leer_estados_pedido();
$listaTiposDocumento = $tipoDocumentoModel->leer_tipos_documento();

$productosAgrupados = [];
foreach ($listaProductos as $p) {
    $nombreCompleto = trim((string)$p['nombre_producto']);
    $partesNombre = explode(' - ', $nombreCompleto);
    $nombreGrupo = trim($partesNombre[0]);
    $productosAgrupados[$nombreGrupo][] = $p;
}
?>
<!DOCTYPE html>

<html class="h-full bg-[#FAF8F9] text-on-surface" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Punto de Venta - Dulce Micro</title>
<link rel="icon" type="image/png" href="/Dulce_Micro/img_logos/favicon.png">
<!-- Material Symbols Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Plus Jakarta Sans Font -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Tailwind Configuration with Design Tokens -->
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-bright": "#FAF8F9",
            "on-background": "#25152e",
            "tertiary": "#714e84",
            "primary-fixed-dim": "#e2b6ff",
            "on-surface": "#241728",
            "error": "#ba1a1a",
            "on-tertiary-fixed-variant": "#5b396d",
            "secondary": "#954363",
            "on-tertiary-fixed": "#2c0b3e",
            "on-primary": "#ffffff",
            "tertiary-container": "#8c669e",
            "on-secondary-fixed": "#3e001f",
            "surface-container-low": "#FAF7FB",
            "surface-container-lowest": "#ffffff",
            "secondary-fixed": "#ffd9e3",
            "on-error-container": "#93000a",
            "surface-tint": "#774b97",
            "surface-container-high": "#f4eef7",
            "tertiary-fixed": "#f5d9ff",
            "inverse-on-surface": "#fcebff",
            "outline-variant": "#EFE8F2",
            "inverse-surface": "#3b2a44",
            "surface-variant": "#f4eaf7",
            "secondary-container": "#fc98bb",
            "error-container": "#ffdad6",
            "on-error": "#ffffff",
            "primary": "#754995",
            "tertiary-fixed-dim": "#e2b7f5",
            "primary-fixed": "#f3daff",
            "on-secondary-container": "#792c4c",
            "on-primary-fixed-variant": "#5e337e",
            "on-primary-fixed": "#2e004d",
            "surface-dim": "#f3ebf5",
            "on-tertiary": "#ffffff",
            "on-surface-variant": "#635868",
            "outline": "#887D8E",
            "on-primary-container": "#fffbff",
            "surface-container": "#f8f3fa",
            "primary-container": "#8f62af",
            "surface": "#ffffff",
            "secondary-fixed-dim": "#ffb0ca",
            "on-secondary-fixed-variant": "#782b4b",
            "on-secondary": "#ffffff",
            "inverse-primary": "#e2b6ff",
            "on-tertiary-container": "#fffbff",
            "surface-container-highest": "#f0e6f3",
            "background": "#FAF8F9"
          },
          borderRadius: {
            "DEFAULT": "1rem",
            "lg": "2rem",
            "xl": "3rem",
            "full": "9999px"
          },
          spacing: {
            "space-md": "1rem",
            "margin": "1rem",
            "space-xl": "2rem",
            "space-lg": "1.5rem",
            "gutter-lg": "1.5rem",
            "gutter": "1rem",
            "space-sm": "0.5rem",
            "space-xs": "0.25rem",
            "space-2xl": "3rem",
            "margin-lg": "1.5rem"
          },
          fontFamily: {
            "body-md": ["Plus Jakarta Sans"],
            "label-sm": ["Plus Jakarta Sans"],
            "headline-md": ["Plus Jakarta Sans"],
            "headline-xl": ["Plus Jakarta Sans"],
            "headline-lg-mobile": ["Plus Jakarta Sans"],
            "label-md": ["Plus Jakarta Sans"],
            "body-sm": ["Plus Jakarta Sans"],
            "label-lg": ["Plus Jakarta Sans"],
            "headline-sm": ["Plus Jakarta Sans"],
            "headline-xl-mobile": ["Plus Jakarta Sans"],
            "headline-lg": ["Plus Jakarta Sans"],
            "body-lg": ["Plus Jakarta Sans"]
          },
          fontSize: {
            "body-md": ["1rem", { lineHeight: "1.5rem", fontWeight: "400" }],
            "label-sm": ["0.75rem", { lineHeight: "1rem", letterSpacing: "0.03em", fontWeight: "600" }],
            "headline-md": ["1.5rem", { lineHeight: "2rem", letterSpacing: "-0.015em", fontWeight: "700" }],
            "headline-xl": ["2.5rem", { lineHeight: "3rem", letterSpacing: "-0.03em", fontWeight: "800" }],
            "headline-lg-mobile": ["1.375rem", { lineHeight: "1.75rem", letterSpacing: "-0.01em", fontWeight: "700" }],
            "label-md": ["0.875rem", { lineHeight: "1.125rem", letterSpacing: "0.02em", fontWeight: "600" }],
            "body-sm": ["0.875rem", { lineHeight: "1.25rem", fontWeight: "400" }],
            "label-lg": ["1rem", { lineHeight: "1.25rem", letterSpacing: "0.01em", fontWeight: "700" }],
            "headline-sm": ["1.25rem", { lineHeight: "1.75rem", letterSpacing: "-0.01em", fontWeight: "600" }],
            "headline-xl-mobile": ["1.75rem", { lineHeight: "2.25rem", letterSpacing: "-0.02em", fontWeight: "800" }],
            "headline-lg": ["2rem", { lineHeight: "2.5rem", letterSpacing: "-0.02em", fontWeight: "700" }],
            "body-lg": ["1.125rem", { lineHeight: "1.75rem", fontWeight: "500" }]
          }
        }
      }
    }
  </script>
<style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    .custom-scroll::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }
    .custom-scroll::-webkit-scrollbar-track {
      background: transparent;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
      background-color: #E2D9E7;
      border-radius: 9999px;
    }
    .touch-btn {
      min-height: 48px;
    }
    .boutique-card {
      background: #FFFFFF;
      border: 1px solid #EFE8F2;
      box-shadow: 0 2px 8px -2px rgba(95, 45, 115, 0.04), 0 1px 3px -1px rgba(0,0,0,0.02);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .boutique-card:hover {
      border-color: #DFCEE7;
      box-shadow: 0 8px 24px -4px rgba(117, 73, 149, 0.08);
    }
  </style>
</head>
<body class="h-full flex flex-col font-body-md text-on-surface bg-[#FAF8F9] overflow-hidden antialiased select-none">
<!-- FORMULARIO REAL DE VENTA -->
<form class="hidden" id="formVenta" action="../controlador/VentaController.php" method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<input type="hidden" name="id_producto" id="inputIdProducto">
<input type="hidden" name="cantidad" id="inputCantidad" value="1">
<input type="hidden" name="cliente_tipo" id="inputClienteTipo" value="existente">
<input type="hidden" name="id_cliente" id="inputIdCliente">
<input type="hidden" name="nuevo_nombre" id="inputNuevoNombre">
<input type="hidden" name="nuevo_id_tipo_documento" id="inputNuevoTipoDocumento">
<input type="hidden" name="nuevo_numero_documento" id="inputNuevoNumeroDocumento">
<input type="hidden" name="nuevo_correo" id="inputNuevoCorreo">
<input type="hidden" name="nuevo_telefono" id="inputNuevoTelefono">
<input type="hidden" name="id_metodo_pago" id="inputIdMetodoPago">
<input type="hidden" name="fecha_pago" value="<?= date('Y-m-d') ?>">
<input type="hidden" name="id_estado_pedido" id="inputIdEstadoPedido">
</form>
<!-- TOP APP BAR (Boutique POS Header) -->
<header class="fixed top-0 left-0 w-full h-[72px] z-50 flex items-center justify-between px-6 bg-white border-b border-[#EFE8F2] shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
<div class="flex items-center gap-3.5">
<!-- Logo Dulce Micro Oficial -->
<div class="w-12 h-12 rounded-2xl overflow-hidden bg-white border border-[#EFE8F2] flex items-center justify-center p-0.5 shadow-xs shrink-0">
<img alt="Dulce Micro Boutique" class="w-full h-full object-contain" src="/Dulce_Micro/img_logos/logo%20principal.png"/>
</div>
<div>
<div class="flex items-center gap-2">
<span class="text-[17px] font-extrabold tracking-tight text-[#241728]">Dulce Micro</span>
<span class="text-[10px] bg-[#FCE8F0] text-[#954363] px-2 py-0.5 rounded-full font-bold tracking-wider uppercase">Boutique POS</span>
</div>
<p class="text-[12px] text-on-surface-variant font-medium">Terminal 01 • Caja Principal • Salón &amp; Vitrina</p>
</div>
</div>
<!-- Central Navigation Links -->
<nav class="hidden md:flex items-center gap-1 h-full">
<a class="h-10 px-4 rounded-xl flex items-center gap-2 text-primary bg-[#F6EEFA] font-bold text-xs tracking-wide transition-colors" href="#">
<span class="material-symbols-outlined text-[19px]">point_of_sale</span> Terminal
</a>
<a class="h-10 px-4 rounded-xl flex items-center gap-2 text-on-surface-variant hover:text-on-surface hover:bg-[#F8F5F9] font-semibold text-xs tracking-wide transition-colors" href="#">
<span class="material-symbols-outlined text-[19px]">receipt_long</span> Pedidos
</a>
<a class="h-10 px-4 rounded-xl flex items-center gap-2 text-on-surface-variant hover:text-on-surface hover:bg-[#F8F5F9] font-semibold text-xs tracking-wide transition-colors" href="#">
<span class="material-symbols-outlined text-[19px]">history</span> Historial
</a>
<a class="h-10 px-4 rounded-xl flex items-center gap-2 text-on-surface-variant hover:text-on-surface hover:bg-[#F8F5F9] font-semibold text-xs tracking-wide transition-colors" href="#">
<span class="material-symbols-outlined text-[19px]">tune</span> Ajustes
</a>
</nav>
<!-- Trailing Actions (Operador y Turno) -->
<div class="flex items-center gap-3">
<div class="flex items-center gap-1.5 text-on-surface-variant mr-1">
<button class="w-9 h-9 rounded-xl flex items-center justify-center hover:bg-[#F8F5F9] text-on-surface-variant transition-colors" title="Notificaciones" type="button">
<span class="material-symbols-outlined text-[20px]">notifications</span>
</button>
<div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-100">
<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
<span class="hidden sm:inline">En línea</span>
</div>
</div>
<div class="hidden sm:flex items-center gap-2.5 pl-3 border-l border-[#EFE8F2]">
<div class="w-8 h-8 rounded-full bg-[#EFE3F7] text-primary flex items-center justify-center font-bold text-xs shadow-xs">
    CP
  </div>
<div class="text-left text-xs leading-tight">
<p class="font-bold text-on-surface">Camila Pérez</p>
<p class="text-on-surface-variant text-[11px]">Cajera principal</p>
</div>
</div>
<div class="flex items-center gap-2">
<button class="px-3.5 py-2 rounded-xl text-xs font-semibold border border-[#E2D9E7] text-on-surface-variant hover:bg-[#F8F5F9] transition-colors" type="button">
    Bloquear
  </button>
<a href="/Dulce_Micro/vista/logout.php" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#954363] text-white hover:bg-[#833854] active:scale-95 transition-all shadow-xs">
    Cerrar Sesión
  </a>
</div>
</div>
</header>
<!-- BODY CONTENT AREA (UNDER TOP BAR) -->
<div class="pt-[72px] flex h-full overflow-hidden">
<!-- SIDEBAR DOCK (Tablet POS Dock) -->
<aside class="hidden lg:flex flex-col justify-between p-4 h-[calc(100vh-72px)] w-60 bg-white border-r border-[#EFE8F2] z-40">
<div class="space-y-4">
<!-- POS CTA Principal -->
<button class="w-full flex items-center justify-center gap-2 bg-[#754995] hover:bg-[#683f85] text-white py-3.5 px-4 rounded-xl font-bold text-sm shadow-xs active:scale-95 transition-all" onclick="irPaso(1)">
<span class="material-symbols-outlined text-[20px]">add_circle</span>
    Nueva Venta
  </button>
<!-- Navigation Tabs POS -->
<div class="space-y-1">
<button class="w-full flex items-center gap-3 bg-[#F6EEFA] text-[#754995] rounded-xl px-3.5 py-2.5 font-bold text-xs tracking-wide text-left transition-colors">
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
      Venta Actual
    </button>
<button class="w-full flex items-center gap-3 text-on-surface-variant hover:bg-[#FAF8F9] px-3.5 py-2.5 rounded-xl font-medium text-xs tracking-wide text-left transition-colors">
<span class="material-symbols-outlined text-[20px]">table_restaurant</span>
      Mesas y Salón
    </button>
<button class="w-full flex items-center gap-3 text-on-surface-variant hover:bg-[#FAF8F9] px-3.5 py-2.5 rounded-xl font-medium text-xs tracking-wide text-left transition-colors">
<span class="material-symbols-outlined text-[20px]">inventory_2</span>
      Inventario Vitrina
    </button>
<button class="w-full flex items-center gap-3 text-on-surface-variant hover:bg-[#FAF8F9] px-3.5 py-2.5 rounded-xl font-medium text-xs tracking-wide text-left transition-colors">
<span class="material-symbols-outlined text-[20px]">analytics</span>
      Arqueo y Ventas
    </button>
<button class="w-full flex items-center gap-3 text-on-surface-variant hover:bg-[#FAF8F9] px-3.5 py-2.5 rounded-xl font-medium text-xs tracking-wide text-left transition-colors">
<span class="material-symbols-outlined text-[20px]">settings</span>
      Configuración
    </button>
</div>
</div>
<!-- Quick Session Footer -->
<div class="border-t border-[#EFE8F2] pt-3 space-y-1">
<a class="flex items-center gap-2.5 text-on-surface-variant hover:bg-[#FAF8F9] px-3 py-2 rounded-xl text-xs font-semibold" href="#">
<span class="material-symbols-outlined text-[18px]">help</span> Soporte POS
  </a>
<a class="flex items-center gap-2.5 text-error hover:bg-rose-50 px-3 py-2 rounded-xl text-xs font-semibold" href="/Dulce_Micro/vista/logout.php">
<span class="material-symbols-outlined text-[18px]">logout</span> Cerrar Sesión
  </a>
</div>
</aside>
<!-- MAIN CANVAS CONTAINER -->
<main class="flex-1 flex flex-col h-[calc(100vh-72px)] overflow-hidden bg-[#FAF8F9]">
<!-- STEPPER BAR HEADER (Ultra Clean & Delicate Minimalist Stepper) -->
<div class="bg-white/90 backdrop-blur-md px-6 py-3 border-b border-[#EFE8F2] flex items-center justify-between z-10 shrink-0">
<div class="flex items-center gap-3">
<span class="text-sm font-extrabold text-[#241728] tracking-tight uppercase">Orden de Venta</span>
<span class="h-3.5 w-px bg-slate-200"></span>
<span class="text-xs text-on-surface-variant font-medium">Boutique Express</span>
</div>
<!-- Stepper Indicator -->
<nav aria-label="Progreso de venta" class="flex items-center bg-[#FAF8F9] px-3 py-1 rounded-full border border-[#EFE8F2]">
<!-- Step 1 -->
<div class="flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full bg-[#754995] text-white shadow-xs transition-all duration-200" id="step-node-1" onclick="irPaso(1)">
<span class="w-5 h-5 rounded-full bg-white/20 text-white flex items-center justify-center text-[11px] font-bold" id="step-icon-1">1</span>
<span class="text-xs font-bold tracking-tight">Producto</span>
</div>
<div class="w-6 h-px bg-[#E2D9E7] mx-1 transition-colors" id="step-line-1"></div>
<!-- Step 2 -->
<div class="flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full text-on-surface-variant hover:bg-[#EFE8F2]/50 transition-all duration-200" id="step-node-2" onclick="irPaso(2)">
<span class="w-5 h-5 rounded-full border border-[#D5CADB] text-on-surface-variant flex items-center justify-center text-[11px] font-bold" id="step-icon-2">2</span>
<span class="text-xs font-semibold tracking-tight">Sabor</span>
</div>
<div class="w-6 h-px bg-[#E2D9E7] mx-1 transition-colors" id="step-line-2"></div>
<!-- Step 3 -->
<div class="flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full text-on-surface-variant hover:bg-[#EFE8F2]/50 transition-all duration-200" id="step-node-3" onclick="irPaso(3)">
<span class="w-5 h-5 rounded-full border border-[#D5CADB] text-on-surface-variant flex items-center justify-center text-[11px] font-bold" id="step-icon-3">3</span>
<span class="text-xs font-semibold tracking-tight">Cliente</span>
</div>
<div class="w-6 h-px bg-[#E2D9E7] mx-1 transition-colors" id="step-line-3"></div>
<!-- Step 4 -->
<div class="flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full text-on-surface-variant hover:bg-[#EFE8F2]/50 transition-all duration-200" id="step-node-4" onclick="irPaso(4)">
<span class="w-5 h-5 rounded-full border border-[#D5CADB] text-on-surface-variant flex items-center justify-center text-[11px] font-bold" id="step-icon-4">4</span>
<span class="text-xs font-semibold tracking-tight">Pago</span>
</div>
</nav>
</div>
<!-- WORKFLOW CONTENT CONTAINER -->
<div class="flex-1 overflow-y-auto custom-scroll p-5 sm:p-7 lg:p-8 flex justify-center items-start">
<div class="w-full max-w-5xl">
<?php if (isset($_GET['exito']) && isset($_GET['id_pedido'])): ?>
<div class="mb-5 flex items-center justify-between gap-3 flex-wrap bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl shadow-sm">
    <div class="flex items-center gap-2 text-sm font-semibold">
        <span class="material-symbols-outlined">check_circle</span>
        Venta registrada correctamente. Pedido #<?= (int)$_GET['id_pedido'] ?>
    </div>
    <a href="/Dulce_Micro/controlador/FacturaPDF.php?id_pedido=<?= (int)$_GET['id_pedido'] ?>" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
        <span class="material-symbols-outlined text-sm align-middle">print</span> Imprimir factura
    </a>
</div>
<?php endif; ?>
<!-- =================== PASO 1: SELECCIÓN DE PRODUCTO BASE =================== -->
<section class="block animate-fadeIn" id="paso1">
<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 gap-3">
<div>
<h1 class="text-2xl font-bold tracking-tight text-[#241728]">Selecciona el Producto Base</h1>
<p class="text-xs text-on-surface-variant mt-0.5">Elige la categoría o línea de repostería para iniciar la orden de caja.</p>
</div>
<div class="flex items-center gap-2 self-start sm:self-auto">
<span class="text-xs font-semibold bg-white text-primary px-3 py-1.5 rounded-full flex items-center gap-2 border border-[#EFE8F2] shadow-2xs">
<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        4 líneas en vitrina
      </span>
</div>
</div>
<!-- #gridProductosBase -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="gridProductosBase">
<?php foreach ($productosAgrupados as $nombre => $variantes):
    $precioMin = min(array_map(fn($v) => (float)$v['precio_base'], $variantes));
    $nombreLower = mb_strtolower($nombre);
    $icono = str_contains($nombreLower, 'torta') && str_contains($nombreLower, 'personal') ? 'palette' : (str_contains($nombreLower, 'torta') ? 'cake' : (str_contains($nombreLower, 'postre') ? 'icecream' : 'cookie'));
    $idGrupo = (int)$variantes[0]['id_producto'];
?>
<div class="producto-card group cursor-pointer boutique-card rounded-2xl p-5 border border-[#EFE8F2] flex flex-col justify-between relative overflow-hidden" onclick="seleccionarGrupo(this, <?= json_encode($nombre, JSON_UNESCAPED_UNICODE) ?>)">
    <div class="absolute top-3.5 right-3.5 select-badge hidden w-6 h-6 rounded-full bg-[#754995] text-white items-center justify-center shadow-xs">
        <span class="material-symbols-outlined text-[15px]">check</span>
    </div>
    <div>
        <div class="w-12 h-12 rounded-xl bg-[#F6EEFA] flex items-center justify-center mb-4 text-[#754995] group-hover:scale-105 transition-transform duration-200">
            <span class="material-symbols-outlined text-[28px]"><?= $icono ?></span>
        </div>
        <h3 class="text-base font-bold text-[#241728] mb-1"><?= htmlspecialchars($nombre) ?></h3>
        <p class="text-[12px] text-on-surface-variant mb-4 leading-relaxed">Producto disponible para venta en caja.</p>
    </div>
    <div class="pt-3 border-t border-[#EFE8F2] flex items-baseline justify-between">
        <span class="text-[11px] font-semibold text-on-surface-variant uppercase tracking-wider">Base</span>
        <span class="text-sm font-extrabold text-[#754995]">Desde <?= '$' . number_format($precioMin, 0, ',', '.') ?></span>
    </div>
</div>
<?php endforeach; ?>
</div>
<!-- Footer Acción Paso 1 -->
<div class="mt-8 flex justify-end">
<button class="touch-btn px-8 py-3.5 bg-[#754995] hover:bg-[#683f85] text-white rounded-xl font-bold text-sm shadow-sm active:scale-95 transition-all flex items-center gap-2" onclick="irPaso(2)">
      Continuar a Sabores →
    </button>
</div>
</section>
<!-- =================== PASO 2: SABOR Y CANTIDAD =================== -->
<section class="hidden animate-fadeIn" id="paso2">
<div class="pb-6">
<span class="text-[11px] font-bold text-[#954363] uppercase tracking-wider" id="subtituloPaso2">Torta Clásica seleccionada</span>
<h1 class="text-2xl font-bold tracking-tight text-[#241728] mt-0.5">Selecciona Sabor y Cantidad</h1>
<p class="text-xs text-on-surface-variant">Escoge la variante de receta exacta y ajusta las porciones para el pedido.</p>
</div>
<!-- Layout Split Sabor + Cantidad -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<!-- Lista de Variantes (#gridSabores) -->
<div class="lg:col-span-2 space-y-2.5" id="gridSabores">
<!-- Se llena dinámicamente desde productos de la base de datos -->
</div>
<!-- Bloque Cantidad & Resumen Inmediato (#bloqueCantidad) -->
<div class="bg-white rounded-2xl p-6 border border-[#EFE8F2] shadow-xs flex flex-col justify-between" id="bloqueCantidad">
<div>
<h3 class="font-bold text-base text-[#241728] mb-1">Unidades del Pedido</h3>
<p class="text-xs text-on-surface-variant mb-5">Ajusta la cantidad de unidades para esta orden.</p>
<div class="bg-[#FAF8F9] rounded-2xl p-5 border border-[#EFE8F2] flex flex-col items-center justify-center text-center">
<span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Cantidad</span>
<div class="flex items-center gap-4 my-1">
<button class="w-12 h-12 rounded-xl bg-white border border-[#E2D9E7] flex items-center justify-center text-xl font-bold text-on-surface hover:bg-slate-50 active:scale-90 transition-transform shadow-2xs" onclick="cambiarCantidad(-1)" type="button">
<span class="material-symbols-outlined text-[22px]">remove</span>
</button>
<span class="text-3xl font-extrabold text-[#241728] min-w-[3rem] text-center" id="cantidadValor">1</span>
<button class="w-12 h-12 rounded-xl bg-white border border-[#E2D9E7] flex items-center justify-center text-xl font-bold text-on-surface hover:bg-slate-50 active:scale-90 transition-transform shadow-2xs" onclick="cambiarCantidad(1)" type="button">
<span class="material-symbols-outlined text-[22px]">add</span>
</button>
</div>
<p class="text-[11px] text-on-surface-variant mt-2 font-medium">Disponibles en vitrina: <span class="font-bold text-emerald-700">8 uds</span></p>
</div>
<!-- Subtotal Temporal -->
<div class="mt-5 pt-4 border-t border-[#EFE8F2] flex justify-between items-center">
<span class="text-xs font-semibold text-on-surface-variant">Subtotal estimado:</span>
<span class="text-xl font-extrabold text-[#754995]" id="subtotalPaso2">$90.000</span>
</div>
</div>
<div class="mt-6 space-y-2.5">
<button class="touch-btn w-full py-3.5 bg-[#754995] hover:bg-[#683f85] text-white rounded-xl font-bold text-sm shadow-sm active:scale-95 transition-all text-center" id="btnContinuarPaso2" onclick="irPaso(3)">
          Continuar al Cliente →
        </button>
<button class="touch-btn w-full py-2.5 text-on-surface-variant font-semibold text-xs hover:bg-[#FAF8F9] rounded-xl transition-colors" onclick="irPaso(1)">
          ← Volver a productos
        </button>
</div>
</div>
</div>
</section>
<!-- =================== PASO 3: ASIGNACIÓN DE CLIENTE =================== -->
<section class="hidden animate-fadeIn" id="paso3">
<div class="pb-6">
<h1 class="text-2xl font-bold tracking-tight text-[#241728]">Datos del Cliente</h1>
<p class="text-xs text-on-surface-variant mt-0.5">Asocia la factura a un cliente registrado o añade uno nuevo de forma ágil.</p>
</div>
<!-- Segmented Control: Existente / Nuevo -->
<div class="flex max-w-md bg-[#EFE8F2] p-1 rounded-xl mb-6">
<button class="flex-1 py-2.5 rounded-lg text-xs font-bold bg-white text-[#754995] shadow-xs transition-all flex items-center justify-center gap-2" id="tabClienteExistente" onclick="modoCliente('existente')">
<span class="material-symbols-outlined text-[17px]">person_search</span>
      Buscar cliente registrado
    </button>
<button class="flex-1 py-2.5 rounded-lg text-xs font-semibold text-on-surface-variant hover:text-on-surface transition-all flex items-center justify-center gap-2" id="tabClienteNuevo" onclick="modoCliente('nuevo')">
<span class="material-symbols-outlined text-[17px]">person_add</span>
      + Registrar nuevo cliente
    </button>
</div>
<!-- CONTENEDOR 1: CLIENTE EXISTENTE (#bloqueClienteExistente) -->
<div class="bg-white rounded-2xl p-6 border border-[#EFE8F2] shadow-xs" id="bloqueClienteExistente">
<div class="relative mb-5">
<span class="material-symbols-outlined absolute left-3.5 top-3.5 text-outline text-[20px]">search</span>
<input class="w-full pl-11 pr-4 py-3 bg-[#FAF8F9] border border-[#E2D9E7] rounded-xl text-sm focus:border-[#754995] focus:ring-2 focus:ring-[#754995]/20 outline-none transition-all" id="buscarCliente" oninput="filtrarClientes()" placeholder="Buscar por nombre, documento o teléfono..." type="text"/>
</div>
<!-- #listaClientesPos -->
<div class="space-y-2 max-h-80 overflow-y-auto custom-scroll pr-1" id="listaClientesPos">
<!-- Se llena por JavaScript -->
</div>
</div>
<!-- CONTENEDOR 2: CLIENTE NUEVO (#bloqueClienteNuevo) -->
<div class="hidden bg-white rounded-2xl p-6 border border-[#EFE8F2] shadow-xs" id="bloqueClienteNuevo">
<h3 class="font-bold text-sm text-[#241728] mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-[#754995] text-[20px]">assignment_ind</span>
      Formulario de Registro Rápido
    </h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="md:col-span-2">
<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Nombre Completo *</label>
<input class="w-full px-4 py-2.5 bg-[#FAF8F9] border border-[#E2D9E7] rounded-xl text-sm focus:border-[#754995] focus:ring-2 focus:ring-[#754995]/20 outline-none" id="formNuevoNombre" placeholder="Ej: Marcela Gómez Salazar" type="text"/>
</div>
<div>
<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Tipo de Documento</label>
<div class="flex gap-2" id="gridTipoDocumento">
<?php foreach ($listaTiposDocumento as $i => $tipo): ?>
<button class="tipo-doc-btn flex-1 py-2.5 <?= $i === 0 ? 'bg-[#754995] text-white border-[#754995]' : 'bg-[#FAF8F9] text-on-surface-variant border-[#E2D9E7]' ?> rounded-xl text-xs font-semibold border" onclick="setTipoDoc(this, <?= (int)$tipo['id_tipo_documento'] ?>)" type="button"><?= htmlspecialchars($tipo['tipo_documento']) ?></button>
<?php endforeach; ?>
</div>
</div>
<div>
<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Número de Documento *</label>
<input class="w-full px-4 py-2.5 bg-[#FAF8F9] border border-[#E2D9E7] rounded-xl text-sm focus:border-[#754995] focus:ring-2 focus:ring-[#754995]/20 outline-none" id="formNuevoNumero" placeholder="Ej: 1020304050" type="text"/>
</div>
<div>
<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Correo Electrónico</label>
<input class="w-full px-4 py-2.5 bg-[#FAF8F9] border border-[#E2D9E7] rounded-xl text-sm focus:border-[#754995] focus:ring-2 focus:ring-[#754995]/20 outline-none" id="formNuevoCorreo" placeholder="cliente@dulcemicro.co" type="email"/>
</div>
<div>
<label class="block text-xs font-bold text-on-surface-variant mb-1.5">Teléfono Móvil</label>
<input class="w-full px-4 py-2.5 bg-[#FAF8F9] border border-[#E2D9E7] rounded-xl text-sm focus:border-[#754995] focus:ring-2 focus:ring-[#754995]/20 outline-none" id="formNuevoTelefono" placeholder="300 123 4567" type="tel"/>
</div>
</div>
</div>
<!-- Footer Paso 3 -->
<div class="mt-8 flex justify-between items-center">
<button class="touch-btn px-6 py-3 text-on-surface-variant font-semibold text-xs hover:bg-slate-100 rounded-xl transition-colors" onclick="irPaso(2)">
      ← Volver a sabor
    </button>
<button class="touch-btn px-8 py-3.5 bg-[#754995] hover:bg-[#683f85] text-white rounded-xl font-bold text-sm shadow-sm active:scale-95 transition-all flex items-center gap-2" onclick="validarClienteYContinuar()">
      Continuar al Pago →
    </button>
</div>
</section>
<!-- =================== PASO 4: PAGO Y FACTURACIÓN =================== -->
<section class="hidden animate-fadeIn" id="paso4">
<div class="pb-5">
<h1 class="text-2xl font-bold tracking-tight text-[#241728]">Cierre y Liquidación</h1>
<p class="text-xs text-on-surface-variant mt-0.5">Selecciona el método de pago, registra el importe recibido y genera la factura.</p>
</div>
<!-- Split Pago Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
<!-- COLUMNA IZQUIERDA: RESUMEN DE LA VENTA (5 cols) -->
<div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-[#EFE8F2] shadow-xs relative">
<div class="flex items-center justify-between pb-4 border-b border-dashed border-[#EFE8F2]">
<div class="flex items-center gap-2">
<div class="w-7 h-7 rounded-lg overflow-hidden border border-[#EFE8F2] p-0.5">
<img alt="Dulce Micro" class="w-full h-full object-contain" src="/Dulce_Micro/img_logos/logo%20principal.png"/>
</div>
<span class="font-extrabold text-xs uppercase tracking-wider text-[#241728]">Ticket de Venta</span>
</div>
<span class="text-[11px] text-on-surface-variant font-medium" id="fechaTicket">Hoy, 10:45 AM</span>
</div>
<div class="py-4 space-y-3.5">
<!-- Info Producto -->
<div>
<span class="text-[10px] font-bold uppercase text-on-surface-variant tracking-wider">Item Seleccionado</span>
<p class="font-bold text-sm text-[#241728] mt-0.5" id="resumenProducto">Torta Clásica - Chocolate 1 Lb (x1)</p>
<p class="text-xs text-on-surface-variant" id="resumenSubtotalItem">Precio unitario: $90.000</p>
</div>
<!-- Info Cliente -->
<div class="pt-3 border-t border-[#EFE8F2]">
<span class="text-[10px] font-bold uppercase text-on-surface-variant tracking-wider">Cliente Asignado</span>
<p class="font-bold text-xs text-[#241728] mt-0.5" id="resumenCliente">Valentina Morales • CC 1023456789</p>
<p class="text-[11px] text-on-surface-variant" id="resumenContacto">valen.morales@gmail.com</p>
</div>
<!-- Breakdown -->
<div class="pt-3 border-t border-[#EFE8F2] space-y-1.5 text-xs text-on-surface-variant">
<div class="flex justify-between">
<span>Subtotal neto</span>
<span class="font-medium text-[#241728]" id="resumenBruto">$75.630</span>
</div>
<div class="flex justify-between">
<span>Impoconsumo / IVA (19%)</span>
<span class="font-medium text-[#241728]" id="resumenIva">$14.370</span>
</div>
</div>
</div>
<!-- Resumen Total Destacado (Visually Dominant) -->
<div class="mt-2 pt-4 border-t-2 border-[#754995]/20 bg-[#FAF8F9] p-4 rounded-xl text-center">
<span class="text-[11px] font-extrabold text-on-surface-variant uppercase tracking-wider">Total a Cobrar</span>
<div class="text-4xl font-extrabold text-[#4D3B56] my-1" id="resumenTotal">$90.000</div>
<span class="inline-block text-[10px] bg-emerald-50 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full border border-emerald-100">IVA incluido</span>
</div>
<!-- Botón Volver -->
<button class="mt-4 w-full py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-slate-100 rounded-xl transition-colors" onclick="irPaso(3)">
        ← Modificar cliente
      </button>
</div>
<!-- COLUMNA DERECHA: MEDIOS DE PAGO Y TECLADO (7 cols) -->
<div class="lg:col-span-7 space-y-4">
<!-- Métodos de Pago (#gridMetodosPago) -->
<div class="bg-white p-5 rounded-2xl border border-[#EFE8F2] shadow-xs">
<label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-2.5">Método de Pago</label>
<div class="grid grid-cols-3 sm:grid-cols-5 gap-2" id="gridMetodosPago">
<!-- Se llena por JavaScript -->
</div>
</div>
<!-- Bloque Efectivo & Teclado (#bloqueEfectivo) -->
<div class="bg-white p-5 rounded-2xl border border-[#EFE8F2] shadow-xs space-y-4" id="bloqueEfectivo">
<!-- Billetes Rápidos (#gridBilletes) -->
<div>
<div class="flex justify-between items-center mb-2">
<span class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Billetes Rápidos</span>
<button class="text-xs font-bold text-[#754995] hover:underline" onclick="fijarMontoExacto()">Importe Exacto</button>
</div>
<div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5" id="gridBilletes">
<!-- Renderizado por JS: $1.000 a $100.000 -->
</div>
</div>
<!-- Input Monto Recibido y Cambio Display -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center bg-[#FAF8F9] p-3.5 rounded-xl border border-[#E2D9E7]">
<div>
<span class="block text-[11px] text-on-surface-variant font-bold uppercase tracking-wider">Recibido</span>
<div class="flex items-center text-xl font-extrabold text-[#241728] mt-0.5">
<span class="text-[#754995] mr-1">$</span>
<input class="w-full bg-transparent border-none p-0 focus:ring-0 text-xl font-extrabold text-[#241728] outline-none" id="montoRecibido" readonly="" type="text" value="100.000"/>
</div>
</div>
<!-- #cambioDisplay -->
<div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-right" id="cambioDisplay">
<span class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Cambio a entregar</span>
<span class="text-lg font-extrabold text-emerald-700" id="cambioValor">$10.000</span>
</div>
</div>
<!-- Teclado Numérico Táctil Ergonómico (48-52px) -->
<div class="grid grid-cols-3 gap-2 pt-1">
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('1')">1</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('2')">2</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('3')">3</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('4')">4</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('5')">5</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('6')">6</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('7')">7</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('8')">8</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('9')">9</button>
<button class="touch-btn h-12 rounded-xl bg-[#F6EEFA] hover:bg-[#EFE3F7] font-bold text-sm text-[#754995] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('000')">000</button>
<button class="touch-btn h-12 rounded-xl bg-[#FAF8F9] hover:bg-[#EFE8F2] font-bold text-lg text-[#241728] border border-[#E2D9E7] shadow-2xs active:scale-95 transition-all" onclick="tecla('0')">0</button>
<button class="touch-btn h-12 rounded-xl bg-rose-50 hover:bg-rose-100 font-bold text-rose-700 border border-rose-200 shadow-2xs active:scale-95 transition-all flex items-center justify-center" onclick="borrarTecla()">
<span class="material-symbols-outlined text-[20px]">backspace</span>
</button>
</div>
</div>
<!-- Estado del Pedido (#gridEstadosPedido) -->
<div class="bg-white p-4 rounded-2xl border border-[#EFE8F2] shadow-xs">
<span class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Estado del Pedido</span>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-2" id="gridEstadosPedido">
<!-- Pills con [Pendiente], [En preparación], [Listo], [Entregado] -->
</div>
</div>
<!-- Botón Final Confirmar Venta (#btnConfirmarVenta) -->
<button class="touch-btn w-full py-4 rounded-xl text-white font-bold text-base shadow-sm hover:shadow-md active:scale-98 transition-all flex items-center justify-center gap-2" id="btnConfirmarVenta" onclick="confirmarVenta()" style="background-color: #35A56A;">
<span class="material-symbols-outlined text-[22px]">check_circle</span>
        ✓ Confirmar venta
      </button>
</div>
</div>
</section>
</div>
</div>
</main>
</div>
<!-- MODAL / CONFIRMACIÓN DE VENTA EXITOSA -->
<div class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" id="modalExito">
<div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-[#EFE8F2] text-center animate-scaleUp">
<div class="w-14 h-14 rounded-full bg-emerald-50 text-[#35A56A] flex items-center justify-center mx-auto mb-3 border border-emerald-100">
<span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
</div>
<h2 class="text-xl font-bold text-[#241728] mb-1">Venta Registrada con Éxito</h2>
<p class="text-xs text-on-surface-variant mb-5">El comprobante ha sido emitido y la comanda pasó a barra de entrega.</p>
<div class="bg-[#FAF8F9] rounded-2xl p-4 border border-[#EFE8F2] mb-6 text-left space-y-2">
<div class="flex justify-between items-center">
<span class="text-xs text-on-surface-variant font-medium">Comprobante:</span>
<span class="text-sm font-bold text-[#754995]" id="modalIdPedido">Pedido #123</span>
</div>
<div class="flex justify-between items-center">
<span class="text-xs text-on-surface-variant font-medium">Total liquidado:</span>
<span class="text-sm font-extrabold text-[#241728]" id="modalTotalCobrado">$90.000</span>
</div>
<div class="flex justify-between items-center">
<span class="text-xs text-on-surface-variant font-medium">Medio de pago:</span>
<span class="text-xs font-bold text-[#954363] bg-[#FCE8F0] px-2.5 py-0.5 rounded-full" id="modalMetodoUsado">Efectivo</span>
</div>
</div>
<div class="grid grid-cols-2 gap-3">
<button class="touch-btn py-3 px-4 rounded-xl border border-[#E2D9E7] text-[#241728] font-bold text-xs hover:bg-slate-50 flex items-center justify-center gap-1.5 transition-colors" onclick="imprimirFactura()">
<span class="material-symbols-outlined text-[17px]">print</span>
      🖨 Imprimir
    </button>
<button class="touch-btn py-3 px-4 rounded-xl bg-[#754995] hover:bg-[#683f85] text-white font-bold text-xs shadow-xs transition-opacity" onclick="reiniciarVenta()">
      Nueva Venta
    </button>
</div>
</div>
</div>
<!-- JAVASCRIPT LOGIC & INTERACTIONS -->
<script>
const productosAgrupados = <?= json_encode($productosAgrupados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const listaClientesData = <?= json_encode($listaClientes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const listaMetodosPagoData = <?= json_encode($listaMetodosPago, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const listaEstadosPedidoData = <?= json_encode($listaEstadosPedido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const estadoPOS = {
    pasoActual: 1,
    grupoActual: null,
    producto: null,
    cantidad: 1,
    clienteTipo: 'existente',
    cliente: null,
    nuevoTipoDocumento: <?= isset($listaTiposDocumento[0]['id_tipo_documento']) ? (int)$listaTiposDocumento[0]['id_tipo_documento'] : 0 ?>,
    metodoPago: null,
    estadoPedido: null,
    montoRecibido: 0
};

function formatCOP(num) {
    return '$' + Number(num || 0).toLocaleString('es-CO');
}

function irPaso(paso) {
    if (paso < 1 || paso > 4) return;
    if (paso === 2 && !estadoPOS.grupoActual) { alert('Selecciona un producto primero.'); return; }
    if (paso === 3 && !estadoPOS.producto) { alert('Selecciona una variante de producto primero.'); return; }
    estadoPOS.pasoActual = paso;

    for (let i = 1; i <= 4; i++) {
        const sec = document.getElementById(`paso${i}`);
        if (sec) sec.classList.toggle('hidden', i !== paso);
        const node = document.getElementById(`step-node-${i}`);
        const icon = document.getElementById(`step-icon-${i}`);
        const line = document.getElementById(`step-line-${i}`);
        if (!node || !icon) continue;
        if (i < paso) {
            node.className = 'flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full bg-[#954363] text-white shadow-xs transition-all duration-200';
            icon.innerHTML = '<span class="material-symbols-outlined text-[13px]">check</span>';
            icon.className = 'w-5 h-5 rounded-full bg-white/20 text-white flex items-center justify-center text-[11px] font-bold';
            if (line) line.className = 'w-6 h-px bg-[#954363] mx-1 transition-colors';
        } else if (i === paso) {
            node.className = 'flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full bg-[#754995] text-white shadow-xs transition-all duration-200';
            icon.innerText = i;
            icon.className = 'w-5 h-5 rounded-full bg-white/25 text-white flex items-center justify-center text-[11px] font-bold';
            if (line) line.className = 'w-6 h-px bg-[#E2D9E7] mx-1 transition-colors';
        } else {
            node.className = 'flex items-center gap-2 cursor-pointer px-3 py-1 rounded-full text-on-surface-variant hover:bg-[#EFE8F2]/50 transition-all duration-200';
            icon.innerText = i;
            icon.className = 'w-5 h-5 rounded-full border border-[#D5CADB] text-on-surface-variant flex items-center justify-center text-[11px] font-bold';
            if (line) line.className = 'w-6 h-px bg-[#E2D9E7] mx-1 transition-colors';
        }
    }

    if (paso === 2) renderSabores();
    if (paso === 3) renderClientes();
    if (paso === 4) {
        actualizarResumenPago();
        renderMetodosPago();
        renderBilletes();
        renderEstadosPedido();
        actualizarCambio();
    }
}

function seleccionarGrupo(elem, nombre) {
    document.querySelectorAll('#gridProductosBase .producto-card').forEach(card => {
        card.classList.remove('border-2', 'border-[#754995]', 'ring-4', 'ring-[#754995]/5');
        card.classList.add('border', 'border-[#EFE8F2]');
        const badge = card.querySelector('.select-badge');
        if (badge) { badge.classList.add('hidden'); badge.classList.remove('flex'); }
    });
    elem.classList.remove('border', 'border-[#EFE8F2]');
    elem.classList.add('border-2', 'border-[#754995]', 'ring-4', 'ring-[#754995]/5');
    const badge = elem.querySelector('.select-badge');
    if (badge) { badge.classList.remove('hidden'); badge.classList.add('flex'); }

    estadoPOS.grupoActual = nombre;
    estadoPOS.producto = null;
    estadoPOS.cantidad = 1;
    document.getElementById('cantidadValor').innerText = '1';
    document.getElementById('inputCantidad').value = '1';
    renderSabores();
    irPaso(2);
}

function renderSabores() {
    const cont = document.getElementById('gridSabores');
    cont.innerHTML = '';
    const variantes = productosAgrupados[estadoPOS.grupoActual] || [];
    if (!variantes.length) {
        cont.innerHTML = '<div class="p-6 bg-white rounded-xl border border-[#EFE8F2] text-sm text-on-surface-variant">No hay variantes disponibles.</div>';
        return;
    }
    variantes.forEach((v, index) => {
        const nombre = `${v.sabor || 'Sin sabor'} - ${v.tamano || 'Sin tamaño'}`;
        const btn = document.createElement('div');
        btn.className = 'sabor-item flex items-center justify-between p-4 rounded-xl bg-white border border-[#EFE8F2] shadow-2xs hover:border-[#D5CADB] cursor-pointer transition-all duration-150';
        btn.onclick = () => seleccionarVariante(btn, v);
        const iniciales = nombre.split(/\s+/).slice(0,2).map(x => x.charAt(0)).join('').toUpperCase();
        btn.innerHTML = `
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-[#F6EEFA] text-[#754995] flex items-center justify-center font-bold text-xs">${iniciales}</div>
                <div><h4 class="font-bold text-sm text-[#241728]">${escapeHtml(nombre)}</h4><p class="text-xs text-on-surface-variant">Variante disponible para venta</p></div>
            </div>
            <div class="text-right"><span class="text-base font-extrabold text-[#241728]">${formatCOP(v.precio_base)}</span><span class="block text-[11px] text-emerald-700 font-semibold">Disponible</span></div>`;
        cont.appendChild(btn);
        if (index === 0) seleccionarVariante(btn, v);
    });
}

function seleccionarVariante(elem, v) {
    document.querySelectorAll('#gridSabores .sabor-item').forEach(item => {
        item.classList.remove('border-2', 'border-[#754995]', 'ring-4', 'ring-[#754995]/5');
        item.classList.add('border', 'border-[#EFE8F2]');
    });
    elem.classList.remove('border', 'border-[#EFE8F2]');
    elem.classList.add('border-2', 'border-[#754995]', 'ring-4', 'ring-[#754995]/5');
    estadoPOS.producto = v;
    estadoPOS.cantidad = 1;
    document.getElementById('cantidadValor').innerText = '1';
    document.getElementById('inputIdProducto').value = v.id_producto;
    document.getElementById('inputCantidad').value = '1';
    actualizarSubtotalPaso2();
}

function cambiarCantidad(delta) {
    estadoPOS.cantidad = Math.max(1, Math.min(50, estadoPOS.cantidad + delta));
    document.getElementById('cantidadValor').innerText = estadoPOS.cantidad;
    document.getElementById('inputCantidad').value = estadoPOS.cantidad;
    actualizarSubtotalPaso2();
}

function actualizarSubtotalPaso2() {
    const total = estadoPOS.producto ? parseFloat(estadoPOS.producto.precio_base) * estadoPOS.cantidad : 0;
    document.getElementById('subtotalPaso2').innerText = formatCOP(total);
}

function normalizarCliente(c) {
    return {
        id: c.id_cliente,
        nombre: c.nombre || '',
        doc: c.numero_documento || 'Sin documento',
        email: c.correo_electronico || c.correo || 'Sin correo',
        tel: c.telefono || 'Sin teléfono'
    };
}

function modoCliente(tipo) {
    estadoPOS.clienteTipo = tipo;
    document.getElementById('inputClienteTipo').value = tipo;
    const tabExistente = document.getElementById('tabClienteExistente');
    const tabNuevo = document.getElementById('tabClienteNuevo');
    const bloqueExistente = document.getElementById('bloqueClienteExistente');
    const bloqueNuevo = document.getElementById('bloqueClienteNuevo');
    if (tipo === 'existente') {
        tabExistente.className = 'flex-1 py-2.5 rounded-lg text-xs font-bold bg-white text-[#754995] shadow-xs transition-all flex items-center justify-center gap-2';
        tabNuevo.className = 'flex-1 py-2.5 rounded-lg text-xs font-semibold text-on-surface-variant hover:text-on-surface transition-all flex items-center justify-center gap-2';
        bloqueExistente.classList.remove('hidden'); bloqueNuevo.classList.add('hidden');
    } else {
        tabNuevo.className = 'flex-1 py-2.5 rounded-lg text-xs font-bold bg-white text-[#754995] shadow-xs transition-all flex items-center justify-center gap-2';
        tabExistente.className = 'flex-1 py-2.5 rounded-lg text-xs font-semibold text-on-surface-variant hover:text-on-surface transition-all flex items-center justify-center gap-2';
        bloqueNuevo.classList.remove('hidden'); bloqueExistente.classList.add('hidden');
    }
}

function renderClientes(filtro = '') {
    const cont = document.getElementById('listaClientesPos');
    const f = filtro.toLowerCase().trim();
    const clientes = listaClientesData.map(normalizarCliente).filter(c => !f || `${c.nombre} ${c.doc} ${c.tel}`.toLowerCase().includes(f));
    cont.innerHTML = '';
    if (!clientes.length) { cont.innerHTML = '<div class="p-6 text-center text-on-surface-variant text-xs">No se encontraron clientes.</div>'; return; }
    clientes.forEach(c => {
        const seleccionado = estadoPOS.cliente && estadoPOS.cliente.id === c.id;
        const item = document.createElement('div');
        item.className = `flex items-center justify-between p-3.5 rounded-xl cursor-pointer border transition-all ${seleccionado ? 'bg-[#F6EEFA] border-[#754995] shadow-2xs' : 'bg-white border-[#EFE8F2] hover:bg-[#FAF8F9]'}`;
        item.onclick = () => seleccionarCliente(c);
        item.innerHTML = `<div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl ${seleccionado ? 'bg-[#754995] text-white' : 'bg-[#FAF8F9] text-on-surface-variant border border-[#E2D9E7]'} flex items-center justify-center text-xs font-bold">${escapeHtml(c.nombre.charAt(0))}</div><div><p class="font-bold text-xs text-[#241728]">${escapeHtml(c.nombre)}</p><p class="text-[11px] text-on-surface-variant">${escapeHtml(c.doc)} • ${escapeHtml(c.tel)}</p></div></div><span class="text-xs ${seleccionado ? 'text-[#754995] font-bold' : 'text-[#887D8E]'}">${seleccionado ? 'Seleccionado' : 'Seleccionar'}</span>`;
        cont.appendChild(item);
    });
}

function filtrarClientes() { renderClientes(document.getElementById('buscarCliente').value); }
function seleccionarCliente(c) {
    estadoPOS.cliente = c;
    document.getElementById('inputIdCliente').value = c.id;
    renderClientes(document.getElementById('buscarCliente').value || '');
}
function setTipoDoc(btn, tipo) {
    document.querySelectorAll('.tipo-doc-btn').forEach(b => b.className = 'tipo-doc-btn flex-1 py-2.5 bg-[#FAF8F9] text-on-surface-variant rounded-xl text-xs font-semibold border border-[#E2D9E7]');
    btn.className = 'tipo-doc-btn flex-1 py-2.5 bg-[#754995] text-white rounded-xl text-xs font-bold border border-[#754995]';
    estadoPOS.nuevoTipoDocumento = tipo;
    document.getElementById('inputNuevoTipoDocumento').value = tipo;
}
function validarClienteYContinuar() {
    if (estadoPOS.clienteTipo === 'existente') {
        if (!estadoPOS.cliente) { alert('Selecciona un cliente.'); return; }
    } else {
        const nombre = document.getElementById('formNuevoNombre').value.trim();
        const doc = document.getElementById('formNuevoNumero').value.trim();
        if (!nombre || !doc || !estadoPOS.nuevoTipoDocumento) { alert('Completa nombre, tipo y número de documento.'); return; }
        document.getElementById('inputNuevoNombre').value = nombre;
        document.getElementById('inputNuevoTipoDocumento').value = estadoPOS.nuevoTipoDocumento;
        document.getElementById('inputNuevoNumeroDocumento').value = doc;
        document.getElementById('inputNuevoCorreo').value = document.getElementById('formNuevoCorreo').value.trim();
        document.getElementById('inputNuevoTelefono').value = document.getElementById('formNuevoTelefono').value.trim();
        estadoPOS.cliente = {id: 0, nombre, doc: doc, email: document.getElementById('formNuevoCorreo').value.trim() || 'Sin correo', tel: document.getElementById('formNuevoTelefono').value.trim() || 'Sin teléfono'};
    }
    irPaso(4);
}

function calcularTotal() { return estadoPOS.producto ? parseFloat(estadoPOS.producto.precio_base) * estadoPOS.cantidad : 0; }
function actualizarResumenPago() {
    const total = calcularTotal();
    const iva = Math.round(total * 0.19 / 1.19);
    const neto = total - iva;
    const p = estadoPOS.producto;
    document.getElementById('resumenProducto').innerText = p ? `${estadoPOS.grupoActual} - ${p.sabor || ''} ${p.tamano || ''} (x${estadoPOS.cantidad})` : '-';
    document.getElementById('resumenSubtotalItem').innerText = p ? `Precio unitario: ${formatCOP(p.precio_base)}` : 'Precio unitario: $0';
    document.getElementById('resumenCliente').innerText = estadoPOS.cliente ? `${estadoPOS.cliente.nombre} • ${estadoPOS.cliente.doc}` : 'Cliente no seleccionado';
    document.getElementById('resumenContacto').innerText = estadoPOS.cliente ? `${estadoPOS.cliente.email} • ${estadoPOS.cliente.tel}` : '';
    document.getElementById('resumenBruto').innerText = formatCOP(neto);
    document.getElementById('resumenIva').innerText = formatCOP(iva);
    document.getElementById('resumenTotal').innerText = formatCOP(total);
    if (estadoPOS.montoRecibido < total) fijarMontoExacto();
}

function renderMetodosPago() {
    const cont = document.getElementById('gridMetodosPago'); cont.innerHTML = '';
    listaMetodosPagoData.forEach(m => {
        const id = m.id_metodos_pago;
        const nombre = m.metodo_pago;
        const activo = String(estadoPOS.metodoPago) === String(id);
        const btn = document.createElement('button'); btn.type='button';
        btn.className = `touch-btn p-3 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all ${activo ? 'bg-[#754995] text-white border-[#754995] shadow-xs' : 'bg-white border-[#E2D9E7] text-on-surface-variant hover:bg-[#FAF8F9]'}`;
        btn.onclick = () => seleccionarMetodoPago(m);
        btn.innerHTML = `<span class="material-symbols-outlined text-[20px]">${iconoPago(nombre)}</span><span class="text-xs font-bold">${escapeHtml(nombre)}</span>`;
        cont.appendChild(btn);
    });
}
function iconoPago(nombre) {
    const n = String(nombre).toLowerCase();
    if (n.includes('efect')) return 'payments'; if (n.includes('nequi')) return 'account_balance_wallet'; if (n.includes('davi')) return 'send_to_mobile'; if (n.includes('pse')) return 'account_balance'; return 'credit_card';
}
function seleccionarMetodoPago(m) {
    estadoPOS.metodoPago = m; document.getElementById('inputIdMetodoPago').value = m.id_metodos_pago; renderMetodosPago();
    const efectivo = String(m.metodo_pago).toLowerCase().includes('efectivo');
    const bloque = document.getElementById('bloqueEfectivo');
    bloque.classList.toggle('opacity-40', !efectivo); bloque.classList.toggle('pointer-events-none', !efectivo);
    if (!efectivo) fijarMontoExacto();
}
function renderBilletes() {
    const cont=document.getElementById('gridBilletes'); cont.innerHTML='';
    [1000,2000,5000,10000,20000,50000,100000].forEach(v=>{const b=document.createElement('button');b.type='button';b.className='py-2 px-1 bg-[#FAF8F9] hover:bg-[#EFE8F2] border border-[#E2D9E7] rounded-lg text-[11px] font-bold text-[#241728] transition-all active:scale-95 shadow-2xs';b.innerText=formatCOP(v);b.onclick=()=>{estadoPOS.montoRecibido=v;document.getElementById('montoRecibido').value=Number(v).toLocaleString('es-CO');actualizarCambio();};cont.appendChild(b);});
}
function tecla(val) { let s=document.getElementById('montoRecibido').value.replace(/\D/g,'')||''; s+=val; const n=parseInt(s,10)||0; estadoPOS.montoRecibido=n; document.getElementById('montoRecibido').value=n.toLocaleString('es-CO'); actualizarCambio(); }
function borrarTecla() { let s=document.getElementById('montoRecibido').value.replace(/\D/g,'').slice(0,-1); const n=parseInt(s,10)||0; estadoPOS.montoRecibido=n; document.getElementById('montoRecibido').value=n.toLocaleString('es-CO'); actualizarCambio(); }
function fijarMontoExacto() { const t=calcularTotal(); estadoPOS.montoRecibido=t; document.getElementById('montoRecibido').value=t.toLocaleString('es-CO'); actualizarCambio(); }
function actualizarCambio() { const t=calcularTotal(), r=estadoPOS.montoRecibido||0, c=r-t, box=document.getElementById('cambioDisplay'), val=document.getElementById('cambioValor'); if(c>=0){box.className='p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-right';val.className='text-lg font-extrabold text-[#35A56A]';val.innerText=formatCOP(c);}else{box.className='p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-right';val.className='text-lg font-extrabold text-[#C0392B]';val.innerText='Faltan: '+formatCOP(Math.abs(c));} }
function renderEstadosPedido() { const cont=document.getElementById('gridEstadosPedido');cont.innerHTML='';listaEstadosPedidoData.forEach(e=>{const id=e.id_estado_pedido,n=e.estado_pedido,activo=String(estadoPOS.estadoPedido)===String(id);const b=document.createElement('button');b.type='button';b.className=`py-2 px-2 rounded-xl text-xs font-bold border transition-all ${activo?'bg-[#954363] text-white border-[#954363] shadow-xs':'bg-[#FAF8F9] text-on-surface-variant border-[#E2D9E7] hover:bg-[#FAF8F9]'}`;b.innerText=n;b.onclick=()=>{estadoPOS.estadoPedido=e;document.getElementById('inputIdEstadoPedido').value=id;renderEstadosPedido();};cont.appendChild(b);}); }
function confirmarVenta() {
    if (!estadoPOS.producto) { alert('Selecciona un producto.'); return; }
    if (!estadoPOS.metodoPago) { alert('Selecciona un método de pago.'); return; }
    if (!estadoPOS.estadoPedido) { alert('Selecciona el estado del pedido.'); return; }
    if (String(estadoPOS.metodoPago.metodo_pago).toLowerCase().includes('efectivo') && estadoPOS.montoRecibido < calcularTotal()) { alert('El monto recibido en efectivo no cubre el total.'); return; }
    document.getElementById('formVenta').submit();
}
function imprimirFactura(){ window.print(); }
function reiniciarVenta(){ window.location.href=window.location.pathname; }
function escapeHtml(value){const div=document.createElement('div');div.textContent=value??'';return div.innerHTML;}

document.addEventListener('DOMContentLoaded',()=>{
    const fecha=document.getElementById('fechaTicket'); if(fecha){fecha.innerText=`Hoy, ${new Date().toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit',hour12:true})}`;}
    const primerCliente=listaClientesData.length ? normalizarCliente(listaClientesData[0]) : null;
    if(primerCliente) estadoPOS.cliente=primerCliente;
    const primerMetodo=listaMetodosPagoData.length ? listaMetodosPagoData[0] : null;
    if(primerMetodo){estadoPOS.metodoPago=primerMetodo;document.getElementById('inputIdMetodoPago').value=primerMetodo.id_metodos_pago;}
    const primerEstado=listaEstadosPedidoData.length ? listaEstadosPedidoData[0] : null;
    if(primerEstado){estadoPOS.estadoPedido=primerEstado;document.getElementById('inputIdEstadoPedido').value=primerEstado.id_estado_pedido;}
    document.getElementById('inputNuevoTipoDocumento').value=estadoPOS.nuevoTipoDocumento;
    renderClientes(); renderMetodosPago(); renderBilletes(); renderEstadosPedido(); modoCliente('existente'); irPaso(1);
});
</script>
</body></html>