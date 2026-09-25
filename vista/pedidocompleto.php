<?php

require_once __DIR__ . "/../modelo/pedidocompleto.php";
require_once __DIR__ . "/../modelo/producto.php";

$pedidoCompleto = new PedidoCompleto();
$productoModel = new Producto();

$listaPedidoCompleto = $pedidoCompleto->leer_pedidos();
$listaProductos = $productoModel->leer_productos();

$registroEditando = null;

if (isset($_GET['editar'])) {
    $registroEditando = $pedidoCompleto->buscar_pedido($_GET['editar']);
}

include __DIR__ . '/partials/header.php';



/*
|--------------------------------------------------------------------------
| BUSQUEDA, ORDEN Y PAGINACION
|--------------------------------------------------------------------------
*/

$columnasValidas = [
    'id_pedido',
    'id_producto',
    'id_cliente',
    'fecha_pedido',
    'fecha_estimada_entrega',
    'cantidad',
    'precio_unitario',
    'subtotal',
    'id_estado_pedido',
    'id_metodo_pago',
    'fecha_pago',
    'id_repostera_asignada'
];

$busqueda = trim($_GET['buscar'] ?? '');

$ordenCol = in_array(
    $_GET['orden'] ?? '',
    $columnasValidas
) ? $_GET['orden'] : 'id_pedido';

$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc'
    ? 'desc'
    : 'asc';

$porPagina = 10;

$paginaActual = max(
    1,
    (int)($_GET['pagina'] ?? 1)
);


/*
|--------------------------------------------------------------------------
| FILTRO DE BUSQUEDA
|--------------------------------------------------------------------------
*/

if ($busqueda !== '') {

    $listaPedidoCompleto = array_filter(
        $listaPedidoCompleto,
        function ($item) use ($busqueda) {

            foreach ($item as $valor) {

                if (
                    stripos(
                        (string)$valor,
                        $busqueda
                    ) !== false
                ) {
                    return true;
                }
            }

            return false;
        }
    );
}


/*
|--------------------------------------------------------------------------
| ORDEN
|--------------------------------------------------------------------------
*/

usort(
    $listaPedidoCompleto,
    function ($a, $b) use ($ordenCol, $ordenDir) {

        $cmp = strnatcasecmp(
            (string)($a[$ordenCol] ?? ''),
            (string)($b[$ordenCol] ?? '')
        );

        return $ordenDir === 'desc'
            ? -$cmp
            : $cmp;
    }
);


/*
|--------------------------------------------------------------------------
| PAGINACION
|--------------------------------------------------------------------------
*/

$totalRegistros = count($listaPedidoCompleto);

$totalPaginas = max(
    1,
    (int)ceil($totalRegistros / $porPagina)
);

$paginaActual = min(
    $paginaActual,
    $totalPaginas
);

$listaPagina = array_slice(
    array_values($listaPedidoCompleto),
    ($paginaActual - 1) * $porPagina,
    $porPagina
);


/*
|--------------------------------------------------------------------------
| FUNCION PARA ORDENAR COLUMNAS
|--------------------------------------------------------------------------
*/

function enlaceOrden_PedidoCompleto(
    $col,
    $ordenCol,
    $ordenDir
) {

    $dir = (
        $ordenCol === $col &&
        $ordenDir === 'asc'
    )
        ? 'desc'
        : 'asc';

    $params = array_merge(
        $_GET,
        [
            'orden' => $col,
            'dir' => $dir
        ]
    );

    return '?' . http_build_query($params);
}

?>

<h1 class="mb-4">Gestión de Pedidos</h1>


<!-- ========================================================= -->
<!-- FORMULARIO DE EDICIÓN                                     -->
<!-- ========================================================= -->

<?php if ($registroEditando): ?>

<form
    action="../controlador/PedidoCompletoController.php"
    method="POST"
    class="row g-3 mb-4"
    onsubmit="return validarFormulario_PedidoCompleto(this);"
    novalidate
