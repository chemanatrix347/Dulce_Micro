<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/tipodocumento.php";

$tipoDocumento = new TipoDocumento();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $tipoDocumento->crear_tipo_documento($_POST['tipo_documento']);
    } elseif ($accion === 'actualizar') {
        $tipoDocumento->actualizar_tipo_documento($_POST['id'], $_POST['tipo_documento']);
    } elseif ($accion === 'eliminar') {
        $tipoDocumento->eliminar_tipo_documento($_POST['id']);
    }

    header("Location: ../vista/tipodocumento.php");
    exit;
}
?>
