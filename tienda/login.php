<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

$volver = in_array($_GET['volver'] ?? '', ['checkout', 'carrito'], true) ? $_GET['volver'] : '';

// Si ya tiene sesión, no tiene sentido mostrar el formulario
if (clienteLogueado()) {
    header('Location: ' . ($volver === 'checkout' ? 'checkout.php' : 'catalogo.php'));
    exit;
}

// Errores y datos escritos que dejó auth.php (se muestran una sola vez)
$flash = $_SESSION['flash_auth'] ?? null;
unset($_SESSION['flash_auth']);

$tab     = $flash['tab'] ?? (($_GET['tab'] ?? '') === 'registro' ? 'registro' : 'login');
$errores = $flash['errores'] ?? [];
$old     = $flash['old'] ?? [];

// 1.3: aviso de sesión expirada por inactividad (lo deja sesion.php al destruir la sesión vieja)
$sesionExpirada = ($_SESSION['aviso_sesion'] ?? '') === 'expirada';
unset($_SESSION['aviso_sesion']);

$conn  = (new conexion())->conn;
$tipos = $conn->query(
    'SELECT id_tipo_documento AS id, tipo_documento AS nombre
       FROM tipo_documento WHERE estado = 1 ORDER BY tipo_documento'
)->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = 'Ingresar';
$paginaActiva = '';
require_once __DIR__ . '/partials/header.php';

$campo = 'w-full pl-11 pr-4 py-3 bg-[#FBF5FD] border border-[#E9DCED] rounded-full text-[#3A2545] placeholder:text-[#9A8AA3] text-sm focus:bg-white focus:border-[#8A5AAE] focus:ring-2 focus:ring-[#8A5AAE]/20 transition-all outline-none';
$icono = 'material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#9A8AA3] text-[20px]';
$boton = 'w-full py-3.5 px-6 rounded-full bg-[#8A5AAE] text-white font-semibold text-base flex items-center justify-center gap-2 transition-all duration-200 hover:bg-[#734493] hover:shadow-lg active:scale-95 shadow-md';
?>

