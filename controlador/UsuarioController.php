<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/usuarios.php";

$usuario = new Usuario();
require_once __DIR__ . '/../vista/partials/solo_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    // La contraseña escrita en el formulario se encripta antes de guardarla
    $contrasena_hash = password_hash($_POST['contrasena_hash'], PASSWORD_DEFAULT);

    if ($accion === 'crear') {
        $usuario->crear_usuario(
            $_POST['nombre_completo'],
            $_POST['correo_electronico'],
            $_POST['id_roles'],
            $contrasena_hash,
            $_POST['fecha_registro'],
            $_POST['estado']
        );
    } elseif ($accion === 'actualizar') {
        $usuario->actualizar_usuario(
            $_POST['id'],
            $_POST['nombre_completo'],
            $_POST['correo_electronico'],
            $_POST['id_roles'],
            $contrasena_hash,
            $_POST['fecha_registro'],
            $_POST['estado']
        );
    } elseif ($accion === 'eliminar') {
        $usuario->eliminar_usuario($_POST['id']);
    }

    header("Location: ../vista/usuarios.php");
    exit;
}
?>
