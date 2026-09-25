<?php
// vista/partials/restringir_roles.php
//
// Uso: antes de incluir este archivo, define el arreglo $rolesPermitidos
// con los nombres de rol que SI pueden entrar a ese modulo, por ejemplo:
//
//   $rolesPermitidos = ['Administrador', 'Gerente General'];
//   require_once __DIR__ . '/partials/restringir_roles.php';
//
// Requiere que la sesion ya este iniciada (header.php o el controlador
// ya la inician antes de llegar aqui) y que el login haya guardado
// $_SESSION['nombre_rol'].

if (!isset($rolesPermitidos) || !is_array($rolesPermitidos)) {
    $rolesPermitidos = [];
}

if (!isset($_SESSION['nombre_rol']) || !in_array($_SESSION['nombre_rol'], $rolesPermitidos, true)) {
    http_response_code(403);
    die('<div style="max-width:600px; margin:3rem auto; padding:2rem; text-align:center; font-family: sans-serif; background:#f8d7da; border:1px solid #f5c2c7; border-radius:.5rem;">
        <h2 style="margin-top:0; color:#842029;">Acceso Denegado</h2>
        <p style="color:#842029;">Tu rol no tiene permiso para ver esta seccion.</p>
        <a href="/Dulce_Micro/index.php" style="color:#842029; text-decoration:underline;">Volver al inicio</a>
    </div>');
}