    <?php
    require_once __DIR__ . "/../modelo/gastodecoracion.php";
    $gastoDecoracion = new GastoDecoracion();
    $listaGastoDecoracion = $gastoDecoracion->leer_gastos_decoracion();

    $registroEditando = null;
    if (isset($_GET['editar'])) {
        $registroEditando = $gastoDecoracion->buscar_gasto_decoracion($_GET['editar']);
    }

    include __DIR__ . '/partials/header.php';
    

    // ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
    $columnasValidas = ['id_gasto_decoracion', 'id_decoracion', 'numero_diseno', 'nombre_diseno', 'fondant_inicial', 'gasto_fondant', 'colorante_inicial', 'gasto_colorante'];
    $busqueda = trim($_GET['buscar'] ?? '');
    $ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_gasto_decoracion';
    $ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
    $porPagina = 10;
    $paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

    if ($busqueda !== '') {
        $listaGastoDecoracion = array_filter($listaGastoDecoracion, function ($item) use ($busqueda) {
            foreach ($item as $valor) {
                if (stripos((string) $valor, $busqueda) !== false) return true;
            }
            return false;
        });
    }

    usort($listaGastoDecoracion, function ($a, $b) use ($ordenCol, $ordenDir) {
        $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
        return $ordenDir === 'desc' ? -$cmp : $cmp;
    });

    $totalRegistros = count($listaGastoDecoracion);
    $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
    $paginaActual = min($paginaActual, $totalPaginas);
    $listaPagina = array_slice(array_values($listaGastoDecoracion), ($paginaActual - 1) * $porPagina, $porPagina);

    function enlaceOrden_GastoDecoracion($col, $ordenCol, $ordenDir) {
        $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
        $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
        return '?' . http_build_query($params);
    }
    ?>

    <h1 class="mb-4">Gestion de GastoDecoracion</h1>

    <?php if ($registroEditando): ?>
        <form action="../controlador/GastoDecoracionController.php" method="POST" class="row g-3 mb-4">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="id" value="<?= $registroEditando['id_gasto_decoracion'] ?>">
            <div class="col-md-4">
                <label class="form-label">Id decoracion</label>
                <input type="number" name="id_decoracion" class="form-control" value="<?= htmlspecialchars($registroEditando['id_decoracion']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Numero diseno</label>
                <input type="number" name="numero_diseno" class="form-control" value="<?= htmlspecialchars($registroEditando['numero_diseno']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombre diseno</label>
                <input type="text" name="nombre_diseno" class="form-control" value="<?= htmlspecialchars($registroEditando['nombre_diseno']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fondant inicial</label>
                <input type="number" step="0.01" name="fondant_inicial" class="form-control" value="<?= htmlspecialchars($registroEditando['fondant_inicial']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Gasto fondant</label>
                <input type="number" step="0.01" name="gasto_fondant" class="form-control" value="<?= htmlspecialchars($registroEditando['gasto_fondant']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Colorante inicial</label>
                <input type="number" step="0.01" name="colorante_inicial" class="form-control" value="<?= htmlspecialchars($registroEditando['colorante_inicial']) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Gasto colorante</label>
                <input type="number" step="0.01" name="gasto_colorante" class="form-control" value="<?= htmlspecialchars($registroEditando['gasto_colorante']) ?>" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="gastodecoracion.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    <?php else: ?>
        <form action="../controlador/GastoDecoracionController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_GastoDecoracion(this);" novalidate>
            <input type="hidden" name="accion" value="crear">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="col-md-4">
                <label class="form-label">Id decoracion</label>
                <input type="number" name="id_decoracion" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Numero diseno</label>
                <input type="number" name="numero_diseno" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombre diseno</label>
                <input type="text" name="nombre_diseno" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fondant inicial</label>
                <input type="number" step="0.01" name="fondant_inicial" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Gasto fondant</label>
                <input type="number" step="0.01" name="gasto_fondant" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Colorante inicial</label>
                <input type="number" step="0.01" name="colorante_inicial" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Gasto colorante</label>
                <input type="number" step="0.01" name="gasto_colorante" class="form-control" required>
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
            <a href="gastodecoracion.php" class="btn btn-outline-secondary">Limpiar</a>
        </div>
    </form>

    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th><a href="<?= enlaceOrden_GastoDecoracion('id_gasto_decoracion', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id gasto decoracion <?= $ordenCol=='id_gasto_decoracion' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('id_decoracion', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id decoracion <?= $ordenCol=='id_decoracion' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('numero_diseno', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Numero diseno <?= $ordenCol=='numero_diseno' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('nombre_diseno', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Nombre diseno <?= $ordenCol=='nombre_diseno' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('fondant_inicial', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Fondant inicial <?= $ordenCol=='fondant_inicial' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('gasto_fondant', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Gasto fondant <?= $ordenCol=='gasto_fondant' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('colorante_inicial', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Colorante inicial <?= $ordenCol=='colorante_inicial' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th><a href="<?= enlaceOrden_GastoDecoracion('gasto_colorante', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Gasto colorante <?= $ordenCol=='gasto_colorante' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($listaPagina as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['id_gasto_decoracion']) ?></td>
                <td><?= htmlspecialchars($item['id_decoracion']) ?></td>
                <td><?= htmlspecialchars($item['numero_diseno']) ?></td>
                <td><?= htmlspecialchars($item['nombre_diseno']) ?></td>
                <td><?= htmlspecialchars($item['fondant_inicial']) ?></td>
                <td><?= htmlspecialchars($item['gasto_fondant']) ?></td>
                <td><?= htmlspecialchars($item['colorante_inicial']) ?></td>
                <td><?= htmlspecialchars($item['gasto_colorante']) ?></td>
                <td>
                    <a href="gastodecoracion.php?editar=<?= $item['id_gasto_decoracion'] ?>" class="btn btn-sm btn-warning">
                        <i class="fa-solid fa-pen"></i> Editar
                    </a>
                    <form action="../controlador/GastoDecoracionController.php" method="POST" style="display:inline">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id" value="<?= $item['id_gasto_decoracion'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($listaPagina)): ?>
            <tr><td colspan="9" class="text-center text-muted">Sin resultados</td></tr>
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
    function validarFormulario_GastoDecoracion(form) {
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
