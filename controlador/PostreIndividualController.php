<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/postresindividuales.php";

$postreIndividual = new PostreIndividual();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $postreIndividual->crear_postre_individual($_POST['nombre_producto'], $_POST['descripcion']);
    } elseif ($accion === 'actualizar') {
        $postreIndividual->actualizar_postre_individual($_POST['id'], $_POST['nombre_producto'], $_POST['descripcion']);
    } elseif ($accion === 'eliminar') {
        $postreIndividual->eliminar_postre_individual($_POST['id']);
    }

    header("Location: ../vista/postresindividuales.php");
    exit;
}
?>
