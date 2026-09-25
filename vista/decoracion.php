<?php
require_once __DIR__ . "/../modelo/decoracion.php";
$decoracion = new Decoracion();
$listaDecoracion = $decoracion->leer_decoraciones();

$registroEditando = null;
if (isset($_GET['editar'])) {
    $registroEditando = $decoracion->buscar_decoracion($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

// ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
$columnasValidas = ['id_decoracion', 'precio'];
$busqueda = trim($_GET['buscar'] ?? '');
$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_decoracion';
$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$porPagina = 10;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($busqueda !== '') {
    $listaDecoracion = array_filter($listaDecoracion, function ($item) use ($busqueda) {
        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) return true;
        }
        return false;
    });
}

usort($listaDecoracion, function ($a, $b) use ($ordenCol, $ordenDir) {
    $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
    return $ordenDir === 'desc' ? -$cmp : $cmp;
});

$totalRegistros = count($listaDecoracion);
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas);
$listaPagina = array_slice(array_values($listaDecoracion), ($paginaActual - 1) * $porPagina, $porPagina);

function enlaceOrden_Decoracion($col, $ordenCol, $ordenDir) {
    $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
    return '?' . http_build_query($params);
}
?>

<h1 class="mb-4">Gestion de Decoracion</h1>

<?php if ($registroEditando): ?>
    <form action="../controlador/DecoracionController.php" method="POST" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $registroEditando['id_decoracion'] ?>">
        <div class="col-md-4">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control" value="<?= htmlspecialchars($registroEditando['precio']) ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="decoracion.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <form action="../controlador/DecoracionController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_Decoracion(this);" novalidate>
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="col-md-4">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
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
        <a href="decoracion.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th><a href="<?= enlaceOrden_Decoracion('id_decoracion', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id decoracion <?= $ordenCol=='id_decoracion' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Decoracion('precio', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Precio <?= $ordenCol=='precio' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($listaPagina as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id_decoracion']) ?></td>
            <td><?= htmlspecialchars($item['precio']) ?></td>
            <td>
                <a href="decoracion.php?editar=<?= $item['id_decoracion'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen"></i> Editar
                </a>
                <form action="../controlador/DecoracionController.php" method="POST" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $item['id_decoracion'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($listaPagina)): ?>
        <tr><td colspan="3" class="text-center text-muted">Sin resultados</td></tr>
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
function validarFormulario_Decoracion(form) {
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
