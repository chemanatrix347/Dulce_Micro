<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/sabor.php";

$sabor = new Sabor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $sabor->crear_sabor($_POST['sabor']);
    } elseif ($accion === 'actualizar') {
        $sabor->actualizar_sabor($_POST['id'], $_POST['sabor']);
    } elseif ($accion === 'eliminar') {
        $sabor->eliminar_sabor($_POST['id']);
    }

    header("Location: ../vista/sabor.php");
    exit;
}
?>
