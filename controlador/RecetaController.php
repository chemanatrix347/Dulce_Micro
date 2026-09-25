<?php

if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}

require_once __DIR__ . "/../modelo/receta.php";

$receta = new Receta();
$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/../vista/partials/restringir_roles.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar CSRF
    if (!hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token'] ?? ''
    )) {
        die("Token de seguridad inválido. Recarga la página e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    try {

        if ($accion === 'crear') {

            $receta->crear_receta(
                $_POST['id_producto'],
                $_POST['id_materia_prima'],
                $_POST['cantidad']
            );

        } elseif ($accion === 'actualizar') {

            $receta->actualizar_receta(
                $_POST['id'],
                $_POST['id_producto'],
                $_POST['id_materia_prima'],
                $_POST['cantidad']
            );

        } elseif ($accion === 'eliminar') {

            $receta->eliminar_receta($_POST['id']);
        }

    } catch (PDOException $e) {

        die("Error al procesar la receta: " . $e->getMessage());
    }

    header("Location: ../vista/receta.php");
    exit;
}

?>