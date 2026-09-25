<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/config_tienda.php';

$tituloPagina = 'Carrito de compras';
$paginaActiva = '';
require_once __DIR__ . '/partials/header.php';   // inicia la sesión TIENDA

$conn = (new conexion())->conn;

/* ---------- Leer el carrito (id => cantidad) y traer los precios de la base ---------- */
$carrito  = $_SESSION['carrito'] ?? [];
$items    = [];
$subtotal = 0.0;
$unidades = 0;
$quitados = false;

if ($carrito) {
    $ids    = array_map('intval', array_keys($carrito));
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt   = $conn->prepare(
        "SELECT id_producto, nombre_producto, precio_base, imagen
           FROM productos
          WHERE estado = 1 AND id_producto IN ($marcas)"
    );
    $stmt->execute($ids);

    $filas = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $filas[(int)$f['id_producto']] = $f;
    }

    foreach ($carrito as $id => $cantidad) {
        $id       = (int)$id;
        $cantidad = (int)$cantidad;

        // Si el producto se desactivó o borró mientras estaba en el carrito, se retira
        if (!isset($filas[$id])) {
            unset($_SESSION['carrito'][$id]);
            $quitados = true;
            continue;
        }

        $f     = $filas[$id];
        $linea = (float)$f['precio_base'] * $cantidad;

        $items[] = [
            'id'       => $id,
            'nombre'   => $f['nombre_producto'],
            'precio'   => (float)$f['precio_base'],
            'imagen'   => $f['imagen'],
            'cantidad' => $cantidad,
            'subtotal' => $linea,
        ];
        $subtotal += $linea;
        $unidades += $cantidad;
    }
}

$envio = $items ? costoEnvio($subtotal) : 0;
$total = $subtotal + $envio;

/* ---------- Fecha y franja de entrega (se recuerdan entre recargas) ---------- */
$dias        = diasDisponibles();
$entrega     = $_SESSION['entrega'] ?? [];
$fechasOk    = array_column($dias, 'valor');
$fechaSel    = in_array($entrega['fecha'] ?? '', $fechasOk, true) ? $entrega['fecha'] : $dias[0]['valor'];
$franjaSel   = isset(FRANJAS[$entrega['franja'] ?? '']) ? $entrega['franja'] : array_key_first(FRANJAS);
$dedicatoria = $entrega['dedicatoria'] ?? '';

$errores = [
    'fecha' => 'Elige una fecha de entrega válida para continuar.',
    'vacio' => 'Tu carrito está vacío.',
];
$mensajeError = $errores[$_GET['error'] ?? ''] ?? null;
?>

<main class="flex-1 max-w-7xl w-full mx-auto px-6 md:px-8 py-8 md:py-12">

    <!-- Migas de pan -->
    <nav class="flex items-center gap-2 text-sm text-[#6E5A7A] mb-6">
        <a class="hover:text-[#8A5AAE] transition-colors" href="/Dulce_Micro/tienda/catalogo.php">Catálogo</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="font-semibold text-[#8A5AAE]">Carrito de compras</span>
    </nav>

    <?php if ($mensajeError): ?>
        <div class="flex items-center gap-2 bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D] font-semibold text-sm px-4 py-3 rounded-2xl mb-6">
            <span class="material-symbols-outlined text-xl">error</span>
            <?= htmlspecialchars($mensajeError) ?>
        </div>
    <?php endif; ?>

    <?php if ($quitados): ?>
        <div class="flex items-center gap-2 bg-[#F1E2F6] border border-[#EAD8EC] text-[#5F3A7E] font-semibold text-sm px-4 py-3 rounded-2xl mb-6">
            <span class="material-symbols-outlined text-xl">info</span>
            Quitamos de tu carrito productos que ya no están disponibles.
        </div>
    <?php endif; ?>

