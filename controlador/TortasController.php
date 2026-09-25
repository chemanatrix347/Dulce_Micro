<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/tortas.php";

$tortas = new Tortas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $tortas->crear_torta($_POST['tipo_torta']);
    } elseif ($accion === 'actualizar') {
        $tortas->actualizar_torta($_POST['id'], $_POST['tipo_torta']);
    } elseif ($accion === 'eliminar') {
        $tortas->eliminar_torta($_POST['id']);
    }

    header("Location: ../vista/tortas.php");
    exit;
}
?>
