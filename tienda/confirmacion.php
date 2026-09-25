<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/factura_lib.php';

exigirCliente();

$conn      = (new conexion())->conn;
$idCliente = (int)$_SESSION['id_cliente'];
$orden     = $_GET['orden'] ?? '';

$d = cargarPedidoWeb($conn, $orden, $idCliente);
if (!$d) {
    header('Location: catalogo.php');
    exit;
}
$p     = $d['pedido'];
$items = $d['items'];
$pago  = $d['pago'];

// La primera vez que se ve esta pantalla se intenta mandar la factura sola.
// Si FPDF o PHPMailer aún no están instalados, no falla la página: solo no se envía.
$avisoFactura = null;
if (!$p['factura_enviada']) {
    [$ok, $mensaje] = enviarFacturaPorCorreo($conn, $orden, $idCliente);
    $avisoFactura = ['ok' => $ok, 'mensaje' => $mensaje];
    if ($ok) {
        $p['factura_enviada'] = 1;
    }
}

// Mensaje que deja reenviar_factura.php al volver de un reenvío manual
$flashFactura = $_SESSION['flash_factura'] ?? null;
unset($_SESSION['flash_factura']);
if ($flashFactura && $flashFactura['orden'] === $orden) {
    $avisoFactura = $flashFactura;
}

$tituloPagina = 'Pedido confirmado';
$paginaActiva = '';
require_once __DIR__ . '/partials/header.php';
?>

