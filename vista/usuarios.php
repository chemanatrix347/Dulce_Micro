<?php
require_once __DIR__ . "/../modelo/usuarios.php";
$usuario = new Usuario();

$listaUsuario = $usuario->leer_usuarios();
$listaRoles = $usuario->leer_roles();
$rolesPorId = [];

foreach ($listaRoles as $rol) {
    $rolesPorId[$rol['id_rol']] = $rol['nombre_rol'];
}

$registroEditando = null;
if (isset($_GET['editar'])) {
    $registroEditando = $usuario->buscar_usuario($_GET['editar']);
}

include __DIR__ . '/partials/header.php';


// ---- Busqueda, orden y paginacion (se aplican sobre el listado ya leido) ----
$columnasValidas = ['id_usuario', 'nombre_completo', 'correo_electronico', 'id_roles', 'contrasena_hash', 'fecha_registro', 'estado'];
$busqueda = trim($_GET['buscar'] ?? '');
$ordenCol = in_array($_GET['orden'] ?? '', $columnasValidas) ? $_GET['orden'] : 'id_usuario';
$ordenDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$porPagina = 10;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($busqueda !== '') {
    $listaUsuario = array_filter($listaUsuario, function ($item) use ($busqueda) {
        foreach ($item as $valor) {
            if (stripos((string) $valor, $busqueda) !== false) return true;
        }
        return false;
    });
}

usort($listaUsuario, function ($a, $b) use ($ordenCol, $ordenDir) {
    $cmp = strnatcasecmp((string) ($a[$ordenCol] ?? ''), (string) ($b[$ordenCol] ?? ''));
    return $ordenDir === 'desc' ? -$cmp : $cmp;
});

$totalRegistros = count($listaUsuario);
$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$paginaActual = min($paginaActual, $totalPaginas);
$listaPagina = array_slice(array_values($listaUsuario), ($paginaActual - 1) * $porPagina, $porPagina);

function enlaceOrden_Usuario($col, $ordenCol, $ordenDir) {
    $dir = ($ordenCol === $col && $ordenDir === 'asc') ? 'desc' : 'asc';
    $params = array_merge($_GET, ['orden' => $col, 'dir' => $dir]);
    return '?' . http_build_query($params);
}
?>

<h1 class="mb-4">Gestion de Usuario</h1>

<?php if ($registroEditando): ?>
    <form action="../controlador/UsuarioController.php" method="POST" class="row g-3 mb-4">
        <input type="hidden" name="accion" value="actualizar">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" value="<?= $registroEditando['id_usuario'] ?>">
        <div class="col-md-4">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($registroEditando['nombre_completo']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Correo electronico</label>
            <input type="text" name="correo_electronico" class="form-control" value="<?= htmlspecialchars($registroEditando['correo_electronico']) ?>" required>
        </div>
        <div class="col-md-4">
    <label class="form-label">Rol</label>

    <select name="id_roles" class="form-select" required>
        <option value="">Seleccione un rol</option>

        <?php foreach ($listaRoles as $rol): ?>
            <option
                value="<?= htmlspecialchars($rol['id_rol']) ?>"
                <?= ($registroEditando['id_roles'] == $rol['id_rol']) ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($rol['nombre_rol']) ?>
            </option>
        <?php endforeach; ?>

    </select>
</div>
        <div class="col-md-4">
            <label class="form-label">Contrasena hash</label>
            <input type="text" name="contrasena_hash" class="form-control" value="<?= htmlspecialchars($registroEditando['contrasena_hash']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha registro</label>
            <input type="date" name="fecha_registro" class="form-control" value="<?= htmlspecialchars($registroEditando['fecha_registro']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($registroEditando['estado']) ?>" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
<?php else: ?>
    <form action="../controlador/UsuarioController.php" method="POST" class="row g-3 mb-4" onsubmit="return validarFormulario_Usuario(this);" novalidate>
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="col-md-4">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre_completo" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Correo electronico</label>
            <input type="text" name="correo_electronico" class="form-control" required>
        </div>
        <div class="col-md-4">
    <label class="form-label">Rol</label>

    <select name="id_roles" class="form-select" required>
        <option value="">Seleccione un rol</option>

        <?php foreach ($listaRoles as $rol): ?>
            <option value="<?= htmlspecialchars($rol['id_rol']) ?>">
                <?= htmlspecialchars($rol['nombre_rol']) ?>
            </option>
        <?php endforeach; ?>

    </select>
</div>
        <div class="col-md-4">
            <label class="form-label">Contrasena hash</label>
            <input type="text" name="contrasena_hash" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha registro</label>
            <input type="date" name="fecha_registro" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <input type="text" name="estado" class="form-control" required>
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
        <a href="usuarios.php" class="btn btn-outline-secondary">Limpiar</a>
    </div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead class="table-dark">
        <tr>
            <th><a href="<?= enlaceOrden_Usuario('id_usuario', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Id usuario <?= $ordenCol=='id_usuario' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Usuario('nombre_completo', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Nombre completo <?= $ordenCol=='nombre_completo' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Usuario('correo_electronico', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Correo electronico <?= $ordenCol=='correo_electronico' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Rol</th>
            <th><a href="<?= enlaceOrden_Usuario('contrasena_hash', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Contrasena hash <?= $ordenCol=='contrasena_hash' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Usuario('fecha_registro', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Fecha registro <?= $ordenCol=='fecha_registro' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th><a href="<?= enlaceOrden_Usuario('estado', $ordenCol, $ordenDir) ?>" class="text-white text-decoration-none">Estado <?= $ordenCol=='estado' ? ($ordenDir=='asc' ? "&#9650;" : "&#9660;") : "" ?></a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($listaPagina as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['id_usuario']) ?></td>
            <td><?= htmlspecialchars($item['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($item['correo_electronico']) ?></td>
            <td><?= htmlspecialchars($rolesPorId[$item['id_roles']] ?? 'Sin rol') ?></td>
            <td><?= htmlspecialchars($item['contrasena_hash']) ?></td>
            <td><?= htmlspecialchars($item['fecha_registro']) ?></td>
            <td><?= htmlspecialchars($item['estado']) ?></td>
            <td>
                <a href="usuarios.php?editar=<?= $item['id_usuario'] ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen"></i> Editar
                </a>
                <form action="../controlador/UsuarioController.php" method="POST" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id" value="<?= $item['id_usuario'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($listaPagina)): ?>
        <tr><td colspan="8" class="text-center text-muted">Sin resultados</td></tr>
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
function validarFormulario_Usuario(form) {
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