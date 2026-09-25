<?php
require_once __DIR__ . '/sesion.php';

// Se borra todo (cliente, carrito y datos de entrega) y se elimina la cookie de sesión
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

header('Location: /Dulce_Micro/tienda/catalogo.php');
exit;