<main class="flex-1 py-10 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto w-full">

    <div class="bg-white rounded-3xl shadow-lila-hover p-6 sm:p-10 md:p-12 relative overflow-hidden border border-[#F3E6F7]">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#F7A8C4] via-[#8A5AAE] to-[#C9A0DC]"></div>

        <div class="text-center max-w-2xl mx-auto mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[#5FA37A]/15 mb-5 ring-8 ring-[#5FA37A]/10">
                <span class="material-symbols-outlined text-[#5FA37A] text-5xl" style="font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">
                ¡Gracias por tu compra, <?= htmlspecialchars(explode(' ', $p['cliente_nombre'])[0]) ?>!
            </h1>
            <p class="text-base text-[#6E5A7A]">
                Tu pedido fue recibido y nuestros reposteros empezarán a prepararlo con ingredientes frescos.
            </p>

            <?php if ($avisoFactura && $avisoFactura['ok']): ?>
                <div class="mt-6 inline-flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-[#F1E2F6] border border-[#E4CEEE] text-[#5F3A7E] text-left shadow-sm">
                    <span class="material-symbols-outlined text-[#8A5AAE] text-2xl shrink-0">mark_email_read</span>
                    <span class="text-sm sm:text-base font-semibold">
                        Te enviamos la factura a tu correo
                        <span class="text-[#8A5AAE] font-bold underline decoration-[#C9A0DC]"><?= htmlspecialchars($p['correo_factura']) ?></span>
                    </span>
                </div>
            <?php elseif ($avisoFactura): ?>
                <div class="mt-6 inline-flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-[#FDE2EC] border border-[#F7A8C4] text-[#6B2244] text-left shadow-sm">
                    <span class="material-symbols-outlined text-2xl shrink-0">error</span>
                    <span class="text-sm sm:text-base font-semibold"><?= htmlspecialchars($avisoFactura['mensaje']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Datos del pedido -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5 rounded-2xl bg-[#FDF3F8]/60 mb-8 border border-[#F3E6F7]">
            <div>
                <span class="text-xs text-[#6E5A7A] uppercase tracking-wider font-semibold">Número de pedido</span>
                <span class="text-2xl text-[#8A5AAE] font-extrabold mt-0.5 block"><?= htmlspecialchars($p['numero_orden']) ?></span>
            </div>
            <div>
                <span class="text-xs text-[#6E5A7A] uppercase tracking-wider font-semibold mb-1 block">Estado</span>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#5FA37A]/15 text-[#5FA37A] font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-[#5FA37A] animate-pulse"></span>
                    Pedido recibido
                </span>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <span class="text-xs text-[#6E5A7A] uppercase tracking-wider font-semibold">Método de pago</span>
                <div class="flex items-center gap-2 mt-1">
                    <span class="material-symbols-outlined text-[#8A5AAE] text-xl">account_balance</span>
                    <span class="text-base font-bold">
                        <?= htmlspecialchars($pago['metodo_pago']) ?>
                        <span class="text-[#5FA37A] font-semibold text-sm">(<?= htmlspecialchars($pago['estado_pago']) ?>)</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Entrega -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            <div class="p-5 rounded-2xl bg-[#F1E2F6]/40 border border-[#F3E6F7] flex items-start gap-3.5">
                <div class="p-2.5 rounded-full bg-[#8A5AAE] text-white flex-shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-xl">event_available</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-[#8A5AAE] uppercase tracking-wider">Fecha estimada de entrega</h2>
                    <p class="text-base mt-1 font-bold"><?= htmlspecialchars(fechaLarga($p['fecha_entrega'])) ?></p>
                    <p class="text-sm text-[#6E5A7A]"><?= FRANJAS[$p['franja']][0] ?? '' ?> (<?= FRANJAS[$p['franja']][1] ?? '' ?>)</p>
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-[#F1E2F6]/40 border border-[#F3E6F7] flex items-start gap-3.5">
                <div class="p-2.5 rounded-full bg-[#8A5AAE] text-white flex-shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-xl">location_on</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-[#8A5AAE] uppercase tracking-wider">Dirección de entrega</h2>
                    <p class="text-base mt-1 font-bold"><?= htmlspecialchars($p['direccion']) ?></p>
                    <p class="text-sm text-[#6E5A7A]"><?= htmlspecialchars(($p['barrio'] ? $p['barrio'] . ', ' : '') . $p['ciudad']) ?></p>
                </div>
            </div>
        </div>

        <!-- Resumen de productos -->
        <div class="rounded-2xl border border-[#F3E6F7] overflow-hidden mb-8">
            <div class="bg-[#F1E2F6]/80 px-6 py-4 flex items-center justify-between border-b border-[#F3E6F7]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#8A5AAE] text-xl">receipt_long</span>
                    <h2 class="text-lg font-bold">Resumen del pedido</h2>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white text-[#8A5AAE] border border-[#F3E6F7]">
                    <?= count($items) ?> <?= count($items) === 1 ? 'producto' : 'productos' ?>
                </span>
            </div>
            <div class="divide-y divide-[#F3E6F7] bg-white">
                <?php foreach ($items as $it):
                    $tieneFoto = !empty($it['imagen']);
                    $foto = $tieneFoto ? '/Dulce_Micro/img_productos/' . rawurlencode($it['imagen']) : $logoTienda;
                ?>
                    <div class="p-5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <img src="<?= htmlspecialchars($foto) ?>" alt=""
                                 class="w-14 h-14 rounded-xl bg-[#FDF3F8] flex-shrink-0 border border-[#F3E6F7] <?= $tieneFoto ? 'object-cover' : 'object-contain p-2 opacity-80' ?>">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold truncate"><?= htmlspecialchars($it['nombre_producto']) ?></h3>
                                <span class="text-xs font-semibold text-[#8A5AAE]">Cantidad: <?= (int)$it['cantidad'] ?></span>
                            </div>
                        </div>
                        <span class="text-lg font-extrabold whitespace-nowrap"><?= formatoCOP((float)$it['subtotal']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="p-6 bg-[#FDF3F8]/70 border-t border-[#F3E6F7]">
                <div class="max-w-xs ml-auto space-y-2 text-sm">
                    <div class="flex justify-between text-[#6E5A7A]">
                        <span>Subtotal:</span>
                        <span class="font-bold"><?= formatoCOP((float)$p['subtotal']) ?></span>
                    </div>
                    <div class="flex justify-between text-[#6E5A7A]">
                        <span>Envío:</span>
                        <span class="font-bold"><?= (float)$p['costo_envio'] > 0 ? formatoCOP((float)$p['costo_envio']) : 'Gratis' ?></span>
                    </div>
                    <div class="pt-3 border-t border-[#F3E6F7] flex justify-between items-baseline">
                        <span class="text-lg font-extrabold">Total pagado:</span>
                        <span class="text-2xl font-extrabold text-[#8A5AAE]"><?= formatoCOP((float)$p['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
            <a href="mis_pedidos.php"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-[#8A5AAE] text-white font-bold text-sm hover:bg-[#734493] transition-all active:scale-95">
                <span class="material-symbols-outlined text-xl">inventory_2</span>
                Ver mis pedidos
            </a>
            <a href="descargar_factura.php?orden=<?= urlencode($orden) ?>"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full bg-[#F1E2F6] text-[#8A5AAE] border-2 border-[#8A5AAE]/40 hover:border-[#8A5AAE] font-bold text-sm transition-all active:scale-95">
                <span class="material-symbols-outlined text-xl">description</span>
                Descargar factura PDF
            </a>
        </div>

        <form method="post" action="reenviar_factura.php" class="text-center pt-6 mt-8 border-t border-[#F3E6F7]">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="orden" value="<?= htmlspecialchars($orden) ?>">
            <button type="submit"
                    class="inline-flex items-center gap-2.5 text-sm font-semibold text-[#8A5AAE] hover:text-[#734493] py-2 px-4 rounded-full hover:bg-[#F1E2F6] transition-colors">
                <span class="material-symbols-outlined text-xl">mail</span>
                Reenviar factura a mi correo
            </button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
