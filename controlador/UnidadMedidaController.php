<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/unidadmedida.php";

$unidadMedida = new UnidadMedida();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $unidadMedida->crear_unidad_medida($_POST['unidad_medida']);
    } elseif ($accion === 'actualizar') {
        $unidadMedida->actualizar_unidad_medida($_POST['id'], $_POST['unidad_medida']);
    } elseif ($accion === 'eliminar') {
        $unidadMedida->eliminar_unidad_medida($_POST['id']);
    }

    header("Location: ../vista/unidadmedida.php");
    exit;
}
?>
