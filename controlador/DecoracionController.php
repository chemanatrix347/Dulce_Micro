<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/decoracion.php";

$decoracion = new Decoracion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $decoracion->crear_decoracion($_POST['precio']);
    } elseif ($accion === 'actualizar') {
        $decoracion->actualizar_decoracion($_POST['id'], $_POST['precio']);
    } elseif ($accion === 'eliminar') {
        $decoracion->eliminar_decoracion($_POST['id']);
    }

    header("Location: ../vista/decoracion.php");
    exit;
}
?>
