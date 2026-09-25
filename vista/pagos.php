<?php
require_once __DIR__ . "/../modelo/pagos.php";
$pago = new Pago();
$listaPago = $pago->leer_pagos();

$registroEditando = null;
if (isset($_GET['editar'])) {
    $registroEditando = $pago->buscar_pago($_GET['editar']);
}

include __DIR__ . '/partials/header.php';

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';

// ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
$columnasValidas = ['id_pagos', 'id_pedido', 'id_metodo_pago', 'monto', 'id_estado_pago', 'fecha_pago'];
$busqueda = trim($_GET['buscar'] ?? '');
$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_pagos';
$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$porPagina = 10;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($busqueda !== '') {
    $listaPago = array_filter($listaPago, function ($item) use ($busqueda) {
        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) return true;
        }
        return false;
    });
}

usort($listaPago, function ($a, $b) use ($ordenCol, $ordenDir) {
    $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
    return $ordenDir === 'desc' ? -$cmp : $cmp;
});

$totalRegistros = count($listaPago);
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas);
$listaPagina = array_slice(array_values($listaPago), ($paginaActual - 1) * $porPagina, $porPagina);

function enlaceOrden_Pago($col, $ordenCol, $ordenDir) {
    $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
    return '?' . http_build_query($params);
}
?>

<h1 class="mb-4">Gestion de Pago</h1>

<?php if ($registroEditando): ?>
    <form action="../controlador/PagoController.php" method="POST" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $registroEditando['id_pagos'] ?>">
        <div class="col-md-4">
            <label class="form-label">Id pedido</label>
            <input type="number" name="id_pedido" class="form-control" value="<?= htmlspecialchars($registroEditando['id_pedido']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id metodo pago</label>
            <input type="number" name="id_metodo_pago" class="form-control" value="<?= htmlspecialchars($registroEditando['id_metodo_pago']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($registroEditando['monto']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id estado pago</label>
            <input type="number" name="id_estado_pago" class="form-control" value="<?= htmlspecialchars($registroEditando['id_estado_pago']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha pago</label>
            <input type="date" name="fecha_pago" class="form-control" value="<?= htmlspecialchars($registroEditando['fecha_pago']) ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="pagos.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <form action="../controlador/PagoController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_Pago(this);" novalidate>
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="col-md-4">
            <label class="form-label">Id pedido</label>
            <input type="number" name="id_pedido" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id metodo pago</label>
            <input type="number" name="id_metodo_pago" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Id estado pago</label>
            <input type="number" name="id_estado_pago" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha pago</label>
            <input type="date" name="fecha_pago" class="form-control" required>
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
        <a href="pagos.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th><a href="<?= enlaceOrden_Pago('id_pagos', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id pagos <?= $ordenCol=='id_pagos' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Pago('id_pedido', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id pedido <?= $ordenCol=='id_pedido' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Pago('id_metodo_pago', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id metodo pago <?= $ordenCol=='id_metodo_pago' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Pago('monto', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Monto <?= $ordenCol=='monto' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Pago('id_estado_pago', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id estado pago <?= $ordenCol=='id_estado_pago' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Pago('fecha_pago', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Fecha pago <?= $ordenCol=='fecha_pago' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($listaPagina as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id_pagos']) ?></td>
            <td><?= htmlspecialchars($item['id_pedido']) ?></td>
            <td><?= htmlspecialchars($item['id_metodo_pago']) ?></td>
            <td><?= htmlspecialchars($item['monto']) ?></td>
            <td><?= htmlspecialchars($item['id_estado_pago']) ?></td>
            <td><?= htmlspecialchars($item['fecha_pago']) ?></td>
            <td>
                <a href="pagos.php?editar=<?= $item['id_pagos'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen"></i> Editar
                </a>
                <form action="../controlador/PagoController.php" method="POST" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $item['id_pagos'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($listaPagina)): ?>
        <tr><td colspan="7" class="text-center text-muted">Sin resultados</td></tr>
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
function validarFormulario_Pago(form) {
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
