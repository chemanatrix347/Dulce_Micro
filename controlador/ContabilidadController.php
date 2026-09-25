<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/contabilidad.php";

$contabilidad = new Contabilidad();

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $contabilidad->crear_movimiento($_POST['tipo_movimiento'], $_POST['monto_transaccion'], $_POST['descripcion_registro'], $_POST['fecha_registro']);
    } elseif ($accion === 'actualizar') {
        $contabilidad->actualizar_movimiento($_POST['id'], $_POST['tipo_movimiento'], $_POST['monto_transaccion'], $_POST['descripcion_registro'], $_POST['fecha_registro']);
    } elseif ($accion === 'eliminar') {
        $contabilidad->eliminar_movimiento($_POST['id']);
    }

    header("Location: ../vista/contabilidad.php");
    exit;
}
?>
