<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

if (!csrfValido()) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$conn   = (new conexion())->conn;
$accion = $_POST['accion'] ?? '';
$volver = $_POST['volver'] ?? '';
$volver = in_array($volver, ['checkout', 'carrito'], true) ? $volver : '';
$destino = ['checkout' => 'checkout.php', 'carrito' => 'carrito.php'][$volver] ?? 'catalogo.php';

/** Regresa al formulario guardando los errores y lo que el cliente había escrito (sin contraseñas). */
function volverAlLogin(string $tab, array $errores, array $old, string $volver): void
{
    $_SESSION['flash_auth'] = ['tab' => $tab, 'errores' => $errores, 'old' => $old];
    header('Location: login.php' . ($volver !== '' ? '?volver=' . urlencode($volver) : ''));
    exit;
}

/** Guarda al cliente en la sesión. Se cambia el id de sesión para evitar fijación de sesión. */
function iniciarSesionCliente(array $c): void
{
    session_regenerate_id(true);
    $_SESSION['id_cliente']     = (int)$c['id_cliente'];
    $_SESSION['cliente_nombre'] = $c['nombre'];
    $_SESSION['cliente_correo'] = $c['correo'];
    unset($_SESSION['login_fallos'], $_SESSION['login_bloqueo']);
}

/* ===================== INGRESAR ===================== */
if ($accion === 'login') {
    $correo = mb_strtolower(trim($_POST['correo'] ?? ''));
    $clave  = $_POST['contrasena'] ?? '';

    if (($_SESSION['login_bloqueo'] ?? 0) > time()) {
        volverAlLogin('login', ['Demasiados intentos. Espera un minuto e inténtalo de nuevo.'], ['correo' => $correo], $volver);
    }

    $st = $conn->prepare(
        'SELECT id_cliente, nombre, correo, contrasena_hash
           FROM clientes WHERE correo = ? AND estado = 1 LIMIT 1'
    );
    $st->execute([$correo]);
    $c = $st->fetch(PDO::FETCH_ASSOC);

    if ($c && !empty($c['contrasena_hash']) && password_verify($clave, $c['contrasena_hash'])) {
        iniciarSesionCliente($c);
        header('Location: ' . $destino);
        exit;
    }

    // Falló: se cuenta el intento y a los 5 seguidos se bloquea 1 minuto
    $_SESSION['login_fallos'] = ($_SESSION['login_fallos'] ?? 0) + 1;
    if ($_SESSION['login_fallos'] >= 5) {
        $_SESSION['login_fallos']  = 0;
        $_SESSION['login_bloqueo'] = time() + 60;
    }
    volverAlLogin(
        'login',
        ['Correo o contraseña incorrectos. Si aún no tienes contraseña en la tienda web, crea tu cuenta.'],
        ['correo' => $correo],
        $volver
    );
}

/* ===================== CREAR CUENTA ===================== */
if ($accion === 'registro') {
    $nombre = trim(preg_replace('/\s+/', ' ', $_POST['nombre'] ?? ''));
    $idTipo = (int)($_POST['id_tipo_documento'] ?? 0);
    $doc    = trim($_POST['numero_documento'] ?? '');
    $correo = mb_strtolower(trim($_POST['correo'] ?? ''));
    $tel    = preg_replace('/\D/', '', $_POST['telefono'] ?? '');
    $clave  = $_POST['contrasena'] ?? '';
    $clave2 = $_POST['confirmar'] ?? '';

    $old = ['nombre' => $nombre, 'id_tipo_documento' => $idTipo, 'numero_documento' => $doc,
            'correo' => $correo, 'telefono' => $tel];

    $errores = [];
    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 150) {
        $errores[] = 'Escribe tu nombre completo.';
    }

    $st = $conn->prepare('SELECT 1 FROM tipo_documento WHERE id_tipo_documento = ? AND estado = 1');
    $st->execute([$idTipo]);
    if (!$st->fetchColumn()) {
        $errores[] = 'Elige un tipo de documento válido.';
    }
    if (!preg_match('/^[A-Za-z0-9\-]{4,30}$/', $doc)) {
        $errores[] = 'El número de documento debe tener entre 4 y 30 letras o números.';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) {
        $errores[] = 'Escribe un correo electrónico válido.';
    }
    if (strlen($tel) < 7 || strlen($tel) > 15) {
        $errores[] = 'Escribe un teléfono de contacto válido.';
    }
    if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/\d/', $clave)) {
        $errores[] = 'La contraseña debe tener mínimo 8 caracteres, con letras y números.';
    }
    if ($clave !== $clave2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    if ($errores) {
        volverAlLogin('registro', $errores, $old, $volver);
    }

    // ¿Ya existe un cliente con ese correo o documento?
    $st = $conn->prepare(
        'SELECT id_cliente, id_tipo_documento, numero_documento, correo, contrasena_hash
           FROM clientes
          WHERE estado = 1 AND (correo = ? OR (id_tipo_documento = ? AND numero_documento = ?))'
    );
    $st->execute([$correo, $idTipo, $doc]);
    $coincidencias = $st->fetchAll(PDO::FETCH_ASSOC);
    $hash = password_hash($clave, PASSWORD_DEFAULT);

    try {
        if (!$coincidencias) {
            // Cliente nuevo
            $st = $conn->prepare(
                'INSERT INTO clientes (nombre, id_tipo_documento, numero_documento, correo, telefono, estado, contrasena_hash)
                 VALUES (?, ?, ?, ?, ?, 1, ?)'
            );
            $st->execute([$nombre, $idTipo, $doc, $correo, $tel, $hash]);
            $idCliente = (int)$conn->lastInsertId();

        } elseif (
            // Cliente que ya compró en el mostrador y aún no tenía contraseña: se vincula su cuenta
            count($coincidencias) === 1
            && empty($coincidencias[0]['contrasena_hash'])
            && (int)$coincidencias[0]['id_tipo_documento'] === $idTipo
            && $coincidencias[0]['numero_documento'] === $doc
            && (empty($coincidencias[0]['correo']) || mb_strtolower($coincidencias[0]['correo']) === $correo)
        ) {
            $idCliente = (int)$coincidencias[0]['id_cliente'];
            $st = $conn->prepare(
                "UPDATE clientes
                    SET contrasena_hash = ?, correo = ?, telefono = COALESCE(NULLIF(telefono, ''), ?)
                  WHERE id_cliente = ?"
            );
            $st->execute([$hash, $correo, $tel, $idCliente]);

        } else {
            volverAlLogin(
                'registro',
                ['Ya existe una cuenta o un cliente con ese correo o documento. Inicia sesión o escríbenos para ayudarte.'],
                $old,
                $volver
            );
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {   // dato duplicado en una llave única
            volverAlLogin('registro', ['Ya existe un cliente con esos datos. Inicia sesión.'], $old, $volver);
        }
        throw $e;
    }

    $st = $conn->prepare('SELECT id_cliente, nombre, correo FROM clientes WHERE id_cliente = ?');
    $st->execute([$idCliente]);
    iniciarSesionCliente($st->fetch(PDO::FETCH_ASSOC));

    header('Location: ' . $destino);
    exit;
}

header('Location: login.php');
exit;
