<?php
require_once __DIR__ . "/../modelo/informes.php";

// ---- Validacion y normalizacion del rango de fechas ----
function fechaValida_Informe($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

// Si no hay fechas o son invalidas, usamos el mes actual como rango por defecto
if (!fechaValida_Informe($desde)) $desde = date('Y-m-01');
if (!fechaValida_Informe($hasta)) $hasta = date('Y-m-d');

// Si el usuario invierte el rango, lo corregimos en vez de fallar
if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}

$informes = new Informes();
$transacciones = $informes->obtener_transacciones($desde, $hasta);
$total = $informes->total_periodo($desde, $hasta);

include __DIR__ . '/partials/header.php';

$rolesPermitidos = ['Administrador', 'Gerente General'];
require_once __DIR__ . '/partials/restringir_roles.php';
?>

<h1 class="mb-4">Informe de Transacciones</h1>

<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-md-3">
        <label class="form-label">Desde</label>
        <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($desde) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Hasta</label>
        <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta) ?>" required>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <a href="informes.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
    <div class="col-md-4 text-md-end">
        <a class="btn btn-danger"
           href="../controlador/exportar_informe.php?tipo=pdf&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        <a class="btn btn-success"
           href="../controlador/exportar_informe.php?tipo=csv&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>">
            <i class="fa-solid fa-file-csv"></i> Exportar CSV
        </a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Cantidad</th>
            <th>Estado pedido</th>
            <th>Movimiento</th>
            <th class="text-end">Monto</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($transacciones as $fila): ?>
        <tr>
            <td><?= htmlspecialchars($fila['id_maestro']) ?></td>
            <td><?= htmlspecialchars($fila['fecha_transaccion']) ?></td>
            <td><?= htmlspecialchars($fila['cliente']) ?></td>
            <td><?= htmlspecialchars($fila['cantidad_pedida']) ?></td>
            <td><?= htmlspecialchars($fila['estado_pedido']) ?></td>
            <td><?= htmlspecialchars($fila['tipo_movimiento']) ?></td>
            <td class="text-end">$<?= number_format((float) $fila['monto_transaccion'], 2, ',', '.') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($transacciones)): ?>
        <tr><td colspan="7" class="text-center text-muted">Sin transacciones en el periodo seleccionado</td></tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-end">Total del periodo</th>
            <th class="text-end">$<?= number_format($total, 2, ',', '.') ?></th>
        </tr>
    </tfoot>
</table>

<?php include __DIR__ . '/partials/footer.php'; ?>