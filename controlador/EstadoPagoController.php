<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/estadopagos.php";

$estadoPago = new EstadoPago();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $estadoPago->crear_estado_pago($_POST['estado_pago']);
    } elseif ($accion === 'actualizar') {
        $estadoPago->actualizar_estado_pago($_POST['id'], $_POST['estado_pago']);
    } elseif ($accion === 'eliminar') {
        $estadoPago->eliminar_estado_pago($_POST['id']);
    }

    header("Location: ../vista/estadopagos.php");
    exit;
}
?>