<main class="flex-grow flex items-center justify-center px-4 py-8 md:py-12 relative overflow-hidden">
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#F7A8C4]/35 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-[#C9A0DC]/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-lg mx-auto z-10">
        <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-lila-hover border border-[#EEDDF3]">

            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center p-2 mb-3 bg-[#FDF3F8] rounded-full">
                    <img src="<?= $logoTienda ?>" alt="Dulce Micro" class="h-24 w-24 object-cover rounded-full">
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Bienvenido a Dulce Micro</h1>
                <p class="text-sm text-[#6E5A7A] mt-1.5">
                    <?= $volver === 'checkout'
                        ? 'Ingresa o crea tu cuenta para finalizar tu compra.'
                        : 'Repostería fina artesanal y momentos dulces para compartir' ?>
                </p>
            </div>

            <!-- Pestañas -->
            <div class="flex items-center p-1.5 bg-[#F1E2F6] rounded-full mb-7" role="tablist">
                <button type="button" id="tab-login" onclick="cambiarTab('login')"
                        class="flex-1 py-2.5 text-center text-sm rounded-full transition-all duration-200">Iniciar sesión</button>
                <button type="button" id="tab-registro" onclick="cambiarTab('registro')"
                        class="flex-1 py-2.5 text-center text-sm rounded-full transition-all duration-200">Crear cuenta</button>
            </div>

            <?php if ($sesionExpirada): ?>
                <div class="mb-5 bg-[#F1E2F6] border border-[#EAD8EC] text-[#5F3A7E] text-sm rounded-2xl px-4 py-3 flex items-center gap-2" role="status">
                    <span class="material-symbols-outlined text-lg">schedule</span>
                    Tu sesión se cerró por inactividad. Ingresa de nuevo para continuar.
                </div>
            <?php endif; ?>

            <?php if ($errores): ?>
                <div class="mb-5 bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D] text-sm rounded-2xl px-4 py-3" role="alert">
                    <ul class="list-disc pl-4 space-y-0.5">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ===== INGRESAR ===== -->
            <form id="form-login" method="post" action="auth.php" class="space-y-5 <?= $tab === 'login' ? '' : 'hidden' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="accion" value="login">
                <input type="hidden" name="volver" value="<?= htmlspecialchars($volver) ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="login-correo">Correo electrónico</label>
                    <div class="relative">
                        <span class="<?= $icono ?>">mail</span>
                        <input id="login-correo" name="correo" type="email" required autocomplete="email"
                               value="<?= htmlspecialchars($tab === 'login' ? ($old['correo'] ?? '') : '') ?>"
                               placeholder="tucorreo@ejemplo.com" class="<?= $campo ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="login-clave">Contraseña</label>
                    <div class="relative">
                        <span class="<?= $icono ?>">lock</span>
                        <input id="login-clave" name="contrasena" type="password" required autocomplete="current-password"
                               placeholder="Tu contraseña" class="<?= $campo ?> pr-12">
                        <button type="button" onclick="verClave('login-clave', this)" aria-label="Mostrar contraseña"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#9A8AA3] hover:text-[#8A5AAE] p-1">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="<?= $boton ?>">
                    <span>Iniciar sesión</span>
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
            </form>

            <!-- ===== CREAR CUENTA ===== -->
            <form id="form-registro" method="post" action="auth.php" class="space-y-4 <?= $tab === 'registro' ? '' : 'hidden' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="accion" value="registro">
                <input type="hidden" name="volver" value="<?= htmlspecialchars($volver) ?>">

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-nombre">Nombre completo</label>
                    <div class="relative">
                        <span class="<?= $icono ?>">person</span>
                        <input id="reg-nombre" name="nombre" type="text" required maxlength="150" autocomplete="name"
                               value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                               placeholder="Mariana Gómez" class="<?= $campo ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold mb-1.5" for="reg-tipo">Tipo de documento</label>
                        <select id="reg-tipo" name="id_tipo_documento" required
                                class="w-full px-4 py-3 bg-[#FBF5FD] border border-[#E9DCED] rounded-full text-[#3A2545] text-sm focus:bg-white focus:border-[#8A5AAE] focus:ring-2 focus:ring-[#8A5AAE]/20 outline-none">
                            <option value="">Elige...</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= (int)($old['id_tipo_documento'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-semibold mb-1.5" for="reg-doc">Número de documento</label>
                        <div class="relative">
                            <span class="<?= $icono ?>">badge</span>
                            <input id="reg-doc" name="numero_documento" type="text" required maxlength="30" inputmode="numeric"
                                   value="<?= htmlspecialchars($old['numero_documento'] ?? '') ?>"
                                   placeholder="1012345678" class="<?= $campo ?>">
                        </div>
                    </div>
                </div>
                <p class="text-xs text-[#6E5A7A] -mt-2 px-1">Lo usamos para generar tu factura de compra.</p>

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-correo">Correo electrónico</label>
                    <div class="relative">
                        <span class="<?= $icono ?>">mail</span>
                        <input id="reg-correo" name="correo" type="email" required maxlength="150" autocomplete="email"
                               value="<?= htmlspecialchars($old['correo'] ?? '') ?>"
                               placeholder="tucorreo@ejemplo.com" class="<?= $campo ?>">
                    </div>
                    <p class="text-xs text-[#6E5A7A] mt-1 px-1">Aquí te enviaremos la factura de tus compras.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-tel">Teléfono / WhatsApp</label>
                    <div class="flex items-center">
                        <div class="flex items-center gap-1.5 px-3.5 py-3 bg-[#F4EBF7] border border-r-0 border-[#E9DCED] rounded-l-full text-[#8A5AAE] font-semibold text-sm select-none shrink-0">
                            <span>+57</span>
                        </div>
                        <input id="reg-tel" name="telefono" type="tel" required maxlength="20" autocomplete="tel-national"
                               value="<?= htmlspecialchars($old['telefono'] ?? '') ?>"
                               placeholder="312 456 7890"
                               class="w-full px-4 py-3 bg-[#FBF5FD] border border-[#E9DCED] rounded-r-full text-[#3A2545] placeholder:text-[#9A8AA3] text-sm focus:bg-white focus:border-[#8A5AAE] focus:ring-2 focus:ring-[#8A5AAE]/20 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-clave">Contraseña</label>
                    <div class="relative">
                        <span class="<?= $icono ?>">lock</span>
                        <input id="reg-clave" name="contrasena" type="password" required minlength="8" autocomplete="new-password"
                               oninput="medirClave(this.value)" placeholder="Mínimo 8 caracteres, con letras y números"
                               class="<?= $campo ?> pr-12">
                        <button type="button" onclick="verClave('reg-clave', this)" aria-label="Mostrar contraseña"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#9A8AA3] hover:text-[#8A5AAE] p-1">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                    <div class="mt-2 px-1">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-[#6E5A7A]">Nivel de seguridad</span>
                            <span id="fuerza-texto" class="font-semibold text-[#8A5AAE]">—</span>
                        </div>
                        <div class="grid grid-cols-4 gap-1.5 h-1.5 w-full">
                            <div class="barra-fuerza h-full rounded-full bg-[#DAC9DF] transition-colors"></div>
                            <div class="barra-fuerza h-full rounded-full bg-[#DAC9DF] transition-colors"></div>
                            <div class="barra-fuerza h-full rounded-full bg-[#DAC9DF] transition-colors"></div>
                            <div class="barra-fuerza h-full rounded-full bg-[#DAC9DF] transition-colors"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5" for="reg-confirmar">Confirmar contraseña</label>
                    <div class="relative">
                        <span class="<?= $icono ?>">lock_reset</span>
                        <input id="reg-confirmar" name="confirmar" type="password" required minlength="8" autocomplete="new-password"
                               placeholder="Repite tu contraseña" class="<?= $campo ?>">
                    </div>
                </div>

                <div class="pt-1">
                    <button type="submit" class="<?= $boton ?>">
                        <span>Crear mi cuenta</span>
                        <span class="material-symbols-outlined text-[20px]">cake</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 text-center">
            <div class="flex items-center justify-center gap-2 p-3 bg-white/80 border border-[#F0DDF4] rounded-2xl">
                <span class="material-symbols-outlined text-[#8A5AAE] text-[20px]">local_shipping</span>
                <span class="text-xs font-semibold">Entregas a domicilio</span>
            </div>
            <div class="flex items-center justify-center gap-2 p-3 bg-white/80 border border-[#F0DDF4] rounded-2xl">
                <span class="material-symbols-outlined text-[#E685A8] text-[20px]">verified_user</span>
                <span class="text-xs font-semibold">Garantía de frescura</span>
            </div>
        </div>
    </div>
</main>

<script>
    // Pestañas ingresar / crear cuenta
    function cambiarTab(modo) {
        var activa   = 'flex-1 py-2.5 text-center text-sm rounded-full transition-all duration-200 font-bold bg-[#8A5AAE] text-white shadow-md';
        var inactiva = 'flex-1 py-2.5 text-center text-sm rounded-full transition-all duration-200 font-semibold text-[#6E5A7A] hover:text-[#8A5AAE]';
        document.getElementById('form-login').classList.toggle('hidden', modo !== 'login');
        document.getElementById('form-registro').classList.toggle('hidden', modo !== 'registro');
        document.getElementById('tab-login').className    = modo === 'login' ? activa : inactiva;
        document.getElementById('tab-registro').className = modo === 'registro' ? activa : inactiva;
    }
    cambiarTab('<?= $tab ?>');

    // Mostrar u ocultar la contraseña
    function verClave(id, boton) {
        var input = document.getElementById(id);
        var visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        boton.querySelector('.material-symbols-outlined').textContent = visible ? 'visibility_off' : 'visibility';
    }

    // Medidor de seguridad (solo orientativo: la regla real se valida en el servidor)
    function medirClave(v) {
        var barras = document.querySelectorAll('.barra-fuerza');
        var texto  = document.getElementById('fuerza-texto');
        var puntos = 0;
        if (v.length >= 8) puntos++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) puntos++;
        if (/\d/.test(v)) puntos++;
        if (/[^A-Za-z0-9]/.test(v)) puntos++;
        var colores = ['#E57373', '#E685A8', '#C9A0DC', '#8A5AAE'];
        var nombres = ['Débil', 'Aceptable', 'Buena', 'Excelente'];
        barras.forEach(function (b, i) { b.style.backgroundColor = (v && i < puntos) ? colores[puntos - 1] : '#DAC9DF'; });
        texto.textContent = v ? nombres[Math.max(puntos - 1, 0)] : '—';
    }
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