<?php if (!$items): ?>

    <!-- Carrito vacío -->
    <div class="bg-white rounded-2xl border border-[#EAD8EC] shadow-lila-soft p-10 md:p-14 text-center max-w-xl mx-auto">
        <span class="material-symbols-outlined text-6xl text-[#C9A0DC]">shopping_bag</span>
        <h1 class="text-2xl font-extrabold text-[#3A2545] mt-4">Tu carrito está vacío</h1>
        <p class="text-sm text-[#6E5A7A] mt-1">Elige algún postre del catálogo y aparecerá aquí.</p>
        <a href="/Dulce_Micro/tienda/catalogo.php"
           class="inline-flex items-center gap-2 mt-6 px-8 py-3.5 rounded-full bg-[#8A5AAE] hover:bg-[#79479e] text-white font-bold text-sm shadow-md transition-all active:scale-95">
            <span class="material-symbols-outlined text-xl">cake</span>
            Ver el catálogo
        </a>
    </div>

<?php else: ?>

    <!-- Un solo formulario: al sumar, restar o quitar se conservan la fecha, la franja y la dedicatoria -->
    <form method="post" action="actualizar_carrito.php" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <!-- Botón por defecto al presionar Enter: solo guarda -->
        <button type="submit" name="accion" value="guardar" class="sr-only" tabindex="-1" aria-hidden="true">Guardar</button>

        <!-- ===== Columna izquierda: productos y dedicatoria ===== -->
        <section class="lg:col-span-7 flex flex-col gap-6">

            <div class="flex items-baseline justify-between pb-2">
                <h1 class="text-3xl font-extrabold text-[#3A2545] tracking-tight">
                    Tu carrito de postres
                    <span class="text-[#6E5A7A] font-normal text-xl">
                        (<?= $unidades ?> <?= $unidades === 1 ? 'producto' : 'productos' ?>)
                    </span>
                </h1>
                <button type="submit" name="accion" value="vaciar"
                        onclick="return confirm('¿Quieres vaciar el carrito?');"
                        class="text-sm text-[#E685A8] hover:text-[#8A5AAE] hover:underline font-semibold flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
                    Vaciar
                </button>
            </div>

            <div class="flex flex-col gap-4">
                <?php foreach ($items as $it):
                    $tieneFoto = !empty($it['imagen']);
                    $foto = $tieneFoto
                        ? '/Dulce_Micro/img_productos/' . rawurlencode($it['imagen'])
                        : $logoTienda;
                ?>
                    <article class="bg-white p-5 sm:p-6 rounded-2xl shadow-lila-soft flex flex-col sm:flex-row gap-5 items-center justify-between border border-[#EAD8EC]/60 hover:border-[#C9A0DC] transition-all duration-200">

                        <div class="flex items-center gap-4 w-full sm:w-auto">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden shrink-0 bg-[#F1E2F6] border border-[#EAD8EC]">
                                <img src="<?= htmlspecialchars($foto) ?>"
                                     alt="<?= htmlspecialchars($it['nombre']) ?>"
                                     class="w-full h-full <?= $tieneFoto ? 'object-cover' : 'object-contain p-3 opacity-80' ?>">
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-lg font-bold text-[#3A2545] line-clamp-2">
                                    <?= htmlspecialchars($it['nombre']) ?>
                                </h3>
                                <p class="text-sm text-[#6E5A7A] mt-0.5">
                                    Precio unitario:
                                    <span class="font-bold text-[#8A5AAE]"><?= formatoCOP($it['precio']) ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 border-[#F1E2F6]">
                            <!-- Cantidad -->
                            <div class="flex items-center bg-[#F1E2F6] rounded-full px-2 py-1">
                                <button type="submit" name="accion" value="restar:<?= $it['id'] ?>" aria-label="Disminuir cantidad"
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-[#8A5AAE] hover:bg-white transition-colors active:scale-95">
                                    <span class="material-symbols-outlined text-[18px]">remove</span>
                                </button>
                                <span class="w-8 text-center font-bold text-[#8A5AAE] text-sm"><?= $it['cantidad'] ?></span>
                                <button type="submit" name="accion" value="sumar:<?= $it['id'] ?>" aria-label="Aumentar cantidad"
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-[#8A5AAE] hover:bg-white transition-colors active:scale-95">
                                    <span class="material-symbols-outlined text-[18px]">add</span>
                                </button>
                            </div>

                            <!-- Subtotal y eliminar -->
                            <div class="text-right flex items-center gap-4">
                                <div class="flex flex-col">
                                    <span class="text-xs text-[#6E5A7A] font-semibold">Subtotal</span>
                                    <span class="text-lg font-extrabold text-[#3A2545]"><?= formatoCOP($it['subtotal']) ?></span>
                                </div>
                                <button type="submit" name="accion" value="quitar:<?= $it['id'] ?>" title="Eliminar producto"
                                        class="p-2 text-[#6E5A7A] hover:text-[#E685A8] hover:bg-[#F1E2F6] rounded-full transition-all">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Dedicatoria -->
            <div class="bg-white p-6 rounded-2xl shadow-lila-soft border border-[#EAD8EC]/60 flex flex-col gap-3">
                <div class="flex items-center gap-2 text-[#3A2545] font-bold text-lg">
                    <span class="material-symbols-outlined text-[#E685A8]">card_giftcard</span>
                    <h2>Dedicatoria o notas especiales</h2>
                </div>
                <p class="text-sm text-[#6E5A7A]">
                    Si quieres un mensaje en la tarjeta o instrucciones de decoración, escríbelos aquí.
                </p>
                <textarea name="dedicatoria" rows="3" maxlength="300"
                          placeholder="Ej: ¡Feliz cumpleaños, mamá!"
                          class="w-full bg-[#FDF3F8] border border-[#EAD8EC] focus:border-[#8A5AAE] focus:ring-2 focus:ring-[#8A5AAE]/30 rounded-2xl p-4 text-sm text-[#3A2545] placeholder-[#6E5A7A] resize-none outline-none transition-all"><?= htmlspecialchars($dedicatoria) ?></textarea>
            </div>
        </section>

        <!-- ===== Columna derecha: entrega y resumen ===== -->
        <aside class="lg:col-span-5 lg:sticky lg:top-28">
            <div class="bg-white p-6 sm:p-7 rounded-2xl shadow-lila-hover flex flex-col gap-6 border border-[#EAD8EC]">

                <!-- Fecha de entrega -->
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#8A5AAE]">calendar_month</span>
                            <h2 class="font-bold text-lg text-[#3A2545]">Fecha de entrega</h2>
                        </div>
                        <span class="bg-[#FDE2EC] text-[#B04468] font-bold text-[11px] px-2.5 py-0.5 rounded-full">Requerido</span>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <?php foreach ($dias as $d): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="fecha" value="<?= $d['valor'] ?>" class="peer sr-only"
                                       <?= $d['valor'] === $fechaSel ? 'checked' : '' ?>>
                                <span class="flex flex-col items-center justify-center p-2 rounded-xl border border-[#EAD8EC] bg-[#FDF3F8] text-[#3A2545] hover:bg-[#F1E2F6] transition-colors
                                             peer-checked:bg-[#8A5AAE] peer-checked:text-white peer-checked:border-[#8A5AAE] peer-checked:shadow-md
                                             peer-focus-visible:ring-2 peer-focus-visible:ring-[#8A5AAE]">
                                    <span class="text-[11px] uppercase"><?= $d['etiqueta'] ?></span>
                                    <span class="font-extrabold text-xl leading-tight"><?= $d['numero'] ?></span>
                                    <span class="text-[10px]"><?= $d['mes'] ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Franja horaria -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <?php foreach (FRANJAS as $clave => [$nombreFranja, $horario]): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="franja" value="<?= $clave ?>" class="peer sr-only"
                                       <?= $clave === $franjaSel ? 'checked' : '' ?>>
                                <span class="flex flex-col p-3 rounded-xl border-2 border-[#EAD8EC] bg-white hover:bg-[#FDF3F8] transition-all
                                             peer-checked:border-[#8A5AAE] peer-checked:bg-[#F1E2F6]
                                             peer-focus-visible:ring-2 peer-focus-visible:ring-[#8A5AAE]">
                                    <span class="text-sm font-bold text-[#3A2545]"><?= $nombreFranja ?></span>
                                    <span class="text-xs text-[#6E5A7A]"><?= $horario ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex items-start gap-2.5 bg-[#F1E2F6]/60 border border-[#EAD8EC] p-3 rounded-xl">
                        <span class="material-symbols-outlined text-[#8A5AAE] text-[18px] shrink-0 mt-0.5">info</span>
                        <p class="text-xs text-[#6E5A7A] leading-relaxed">
                            Preparamos tus postres con anticipación para garantizar frescura artesanal.
                        </p>
                    </div>
                </div>

                <div class="h-px bg-[#EAD8EC] w-full"></div>

                <!-- Resumen -->
                <div class="flex flex-col gap-3">
                    <h3 class="font-bold text-lg text-[#3A2545]">Resumen de compra</h3>

                    <div class="flex justify-between items-center text-sm text-[#6E5A7A]">
                        <span>Subtotal</span>
                        <span class="font-bold text-[#3A2545]"><?= formatoCOP($subtotal) ?></span>
                    </div>

                    <div class="flex justify-between items-center text-sm text-[#6E5A7A]">
                        <div class="flex items-center gap-2">
                            <span>Costo de envío</span>
                            <?php if ($envio === 0): ?>
                                <span class="inline-flex items-center gap-1 bg-[#5FA37A]/15 text-[#2E6A45] font-bold text-xs px-2 py-0.5 rounded-full">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Envío gratis
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($envio === 0): ?>
                            <div class="flex items-center gap-1.5">
                                <span class="line-through text-xs"><?= formatoCOP(ENVIO_COSTO) ?></span>
                                <span class="font-bold text-[#2E6A45]">$ 0 COP</span>
                            </div>
                        <?php else: ?>
                            <span class="font-bold text-[#3A2545]"><?= formatoCOP($envio) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($envio === 0): ?>
                        <p class="text-xs text-[#2E6A45] font-medium bg-[#5FA37A]/10 border border-[#5FA37A]/20 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">celebration</span>
                            Tu compra califica para envío gratis.
                        </p>
                    <?php else: ?>
                        <p class="text-xs text-[#5F3A7E] font-medium bg-[#F1E2F6] border border-[#EAD8EC] px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                            Agrega <?= formatoCOP(ENVIO_GRATIS_DESDE - $subtotal) ?> más y el envío es gratis.
                        </p>
                    <?php endif; ?>

                    <div class="h-px bg-[#EAD8EC] w-full my-1"></div>

                    <div class="flex justify-between items-baseline pt-1">
                        <span class="text-xl font-bold text-[#3A2545]">Total a pagar:</span>
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#3A2545] tracking-tight"><?= formatoCOP($total) ?></span>
                    </div>
                </div>

                <!-- Continuar -->
                <div class="flex flex-col gap-3 pt-2">
                    <button type="submit" name="accion" value="continuar"
                            class="w-full bg-[#8A5AAE] hover:bg-[#78469C] active:scale-[0.98] text-white rounded-full py-4 px-6 text-base font-bold flex items-center justify-center gap-3 shadow-md hover:shadow-lg transition-all duration-200">
                        <span>Continuar al pago</span>
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                    <a href="/Dulce_Micro/tienda/catalogo.php"
                       class="text-center text-sm font-bold text-[#8A5AAE] hover:text-[#78469C] hover:underline transition-colors py-1 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">keyboard_backspace</span>
                        <span>Seguir comprando postres</span>
                    </a>
                </div>

                <div class="pt-3 border-t border-[#EAD8EC] flex items-center justify-center gap-3 text-[#6E5A7A] text-[12px] flex-wrap">
                    <span class="font-bold text-[#8A5AAE]">PSE</span><span>•</span>
                    <span class="font-bold text-[#8A5AAE]">Nequi</span><span>•</span>
                    <span class="font-bold text-[#8A5AAE]">Daviplata</span><span>•</span>
                    <span class="font-medium">Tarjetas</span>
                </div>
            </div>
        </aside>
    </form>

<?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
