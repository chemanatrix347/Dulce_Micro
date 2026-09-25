<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/config_tienda.php';

exigirCliente('pedidos');

$conn      = (new conexion())->conn;
$idCliente = (int)$_SESSION['id_cliente'];

/* ---------- Filtro de la URL ---------- */
$filtro = in_array($_GET['filtro'] ?? '', ['curso', 'entregados'], true) ? $_GET['filtro'] : 'todos';

/* ---------- Pedidos del cliente, con el estado más atrasado de sus productos ---------- */
$stPedidos = $conn->prepare(
    "SELECT w.id_pedido_web, w.numero_orden, w.fecha_entrega, w.franja, w.subtotal, w.costo_envio, w.total, w.fecha_creacion,
            MIN(pc.id_estado_pedido) AS id_estado_pedido
       FROM pedido_web w
       JOIN pedido_completo pc ON pc.id_pedido_web = w.id_pedido_web AND pc.estado = 1
      WHERE w.id_cliente = ? AND w.estado = 1
      GROUP BY w.id_pedido_web
      ORDER BY w.fecha_creacion DESC"
);
$stPedidos->execute([$idCliente]);
$todosLosPedidos = $stPedidos->fetchAll(PDO::FETCH_ASSOC);

// El progreso se muestra en 4 pasos; tu tabla estado_pedido tiene 5, así que
// "Completado" y "Enviado" comparten el paso 3 ("Listo").
$etapaVisual = function (int $idEstado): int {
    return match (true) {
        $idEstado <= 1 => 1,   // Pendiente      -> Recibido
        $idEstado === 2 => 2,  // En preparación
        $idEstado <= 4 => 3,   // Completado / Enviado -> Listo
        default => 4,          // Entregado
    };
};

$conteos = ['todos' => count($todosLosPedidos), 'curso' => 0, 'entregados' => 0];
foreach ($todosLosPedidos as $p) {
    $etapaVisual((int)$p['id_estado_pedido']) === 4 ? $conteos['entregados']++ : $conteos['curso']++;
}

$pedidos = array_values(array_filter($todosLosPedidos, function ($p) use ($filtro, $etapaVisual) {
    if ($filtro === 'todos') return true;
    $e = $etapaVisual((int)$p['id_estado_pedido']);
    return $filtro === 'entregados' ? $e === 4 : $e < 4;
}));

// Nombre de cada estado, tal como está en tu tabla estado_pedido (para la etiqueta de cabecera)
$estados = $conn->query('SELECT id_estado_pedido, estado_pedido FROM estado_pedido')
                ->fetchAll(PDO::FETCH_KEY_PAIR);

/* ---------- Productos y pago de cada pedido, en dos consultas ---------- */
$itemsPorPedido = [];
$pagoPorPedido  = [];
if ($todosLosPedidos) {
    $ids    = array_column($todosLosPedidos, 'id_pedido_web');
    $marcas = implode(',', array_fill(0, count($ids), '?'));

    $stItems = $conn->prepare(
        "SELECT pc.id_pedido_web, pc.id_pedido, pc.cantidad, pc.subtotal,
                pr.nombre_producto, pr.descripcion, pr.imagen
           FROM pedido_completo pc
           LEFT JOIN productos pr ON pr.id_producto = pc.id_producto
          WHERE pc.id_pedido_web IN ($marcas) AND pc.estado = 1
          ORDER BY pc.id_pedido"
    );
    $stItems->execute($ids);
    $idPedidoAWeb = [];
    foreach ($stItems->fetchAll(PDO::FETCH_ASSOC) as $it) {
        $itemsPorPedido[(int)$it['id_pedido_web']][] = $it;
        $idPedidoAWeb[(int)$it['id_pedido']] = (int)$it['id_pedido_web'];
    }

    if ($idPedidoAWeb) {
        $marcasPago = implode(',', array_fill(0, count($idPedidoAWeb), '?'));
        $stPago = $conn->prepare(
            "SELECT pg.id_pedido, mp.metodo_pago, ep.estado_pago
               FROM pagos pg
               JOIN metodos_pago mp ON mp.id_metodos_pago = pg.id_metodo_pago
               JOIN estado_pagos ep ON ep.id_estado_pago = pg.id_estado_pago
              WHERE pg.id_pedido IN ($marcasPago)"
        );
        $stPago->execute(array_keys($idPedidoAWeb));
        foreach ($stPago->fetchAll(PDO::FETCH_ASSOC) as $pg) {
            $idWeb = $idPedidoAWeb[(int)$pg['id_pedido']] ?? null;
            if ($idWeb !== null && !isset($pagoPorPedido[$idWeb])) {
                $pagoPorPedido[$idWeb] = $pg;   // el primero encontrado representa el pago del pedido
            }
        }
    }
}

