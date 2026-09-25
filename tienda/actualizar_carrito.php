<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('TIENDA');   // misma sesión que usa partials/header.php
    session_start();
}
require_once __DIR__ . '/config_tienda.php';

// Solo POST y con token CSRF válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

/* 1. Guardar lo que el cliente eligió (así no se pierde al sumar o restar).
      Solo se aceptan fechas y franjas que la tienda realmente ofrece. */
$fechasValidas = array_column(diasDisponibles(), 'valor');
$fecha  = $_POST['fecha']  ?? '';
$franja = $_POST['franja'] ?? '';

$_SESSION['entrega'] = [
    'fecha'       => in_array($fecha, $fechasValidas, true) ? $fecha : '',
    'franja'      => isset(FRANJAS[$franja]) ? $franja : array_key_first(FRANJAS),
    'dedicatoria' => mb_substr(trim($_POST['dedicatoria'] ?? ''), 0, 300),
];

/* 2. Ejecutar la acción */
$accion  = $_POST['accion'] ?? 'guardar';
$carrito = $_SESSION['carrito'] ?? [];

// sumar:12 | restar:12 | quitar:12
if (preg_match('/^(sumar|restar|quitar):(\d+)$/', $accion, $m)) {
    $id = (int)$m[2];

    if (isset($carrito[$id])) {
        if ($m[1] === 'sumar') {
            $carrito[$id] = min($carrito[$id] + 1, MAX_POR_PRODUCTO);
        } elseif ($m[1] === 'restar') {
            $carrito[$id]--;
        } else {
            $carrito[$id] = 0;
        }
        if ($carrito[$id] <= 0) {
            unset($carrito[$id]);
        }
    }
    $_SESSION['carrito'] = $carrito;
    header('Location: carrito.php');
    exit;
}

if ($accion === 'vaciar') {
    $_SESSION['carrito'] = [];
    header('Location: carrito.php');
    exit;
}

if ($accion === 'continuar') {
    if (!$carrito) {
        header('Location: carrito.php?error=vacio');
        exit;
    }
    if ($_SESSION['entrega']['fecha'] === '') {
        header('Location: carrito.php?error=fecha');
        exit;
    }
    // Para pagar hay que tener cuenta: si no ha ingresado, primero al login
    if (empty($_SESSION['id_cliente'])) {
        header('Location: login.php?volver=checkout');
        exit;
    }
    header('Location: checkout.php');
    exit;
}

header('Location: carrito.php');
exit;
