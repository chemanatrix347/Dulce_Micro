<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/tablamaestra.php";

$tablaMaestra = new TablaMaestra();
$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $tablaMaestra->crear_maestro($_POST['fecha_transaccion'], $_POST['id_cliente'], $_POST['id_materia_prima'], $_POST['id_tortas'], $_POST['id_postre_individual'], $_POST['cantidad_pedida'], $_POST['id_pago'], $_POST['id_contabilidad'], $_POST['id_estado_pedido']);
    } elseif ($accion === 'actualizar') {
        $tablaMaestra->actualizar_maestro($_POST['id'], $_POST['fecha_transaccion'], $_POST['id_cliente'], $_POST['id_materia_prima'], $_POST['id_tortas'], $_POST['id_postre_individual'], $_POST['cantidad_pedida'], $_POST['id_pago'], $_POST['id_contabilidad'], $_POST['id_estado_pedido']);
    } elseif ($accion === 'eliminar') {
        $tablaMaestra->eliminar_maestro($_POST['id']);
    }

    header("Location: ../vista/tablamaestra.php");
    exit;
}
?>
