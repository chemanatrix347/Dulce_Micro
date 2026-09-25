<?php

if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}

require_once __DIR__ . "/../modelo/producto.php";

$producto = new Producto();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificación CSRF
    if (!hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token'] ?? ''
    )) {
        die("Token de seguridad inválido. Recarga la página e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    /*
     * Los tipos de torta y postre individual son opcionales
     * dependiendo de la categoría seleccionada.
     */
    $id_tortas = !empty($_POST['id_tortas'])
        ? $_POST['id_tortas']
        : null;

    $id_postre_individual = !empty($_POST['id_postre_individual'])
        ? $_POST['id_postre_individual']
        : null;


    if ($accion === 'crear') {

        $producto->crear_producto(
            $_POST['nombre_producto'],
            $_POST['descripcion'],
            $_POST['id_categoria_postre'],
            $id_tortas,
            $id_postre_individual,
            $_POST['id_sabor'],
            $_POST['id_tamano'],
            $_POST['precio_base']
        );

    } elseif ($accion === 'actualizar') {

        $producto->actualizar_producto(
            $_POST['id'],
            $_POST['nombre_producto'],
            $_POST['descripcion'],
            $_POST['id_categoria_postre'],
            $id_tortas,
            $id_postre_individual,
            $_POST['id_sabor'],
            $_POST['id_tamano'],
            $_POST['precio_base']
        );

    } elseif ($accion === 'eliminar') {

        $producto->eliminar_producto($_POST['id']);
    }

    header("Location: ../vista/producto.php");
    exit;
}
?>