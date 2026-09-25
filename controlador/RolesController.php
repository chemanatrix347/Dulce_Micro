<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/roles.php";

$roles = new Roles();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $roles->crear_rol($_POST['nombre_rol']);
    } elseif ($accion === 'actualizar') {
        $roles->actualizar_rol($_POST['id'], $_POST['nombre_rol']);
    } elseif ($accion === 'eliminar') {
        $roles->eliminar_rol($_POST['id']);
    }

    header("Location: ../vista/roles.php");
    exit;
}
?>