>

    <input
        type="hidden"
        name="accion"
        value="actualizar"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
    >

    <input
        type="hidden"
        name="id"
        value="<?= htmlspecialchars($registroEditando['id_pedido']) ?>"
    >


    <!-- CLIENTE -->

    <div class="col-md-4">

        <label class="form-label">
            ID cliente
        </label>

        <input
            type="number"
            name="id_cliente"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['id_cliente']) ?>"
            required
        >

    </div>


    <!-- FECHA PEDIDO -->

    <div class="col-md-4">

        <label class="form-label">
            Fecha pedido
        </label>

        <input
            type="date"
            name="fecha_pedido"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['fecha_pedido']) ?>"
            required
        >

    </div>


    <!-- FECHA ENTREGA -->

    <div class="col-md-4">

        <label class="form-label">
            Fecha estimada entrega
        </label>

        <input
            type="date"
            name="fecha_estimada_entrega"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['fecha_estimada_entrega']) ?>"
            required
        >

    </div>


    <!-- PRODUCTO -->

    <div class="col-md-8">

        <label class="form-label">
            Producto
        </label>

        <select
            name="id_producto"
            id="productoEditar"
            class="form-select"
            onchange="actualizarProductoEditar()"
            required
        >

            <option value="">
                -- Seleccione un producto --
            </option>

            <?php foreach ($listaProductos as $producto): ?>

                <option
                    value="<?= htmlspecialchars($producto['id_producto']) ?>"
                    data-precio="<?= htmlspecialchars($producto['precio_base']) ?>"
                    <?= (
                        isset($registroEditando['id_producto']) &&
                        $registroEditando['id_producto'] == $producto['id_producto']
                    ) ? 'selected' : '' ?>
                >

                    <?= htmlspecialchars($producto['nombre_producto']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- CANTIDAD -->

    <div class="col-md-4">

        <label class="form-label">
            Cantidad
        </label>

        <input
            type="number"
            name="cantidad"
            id="cantidadEditar"
            class="form-control"
            min="1"
            value="<?= htmlspecialchars($registroEditando['cantidad']) ?>"
            oninput="calcularSubtotalEditar()"
            required
        >

    </div>


    <!-- PRECIO -->

    <div class="col-md-4">

        <label class="form-label">
            Precio unitario
        </label>

        <input
            type="number"
            step="0.01"
            name="precio_unitario"
            id="precioEditar"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['precio_unitario']) ?>"
            readonly
        >

    </div>


    <!-- SUBTOTAL -->

    <div class="col-md-4">

        <label class="form-label">
            Subtotal
        </label>

        <input
            type="number"
            step="0.01"
            name="subtotal"
            id="subtotalEditar"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['subtotal']) ?>"
            readonly
        >

    </div>


    <!-- ESTADO -->

    <div class="col-md-4">

        <label class="form-label">
            ID estado pedido
        </label>

        <input
            type="number"
            name="id_estado_pedido"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['id_estado_pedido']) ?>"
            required
        >

    </div>


    <!-- METODO PAGO -->

    <div class="col-md-4">

        <label class="form-label">
            ID método pago
        </label>

        <input
            type="number"
            name="id_metodo_pago"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['id_metodo_pago']) ?>"
            required
        >

    </div>


    <!-- FECHA PAGO -->

    <div class="col-md-4">

        <label class="form-label">
            Fecha pago
        </label>

        <input
            type="date"
            name="fecha_pago"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['fecha_pago']) ?>"
            required
        >

    </div>


    <!-- REPOSTERA -->

    <div class="col-md-4">

        <label class="form-label">
            ID repostera asignada
        </label>

        <input
            type="number"
            name="id_repostera_asignada"
            class="form-control"
            value="<?= htmlspecialchars($registroEditando['id_repostera_asignada']) ?>"
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
            href="pedidocompleto.php"
            class="btn btn-secondary"
        >
            Cancelar
        </a>

    </div>

</form>


<?php else: ?>


<!-- ========================================================= -->
<!-- FORMULARIO CREAR PEDIDO                                   -->
<!-- ========================================================= -->

