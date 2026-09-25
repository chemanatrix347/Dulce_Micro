<?php

if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}



require_once __DIR__ . "/../modelo/pedidocompleto.php";

$pedidoCompleto = new PedidoCompleto();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar CSRF
    if (!hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token'] ?? ''
    )) {
        die("Token de seguridad inválido. Recarga la página e intenta de nuevo.");
    }

    $accion = $_POST['accion'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */
    if ($accion === 'crear') {

        $pedidoCompleto->crear_pedido(
            $_POST['id_cliente'],
            $_POST['fecha_pedido'],
            $_POST['fecha_estimada_entrega'],
            $_POST['id_producto'],
            $_POST['cantidad'],
            $_POST['id_estado_pedido'],
            $_POST['id_metodo_pago'],
            $_POST['fecha_pago'],
            $_POST['id_repostera_asignada']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */
    elseif ($accion === 'actualizar') {

        $pedidoCompleto->actualizar_pedido(
            $_POST['id'],
            $_POST['id_cliente'],
            $_POST['fecha_pedido'],
            $_POST['fecha_estimada_entrega'],
            $_POST['id_producto'],
            $_POST['cantidad'],
            $_POST['id_estado_pedido'],
            $_POST['id_metodo_pago'],
            $_POST['fecha_pago'],
            $_POST['id_repostera_asignada']
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */
    elseif ($accion === 'eliminar') {

        $pedidoCompleto->eliminar_pedido(
            $_POST['id']
        );
    }


    // Regresar a la vista
    header("Location: ../vista/pedidocompleto.php");
    exit;
}

?>