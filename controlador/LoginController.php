<?php
session_name("LOGIN");
session_start();

require_once __DIR__ . "/../modelo/usuarios.php";
require_once __DIR__ . "/../modelo/roles.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $correo = $_POST['correo'] ?? '';
    $clave = $_POST['clave'] ?? '';

    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->buscar_por_correo($correo);

    // password_verify compara la clave escrita con el hash guardado en contrasena_hash
    if ($usuario && password_verify($clave, $usuario['contrasena_hash'])) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['Nombre'] = $usuario['nombre_completo'];
        $_SESSION['id_roles'] = $usuario['id_roles'];

        // Guardamos tambien el NOMBRE del rol, para poder comparar por nombre en el RBAC
        $rolesModel = new Roles();
        $rol = $rolesModel->buscar_rol($usuario['id_roles']);
        $_SESSION['nombre_rol'] = $rol ? $rol['nombre_rol'] : '';

// El Vendedor entra directo a Nueva Venta, los demas roles ven el Dashboard
if ($_SESSION['nombre_rol'] === 'Vendedor') {
    header("Location: ../vista/nuevaventa.php");
} else {
    header("Location: ../index.php");
}
exit();

        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../vista/login.php?error=1");
        exit();
    }
}
?>
