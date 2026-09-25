<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/config_tienda.php';
require_once __DIR__ . '/fidelizacion.php';

exigirCliente();

$conn      = (new conexion())->conn;
$idCliente = (int)$_SESSION['id_cliente'];

$st = $conn->prepare(
    'SELECT nombre, correo, telefono, fecha_nacimiento, direccion, ciudad, barrio,
            numero_documento, tipo_documento, foto_perfil, puntos_dulces
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

// Si hoy es su cumpleaños, genera (una sola vez al año) el cupón automático
verificarCuponCumpleanos($conn, $idCliente);
$flashCumple = $_SESSION['flash_cumple'] ?? null;
unset($_SESSION['flash_cumple']);

// Cuántos pedidos ha hecho, para la tarjeta de resumen
$st = $conn->prepare('SELECT COUNT(*) FROM pedido_web WHERE id_cliente = ? AND estado = 1');
$st->execute([$idCliente]);
$totalPedidos = (int)$st->fetchColumn();

$flash   = $_SESSION['flash_perfil'] ?? null;
unset($_SESSION['flash_perfil']);
$errores = $flash['errores'] ?? [];
$seccion = $flash['seccion'] ?? '';
$old     = $flash['old'] ?? [];
$val     = fn(string $campo) => htmlspecialchars($old[$campo] ?? $cliente[$campo] ?? '');

$tituloPagina = 'Mi perfil';
$paginaActiva = '';
require_once __DIR__ . '/partials/header.php';

$campo   = 'w-full pl-11 pr-4 py-2.5 bg-[#FDF3F8] text-[#3A2545] text-sm rounded-2xl border border-[#EAD8EC] focus:border-[#8A5AAE] focus:bg-white focus:ring-2 focus:ring-[#8A5AAE]/20 transition-all outline-none';
$icono   = 'material-symbols-outlined absolute left-3.5 top-3 text-[#6E5A7A] text-xl';
$inicial = mb_strtoupper(mb_substr($cliente['nombre'], 0, 1));

// Ruta pública de la foto de perfil, o null si el cliente aún no ha subido una
$fotoUrl = !empty($cliente['foto_perfil'])
    ? '/Dulce_Micro/img_clientes/' . htmlspecialchars($cliente['foto_perfil'])
    : null;
?>

<main class="flex-grow max-w-5xl w-full mx-auto px-4 sm:px-6 md:px-8 py-8 md:py-10">

    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight">Mi perfil</h1>
        <p class="text-sm text-[#6E5A7A] mt-1">Gestiona tu información personal y tu dirección de entrega.</p>
    </div>

    <?php if ($flashCumple): ?>
        <div class="flex items-center gap-3 bg-gradient-to-r from-[#FADBE8] to-[#F1E2F6] border border-[#E685A8]/40 text-[#3A2545] text-sm font-semibold px-4 py-3 rounded-2xl mb-6">
            <span class="material-symbols-outlined text-2xl text-[#E685A8]">cake</span>
            <span>
                ¡Feliz cumpleaños! 🎉 Te regalamos el cupón
                <span class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-[#8A5AAE]/20"><?= htmlspecialchars($flashCumple['codigo']) ?></span>
                con 20% de descuento, válido hasta el <?= htmlspecialchars($flashCumple['expira']) ?>.
            </span>
        </div>
    <?php endif; ?>

    <?php if ($flash && !empty($flash['ok'])): ?>
        <div class="flex items-center gap-2 bg-[#5FA37A]/15 border border-[#5FA37A]/30 text-[#2E6B47] font-semibold text-sm px-4 py-3 rounded-2xl mb-6">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            <?= htmlspecialchars($flash['mensaje']) ?>
        </div>
    <?php elseif ($errores): ?>
        <div class="mb-6 bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D] text-sm rounded-2xl px-4 py-3">
            <ul class="list-disc pl-4 space-y-0.5">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- ===== Columna izquierda: resumen ===== -->
        <aside class="lg:col-span-4 flex flex-col gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-lila-soft border border-[#EAD8EC] flex flex-col items-center text-center">

                <!-- Avatar con botón de cámara superpuesto -->
                <div class="relative mb-4">
                    <?php if ($fotoUrl): ?>
                        <img id="avatar-preview"
                             src="<?= $fotoUrl ?>"
                             alt="Avatar de <?= htmlspecialchars($cliente['nombre']) ?>"
                             class="w-24 h-24 rounded-full object-cover ring-4 ring-[#FADBE8]">
                    <?php else: ?>
                        <div id="avatar-preview"
                             class="w-24 h-24 rounded-full bg-[#F1E2F6] text-[#8A5AAE] flex items-center justify-center text-3xl font-extrabold ring-4 ring-[#FADBE8]">
                            <?= htmlspecialchars($inicial) ?>
                        </div>
                    <?php endif; ?>

                    <label for="foto_input"
                           class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#8A5AAE] text-white flex items-center justify-center shadow-md hover:bg-[#734493] hover:scale-105 active:scale-95 cursor-pointer transition-all"
                           title="Actualizar foto">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                    </label>
                </div>

                <h2 class="text-xl font-bold"><?= htmlspecialchars($cliente['nombre']) ?></h2>
                <p class="text-sm text-[#6E5A7A]">Cliente Dulce Micro</p>

                <!-- Resumen: pedidos y Puntos Dulces -->
                <div class="w-full mt-6 pt-5 border-t border-[#EAD8EC] flex justify-around text-center">
                    <div>
                        <div class="text-2xl font-extrabold"><?= $totalPedidos ?></div>
                        <div class="text-xs font-semibold text-[#6E5A7A]">
                            <?= $totalPedidos === 1 ? 'Pedido realizado' : 'Pedidos realizados' ?>
                        </div>
                    </div>
                    <div class="w-px bg-[#EAD8EC]"></div>
                    <div>
                        <div class="text-2xl font-extrabold text-[#E685A8]"><?= (int) $cliente['puntos_dulces'] ?></div>
                        <div class="text-xs font-semibold text-[#6E5A7A]">Puntos Dulces</div>
                    </div>
                </div>
            </div>

            <a href="mis_pedidos.php"
               class="bg-white rounded-2xl p-4 shadow-lila-soft border border-[#EAD8EC] flex items-center justify-between hover:bg-[#F1E2F6] transition-colors">
                <span class="flex items-center gap-3 font-semibold text-sm">
                    <span class="material-symbols-outlined text-[#8A5AAE]">inventory_2</span>
                    Ver mis pedidos
                </span>
                <span class="material-symbols-outlined text-[#8A5AAE]">arrow_forward</span>
            </a>
        </aside>

        <!-- ===== Columna derecha: formularios ===== -->
        <section class="lg:col-span-8 flex flex-col gap-6">

            <!-- Datos personales y dirección -->
            <form method="post" action="actualizar_perfil.php" class="bg-white rounded-2xl p-6 md:p-8 shadow-lila-soft border border-[#EAD8EC]">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="accion" value="datos">

                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-[#EAD8EC]">
                    <span class="p-2.5 bg-[#F1E2F6] rounded-full text-[#8A5AAE]">
                        <span class="material-symbols-outlined">person</span>
                    </span>
                    <div>
                        <h2 class="text-xl font-bold">Datos personales</h2>
                        <p class="text-xs text-[#6E5A7A]">Tu documento no se puede cambiar aquí; escríbenos si necesitas corregirlo.</p>
                    </div>
                </div>

                <!-- Foto de Perfil (segundo punto de entrada, mismo input real) -->
                <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-[#FDF3F8] mb-6 border border-[#EAD8EC]">
                    <?php if ($fotoUrl): ?>
                        <img id="avatar-preview-form"
                             src="<?= $fotoUrl ?>"
                             alt="Vista previa de foto"
                             class="w-16 h-16 rounded-full object-cover ring-2 ring-[#E685A8]">
                    <?php else: ?>
                        <div id="avatar-preview-form"
                             class="w-16 h-16 rounded-full bg-[#F1E2F6] text-[#8A5AAE] flex items-center justify-center text-xl font-extrabold ring-2 ring-[#E685A8]">
                            <?= htmlspecialchars($inicial) ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex-1 text-center sm:text-left">
                        <p class="text-sm font-bold">Foto de Perfil</p>
                        <p class="text-xs text-[#6E5A7A]">Formato PNG, JPG o WebP. Máximo 2MB.</p>
                    </div>

                    <button type="button" id="btn-cambiar-foto"
                            class="px-5 py-2.5 rounded-full bg-[#8A5AAE] text-white font-semibold text-xs hover:bg-[#734493] transition-colors inline-flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-base">upload</span>
                        <span>Cambiar foto</span>
                    </button>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FDF3F8] border border-[#EAD8EC] text-sm mb-6">
                    <span class="text-[#6E5A7A]">Documento:</span>
                    <span class="font-semibold ml-1">
                        <?= htmlspecialchars(trim(($cliente['tipo_documento'] ?? '') . ' ' . ($cliente['numero_documento'] ?? ''))) ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold mb-1.5" for="nombre">Nombre completo</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">badge</span>
                            <input id="nombre" name="nombre" type="text" required maxlength="150"
                                   value="<?= $val('nombre') ?>" class="<?= $campo ?>">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="correo">Correo electrónico</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">alternate_email</span>
                            <input id="correo" name="correo" type="email" required maxlength="150"
                                   value="<?= $val('correo') ?>" class="<?= $campo ?>">
                        </div>
                        <p class="text-[11px] text-[#6E5A7A] mt-1">Aquí llegan tus facturas y es lo que usas para ingresar.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="telefono">Teléfono / WhatsApp</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">phone_iphone</span>
                            <input id="telefono" name="telefono" type="tel" required maxlength="20"
                                   value="<?= $val('telefono') ?>" class="<?= $campo ?>">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold" for="fecha_nacimiento">Fecha de cumpleaños</label>
                            <span class="text-[11px] font-bold text-[#E685A8] bg-[#FADBE8] px-2 py-0.5 rounded-full">¡Recibe un cupón!</span>
                        </div>
                        <div class="relative">
                            <span class="<?= $icono ?>">cake</span>
                            <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                                   value="<?= $val('fecha_nacimiento') ?>" class="<?= $campo ?>">
                        </div>
                        <p class="text-[11px] text-[#6E5A7A] mt-1">Cada año te damos un cupón de 20% de descuento en tu cumpleaños.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="ciudad">Ciudad</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">apartment</span>
                            <select id="ciudad" name="ciudad" class="<?= $campo ?> appearance-none cursor-pointer">
                                <option value="">Elige...</option>
                                <?php foreach (CIUDADES_ENTREGA as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>" <?= $val('ciudad') === $c ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="barrio">Barrio</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">map</span>
                            <input id="barrio" name="barrio" type="text" maxlength="100"
                                   value="<?= $val('barrio') ?>" class="<?= $campo ?>">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold mb-1.5" for="direccion">Dirección principal de entrega</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">pin_drop</span>
                            <input id="direccion" name="direccion" type="text" maxlength="255"
                                   value="<?= $val('direccion') ?>" class="<?= $campo ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-[#EAD8EC] flex justify-end">
                    <button type="submit"
                            class="px-8 py-3 rounded-full bg-[#8A5AAE] hover:bg-[#734493] text-white font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">check</span>
                        Guardar cambios
                    </button>
                </div>
            </form>

            <!-- Cambiar contraseña -->
            <form method="post" action="actualizar_perfil.php" class="bg-white rounded-2xl p-6 md:p-8 shadow-lila-soft border border-[#EAD8EC]">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="accion" value="clave">

                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-[#EAD8EC]">
                    <span class="p-2.5 bg-[#F1E2F6] rounded-full text-[#8A5AAE]">
                        <span class="material-symbols-outlined">lock</span>
                    </span>
                    <h2 class="text-xl font-bold">Seguridad y contraseña</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="actual">Contraseña actual</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">lock</span>
                            <input id="actual" name="actual" type="password" required autocomplete="current-password" class="<?= $campo ?>">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="nueva">Nueva contraseña</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">lock_reset</span>
                            <input id="nueva" name="nueva" type="password" required minlength="8" autocomplete="new-password" class="<?= $campo ?>">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1.5" for="nueva2">Confirmar nueva</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">lock_reset</span>
                            <input id="nueva2" name="nueva2" type="password" required minlength="8" autocomplete="new-password" class="<?= $campo ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-[#EAD8EC] flex justify-end">
                    <button type="submit"
                            class="px-8 py-3 rounded-full border-2 border-[#8A5AAE] text-[#8A5AAE] hover:bg-[#F1E2F6] font-bold text-sm transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">lock_reset</span>
                        Cambiar contraseña
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<!-- Formulario oculto que realmente sube el archivo. Los dos triggers visuales
     (el ícono de cámara del sidebar y el botón "Cambiar foto" del formulario)
     apuntan a este mismo input, para no duplicar la subida. -->
<form id="form-foto" method="post" action="actualizar_foto.php" enctype="multipart/form-data" class="hidden">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="file" id="foto_input" name="foto" accept="image/png, image/jpeg, image/webp">
</form>

<script>
    (function () {
        const inputReal   = document.getElementById('foto_input');
        const botonFormu  = document.getElementById('btn-cambiar-foto');

        // El botón "Cambiar foto" del formulario abre el mismo selector de archivo
        // que el label de la cámara (ese ya funciona nativamente por el atributo "for").
        botonFormu?.addEventListener('click', () => inputReal.click());

        // Al elegir un archivo, se sube automáticamente (sin botón extra de "guardar").
        inputReal?.addEventListener('change', () => {
            if (inputReal.files && inputReal.files[0]) {
                document.getElementById('form-foto').submit();
            }
        });
    })();
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
