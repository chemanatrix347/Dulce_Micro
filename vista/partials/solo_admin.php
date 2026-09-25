<?php
// Debe incluirse DESPUES de session_start() (header.php ya lo hace en las vistas;
// los controladores necesitan su propio session_start() antes de este archivo).
if (($_SESSION['nombre_rol'] ?? '') !== 'Administrador') {
    http_response_code(403);
    die('<div style="max-width:500px;margin:80px auto;padding:30px;text-align:center;font-family:sans-serif;border:1px solid #f5c2c7;background:#f8d7da;border-radius:8px;">
        <h3>Acceso Denegado</h3>
        <p>Tu rol no tiene permiso para ver esta seccion.</p>
        <a href="/Dulce_Micro/index.php">Volver al inicio</a>
    </div>');
}
?>
