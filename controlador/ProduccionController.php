<?php
// controlador/ProduccionController.php

session_name("LOGIN");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../vista/login.php");
    exit();
}

require_once __DIR__ . "/../modelo/produccion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificacion CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
    }

    $idProducto = $_POST['id_producto'] ?? '';
    $cantidadProducida = max(1, (int) ($_POST['cantidad_producida'] ?? 0));

    if ($idProducto === '' || $cantidadProducida < 1) {
        $_SESSION['resultado_produccion'] = [
            'ok' => false,
            'mensaje' => 'Selecciona un producto y una cantidad valida.'
        ];
        header("Location: ../vista/produccion.php");
        exit();
    }

    $produccion = new Produccion();
    $resultado = $produccion->producir($idProducto, $cantidadProducida);

    $_SESSION['resultado_produccion'] = $resultado;

    header("Location: ../vista/produccion.php");
    exit();
}

header("Location: ../vista/produccion.php");
exit();
