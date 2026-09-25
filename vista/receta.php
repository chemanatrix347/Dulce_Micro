<?php

require_once __DIR__ . "/../modelo/receta.php";
require_once __DIR__ . "/../modelo/producto.php";
require_once __DIR__ . "/../modelo/materiaprima.php";

$recetaModel = new Receta();
$productoModel = new Producto();
$materiaPrimaModel = new MateriaPrima();

$listaRecetas = $recetaModel->leer_recetas();
$listaProductos = $productoModel->leer_productos();
$listaMateriasPrimas = $materiaPrimaModel->leer_materias_primas();

$registroEditando = null;

if (isset($_GET['editar'])) {
    $registroEditando = $recetaModel->buscar_receta($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';


// ======================================================
// BUSQUEDA, ORDEN Y PAGINACION
// ======================================================

$columnasValidas = [
    'id_receta',
    'nombre_producto',
    'nombre_insumo',
    'cantidad'
];

$busqueda = trim($_GET['buscar'] ?? '');

$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas)
    ? $_GET['orden']
    : 'id_receta';

$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc'
    ? 'desc'
    : 'asc';

$porPagina = 10;

$paginaActual = max(
    1,
    (int) ($_GET['pagina'] ?? 1)
);


// ======================================================
// FILTRO DE BUSQUEDA
// ======================================================

if ($busqueda !== '') {

    $listaRecetas = array_filter(
        $listaRecetas,
        function ($item) use ($busqueda) {

            foreach ($item as $valor) {

                if (
                    stripos(
                        (string) $valor,
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


// ======================================================
// ORDENAMIENTO
// ======================================================

usort(
    $listaRecetas,
    function ($a, $b) use ($ordenCol, $ordenDir) {

        $cmp = strnatcasecmp(
            (string) ($a[$ordenCol] ?? ''),
            (string) ($b[$ordenCol] ?? '')
        );

        return $ordenDir === 'desc'
            ? -$cmp
            : $cmp;
    }
);


// ======================================================
// PAGINACION
// ======================================================

$totalRegistros = count($listaRecetas);

$totalPaginas = max(
    1,
    (int) ceil($totalRegistros / $porPagina)
);

$paginaActual = min(
    $paginaActual,
    $totalPaginas
);

$listaPagina = array_slice(
    array_values($listaRecetas),
    ($paginaActual - 1) * $porPagina,
    $porPagina
);


// ======================================================
// FUNCION PARA ORDENAR COLUMNAS
// ======================================================

function enlaceOrden_Receta($col, $ordenCol, $ordenDir)
{
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

<div class="container-fluid">

    <h1 class="mb-4">Gestión de Recetas</h1>


    <!-- ================================================= -->
    <!-- FORMULARIO -->
    <!-- ================================================= -->

    <?php if ($registroEditando): ?>

        <form
            action="../controlador/RecetaController.php"
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
                value="<?= htmlspecialchars($registroEditando['id_receta']) ?>"
            >


            <!-- PRODUCTO -->

            <div class="col-md-4">

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

                    <?php foreach ($listaProductos as $producto): ?>

                        <option
                            value="<?= htmlspecialchars($producto['id_producto']) ?>"
                            <?= (
                                $registroEditando['id_producto']
                                == $producto['id_producto']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $producto['nombre_producto']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- MATERIA PRIMA -->

            <div class="col-md-4">

                <label class="form-label">
                    Materia prima
                </label>

                <select
                    name="id_materia_prima"
                    class="form-select"
                    required
                >

                    <option value="">
                        Seleccione una materia prima
                    </option>

                    <?php foreach ($listaMateriasPrimas as $materia): ?>

                        <option
                            value="<?= htmlspecialchars($materia['id_materia_prima']) ?>"
                            <?= (
                                $registroEditando['id_materia_prima']
                                == $materia['id_materia_prima']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $materia['nombre_insumo']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- CANTIDAD -->

            <div class="col-md-4">

                <label class="form-label">
                    Cantidad necesaria
                </label>

                <input
                    type="number"
                    name="cantidad"
                    class="form-control"
                    min="0.01"
                    step="0.01"
                    value="<?= htmlspecialchars(
                        $registroEditando['cantidad']
                    ) ?>"
                    required
                >

            </div>


            <!-- BOTONES -->

            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Guardar cambios
                </button>

                <a
                    href="receta.php"
                    class="btn btn-secondary"
                >
                    Cancelar
                </a>

            </div>

        </form>


    <?php else: ?>


        <form
            action="../controlador/RecetaController.php"
            method="POST"
            class="row g-3 mb-4"
            onsubmit="return validarFormulario_Receta(this);"
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


            <!-- PRODUCTO -->

            <div class="col-md-4">

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

                    <?php foreach ($listaProductos as $producto): ?>

                        <option
                            value="<?= htmlspecialchars($producto['id_producto']) ?>"
                        >

                            <?= htmlspecialchars(
                                $producto['nombre_producto']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- MATERIA PRIMA -->

            <div class="col-md-4">

                <label class="form-label">
                    Materia prima
                </label>

                <select
                    name="id_materia_prima"
                    class="form-select"
                    required
                >

                    <option value="">
                        Seleccione una materia prima
                    </option>

                    <?php foreach ($listaMateriasPrimas as $materia): ?>

                        <option
                            value="<?= htmlspecialchars($materia['id_materia_prima']) ?>"
                        >

                            <?= htmlspecialchars(
                                $materia['nombre_insumo']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- CANTIDAD -->

            <div class="col-md-4">

                <label class="form-label">
                    Cantidad necesaria
                </label>

                <input
                    type="number"
                    name="cantidad"
                    class="form-control"
                    min="0.01"
                    step="0.01"
                    placeholder="Ej. 500"
                    required
                >

            </div>


            <!-- BOTON -->

            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Agregar receta
                </button>

            </div>

        </form>

    <?php endif; ?>


    <hr>


    <!-- ================================================= -->
    <!-- BUSCADOR -->
    <!-- ================================================= -->

    <form
        method="GET"
        class="row g-3 mb-4"
    >

        <div class="col-md-6">

            <label class="form-label">
                Buscar receta
            </label>

            <input
                type="text"
                name="buscar"
                class="form-control"
                placeholder="Producto, materia prima..."
                value="<?= htmlspecialchars($busqueda) ?>"
            >

        </div>

        <div class="col-md-2 d-flex align-items-end">

            <button
                type="submit"
                class="btn btn-primary"
            >
                Buscar
            </button>

        </div>

        <?php if ($busqueda !== ''): ?>

            <div class="col-md-2 d-flex align-items-end">

                <a
                    href="receta.php"
                    class="btn btn-secondary"
                >
                    Limpiar
                </a>

            </div>

        <?php endif; ?>

    </form>


    <!-- ================================================= -->
    <!-- TABLA -->
    <!-- ================================================= -->

    <div class="table-responsive">

        <table class="table table-bordered table-striped table-hover">

            <thead class="table-dark">

                <tr>

                    <th>
                        <a
                            href="<?= enlaceOrden_Receta(
                                'id_receta',
                                $ordenCol,
                                $ordenDir
                            ) ?>"
                            class="text-white text-decoration-none"
                        >
                            ID
                        </a>
                    </th>

                    <th>
                        <a
                            href="<?= enlaceOrden_Receta(
                                'nombre_producto',
                                $ordenCol,
                                $ordenDir
                            ) ?>"
                            class="text-white text-decoration-none"
                        >
                            Producto
                        </a>
                    </th>

                    <th>
                        <a
                            href="<?= enlaceOrden_Receta(
                                'nombre_insumo',
                                $ordenCol,
                                $ordenDir
                            ) ?>"
                            class="text-white text-decoration-none"
                        >
                            Materia prima
                        </a>
                    </th>

                    <th>
                        <a
                            href="<?= enlaceOrden_Receta(
                                'cantidad',
                                $ordenCol,
                                $ordenDir
                            ) ?>"
                            class="text-white text-decoration-none"
                        >
                            Cantidad
                        </a>
                    </th>

                    <th>
                        Acciones
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (empty($listaPagina)): ?>

                    <tr>

                        <td
                            colspan="5"
                            class="text-center"
                        >
                            No hay recetas registradas.
                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($listaPagina as $r): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars(
                                    $r['id_receta']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $r['nombre_producto']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $r['nombre_insumo']
                                ) ?>
                            </td>

                            <td>

                                <?= htmlspecialchars(
                                    $r['cantidad']
                                ) ?>

                                <?php if (!empty($r['unidad_medida'])): ?>

                                    <?= htmlspecialchars(
                                        $r['unidad_medida']
                                    ) ?>

                                <?php endif; ?>

                            </td>

                            <td>

                                <a
                                    href="receta.php?editar=<?= urlencode(
                                        $r['id_receta']
                                    ) ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Editar
                                </a>


                                <form
                                    action="../controlador/RecetaController.php"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('¿Está seguro de eliminar esta receta?');"
                                >

                                    <input
                                        type="hidden"
                                        name="accion"
                                        value="eliminar"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars(
                                            $_SESSION['csrf_token'] ?? ''
                                        ) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= htmlspecialchars(
                                            $r['id_receta']
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm"
                                    >
                                        Eliminar
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- ================================================= -->
    <!-- PAGINACION -->
    <!-- ================================================= -->

    <?php if ($totalPaginas > 1): ?>

        <nav aria-label="Paginación">

            <ul class="pagination">

                <?php for (
                    $p = 1;
                    $p <= $totalPaginas;
                    $p++
                ): ?>

                    <li
                        class="page-item <?= $p == $paginaActual
                            ? 'active'
                            : '' ?>"
                    >

                        <a
                            class="page-link"
                            href="?<?= http_build_query(
                                array_merge(
                                    $_GET,
                                    [
                                        'pagina' => $p
                                    ]
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

</div>


<script>

function validarFormulario_Receta(form) {

    let valido = true;

    form.querySelectorAll('[required]').forEach(function(campo) {

        if (!campo.value.trim()) {

            campo.classList.add('is-invalid');

            valido = false;

        } else {

            campo.classList.remove('is-invalid');

        }

    });


    if (!valido) {

        alert(
            'Por favor completa todos los campos obligatorios.'
        );

    }


    return valido;
}

</script>


<?php include __DIR__ . '/partials/footer.php'; ?>