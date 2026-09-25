<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/clientes.php";

$cliente = new Cliente();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $cliente->crear_cliente($_POST['nombre'], $_POST['id_tipo_documento'], $_POST['numero_documento'], $_POST['correo'], $_POST['telefono']);
    } elseif ($accion === 'actualizar') {
        $cliente->actualizar_cliente($_POST['id'], $_POST['nombre'], $_POST['id_tipo_documento'], $_POST['numero_documento'], $_POST['correo'], $_POST['telefono']);
    } elseif ($accion === 'eliminar') {
        $cliente->eliminar_cliente($_POST['id']);
    }

    header("Location: ../vista/clientes.php");
    exit;
}
?>
