<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/materiaprima.php";

$materiaPrima = new MateriaPrima();
$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $materiaPrima->crear_materia_prima($_POST['nombre_insumo'], $_POST['descripcion'], $_POST['stock_disponible'], $_POST['id_unidad_medida']);
    } elseif ($accion === 'actualizar') {
        $materiaPrima->actualizar_materia_prima($_POST['id'], $_POST['nombre_insumo'], $_POST['descripcion'], $_POST['stock_disponible'], $_POST['id_unidad_medida']);
    } elseif ($accion === 'eliminar') {
        $materiaPrima->eliminar_materia_prima($_POST['id']);
    }

    header("Location: ../vista/materiaprima.php");
    exit;
}
?>
