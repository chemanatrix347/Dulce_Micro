<?php

if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}



require_once __DIR__ . "/../modelo/inventario.php";

$inventario = new Inventario();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =========================================================
    // VALIDAR CSRF
    // =========================================================

    if (!hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token'] ?? ''
    )) {

        die(
            "Token de seguridad inválido. " .
            "Recarga la página e intenta de nuevo."
        );
    }


    $accion = $_POST['accion'] ?? '';


    try {

        // =====================================================
        // CREAR
        // =====================================================

        if ($accion === 'crear') {

            $inventario->crear_inventario(
                $_POST['id_producto'],
                $_POST['cantidad']
            );
        }


        // =====================================================
        // ACTUALIZAR
        // =====================================================

        elseif ($accion === 'actualizar') {

            $inventario->actualizar_inventario(
                $_POST['id'],
                $_POST['id_producto'],
                $_POST['cantidad']
            );
        }


        // =====================================================
        // ELIMINAR
        // =====================================================

        elseif ($accion === 'eliminar') {

            $inventario->eliminar_inventario(
                $_POST['id']
            );
        }


    } catch (PDOException $e) {

        die(
            "Error al procesar el inventario: " .
            $e->getMessage()
        );
    }


    header("Location: ../vista/inventario.php");
    exit;
}

?>