<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/reposteras.php";

$repostera = new Repostera();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $repostera->crear_repostera($_POST['nombre_repostera']);
    } elseif ($accion === 'actualizar') {
        $repostera->actualizar_repostera($_POST['id'], $_POST['nombre_repostera']);
    } elseif ($accion === 'eliminar') {
        $repostera->eliminar_repostera($_POST['id']);
    }

    header("Location: ../vista/reposteras.php");
    exit;
}
?>