/** Color de la etiqueta de estado, siguiendo la misma paleta del resto de la tienda. */
function estiloEstado(int $id): string
{
    return match (true) {
        $id <= 1  => 'bg-[#F1E2F6] text-[#5F3A7E]',     // Pendiente / Recibido
        $id === 2 => 'bg-[#F7A8C4] text-[#6B2244]',     // En preparación
        $id <= 4  => 'bg-[#C9A0DC] text-[#2A0A42]',     // Completado / Enviado
        default   => 'bg-[#5FA37A] text-white',         // Entregado
    };
}

$PASOS = [1 => ['Recibido', 'check'], 2 => ['En preparación', 'skillet'], 3 => ['Listo', 'inventory_2'], 4 => ['Entregado', 'home_pin']];

$flashFactura = $_SESSION['flash_factura'] ?? null;
unset($_SESSION['flash_factura']);

$tituloPagina = 'Mis pedidos';
$paginaActiva = 'pedidos';
require_once __DIR__ . '/partials/header.php';

$pill = function (string $href, string $icono, string $texto, int $cantidad, bool $activo): string {
    $base = 'px-5 py-2 rounded-full text-sm font-semibold transition-all active:scale-95 flex items-center gap-2 shrink-0';
    $estilo = $activo
        ? $base . ' bg-[#8A5AAE] hover:bg-[#764A98] text-white shadow-lila-hover'
        : $base . ' bg-[#F1E2F6] text-[#5F3A7E] hover:bg-[#EAD7F0]';
    $chip = $activo ? 'bg-white/25 text-white' : 'bg-white text-[#5F3A7E]';
    return '<a href="' . htmlspecialchars($href) . '" class="' . $estilo . '">'
        . '<span class="material-symbols-outlined text-base">' . $icono . '</span>'
        . '<span>' . htmlspecialchars($texto) . '</span>'
        . '<span class="px-2 py-0.5 rounded-full text-xs font-bold ' . $chip . '">' . $cantidad . '</span>'
        . '</a>';
};
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-8 md:py-12">

    <!-- Encabezado -->
    <div class="mb-8 md:mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#F1E2F6] rounded-full text-xs font-semibold text-[#5F3A7E] mb-3">
                <span class="material-symbols-outlined text-sm text-[#8A5AAE]" style="font-variation-settings:'FILL' 1;">cake</span>
                Repostería artesanal · Entregas programadas
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Mis pedidos</h1>
            <p class="text-sm text-[#6E5A7A] mt-1.5 max-w-2xl">Consulta el estado y las facturas de tus compras en Dulce Micro.</p>
        </div>
        <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl shadow-lila-soft border border-[#E8D9EE]">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#E685A8] opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-[#5FA37A]"></span>
            </span>
            <div class="text-left">
                <p class="text-xs font-bold">Zona de entrega activa</p>
                <p class="text-xs text-[#6E5A7A]"><?= htmlspecialchars(CIUDADES_ENTREGA[0] ?? '') ?> · Cobertura garantizada</p>
            </div>
        </div>
    </div>

    <?php if ($flashFactura): ?>
        <div class="flex items-center gap-2 <?= $flashFactura['ok'] ? 'bg-[#5FA37A]/15 text-[#2E6B47] border border-[#5FA37A]/30' : 'bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D]' ?> font-semibold text-sm px-4 py-3 rounded-2xl mb-6">
            <span class="material-symbols-outlined text-xl"><?= $flashFactura['ok'] ? 'mark_email_read' : 'error' ?></span>
            <?= htmlspecialchars($flashFactura['mensaje']) ?>
        </div>
    <?php endif; ?>

    <?php if (!$todosLosPedidos): ?>
        <div class="bg-white rounded-2xl border border-[#EAD8EC] shadow-lila-soft p-10 md:p-14 text-center">
            <span class="material-symbols-outlined text-6xl text-[#C9A0DC]">inventory_2</span>
            <h2 class="text-xl font-bold mt-4">Todavía no tienes pedidos</h2>
            <p class="text-sm text-[#6E5A7A] mt-1">Cuando hagas tu primera compra, aparecerá aquí.</p>
            <a href="catalogo.php"
               class="inline-flex items-center gap-2 mt-6 px-8 py-3.5 rounded-full bg-[#8A5AAE] hover:bg-[#79479e] text-white font-bold text-sm shadow-md transition-all active:scale-95">
                <span class="material-symbols-outlined text-xl">cake</span>
                Ir al catálogo
            </a>
        </div>
    <?php else: ?>

        <!-- Filtros -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-2 custom-scrollbar">
            <?= $pill('mis_pedidos.php', 'apps', 'Todos', $conteos['todos'], $filtro === 'todos') ?>
            <?= $pill('mis_pedidos.php?filtro=curso', 'skillet', 'En curso', $conteos['curso'], $filtro === 'curso') ?>
            <?= $pill('mis_pedidos.php?filtro=entregados', 'check_circle', 'Entregados', $conteos['entregados'], $filtro === 'entregados') ?>
        </div>

        <?php if (!$pedidos): ?>
            <div class="bg-white rounded-2xl border border-[#EAD8EC] shadow-lila-soft p-10 text-center mt-4">
                <span class="material-symbols-outlined text-5xl text-[#C9A0DC]">filter_alt_off</span>
                <p class="text-sm text-[#6E5A7A] mt-3">No tienes pedidos en esta categoría.</p>
            </div>
        <?php endif; ?>

        <div class="space-y-6 mt-4">
            <?php foreach ($pedidos as $ped):
                $idWeb        = (int)$ped['id_pedido_web'];
                $items        = $itemsPorPedido[$idWeb] ?? [];
                $pago         = $pagoPorPedido[$idWeb] ?? null;
                $idEstado     = (int)$ped['id_estado_pedido'];
                $nombreEstado = $estados[$idEstado] ?? 'En proceso';
                $pasoActual   = $etapaVisual($idEstado);
                $unidades     = array_sum(array_column($items, 'cantidad'));
            ?>
                <article class="bg-white rounded-2xl shadow-lila-soft p-6 md:p-8 border border-[#E8D9EE] relative overflow-hidden hover:shadow-lila-hover transition-all">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#F7A8C4] via-[#8A5AAE] to-[#C9A0DC]"></div>

                    <!-- Cabecera del pedido -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-6 border-b border-[#F1E2F6] gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-lg font-bold"><?= htmlspecialchars($ped['numero_orden']) ?></span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= estiloEstado($idEstado) ?>">
                                <?= htmlspecialchars($nombreEstado) ?>
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[#6E5A7A] text-sm">
                            <span class="material-symbols-outlined text-lg text-[#8A5AAE]">event</span>
                            Entrega estimada:
                            <strong class="text-[#3A2545] font-semibold"><?= htmlspecialchars(fechaLarga($ped['fecha_entrega'])) ?> (<?= FRANJAS[$ped['franja']][1] ?? '' ?>)</strong>
                        </div>
                    </div>

                    <!-- Progreso en 4 pasos -->
                    <div class="py-6 border-b border-[#F1E2F6]">
                        <p class="text-xs text-[#6E5A7A] uppercase tracking-wider mb-4 font-semibold">Estado del pedido</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <?php foreach ($PASOS as $n => [$nombrePaso, $icono]):
                                $completo = $n < $pasoActual;
                                $activo   = $n === $pasoActual;
                            ?>
                                <div class="flex items-center gap-3 p-3 rounded-xl border
                                    <?= $activo ? 'bg-[#F1E2F6] border-[#8A5AAE]/50 shadow-sm' : ($completo ? 'bg-[#FAF0F7] border-[#E8D9EE]' : 'bg-[#FAF0F7] border-[#E8D9EE] opacity-60') ?>">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                                        <?= $completo || $activo ? 'bg-[#8A5AAE] text-white' : 'bg-[#EAD7F0] text-[#6E5A7A]' ?>">
                                        <span class="material-symbols-outlined text-lg" <?= $completo ? "style=\"font-variation-settings:'FILL' 1;\"" : '' ?>>
                                            <?= $completo ? 'check' : $icono ?>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold <?= $activo || $completo ? 'text-[#3A2545]' : 'text-[#6E5A7A]' ?>"><?= $n ?>. <?= $nombrePaso ?></p>
                                        <?php if ($activo): ?><p class="text-xs text-[#6E5A7A]">En este momento</p><?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Productos y resumen de pago -->
                    <div class="py-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <div class="lg:col-span-8 space-y-3">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#8A5AAE] text-base">receipt_long</span>
                                Productos incluidos (<?= $unidades ?> <?= $unidades === 1 ? 'unidad' : 'unidades' ?>)
                            </h3>
                            <div class="space-y-2.5">
                                <?php foreach ($items as $it):
                                    $tieneFoto = !empty($it['imagen']);
                                    $foto = $tieneFoto ? '/Dulce_Micro/img_productos/' . rawurlencode($it['imagen']) : $logoTienda;
                                ?>
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAF0F7] hover:bg-[#F7EAF4] transition-colors gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-11 h-11 rounded-lg bg-[#F1E2F6] overflow-hidden shrink-0 flex items-center justify-center">
                                                <img src="<?= htmlspecialchars($foto) ?>" alt=""
                                                     class="w-full h-full <?= $tieneFoto ? 'object-cover' : 'object-contain p-1.5 opacity-80' ?>">
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="text-sm font-semibold truncate"><?= htmlspecialchars($it['nombre_producto'] ?? 'Producto') ?></h4>
                                                <p class="text-xs text-[#6E5A7A] truncate">
                                                    <?php if (!empty($it['descripcion'])): ?>
                                                        <?= htmlspecialchars(mb_strimwidth($it['descripcion'], 0, 45, '…')) ?> ·
                                                    <?php endif; ?>
                                                    Cantidad: <?= (int)$it['cantidad'] ?>
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-[#8A5AAE] whitespace-nowrap"><?= formatoCOP((float)$it['subtotal']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="lg:col-span-4 bg-[#FAF0F7] p-5 rounded-2xl shadow-lila-soft border border-[#E8D9EE] space-y-3">
                            <p class="text-xs text-[#6E5A7A] uppercase font-bold tracking-wider">Resumen de pago</p>
                            <div class="flex justify-between text-sm text-[#6E5A7A]">
                                <span>Subtotal</span>
                                <span><?= formatoCOP((float)$ped['subtotal']) ?></span>
                            </div>
                            <div class="flex justify-between text-sm text-[#6E5A7A]">
                                <span>Envío</span>
                                <span><?= (float)$ped['costo_envio'] > 0 ? formatoCOP((float)$ped['costo_envio']) : 'Gratis' ?></span>
                            </div>
                            <div class="pt-3 border-t border-[#E8D9EE] flex justify-between items-baseline">
                                <span class="text-sm font-bold">Total</span>
                                <span class="text-xl font-extrabold text-[#8A5AAE]"><?= formatoCOP((float)$ped['total']) ?></span>
                            </div>
                            <?php if ($pago): ?>
                                <div class="flex items-center gap-2 pt-1 text-xs text-[#6E5A7A]">
                                    <span class="material-symbols-outlined text-base text-[#5FA37A]" style="font-variation-settings:'FILL' 1;">verified</span>
                                    <span><?= htmlspecialchars($pago['estado_pago']) ?> con <strong><?= htmlspecialchars($pago['metodo_pago']) ?></strong></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="pt-6 border-t border-[#F1E2F6] flex flex-wrap items-center gap-3">
                        <a href="descargar_factura.php?orden=<?= urlencode($ped['numero_orden']) ?>"
                           class="px-5 py-2.5 rounded-full border border-[#8A5AAE] text-[#8A5AAE] hover:bg-[#F1E2F6] transition-all active:scale-95 text-sm font-semibold flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                            Descargar factura PDF
                        </a>
                        <form method="post" action="reenviar_factura.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="orden" value="<?= htmlspecialchars($ped['numero_orden']) ?>">
                            <input type="hidden" name="volver" value="pedidos">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-full border border-[#8A5AAE] text-[#8A5AAE] hover:bg-[#F1E2F6] transition-all active:scale-95 text-sm font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">mail</span>
                                Reenviar factura al correo
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (WHATSAPP_TIENDA !== ''): ?>
            <div class="mt-12 bg-white rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 border border-[#E8D9EE] shadow-lila-soft">
                <div class="flex items-center gap-4 text-left">
                    <div class="w-12 h-12 rounded-full bg-[#F1E2F6] text-[#8A5AAE] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">support_agent</span>
                    </div>
                    <div>
                        <h4 class="font-bold">¿Tienes alguna pregunta sobre tu pedido?</h4>
                        <p class="text-sm text-[#6E5A7A]">Escríbenos y te ayudamos con horarios o personalizaciones.</p>
                    </div>
                </div>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP_TIENDA) ?>" target="_blank" rel="noopener noreferrer"
                   class="shrink-0 px-6 py-3 rounded-full bg-[#25D366] text-white font-bold text-sm flex items-center gap-2 hover:opacity-90 active:scale-95 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-xl">chat</span>
                    Atención WhatsApp
                </a>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
