<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/tamano.php";

$tamano = new Tamano();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $tamano->crear_tamano($_POST['tamano']);
    } elseif ($accion === 'actualizar') {
        $tamano->actualizar_tamano($_POST['id'], $_POST['tamano']);
    } elseif ($accion === 'eliminar') {
        $tamano->eliminar_tamano($_POST['id']);
    }

    header("Location: ../vista/tamano.php");
    exit;
}
?>
