<?php

require_once __DIR__ . "/../modelo/inventario.php";
require_once __DIR__ . "/../modelo/producto.php";

$inventario = new Inventario();
$producto = new Producto();

$listaInventario = $inventario->leer_inventarios();
$listaProductos = $producto->leer_productos();

$registroEditando = null;

if (isset($_GET['editar'])) {
    $registroEditando = $inventario->buscar_inventario($_GET['editar']);
}

include __DIR__ . '/partials/header.php';


// -----------------------------
// Búsqueda y paginación
// -----------------------------

$busqueda = trim($_GET['buscar'] ?? '');

if ($busqueda !== '') {
    $listaInventario = array_filter($listaInventario, function ($item) use ($busqueda) {

        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) {
                return true;
            }
        }

        return false;
    });
}

$porPagina = 10;

$paginaActual = max(
    1,
    (int) ($_GET['pagina'] ?? 1)
);

$totalRegistros = count($listaInventario);

$totalPaginas = max(
    1,
    (int) ceil($totalRegistros / $porPagina)
);

$paginaActual = min(
    $paginaActual,
    $totalPaginas
);

$listaPagina = array_slice(
    array_values($listaInventario),
    ($paginaActual - 1) * $porPagina,
    $porPagina
);

?>

<h1 class="mb-4">Gestión de Inventario</h1>

<!-- ========================================= -->
<!-- FORMULARIO -->
<!-- ========================================= -->

<?php if ($registroEditando): ?>

<form
    action="../controlador/InventarioController.php"
    method="POST"
    class="row g-3 mb-4"
