<?php
require_once __DIR__ . "/../modelo/materiaprima.php";
$materiaPrima = new MateriaPrima();
require_once __DIR__ . "/../modelo/unidadmedida.php";
$unidadMedidaModel = new UnidadMedida();
$listaUnidades = $unidadMedidaModel->leer_unidades_medida();
$nombresUnidad = array_column($listaUnidades, 'unidad_medida', 'id_unidad_medida');
$listaMateriaPrima = $materiaPrima->leer_materias_primas();

$registroEditando = null;
if (isset($_GET['editar'])) {
    $registroEditando = $materiaPrima->buscar_materia_prima($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';

// ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
$columnasValidas = ['id_materia_prima', 'nombre_insumo', 'descripcion', 'stock_disponible', 'id_unidad_medida'];
$busqueda = trim($_GET['buscar'] ?? '');
$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_materia_prima';
$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$porPagina = 10;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($busqueda !== '') {
    $listaMateriaPrima = array_filter($listaMateriaPrima, function ($item) use ($busqueda) {
        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) return true;
        }
        return false;
    });
}

usort($listaMateriaPrima, function ($a, $b) use ($ordenCol, $ordenDir) {
    $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
    return $ordenDir === 'desc' ? -$cmp : $cmp;
});

$totalRegistros = count($listaMateriaPrima);
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas);
$listaPagina = array_slice(array_values($listaMateriaPrima), ($paginaActual - 1) * $porPagina, $porPagina);

function enlaceOrden_MateriaPrima($col, $ordenCol, $ordenDir) {
    $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
    return '?' . http_build_query($params);
}
?>

<h1 class="mb-4">Gestion de MateriaPrima</h1>

<?php if ($registroEditando): ?>
    <form action="../controlador/MateriaPrimaController.php" method="POST" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $registroEditando['id_materia_prima'] ?>">
        <div class="col-md-4">
            <label class="form-label">Nombre insumo</label>
            <input type="text" name="nombre_insumo" class="form-control" value="<?= htmlspecialchars($registroEditando['nombre_insumo']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Descripcion</label>
            <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($registroEditando['descripcion']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Stock disponible</label>
            <input type="number" step="0.01" name="stock_disponible" class="form-control" value="<?= htmlspecialchars($registroEditando['stock_disponible']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Unidad de medida</label>
            <select name="id_unidad_medida" class="form-control" required>
                <option value="">Selecciona...</option>
                <?php foreach ($listaUnidades as $u): ?>
                    <option value="<?= $u['id_unidad_medida'] ?>" <?= $u['id_unidad_medida'] == $registroEditando['id_unidad_medida'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['unidad_medida']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="materiaprima.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <form action="../controlador/MateriaPrimaController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_MateriaPrima(this);" novalidate>
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="col-md-4">
            <label class="form-label">Nombre insumo</label>
            <input type="text" name="nombre_insumo" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Descripcion</label>
            <input type="text" name="descripcion" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Stock disponible</label>
            <input type="number" step="0.01" name="stock_disponible" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Unidad de medida</label>
            <select name="id_unidad_medida" class="form-control" required>
                <option value="">Selecciona...</option>
                <?php foreach ($listaUnidades as $u): ?>
                    <option value="<?= $u['id_unidad_medida'] ?>"><?= htmlspecialchars($u['unidad_medida']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">Crear</button>
        </div>
    </form>
<?php endif; ?>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar..." value="<?= htmlspecialchars($busqueda) ?>">
    </div>
    <input type="hidden" name="orden" value="<?= htmlspecialchars($ordenCol) ?>">
    <input type="hidden" name="dir" value="<?= htmlspecialchars($ordenDir) ?>">
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <a href="materiaprima.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
    <div class="col-md-4 text-md-end">
        <a class="btn btn-danger" href="../controlador/exportar_materiaprima.php?tipo=pdf">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        <a class="btn btn-success" href="../controlador/exportar_materiaprima.php?tipo=csv">
            <i class="fa-solid fa-file-csv"></i> Exportar CSV
        </a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th><a href="<?= enlaceOrden_MateriaPrima('id_materia_prima', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id materia prima <?= $ordenCol=='id_materia_prima' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_MateriaPrima('nombre_insumo', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Nombre insumo <?= $ordenCol=='nombre_insumo' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_MateriaPrima('descripcion', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Descripcion <?= $ordenCol=='descripcion' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_MateriaPrima('stock_disponible', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Stock disponible <?= $ordenCol=='stock_disponible' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_MateriaPrima('id_unidad_medida', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Unidad de medida <?= $ordenCol=='id_unidad_medida' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($listaPagina as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id_materia_prima']) ?></td>
            <td><?= htmlspecialchars($item['nombre_insumo']) ?></td>
            <td><?= htmlspecialchars($item['descripcion']) ?></td>
            <td><?= htmlspecialchars($item['stock_disponible']) ?></td>
            <td><?= htmlspecialchars($nombresUnidad[$item['id_unidad_medida']] ?? $item['id_unidad_medida']) ?></td>
            <td>
                <a href="materiaprima.php?editar=<?= $item['id_materia_prima'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen"></i> Editar
                </a>
                <form action="../controlador/MateriaPrimaController.php" method="POST" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $item['id_materia_prima'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($listaPagina)): ?>
        <tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPaginas > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <li class="page-item <?= $p === $paginaActual ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $p])) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<script>
function validarFormulario_MateriaPrima(form) {
    let valido = true;
    form.querySelectorAll('[required]').forEach(function (campo) {
        if (!campo.value.trim()) {
            campo.classList.add('is-invalid');
            valido = false;
        } else {
            campo.classList.remove('is-invalid');
        }
    });
    if (!valido) alert('Por favor completa todos los campos obligatorios.');
    return valido;
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>