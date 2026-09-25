<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/pagos.php";

$pago = new Pago();
$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $pago->crear_pago($_POST['id_pedido'], $_POST['id_metodo_pago'], $_POST['monto'], $_POST['id_estado_pago'], $_POST['fecha_pago']);
    } elseif ($accion === 'actualizar') {
        $pago->actualizar_pago($_POST['id'], $_POST['id_pedido'], $_POST['id_metodo_pago'], $_POST['monto'], $_POST['id_estado_pago'], $_POST['fecha_pago']);
    } elseif ($accion === 'eliminar') {
        $pago->eliminar_pago($_POST['id']);
    }

    header("Location: ../vista/pagos.php");
    exit;
}
?>
