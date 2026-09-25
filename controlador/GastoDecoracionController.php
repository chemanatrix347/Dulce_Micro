<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/gastodecoracion.php";

$gastoDecoracion = new GastoDecoracion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $gastoDecoracion->crear_gasto_decoracion($_POST['id_decoracion'], $_POST['numero_diseno'], $_POST['nombre_diseno'], $_POST['fondant_inicial'], $_POST['gasto_fondant'], $_POST['colorante_inicial'], $_POST['gasto_colorante']);
    } elseif ($accion === 'actualizar') {
        $gastoDecoracion->actualizar_gasto_decoracion($_POST['id'], $_POST['id_decoracion'], $_POST['numero_diseno'], $_POST['nombre_diseno'], $_POST['fondant_inicial'], $_POST['gasto_fondant'], $_POST['colorante_inicial'], $_POST['gasto_colorante']);
    } elseif ($accion === 'eliminar') {
        $gastoDecoracion->eliminar_gasto_decoracion($_POST['id']);
    }

    header("Location: ../vista/gastodecoracion.php");
    exit;
}
?>
