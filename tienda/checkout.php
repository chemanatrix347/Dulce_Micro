<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/config_tienda.php';

exigirCliente('checkout');

$conn      = (new conexion())->conn;
$idCliente = (int)$_SESSION['id_cliente'];

/* ---------- Requisitos: carrito con productos y fecha de entrega elegida ---------- */
$carga = cargarItemsCarrito($conn, $_SESSION['carrito'] ?? []);
if (!$carga['items']) {
    header('Location: carrito.php?error=vacio');
    exit;
}
$entrega = $_SESSION['entrega'] ?? [];
$fechasOk = array_column(diasDisponibles(), 'valor');
if (!in_array($entrega['fecha'] ?? '', $fechasOk, true) || !isset(FRANJAS[$entrega['franja'] ?? ''])) {
    header('Location: carrito.php?error=fecha');
    exit;
}

$items    = $carga['items'];
$subtotal = $carga['subtotal'];
$envio    = costoEnvio($subtotal);
$total    = $subtotal + $envio;

/* ---------- Datos del cliente y métodos de pago ---------- */
$st = $conn->prepare(
    'SELECT c.nombre, c.correo, c.telefono, c.direccion, c.ciudad, c.barrio,
            c.numero_documento, td.tipo_documento
       FROM clientes c
       LEFT JOIN tipo_documento td ON td.id_tipo_documento = c.id_tipo_documento
      WHERE c.id_cliente = ? AND c.estado = 1'
);
$st->execute([$idCliente]);
$cliente = $st->fetch(PDO::FETCH_ASSOC);
if (!$cliente) {
    header('Location: logout.php');
    exit;
}

$metodos = $conn->query(
    'SELECT id_metodos_pago AS id, metodo_pago AS nombre
       FROM metodos_pago WHERE estado = 1 ORDER BY id_metodos_pago'
)->fetchAll(PDO::FETCH_ASSOC);

/* ---------- Errores y datos escritos si el envío falló ---------- */
$flash = $_SESSION['checkout_flash'] ?? null;
unset($_SESSION['checkout_flash']);
$errores = $flash['errores'] ?? [];
$old     = $flash['old'] ?? [];

$val = function (string $clave, ?string $porDefecto) use ($old): string {
    return (string)($old[$clave] ?? $porDefecto ?? '');
};

$tituloPagina = 'Finalizar compra';
$paginaActiva = '';
require_once __DIR__ . '/partials/header.php';

$campo = 'w-full bg-white rounded-full px-4 py-3 border border-[#EBD3E6] text-base text-[#3A2545] focus:border-[#8A5AAE] focus:ring-2 focus:ring-[#F1E2F6] focus:outline-none transition-all placeholder:text-[#6E5A7A]/60';
?>

