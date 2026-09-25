<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}

require_once __DIR__ . "/../modelo/producto.php";
require_once __DIR__ . "/../modelo/categoriapostre.php";
require_once __DIR__ . "/../modelo/sabor.php";
require_once __DIR__ . "/../modelo/tamano.php";
require_once __DIR__ . "/../config/conexion.php";

$producto = new Producto();
$categoriaPostre = new CategoriaPostre();
$sabor = new Sabor();
$tamano = new Tamano();

$listaProductos = $producto->leer_productos();
$listaCategorias = $categoriaPostre->leer_categorias_postre();
$listaSabores = $sabor->leer_sabores();
$listaTamanos = $tamano->leer_tamanos();


/*
 * Catálogo de tipos de torta
 */
$conexion = new conexion();
$db = $conexion->conn;

$stmtTortas = $db->query("
    SELECT
        id_tortas,
        tipo_torta
    FROM tortas
    WHERE estado = 1
    ORDER BY tipo_torta ASC
");

$listaTortas = $stmtTortas->fetchAll(PDO::FETCH_ASSOC);


$registroEditando = null;

if (isset($_GET['editar'])) {
    $registroEditando = $producto->buscar_producto($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

?>

<h1 class="mb-4">Gestión de Productos</h1>


<?php if ($registroEditando): ?>

<form action="../controlador/ProductoController.php"
      method="POST"
      class="row g-3 mb-4">

    <input type="hidden"
           name="accion"
           value="actualizar">

    <input type="hidden"
           name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <input type="hidden"
           name="id"
           value="<?= htmlspecialchars($registroEditando['id_producto']) ?>">


    <!-- Nombre -->
    <div class="col-md-6">

        <label class="form-label">
            Nombre del producto
        </label>

        <input type="text"
               name="nombre_producto"
               class="form-control"
               value="<?= htmlspecialchars($registroEditando['nombre_producto']) ?>"
               required>

    </div>


    <!-- Descripción -->
    <div class="col-md-6">

        <label class="form-label">
            Descripción
        </label>

        <input type="text"
               name="descripcion"
               class="form-control"
               value="<?= htmlspecialchars($registroEditando['descripcion'] ?? '') ?>">

    </div>


    <!-- Categoría -->
    <div class="col-md-4">

        <label class="form-label">
            Categoría
        </label>

        <select name="id_categoria_postre"
                id="categoria_editar"
                class="form-select"
                required>

            <option value="">
                Seleccione una categoría
            </option>

            <?php foreach ($listaCategorias as $categoria): ?>

                <option value="<?= $categoria['id_categoria_postre'] ?>"
                    <?= $categoria['id_categoria_postre'] == $registroEditando['id_categoria_postre']
                        ? 'selected'
                        : '' ?>>

                    <?= htmlspecialchars($categoria['categoria_postre']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Tipo de torta -->
    <div class="col-md-4"
         id="contenedor_torta_editar">

        <label class="form-label">
            Tipo de torta
        </label>

        <select name="id_tortas"
                id="id_tortas_editar"
                class="form-select">

            <option value="">
                Seleccione un tipo de torta
            </option>

            <?php foreach ($listaTortas as $torta): ?>

                <option value="<?= $torta['id_tortas'] ?>"
                    <?= ($registroEditando['id_tortas'] ?? '') == $torta['id_tortas']
                        ? 'selected'
                        : '' ?>>

                    <?= htmlspecialchars($torta['tipo_torta']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Sabor -->
    <div class="col-md-4">

        <label class="form-label">
            Sabor
        </label>

        <select name="id_sabor"
                class="form-select"
                required>

            <option value="">
                Seleccione un sabor
            </option>

            <?php foreach ($listaSabores as $item): ?>

                <option value="<?= $item['id_sabor'] ?>"
                    <?= $item['id_sabor'] == $registroEditando['id_sabor']
                        ? 'selected'
                        : '' ?>>

                    <?= htmlspecialchars($item['sabor']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Tamaño -->
    <div class="col-md-4">

        <label class="form-label">
            Tamaño
        </label>

        <select name="id_tamano"
                class="form-select"
                required>

            <option value="">
                Seleccione un tamaño
            </option>

            <?php foreach ($listaTamanos as $item): ?>

                <option value="<?= $item['id_tamano'] ?>"
                    <?= $item['id_tamano'] == $registroEditando['id_tamano']
                        ? 'selected'
                        : '' ?>>

                    <?= htmlspecialchars($item['tamano']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Precio -->
    <div class="col-md-4">

        <label class="form-label">
            Precio base
        </label>

        <input type="number"
               name="precio_base"
               step="0.01"
               min="0"
               class="form-control"
               value="<?= htmlspecialchars($registroEditando['precio_base']) ?>"
               required>

    </div>


    <!-- Botones -->
    <div class="col-12">

        <button type="submit"
                class="btn btn-primary">

            Guardar cambios

        </button>

        <a href="producto.php"
           class="btn btn-secondary">

            Cancelar

        </a>

    </div>

</form>


<?php else: ?>


<form action="../controlador/ProductoController.php"
      method="POST"
      class="row g-3 mb-4">

    <input type="hidden"
           name="accion"
           value="crear">

    <input type="hidden"
           name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">


    <!-- Nombre -->
    <div class="col-md-6">

        <label class="form-label">
            Nombre del producto
        </label>

        <input type="text"
               name="nombre_producto"
               class="form-control"
               placeholder="Ej: Torta de Chocolate"
               required>

    </div>


    <!-- Descripción -->
    <div class="col-md-6">

        <label class="form-label">
            Descripción
        </label>

        <input type="text"
               name="descripcion"
               class="form-control"
               placeholder="Descripción del producto">

    </div>


    <!-- Categoría -->
    <div class="col-md-4">

        <label class="form-label">
            Categoría
        </label>

        <select name="id_categoria_postre"
                id="categoria"
                class="form-select"
                required>

            <option value="">
                Seleccione una categoría
            </option>

            <?php foreach ($listaCategorias as $categoria): ?>

                <option value="<?= $categoria['id_categoria_postre'] ?>">

                    <?= htmlspecialchars($categoria['categoria_postre']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Tipo de torta -->
    <div class="col-md-4"
         id="contenedor_torta"
         style="display:none;">

        <label class="form-label">
            Tipo de torta
        </label>

        <select name="id_tortas"
                id="id_tortas"
                class="form-select">

            <option value="">
                Seleccione un tipo de torta
            </option>

            <?php foreach ($listaTortas as $torta): ?>

                <option value="<?= $torta['id_tortas'] ?>">

                    <?= htmlspecialchars($torta['tipo_torta']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Sabor -->
    <div class="col-md-4">

        <label class="form-label">
            Sabor
        </label>

        <select name="id_sabor"
                class="form-select"
                required>

            <option value="">
                Seleccione un sabor
            </option>

            <?php foreach ($listaSabores as $item): ?>

                <option value="<?= $item['id_sabor'] ?>">

                    <?= htmlspecialchars($item['sabor']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Tamaño -->
    <div class="col-md-4">

        <label class="form-label">
            Tamaño
        </label>

        <select name="id_tamano"
                class="form-select"
                required>

            <option value="">
                Seleccione un tamaño
            </option>

            <?php foreach ($listaTamanos as $item): ?>

                <option value="<?= $item['id_tamano'] ?>">

                    <?= htmlspecialchars($item['tamano']) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </div>


    <!-- Precio -->
    <div class="col-md-4">

        <label class="form-label">
            Precio base
        </label>

        <input type="number"
               name="precio_base"
               step="0.01"
               min="0"
               class="form-control"
               placeholder="0.00"
               required>

    </div>


    <!-- Botón -->
    <div class="col-12">

        <button type="submit"
                class="btn btn-success">

            Crear producto

        </button>

    </div>

</form>

<?php endif; ?>


<hr>


<h2 class="mb-3">
    Productos registrados
</h2>


<div class="table-responsive">

<table class="table table-striped table-bordered align-middle">

    <thead class="table-dark">

        <tr>

            <th>ID</th>

            <th>Producto</th>

            <th>Descripción</th>

            <th>Categoría</th>

            <th>Tipo</th>

            <th>Sabor</th>

            <th>Tamaño</th>

            <th>Precio base</th>

            <th>Acciones</th>

        </tr>

    </thead>


    <tbody>

    <?php foreach ($listaProductos as $item): ?>

        <tr>

            <td>
                <?= htmlspecialchars($item['id_producto']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['nombre_producto']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['descripcion'] ?? '') ?>
            </td>

            <td>
                <?= htmlspecialchars($item['categoria_postre']) ?>
            </td>

            <td>

                <?php if (!empty($item['tipo_torta'])): ?>

                    <?= htmlspecialchars($item['tipo_torta']) ?>

                <?php else: ?>

                    <span class="text-muted">
                        No definido
                    </span>

                <?php endif; ?>

            </td>

            <td>
                <?= htmlspecialchars($item['sabor']) ?>
            </td>

            <td>
                <?= htmlspecialchars($item['tamano']) ?>
            </td>

            <td>
                $<?= number_format(
                    (float)$item['precio_base'],
                    2,
                    ',',
                    '.'
                ) ?>
            </td>

            <td>

                <a href="producto.php?editar=<?= $item['id_producto'] ?>"
                   class="btn btn-sm btn-warning">

                    <i class="fa-solid fa-pen"></i>
                    Editar

                </a>


                <form action="../controlador/ProductoController.php"
                      method="POST"
                      style="display:inline">

                    <input type="hidden"
                           name="accion"
                           value="eliminar">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <input type="hidden"
                           name="id"
                           value="<?= $item['id_producto'] ?>">

                    <button type="submit"
                            class="btn btn-sm btn-danger">

                        <i class="fa-solid fa-trash"></i>
                        Eliminar

                    </button>

                </form>

            </td>

        </tr>

    <?php endforeach; ?>


    <?php if (empty($listaProductos)): ?>

        <tr>

            <td colspan="9"
                class="text-center text-muted">

                No hay productos registrados.

            </td>

        </tr>

    <?php endif; ?>

    </tbody>

</table>

</div>


<script>

/*
 * Mostrar u ocultar el tipo de torta
 * según la categoría seleccionada.
 *
 * Categorías:
 * 1 = Torta Clásica
 * 2 = Torta Personalizada
 * 3 = Postre Individual
 * 4 = Decoración
 */

function actualizarTipoProducto(categoriaId, modo) {

    let contenedorTorta;
    let selectTorta;

    if (modo === 'editar') {

        contenedorTorta =
            document.getElementById('contenedor_torta_editar');

        selectTorta =
            document.getElementById('id_tortas_editar');

    } else {

        contenedorTorta =
            document.getElementById('contenedor_torta');

        selectTorta =
            document.getElementById('id_tortas');
    }


    // Ocultar inicialmente
    contenedorTorta.style.display = 'none';

    // Quitar obligatoriedad
    selectTorta.required = false;


    /*
     * Si es Torta Clásica o Torta Personalizada
     */
    if (categoriaId === '1' || categoriaId === '2') {

        contenedorTorta.style.display = 'block';

        selectTorta.required = true;

    } else {

        selectTorta.value = '';

    }
}


/*
 * Formulario CREAR
 */
const categoriaCrear =
    document.getElementById('categoria');

if (categoriaCrear) {

    categoriaCrear.addEventListener('change', function() {

        actualizarTipoProducto(
            this.value,
            'crear'
        );

    });

}


/*
 * Formulario EDITAR
 */
const categoriaEditar =
    document.getElementById('categoria_editar');

if (categoriaEditar) {

    actualizarTipoProducto(
        categoriaEditar.value,
        'editar'
    );


    categoriaEditar.addEventListener('change', function() {

        actualizarTipoProducto(
            this.value,
            'editar'
        );

    });

}

</script>


<?php include __DIR__ . '/partials/footer.php'; ?>