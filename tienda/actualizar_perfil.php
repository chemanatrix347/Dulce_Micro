<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

exigirCliente();

if (!csrfValido()) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$conn      = (new conexion())->conn;
$idCliente = (int)$_SESSION['id_cliente'];
$accion    = $_POST['accion'] ?? '';

function volverAlPerfil(array $errores, array $old, string $seccion): void
{
    $_SESSION['flash_perfil'] = ['errores' => $errores, 'old' => $old, 'seccion' => $seccion];
    header('Location: perfil.php');
    exit;
}

/* ===================== DATOS PERSONALES Y DE ENTREGA ===================== */
if ($accion === 'datos') {
    $nombre    = trim(preg_replace('/\s+/', ' ', $_POST['nombre'] ?? ''));
    $telefono  = preg_replace('/\D/', '', $_POST['telefono'] ?? '');
    $correo    = mb_strtolower(trim($_POST['correo'] ?? ''));
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad    = trim($_POST['ciudad'] ?? '');
    $barrio    = trim($_POST['barrio'] ?? '');

    $old = compact('nombre', 'telefono', 'correo', 'direccion', 'ciudad', 'barrio');

    $errores = [];
    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 150) {
        $errores[] = 'Escribe tu nombre completo.';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) {
        $errores[] = 'Escribe un correo electrónico válido.';
    }
    if (strlen($telefono) < 7 || strlen($telefono) > 15) {
        $errores[] = 'Escribe un teléfono de contacto válido.';
    }
    if ($errores) {
        volverAlPerfil($errores, $old, 'datos');
    }

    // El correo debe seguir siendo único: es lo que usa el cliente para iniciar sesión
    $st = $conn->prepare('SELECT 1 FROM clientes WHERE correo = ? AND id_cliente <> ? AND estado = 1');
    $st->execute([$correo, $idCliente]);
    if ($st->fetchColumn()) {
        volverAlPerfil(['Ese correo ya lo usa otra cuenta.'], $old, 'datos');
    }

    $st = $conn->prepare(
        'UPDATE clientes
            SET nombre = ?, telefono = ?, correo = ?, direccion = ?, ciudad = ?, barrio = ?
          WHERE id_cliente = ?'
    );
    $st->execute([$nombre, $telefono, $correo, $direccion, $ciudad, $barrio, $idCliente]);

    $_SESSION['cliente_nombre'] = $nombre;
    $_SESSION['cliente_correo'] = $correo;
    $_SESSION['flash_perfil']   = ['ok' => true, 'mensaje' => 'Tus datos se actualizaron correctamente.'];
    header('Location: perfil.php');
    exit;
}

/* ===================== CAMBIAR CONTRASEÑA ===================== */
if ($accion === 'clave') {
    $actual = $_POST['actual'] ?? '';
    $nueva  = $_POST['nueva'] ?? '';
    $nueva2 = $_POST['nueva2'] ?? '';

    $st = $conn->prepare('SELECT contrasena_hash FROM clientes WHERE id_cliente = ?');
    $st->execute([$idCliente]);
    $hashActual = $st->fetchColumn();

    $errores = [];
    if (!$hashActual || !password_verify($actual, $hashActual)) {
        $errores[] = 'Tu contraseña actual no es correcta.';
    }
    if (strlen($nueva) < 8 || !preg_match('/[A-Za-z]/', $nueva) || !preg_match('/\d/', $nueva)) {
        $errores[] = 'La nueva contraseña debe tener mínimo 8 caracteres, con letras y números.';
    }
    if ($nueva !== $nueva2) {
        $errores[] = 'Las contraseñas nuevas no coinciden.';
    }
    if ($errores) {
        volverAlPerfil($errores, [], 'clave');
    }

    $st = $conn->prepare('UPDATE clientes SET contrasena_hash = ? WHERE id_cliente = ?');
    $st->execute([password_hash($nueva, PASSWORD_DEFAULT), $idCliente]);

    $_SESSION['flash_perfil'] = ['ok' => true, 'mensaje' => 'Tu contraseña se actualizó correctamente.'];
    header('Location: perfil.php');
    exit;
}

header('Location: perfil.php');
exit;