<main class="flex-grow max-w-7xl w-full mx-auto px-4 md:px-8 py-8 md:py-12">

    <!-- Pasos -->
    <nav aria-label="Progreso de compra" class="flex items-center justify-center gap-3 md:gap-5 mb-8 text-[13px]">
        <span class="flex items-center gap-2 text-[#6E5A7A] font-medium">
            <span class="w-6 h-6 rounded-full bg-[#EBD8E7] flex items-center justify-center"><span class="material-symbols-outlined text-[15px]" style="font-variation-settings:'FILL' 1;">check</span></span>
            Carrito
        </span>
        <span class="w-6 h-0.5 bg-[#EBD3E6]"></span>
        <span class="flex items-center gap-2 text-[#8A5AAE] font-bold">
            <span class="w-6 h-6 rounded-full bg-[#8A5AAE] text-white text-[12px] flex items-center justify-center">2</span>
            Datos y pago
        </span>
        <span class="w-6 h-0.5 bg-[#EBD3E6]"></span>
        <span class="flex items-center gap-2 text-[#6E5A7A] opacity-70">
            <span class="w-6 h-6 rounded-full bg-[#F5E6F0] text-[12px] flex items-center justify-center font-semibold">3</span>
            Confirmación
        </span>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-extrabold">Finalizar compra</h1>
        <p class="text-[#6E5A7A] mt-1">Completa los datos de entrega y elige cómo quieres pagar.</p>
    </div>

    <?php if (MODO_PAGO_SIMULADO): ?>
        <div class="flex items-start gap-3 bg-[#FDE2EC] border border-[#F7A8C4] text-[#6B2244] text-sm rounded-2xl px-4 py-3 mb-6" role="note">
            <span class="material-symbols-outlined text-[22px] shrink-0">science</span>
            <p><strong>Modo de prueba:</strong> los pagos electrónicos se registran como aprobados, pero no se cobra ningún dinero real.</p>
        </div>
    <?php endif; ?>

    <?php if ($errores): ?>
        <div class="mb-6 bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D] text-sm rounded-2xl px-4 py-3" role="alert">
            <ul class="list-disc pl-4 space-y-0.5">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="procesar_pedido.php" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- ===== Columna izquierda ===== -->
        <section class="lg:col-span-7 flex flex-col gap-8">

            <!-- 1. Datos de entrega -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-lila-soft border border-[#EBD3E6]">
                <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#FAF0F6]">
                    <span class="w-8 h-8 rounded-full bg-[#8A5AAE] text-white flex items-center justify-center font-bold text-sm">1</span>
                    <h2 class="text-xl font-bold">Datos de entrega</h2>
                </div>

                <!-- Facturación: solo lectura -->
                <div class="mb-6 bg-[#FAF0F6] rounded-xl p-4 border border-[#EBD3E6] text-sm">
                    <p class="text-xs font-semibold text-[#6E5A7A] mb-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#8A5AAE]">receipt_long</span>
                        Factura a nombre de
                    </p>
                    <p class="font-bold text-base"><?= htmlspecialchars($cliente['nombre']) ?></p>
                    <p class="text-[#6E5A7A]">
                        <?= htmlspecialchars(trim(($cliente['tipo_documento'] ?? '') . ' ' . ($cliente['numero_documento'] ?? ''))) ?>
                    </p>
                    <div class="mt-3 flex items-center gap-2.5 bg-[#F1E2F6] border border-[#C9A0DC]/50 rounded-xl p-3">
                        <span class="material-symbols-outlined text-[20px] text-[#8A5AAE]" style="font-variation-settings:'FILL' 1;">mark_email_read</span>
                        <p class="font-semibold">
                            Tu factura llegará a <span class="text-[#8A5AAE]"><?= htmlspecialchars($cliente['correo']) ?></span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" for="telefono">Teléfono de contacto / WhatsApp *</label>
                        <input id="telefono" name="telefono" type="tel" required maxlength="20" placeholder="312 456 7890"
                               value="<?= htmlspecialchars($val('telefono', $cliente['telefono'])) ?>" class="<?= $campo ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" for="ciudad">Ciudad de entrega *</label>
                        <select id="ciudad" name="ciudad" required class="<?= $campo ?> cursor-pointer">
                            <?php foreach (CIUDADES_ENTREGA as $ciudad): ?>
                                <option value="<?= htmlspecialchars($ciudad) ?>"
                                    <?= $val('ciudad', $cliente['ciudad']) === $ciudad ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ciudad) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold mb-1.5" for="direccion">Dirección exacta de entrega *</label>
                        <input id="direccion" name="direccion" type="text" required maxlength="255"
                               placeholder="Calle, carrera, número, apto o casa"
                               value="<?= htmlspecialchars($val('direccion', $cliente['direccion'])) ?>" class="<?= $campo ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold mb-1.5" for="barrio">Barrio</label>
                        <input id="barrio" name="barrio" type="text" maxlength="100"
                               value="<?= htmlspecialchars($val('barrio', $cliente['barrio'])) ?>" class="<?= $campo ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold mb-1.5" for="indicaciones">Indicaciones adicionales (opcional)</label>
                        <textarea id="indicaciones" name="indicaciones" rows="2" maxlength="300"
                                  placeholder="Ej: dejar en portería a nombre de Mariana"
                                  class="w-full bg-white rounded-2xl px-4 py-3 border border-[#EBD3E6] text-base focus:border-[#8A5AAE] focus:ring-2 focus:ring-[#F1E2F6] focus:outline-none resize-none transition-all placeholder:text-[#6E5A7A]/60"><?= htmlspecialchars($val('indicaciones', '')) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 2. Método de pago -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-lila-soft border border-[#EBD3E6]">
                <div class="flex items-center gap-3 mb-6 pb-3 border-b border-[#FAF0F6]">
                    <span class="w-8 h-8 rounded-full bg-[#8A5AAE] text-white flex items-center justify-center font-bold text-sm">2</span>
                    <h2 class="text-xl font-bold">Elige tu método de pago</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($metodos as $m):
                        [$titulo, $descripcion, $icono] = infoMetodoPago($m['nombre']);
                    ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="id_metodo_pago" value="<?= (int)$m['id'] ?>" required class="peer sr-only"
                                   <?= (int)($old['id_metodo_pago'] ?? 0) === (int)$m['id'] ? 'checked' : '' ?>>
                            <span class="flex items-center gap-3.5 p-4 rounded-2xl border-2 border-[#EBD3E6] bg-white hover:bg-[#FAF0F6] transition-all
                                         peer-checked:border-[#8A5AAE] peer-checked:bg-[#F1E2F6]
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-[#8A5AAE]">
                                <span class="w-12 h-12 rounded-xl bg-[#F1E2F6] text-[#8A5AAE] flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[26px]"><?= $icono ?></span>
                                </span>
                                <span class="flex flex-col min-w-0">
                                    <span class="font-bold text-[17px]"><?= htmlspecialchars($titulo) ?></span>
                                    <span class="text-xs text-[#6E5A7A]"><?= htmlspecialchars($descripcion) ?></span>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="mt-5 p-3.5 rounded-xl bg-[#FAF0F6] border border-[#EBD3E6] flex items-center gap-3 text-[#6E5A7A]">
                    <span class="material-symbols-outlined text-[#8A5AAE] text-[20px]">shield</span>
                    <p class="text-xs sm:text-sm">Tus datos de pago no se guardan en esta tienda.</p>
                </div>
            </div>
        </section>

        <!-- ===== Columna derecha: resumen ===== -->
        <aside class="lg:col-span-5 lg:sticky lg:top-28">
            <div class="bg-white rounded-2xl p-6 md:p-7 shadow-lila-hover border border-[#EBD3E6]">
                <div class="flex items-center justify-between pb-4 border-b border-[#FAF0F6]">
                    <h2 class="text-xl font-bold">Resumen del pedido</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-[#F1E2F6] text-[#8A5AAE] font-bold">
                        <?= $carga['unidades'] ?> <?= $carga['unidades'] === 1 ? 'postre' : 'postres' ?>
                    </span>
                </div>

                <div class="divide-y divide-[#FAF0F6] my-2">
                    <?php foreach ($items as $it):
                        $tieneFoto = !empty($it['imagen']);
                        $foto = $tieneFoto ? '/Dulce_Micro/img_productos/' . rawurlencode($it['imagen']) : $logoTienda;
                    ?>
                        <div class="py-4 flex items-center gap-3.5">
                            <img src="<?= htmlspecialchars($foto) ?>" alt=""
                                 class="w-16 h-16 rounded-xl bg-[#FAF0F6] flex-shrink-0 border border-[#EBD3E6] <?= $tieneFoto ? 'object-cover' : 'object-contain p-2 opacity-80' ?>">
                            <div class="flex-grow min-w-0">
                                <h3 class="text-sm font-bold line-clamp-2"><?= htmlspecialchars($it['nombre']) ?></h3>
                                <span class="text-xs text-[#B04468] font-bold">Cant: <?= $it['cantidad'] ?></span>
                            </div>
                            <span class="text-base font-extrabold whitespace-nowrap"><?= formatoCOP($it['subtotal']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FAF0F6] flex items-start gap-3 my-4 border border-[#EBD3E6]">
                    <span class="material-symbols-outlined text-[#8A5AAE] text-[22px] mt-0.5" style="font-variation-settings:'FILL' 1;">calendar_clock</span>
                    <div>
                        <span class="text-[11px] font-bold text-[#8A5AAE] block">Entrega programada</span>
                        <p class="text-sm font-bold"><?= htmlspecialchars(fechaLarga($entrega['fecha'])) ?></p>
                        <p class="text-xs text-[#6E5A7A]"><?= FRANJAS[$entrega['franja']][0] ?> (<?= FRANJAS[$entrega['franja']][1] ?>)</p>
                        <a href="carrito.php" class="text-xs font-semibold text-[#8A5AAE] hover:underline">Cambiar</a>
                    </div>
                </div>

                <div class="space-y-2.5 pt-2 pb-4 border-t border-[#FAF0F6] text-sm">
                    <div class="flex justify-between text-[#6E5A7A]">
                        <span>Subtotal productos:</span>
                        <span class="font-bold text-[#3A2545]"><?= formatoCOP($subtotal) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-[#6E5A7A]">
                        <span>Envío a domicilio:</span>
                        <?php if ($envio === 0): ?>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#5FA37A]/15 text-[#2E6B47]">GRATIS</span>
                        <?php else: ?>
                            <span class="font-bold text-[#3A2545]"><?= formatoCOP($envio) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-between items-baseline pt-3 border-t border-dashed border-[#EBD3E6]">
                        <span class="text-lg font-bold">Total a pagar</span>
                        <span class="text-2xl font-extrabold text-[#8A5AAE]"><?= formatoCOP($total) ?></span>
                    </div>
                </div>

                <button type="submit"
                        class="w-full mt-2 py-4 px-6 rounded-full bg-[#8A5AAE] hover:bg-[#78479C] text-white text-base font-bold shadow-md hover:shadow-lg transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                    <span>Confirmar pedido</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
                <a href="carrito.php" class="block text-center mt-3 text-sm font-bold text-[#8A5AAE] hover:underline">Volver al carrito</a>
            </div>
        </aside>
    </form>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