>

    <input
        type="hidden"
        name="accion"
        value="actualizar"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
    >

    <input
        type="hidden"
        name="id"
        value="<?= htmlspecialchars($registroEditando['id_inventario']) ?>"
    >

    <div class="col-md-8">

        <label class="form-label">
            Producto
        </label>

        <select
            name="id_producto"
            class="form-select"
            required
        >

            <option value="">
                Seleccione un producto
            </option>

            <?php foreach ($listaProductos as $prod): ?>

                <option
                    value="<?= $prod['id_producto'] ?>"
                    <?= ($registroEditando['id_producto'] == $prod['id_producto']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($prod['nombre_producto']) ?>
                    - $<?= number_format($prod['precio_base'], 0, ',', '.') ?>
                </option>

            <?php endforeach; ?>

        </select>

    </div>

    <div class="col-md-4">

        <label class="form-label">
            Cantidad
        </label>

        <input
            type="number"
            name="cantidad"
            class="form-control"
            min="0"
            step="1"
            value="<?= htmlspecialchars($registroEditando['cantidad']) ?>"
            required
        >

    </div>

    <div class="col-12">

        <button
            type="submit"
            class="btn btn-primary"
        >
            Guardar cambios
        </button>

        <a
            href="inventario.php"
            class="btn btn-secondary"
        >
            Cancelar
        </a>

    </div>

</form>

<?php else: ?>

<form
    action="../controlador/InventarioController.php"
    method="POST"
    class="row g-3 mb-4"
    onsubmit="return validarFormulario_Inventario(this);"
    novalidate
>

    <input
        type="hidden"
        name="accion"
        value="crear"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
    >

    <div class="col-md-8">

        <label class="form-label">
            Producto
        </label>

        <select
            name="id_producto"
            class="form-select"
            required
        >

            <option value="">
                Seleccione un producto
            </option>

            <?php foreach ($listaProductos as $prod): ?>

                <option value="<?= $prod['id_producto'] ?>">
                    <?= htmlspecialchars($prod['nombre_producto']) ?>
                    - $<?= number_format($prod['precio_base'], 0, ',', '.') ?>
                </option>

            <?php endforeach; ?>

        </select>

    </div>

    <div class="col-md-4">

        <label class="form-label">
            Cantidad
        </label>

        <input
            type="number"
            name="cantidad"
            class="form-control"
            min="0"
            step="1"
            value="0"
            required
        >

    </div>

    <div class="col-12">

        <button
            type="submit"
            class="btn btn-success"
        >
            Crear inventario
        </button>

    </div>

</form>

<?php endif; ?>


<!-- ========================================= -->
<!-- BÚSQUEDA -->
<!-- ========================================= -->

<form method="GET" class="row g-2 mb-3">

    <div class="col-md-4">

        <input
            type="text"
            name="buscar"
            class="form-control"
            placeholder="Buscar producto..."
            value="<?= htmlspecialchars($busqueda) ?>"
        >

    </div>

    <div class="col-md-2">

        <button
            type="submit"
            class="btn btn-outline-primary"
        >
            Buscar
        </button>

        <a
            href="inventario.php"
            class="btn btn-outline-secondary"
        >
            Limpiar
        </a>

    </div>

</form>


<!-- ========================================= -->
<!-- TABLA -->
<!-- ========================================= -->

<div class="table-responsive">

<table class="table table-striped table-bordered align-middle">

    <thead class="table-dark">

        <tr>

            <th>ID</th>

            <th>Producto</th>

            <th>Categoría</th>

            <th>Sabor</th>

            <th>Tamaño</th>

            <th>Precio</th>

            <th>Cantidad</th>

            <th>Acciones</th>

        </tr>

    </thead>

    <tbody>

    <?php foreach ($listaPagina as $item): ?>

        <tr>

            <td>
                <?= htmlspecialchars($item['id_inventario']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['nombre_producto']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['categoria_postre']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['sabor']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['tamano']) ?>
            </td>

            <td>
                $<?= number_format($item['precio_unitario_base'], 0, ',', '.') ?>
            </td>

            <td>
                <?= htmlspecialchars($item['cantidad']) ?>
            </td>

            <td>

                <a
                    href="inventario.php?editar=<?= $item['id_inventario'] ?>"
                    class="btn btn-sm btn-warning"
                >
                    <i class="fa-solid fa-pen"></i>
                    Editar
                </a>

                <form
                    action="../controlador/InventarioController.php"
                    method="POST"
                    style="display:inline"
                >

                    <input
                        type="hidden"
                        name="accion"
                        value="eliminar"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                    >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $item['id_inventario'] ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('¿Deseas eliminar este registro del inventario?');"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Eliminar
                    </button>

                </form>

            </td>

        </tr>

    <?php endforeach; ?>


    <?php if (empty($listaPagina)): ?>

        <tr>

            <td
                colspan="8"
                class="text-center text-muted"
            >
                No hay registros de inventario.
            </td>

        </tr>

    <?php endif; ?>

    </tbody>

</table>

</div>


<!-- ========================================= -->
<!-- PAGINACIÓN -->
<!-- ========================================= -->

<?php if ($totalPaginas > 1): ?>

<nav>

    <ul class="pagination">

        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>

            <li
                class="page-item <?= $p === $paginaActual ? 'active' : '' ?>"
            >

                <a
                    class="page-link"
                    href="?<?= http_build_query([
                        'buscar' => $busqueda,
                        'pagina' => $p
                    ]) ?>"
                >
                    <?= $p ?>
                </a>

            </li>

        <?php endfor; ?>

    </ul>

</nav>

<?php endif; ?>


<script>

function validarFormulario_Inventario(form) {

    const producto = form.querySelector('[name="id_producto"]');
    const cantidad = form.querySelector('[name="cantidad"]');

    if (!producto.value) {

        alert('Seleccione un producto.');

        producto.classList.add('is-invalid');

        return false;
    }

    if (cantidad.value === '' || Number(cantidad.value) < 0) {

        alert('Ingrese una cantidad válida.');

        cantidad.classList.add('is-invalid');

        return false;
    }

    return true;
}

</script>


<?php include __DIR__ . '/partials/footer.php'; ?>