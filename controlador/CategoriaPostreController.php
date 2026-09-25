<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/categoriapostre.php";

$categoriaPostre = new CategoriaPostre();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $categoriaPostre->crear_categoria_postre($_POST['categoria_postre']);
    } elseif ($accion === 'actualizar') {
        $categoriaPostre->actualizar_categoria_postre($_POST['id'], $_POST['categoria_postre']);
    } elseif ($accion === 'eliminar') {
        $categoriaPostre->eliminar_categoria_postre($_POST['id']);
    }

    header("Location: ../vista/categoriapostre.php");
    exit;
}
?>
