<?php
// vista/produccion.php

session_name("LOGIN");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Restringir a Administrador y Gerente General, igual que Materia prima y Receta.
$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';

require_once __DIR__ . "/../modelo/producto.php";

$productoModelo = new Producto();
$productos = $productoModelo->leer_productos();

$resultado = $_SESSION['resultado_produccion'] ?? null;
unset($_SESSION['resultado_produccion']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Producción</h1>
    <p class="mb-4">
        Registra los productos que se acaban de preparar. El sistema descuenta
        automáticamente la materia prima según la receta de cada producto y
        suma las unidades producidas al inventario.
    </p>

    <?php if ($resultado): ?>
        <div class="alert <?= $resultado['ok'] ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($resultado['mensaje']) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Producir un lote</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="../controlador/ProduccionController.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group mb-3">
                    <label for="id_producto">Producto</label>
                    <select name="id_producto" id="id_producto" class="form-control" required>
                        <option value="">-- Selecciona un producto --</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= (int) $p['id_producto'] ?>">
                                <?= htmlspecialchars($p['nombre_producto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        La lista se toma directo de la tabla de productos, así que
                        cualquier producto nuevo que registres (con su receta)
                        aparece aquí automáticamente.
                    </small>
                </div>

                <div class="form-group mb-3">
                    <label for="cantidad_producida">Cantidad producida</label>
                    <input type="number" name="cantidad_producida" id="cantidad_producida"
                           class="form-control" min="1" step="1" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-kitchen-set"></i> Producir
                </button>
            </form>
        </div>
    </div>

</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