<form
    action="../controlador/PedidoCompletoController.php"
    method="POST"
    class="row g-3 mb-4"
    onsubmit="return validarFormulario_PedidoCompleto(this);"
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
        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
    >


    <!-- CLIENTE -->

    <div class="col-md-4">

        <label class="form-label">
            ID cliente
        </label>

        <input
            type="number"
            name="id_cliente"
            class="form-control"
            min="1"
            required
        >

    </div>


    <!-- FECHA PEDIDO -->

    <div class="col-md-4">

        <label class="form-label">
            Fecha pedido
        </label>

        <input
            type="date"
            name="fecha_pedido"
            class="form-control"
            required
        >

    </div>


    <!-- FECHA ENTREGA -->

    <div class="col-md-4">

        <label class="form-label">
            Fecha estimada entrega
        </label>

        <input
            type="date"
            name="fecha_estimada_entrega"
            class="form-control"
            required
        >

    </div>


    <!-- PRODUCTO -->

    <div class="col-md-8">

        <label class="form-label">
            Producto
        </label>

        <select
            name="id_producto"
            id="productoCrear"
            class="form-select"
            onchange="actualizarProductoCrear()"
            required
        >

            <option value="">
                -- Seleccione un producto --
            </option>

            <?php foreach ($listaProductos as $producto): ?>

                <option
                    value="<?= htmlspecialchars($producto['id_producto']) ?>"
                    data-precio="<?= htmlspecialchars($producto['precio_base']) ?>"
                >

                    <?= htmlspecialchars($producto['nombre_producto']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- CANTIDAD -->

    <div class="col-md-4">

        <label class="form-label">
            Cantidad
        </label>

        <input
            type="number"
            name="cantidad"
            id="cantidadCrear"
            class="form-control"
            min="1"
            value="1"
            oninput="calcularSubtotalCrear()"
            required
        >

    </div>


    <!-- PRECIO -->

    <div class="col-md-4">

        <label class="form-label">
            Precio unitario
        </label>

        <input
            type="number"
            step="0.01"
            id="precioCrear"
            class="form-control"
            readonly
        >

    </div>


    <!-- SUBTOTAL -->

    <div class="col-md-4">

        <label class="form-label">
            Subtotal
        </label>

        <input
            type="number"
            step="0.01"
            id="subtotalCrear"
            class="form-control"
            readonly
        >

    </div>


    <!-- ESTADO -->

    <div class="col-md-4">

        <label class="form-label">
            ID estado pedido
        </label>

        <input
            type="number"
            name="id_estado_pedido"
            class="form-control"
            min="1"
            required
        >

    </div>


    <!-- METODO PAGO -->

    <div class="col-md-4">

        <label class="form-label">
            ID método pago
        </label>

        <input
            type="number"
            name="id_metodo_pago"
            class="form-control"
            min="1"
            required
        >

    </div>


    <!-- FECHA PAGO -->

    <div class="col-md-4">

        <label class="form-label">
            Fecha pago
        </label>

        <input
            type="date"
            name="fecha_pago"
            class="form-control"
            required
        >

    </div>


    <!-- REPOSTERA -->

    <div class="col-md-4">

        <label class="form-label">
            ID repostera asignada
        </label>

        <input
            type="number"
            name="id_repostera_asignada"
            class="form-control"
            min="1"
            required
        >

    </div>


    <div class="col-12">

        <button
            type="submit"
            class="btn btn-success"
        >
            Crear pedido
        </button>

    </div>

</form>

<?php endif; ?>


<!-- ========================================================= -->
<!-- BUSQUEDA                                                  -->
<!-- ========================================================= -->

<form method="GET" class="row g-2 mb-3">

    <div class="col-md-4">

        <input
            type="text"
            name="buscar"
            class="form-control"
            placeholder="Buscar..."
            value="<?= htmlspecialchars($busqueda) ?>"
        >

    </div>

    <input
        type="hidden"
        name="orden"
        value="<?= htmlspecialchars($ordenCol) ?>"
    >

    <input
        type="hidden"
        name="dir"
        value="<?= htmlspecialchars($ordenDir) ?>"
    >

    <div class="col-md-2">

        <button
            type="submit"
            class="btn btn-outline-primary"
        >
            Buscar
        </button>

        <a
            href="pedidocompleto.php"
            class="btn btn-outline-secondary"
        >
            Limpiar
        </a>

    </div>

</form>


<!-- ========================================================= -->
<!-- TABLA                                                     -->
<!-- ========================================================= -->

<div class="table-responsive">

<table class="table table-striped table-bordered align-middle">

    <thead class="table-dark">

        <tr>

            <th>
                <a
                    href="<?= enlaceOrden_PedidoCompleto('id_pedido', $ordenCol, $ordenDir) ?>"
                    class="text-white text-decoration-none"
                >
                    ID Pedido
                </a>
            </th>

            <th>Producto</th>

            <th>Cliente</th>

            <th>Fecha pedido</th>

            <th>Fecha entrega</th>

            <th>Cantidad</th>

            <th>Precio unitario</th>

            <th>Subtotal</th>

            <th>Estado</th>

            <th>Método pago</th>

            <th>Fecha pago</th>

            <th>Repostera</th>

            <th>Acciones</th>

        </tr>

    </thead>


    <tbody>

    <?php foreach ($listaPagina as $item): ?>

        <tr>

            <!-- ID PEDIDO -->

            <td>
                <?= htmlspecialchars($item['id_pedido']) ?>
            </td>


            <!-- PRODUCTO -->

            <td>

                <?php if (!empty($item['nombre_producto'])): ?>

                    <?= htmlspecialchars($item['nombre_producto']) ?>

                <?php else: ?>

                    <span class="text-muted">
                        Producto histórico no vinculado
                    </span>

                <?php endif; ?>

            </td>


            <!-- CLIENTE -->

            <td>
                <?= htmlspecialchars($item['id_cliente']) ?>
            </td>


            <!-- FECHA PEDIDO -->

            <td>
                <?= htmlspecialchars($item['fecha_pedido']) ?>
            </td>


            <!-- FECHA ENTREGA -->

            <td>
                <?= htmlspecialchars($item['fecha_estimada_entrega']) ?>
            </td>


            <!-- CANTIDAD -->

            <td>
                <?= htmlspecialchars($item['cantidad']) ?>
            </td>


            <!-- PRECIO -->

            <td>

                $<?= number_format(
                    (float)$item['precio_unitario'],
                    2,
                    ',',
                    '.'
                ) ?>

            </td>


            <!-- SUBTOTAL -->

            <td>

                $<?= number_format(
                    (float)$item['subtotal'],
                    2,
                    ',',
                    '.'
                ) ?>

            </td>


            <!-- ESTADO -->

            <td>
                <?= htmlspecialchars($item['id_estado_pedido']) ?>
            </td>


            <!-- METODO PAGO -->

            <td>
                <?= htmlspecialchars($item['id_metodo_pago']) ?>
            </td>


            <!-- FECHA PAGO -->

            <td>
                <?= htmlspecialchars($item['fecha_pago']) ?>
            </td>


            <!-- REPOSTERA -->

            <td>
                <?= htmlspecialchars($item['id_repostera_asignada']) ?>
            </td>


            <!-- ACCIONES -->

            <td style="white-space: nowrap;">

                <a
                    href="pedidocompleto.php?editar=<?= $item['id_pedido'] ?>"
                    class="btn btn-sm btn-warning"
                >
                    <i class="fa-solid fa-pen"></i>
                    Editar
                </a>


                <form
                    action="../controlador/PedidoCompletoController.php"
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
                        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                    >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= $item['id_pedido'] ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-sm btn-danger"
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
                colspan="13"
                class="text-center text-muted"
            >
                Sin resultados
            </td>

        </tr>

    <?php endif; ?>

    </tbody>

</table>

</div>


<!-- ========================================================= -->
<!-- PAGINACION                                                -->
<!-- ========================================================= -->

<?php if ($totalPaginas > 1): ?>

<nav>

    <ul class="pagination">

        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>

            <li
                class="page-item <?= $p === $paginaActual ? 'active' : '' ?>"
            >

                <a
                    class="page-link"
                    href="?<?= http_build_query(
                        array_merge(
                            $_GET,
                            ['pagina' => $p]
                        )
                    ) ?>"
                >
                    <?= $p ?>
                </a>

            </li>

        <?php endfor; ?>

    </ul>

