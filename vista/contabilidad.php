<?php
require_once __DIR__ . "/../modelo/contabilidad.php";
$contabilidad = new Contabilidad();
$listaContabilidad = $contabilidad->leer_movimientos();

$registroEditando = null;
if (isset($_GET['editar'])) {
    $registroEditando = $contabilidad->buscar_movimiento($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';

// ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
$columnasValidas = ['id_contabilidad', 'tipo_movimiento', 'monto_transaccion', 'descripcion_registro', 'fecha_registro'];
$busqueda = trim($_GET['buscar'] ?? '');
$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_contabilidad';
$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$porPagina = 10;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($busqueda !== '') {
    $listaContabilidad = array_filter($listaContabilidad, function ($item) use ($busqueda) {
        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) return true;
        }
        return false;
    });
}

usort($listaContabilidad, function ($a, $b) use ($ordenCol, $ordenDir) {
    $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
    return $ordenDir === 'desc' ? -$cmp : $cmp;
});

$totalRegistros = count($listaContabilidad);
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas);
$listaPagina = array_slice(array_values($listaContabilidad), ($paginaActual - 1) * $porPagina, $porPagina);

function enlaceOrden_Contabilidad($col, $ordenCol, $ordenDir) {
    $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
    return '?' . http_build_query($params);
}
?>

<h1 class="mb-4">Gestion de Contabilidad</h1>

<?php if ($registroEditando): ?>
    <form action="../controlador/ContabilidadController.php" method="POST" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $registroEditando['id_contabilidad'] ?>">
        <div class="col-md-4">
            <label class="form-label">Tipo movimiento</label>
            <input type="text" name="tipo_movimiento" class="form-control" value="<?= htmlspecialchars($registroEditando['tipo_movimiento']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Monto transaccion</label>
            <input type="number" step="0.01" name="monto_transaccion" class="form-control" value="<?= htmlspecialchars($registroEditando['monto_transaccion']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Descripcion registro</label>
            <input type="text" name="descripcion_registro" class="form-control" value="<?= htmlspecialchars($registroEditando['descripcion_registro']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha registro</label>
            <input type="date" name="fecha_registro" class="form-control" value="<?= htmlspecialchars($registroEditando['fecha_registro']) ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="contabilidad.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <form action="../controlador/ContabilidadController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_Contabilidad(this);" novalidate>
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="col-md-4">
            <label class="form-label">Tipo movimiento</label>
            <input type="text" name="tipo_movimiento" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Monto transaccion</label>
            <input type="number" step="0.01" name="monto_transaccion" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Descripcion registro</label>
            <input type="text" name="descripcion_registro" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha registro</label>
            <input type="date" name="fecha_registro" class="form-control" required>
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
        <a href="contabilidad.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th><a href="<?= enlaceOrden_Contabilidad('id_contabilidad', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id contabilidad <?= $ordenCol=='id_contabilidad' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Contabilidad('tipo_movimiento', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Tipo movimiento <?= $ordenCol=='tipo_movimiento' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Contabilidad('monto_transaccion', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Monto transaccion <?= $ordenCol=='monto_transaccion' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Contabilidad('descripcion_registro', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Descripcion registro <?= $ordenCol=='descripcion_registro' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Contabilidad('fecha_registro', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Fecha registro <?= $ordenCol=='fecha_registro' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($listaPagina as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id_contabilidad']) ?></td>
            <td><?= htmlspecialchars($item['tipo_movimiento']) ?></td>
            <td><?= htmlspecialchars($item['monto_transaccion']) ?></td>
            <td><?= htmlspecialchars($item['descripcion_registro']) ?></td>
            <td><?= htmlspecialchars($item['fecha_registro']) ?></td>
            <td>
                <a href="contabilidad.php?editar=<?= $item['id_contabilidad'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen"></i> Editar
                </a>
                <form action="../controlador/ContabilidadController.php" method="POST" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $item['id_contabilidad'] ?>">
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
function validarFormulario_Contabilidad(form) {
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
