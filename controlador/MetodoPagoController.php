<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/metodospago.php";

$metodoPago = new MetodoPago();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $metodoPago->crear_metodo_pago($_POST['metodo_pago']);
    } elseif ($accion === 'actualizar') {
        $metodoPago->actualizar_metodo_pago($_POST['id'], $_POST['metodo_pago']);
    } elseif ($accion === 'eliminar') {
        $metodoPago->eliminar_metodo_pago($_POST['id']);
    }

    header("Location: ../vista/metodospago.php");
    exit;
}
?>