</nav>

<?php endif; ?>


<!-- ========================================================= -->
<!-- JAVASCRIPT                                                -->
<!-- ========================================================= -->

<script>

function actualizarProductoCrear()
{
    const select = document.getElementById('productoCrear');

    const opcion =
        select.options[select.selectedIndex];

    const precio =
        parseFloat(
            opcion.dataset.precio || 0
        );

    document.getElementById('precioCrear').value =
        precio.toFixed(2);

    calcularSubtotalCrear();
}


function calcularSubtotalCrear()
{
    const precio =
        parseFloat(
            document.getElementById('precioCrear').value || 0
        );

    const cantidad =
        parseFloat(
            document.getElementById('cantidadCrear').value || 0
        );

    const subtotal =
        precio * cantidad;

    document.getElementById('subtotalCrear').value =
        subtotal.toFixed(2);
}


function actualizarProductoEditar()
{
    const select =
        document.getElementById('productoEditar');

    const opcion =
        select.options[select.selectedIndex];

    const precio =
        parseFloat(
            opcion.dataset.precio || 0
        );

    document.getElementById('precioEditar').value =
        precio.toFixed(2);

    calcularSubtotalEditar();
}


function calcularSubtotalEditar()
{
    const precio =
        parseFloat(
            document.getElementById('precioEditar').value || 0
        );

    const cantidad =
        parseFloat(
            document.getElementById('cantidadEditar').value || 0
        );

    const subtotal =
        precio * cantidad;

    document.getElementById('subtotalEditar').value =
        subtotal.toFixed(2);
}


function validarFormulario_PedidoCompleto(form)
{
    let valido = true;

    form.querySelectorAll('[required]').forEach(
        function(campo)
        {
            if (!campo.value.trim())
            {
                campo.classList.add('is-invalid');
                valido = false;
            }
            else
            {
                campo.classList.remove('is-invalid');
            }
        }
    );

    if (!valido)
    {
        alert(
            'Por favor completa todos los campos obligatorios.'
        );
    }

    return valido;
}


/*
|--------------------------------------------------------------------------
| Inicializar formulario de edición
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function()
    {
        const productoEditar =
            document.getElementById('productoEditar');

        if (productoEditar)
        {
            /*
            No recalculamos automáticamente el precio
            al abrir un pedido histórico.

            El precio histórico debe conservarse.
            */
        }
    }
);

</script>


<?php

include __DIR__ . '/partials/footer.php';

?>