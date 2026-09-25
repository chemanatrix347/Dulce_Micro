<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';

exigirCliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    header('Location: perfil.php');
    exit;
}

$idCliente = (int)$_SESSION['id_cliente'];
$errores   = [];

if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $errores[] = 'No se pudo subir la imagen. Intenta de nuevo.';
} else {
    $archivo    = $_FILES['foto'];
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime       = mime_content_type($archivo['tmp_name']);
    $maxBytes   = 2 * 1024 * 1024; // 2MB

    if (!isset($permitidos[$mime])) {
        $errores[] = 'Formato no permitido. Usa PNG, JPG o WebP.';
    } elseif ($archivo['size'] > $maxBytes) {
        $errores[] = 'La imagen supera el máximo de 2MB.';
    } else {
        $ext         = $permitidos[$mime];
        $nombreNuevo = 'cliente_' . $idCliente . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $rutaDestino = __DIR__ . '/../img_clientes/' . $nombreNuevo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $conn = (new conexion())->conn;

            // Borra la foto anterior del disco, si existía
            $st = $conn->prepare('SELECT foto_perfil FROM clientes WHERE id_cliente = ?');
            $st->execute([$idCliente]);
            $anterior = $st->fetchColumn();

            $conn->prepare('UPDATE clientes SET foto_perfil = ? WHERE id_cliente = ?')
                 ->execute([$nombreNuevo, $idCliente]);

            if ($anterior && file_exists(__DIR__ . '/../img_clientes/' . $anterior)) {
                unlink(__DIR__ . '/../img_clientes/' . $anterior);
            }
        } else {
            $errores[] = 'Error al guardar la imagen en el servidor.';
        }
    }
}

$_SESSION['flash_perfil'] = $errores
    ? ['errores' => $errores]
    : ['ok' => true, 'mensaje' => 'Foto de perfil actualizada.'];

header('Location: perfil.php');
exit;
