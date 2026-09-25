<?php
require_once __DIR__ . "/../modelo/tablamaestra.php";
$tablaMaestra = new TablaMaestra();
$listaTablaMaestra = $tablaMaestra->leer_maestros();

$registroEditando = null;
if (isset($_GET['editar'])) {
    $registroEditando = $tablaMaestra->buscar_maestro($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';

// ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
$columnasValidas = ['id_maestro', 'fecha_transaccion', 'id_cliente', 'id_materia_prima', 'id_tortas', 'id_postre_individual', 'cantidad_pedida', 'id_pago', 'id_contabilidad', 'id_estado_pedido'];
$busqueda = trim($_GET['buscar'] ?? '');
$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_maestro';
$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$porPagina = 10;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($busqueda !== '') {
    $listaTablaMaestra = array_filter($listaTablaMaestra, function ($item) use ($busqueda) {
        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) return true;
        }
        return false;
    });
}

usort($listaTablaMaestra, function ($a, $b) use ($ordenCol, $ordenDir) {
    $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
    return $ordenDir === 'desc' ? -$cmp : $cmp;
});

$totalRegistros = count($listaTablaMaestra);
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas);
$listaPagina = array_slice(array_values($listaTablaMaestra), ($paginaActual - 1) * $porPagina, $porPagina);

function enlaceOrden_TablaMaestra($col, $ordenCol, $ordenDir) {
    $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
    return '?' . http_build_query($params);
}
?>

<h1 class="mb-4">Gestion de TablaMaestra</h1>

<?php if ($registroEditando): ?>
    <form action="../controlador/TablaMaestraController.php" method="POST" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $registroEditando['id_maestro'] ?>">
        <div class="col-md-4">
            <label class="form-label">Fecha transaccion</label>
            <input type="date" name="fecha_transaccion" class="form-control" value="<?= htmlspecialchars($registroEditando['fecha_transaccion']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id cliente</label>
            <input type="number" name="id_cliente" class="form-control" value="<?= htmlspecialchars($registroEditando['id_cliente']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id materia prima</label>
            <input type="number" name="id_materia_prima" class="form-control" value="<?= htmlspecialchars($registroEditando['id_materia_prima']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id tortas</label>
            <input type="number" name="id_tortas" class="form-control" value="<?= htmlspecialchars($registroEditando['id_tortas']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id postre individual</label>
            <input type="number" name="id_postre_individual" class="form-control" value="<?= htmlspecialchars($registroEditando['id_postre_individual']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Cantidad pedida</label>
            <input type="text" name="cantidad_pedida" class="form-control" value="<?= htmlspecialchars($registroEditando['cantidad_pedida']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id pago</label>
            <input type="number" name="id_pago" class="form-control" value="<?= htmlspecialchars($registroEditando['id_pago']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id contabilidad</label>
            <input type="number" name="id_contabilidad" class="form-control" value="<?= htmlspecialchars($registroEditando['id_contabilidad']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id estado pedido</label>
            <input type="number" name="id_estado_pedido" class="form-control" value="<?= htmlspecialchars($registroEditando['id_estado_pedido']) ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="tablamaestra.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <form action="../controlador/TablaMaestraController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_TablaMaestra(this);" novalidate>
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="col-md-4">
            <label class="form-label">Fecha transaccion</label>
            <input type="date" name="fecha_transaccion" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id cliente</label>
            <input type="number" name="id_cliente" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id materia prima</label>
            <input type="number" name="id_materia_prima" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id tortas</label>
            <input type="number" name="id_tortas" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id postre individual</label>
            <input type="number" name="id_postre_individual" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Cantidad pedida</label>
            <input type="text" name="cantidad_pedida" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id pago</label>
            <input type="number" name="id_pago" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id contabilidad</label>
            <input type="number" name="id_contabilidad" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id estado pedido</label>
            <input type="number" name="id_estado_pedido" class="form-control" required>
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
        <a href="tablamaestra.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_maestro', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id maestro <?= $ordenCol=='id_maestro' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('fecha_transaccion', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Fecha transaccion <?= $ordenCol=='fecha_transaccion' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_cliente', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id cliente <?= $ordenCol=='id_cliente' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_materia_prima', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id materia prima <?= $ordenCol=='id_materia_prima' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_tortas', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id tortas <?= $ordenCol=='id_tortas' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_postre_individual', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id postre individual <?= $ordenCol=='id_postre_individual' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('cantidad_pedida', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Cantidad pedida <?= $ordenCol=='cantidad_pedida' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_pago', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id pago <?= $ordenCol=='id_pago' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_contabilidad', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id contabilidad <?= $ordenCol=='id_contabilidad' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_TablaMaestra('id_estado_pedido', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id estado pedido <?= $ordenCol=='id_estado_pedido' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($listaPagina as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id_maestro']) ?></td>
            <td><?= htmlspecialchars($item['fecha_transaccion']) ?></td>
            <td><?= htmlspecialchars($item['id_cliente']) ?></td>
            <td><?= htmlspecialchars($item['id_materia_prima']) ?></td>
            <td><?= htmlspecialchars($item['id_tortas']) ?></td>
            <td><?= htmlspecialchars($item['id_postre_individual']) ?></td>
            <td><?= htmlspecialchars($item['cantidad_pedida']) ?></td>
            <td><?= htmlspecialchars($item['id_pago']) ?></td>
            <td><?= htmlspecialchars($item['id_contabilidad']) ?></td>
            <td><?= htmlspecialchars($item['id_estado_pedido']) ?></td>
            <td>
                <a href="tablamaestra.php?editar=<?= $item['id_maestro'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen"></i> Editar
                </a>
                <form action="../controlador/TablaMaestraController.php" method="POST" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $item['id_maestro'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($listaPagina)): ?>
        <tr><td colspan="11" class="text-center text-muted">Sin resultados</td></tr>
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
function validarFormulario_TablaMaestra(form) {
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
