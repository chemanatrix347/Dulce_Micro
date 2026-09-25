<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('TIENDA');   // misma sesión que usa partials/header.php
    session_start();
}
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelo/inventario.php'; // NUEVO: para validar stock disponible

// Solo POST y con token CSRF válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    exit('Solicitud no válida.');
}

$idProducto = (int)($_POST['id_producto'] ?? 0);

// Se verifica en la base de datos que el producto exista y esté activo.
// El carrito guarda solo id => cantidad: el precio NUNCA viene del navegador,
// se vuelve a leer de la tabla productos cuando se muestra el carrito o se cobra.
$conn = (new conexion())->conn;
$stmt = $conn->prepare('SELECT id_producto FROM productos WHERE id_producto = ? AND estado = 1');
$stmt->execute([$idProducto]);

if (!$stmt->fetchColumn()) {
    header('Location: catalogo.php?error=1');
    exit;
}

// ---------------------------------------------------------------
// NUEVO: validar que haya stock disponible antes de sumar al carrito.
// Se compara contra lo que YA tiene el cliente en el carrito, para
// que no pueda acumular más unidades de las que existen en inventario.
// ---------------------------------------------------------------
$inventario = new Inventario();
$filaInventario  = $inventario->obtener_por_producto($idProducto);
$stockDisponible = $filaInventario ? (int)$filaInventario['cantidad'] : 0;
$enCarrito       = (int)($_SESSION['carrito'][$idProducto] ?? 0);

if ($stockDisponible <= 0 || ($enCarrito + 1) > $stockDisponible) {
    header('Location: catalogo.php?agotado=1');
    exit;
}
// ---------------------------------------------------------------

$_SESSION['carrito'][$idProducto] = $enCarrito + 1;
header('Location: catalogo.php?agregado=1');
exit;
