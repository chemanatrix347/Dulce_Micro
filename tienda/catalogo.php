<?php
require_once __DIR__ . '/../config/conexion.php';
$conn = (new conexion())->conn;

/* =====================================================================
 * Tablas y columnas usadas (verificadas en phpMyAdmin, base login_db):
 *
 *   productos          -> id_producto, nombre_producto, descripcion,
 *                         id_categoria_postre, id_sabor, precio_base,
 *                         imagen, estado
 *   sabor              -> id_sabor, sabor, estado
 *   categoria_postre   -> id_categoria_postre, categoria_postre, estado
 *   inventario         -> id_producto, cantidad, estado   (NUEVO)
 *
 * "estado = 1" se asume como registro activo (soft delete).
 * Si tu valor de "activo" es otro, cámbialo en las consultas de abajo.
 * ===================================================================== */

$tituloPagina = 'Catálogo de Postres';
$paginaActiva = 'catalogo';
require_once __DIR__ . '/partials/header.php';

/* ---------- Filtros que llegan por GET ---------- */
$q       = trim($_GET['q'] ?? '');
$idCat   = (int)($_GET['categoria'] ?? 0);
$idSabor = (int)($_GET['sabor'] ?? 0);
$orden   = $_GET['orden'] ?? 'nombre';
$limite  = min(max((int)($_GET['limite'] ?? 8), 8), 200);

$ordenesPermitidos = [
    'nombre' => 'p.nombre_producto ASC',
    'menor'  => 'p.precio_base ASC',
    'mayor'  => 'p.precio_base DESC',
];
$ordenSql = $ordenesPermitidos[$orden] ?? $ordenesPermitidos['nombre'];

/* ---------- WHERE dinámico con parámetros (evita inyección SQL) ---------- */
$where  = ['p.estado = 1'];
$params = [];

if ($q !== '') {
    $where[] = '(p.nombre_producto LIKE :q1 OR p.descripcion LIKE :q2)';
    $params[':q1'] = '%' . $q . '%';
    $params[':q2'] = '%' . $q . '%';
}
if ($idCat > 0) {
    $where[] = 'p.id_categoria_postre = :cat';
    $params[':cat'] = $idCat;
}
if ($idSabor > 0) {
    $where[] = 'p.id_sabor = :sabor';
    $params[':sabor'] = $idSabor;
}
$whereSql = implode(' AND ', $where);

/* ---------- Consultas ---------- */
$stmtTotal = $conn->prepare("SELECT COUNT(*) FROM productos p WHERE $whereSql");
$stmtTotal->execute($params);
$totalProductos = (int)$stmtTotal->fetchColumn();

/* NUEVO: se agrega COALESCE(i.cantidad, 0) AS stock mediante LEFT JOIN inventario,
 * para poder mostrar "Agotado" y deshabilitar el botón "Agregar" cuando el
 * producto no tenga unidades disponibles. */
$stmt = $conn->prepare(
    "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio_base, p.imagen,
            s.sabor AS nombre_sabor,
            COALESCE(i.cantidad, 0) AS stock
       FROM productos p
       LEFT JOIN sabor s ON s.id_sabor = p.id_sabor
       LEFT JOIN inventario i ON i.id_producto = p.id_producto AND i.estado = 1
      WHERE $whereSql
      ORDER BY $ordenSql
      LIMIT $limite"          // $limite ya es un entero validado
);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Chips de filtro: salen de las tablas, así una categoría o sabor nuevo aparece solo
$categorias = $conn->query(
    "SELECT id_categoria_postre AS id, categoria_postre AS nombre
       FROM categoria_postre WHERE estado = 1 ORDER BY categoria_postre"
)->fetchAll(PDO::FETCH_ASSOC);

$sabores = $conn->query(
    "SELECT id_sabor AS id, sabor AS nombre
       FROM sabor WHERE estado = 1 ORDER BY sabor"
)->fetchAll(PDO::FETCH_ASSOC);

/* ---------- Helper: arma la URL conservando los demás filtros ---------- */
function urlCatalogo(array $cambios = []): string
{
    $actual = array_merge($_GET, $cambios);
    unset($actual['agregado'], $actual['error'], $actual['agotado']);
    $actual = array_filter($actual, fn($v) => $v !== '' && $v !== 0 && $v !== '0' && $v !== null);
    return 'catalogo.php' . ($actual ? '?' . http_build_query($actual) : '');
}

$chipActivo = 'px-5 py-2 rounded-full font-semibold text-sm bg-[#8A5AAE] text-white shadow-sm shrink-0';
$chipNormal = 'px-5 py-2 rounded-full font-semibold text-sm bg-[#F1E2F6] hover:bg-[#E7D0EF] text-[#6E5A7A] hover:text-[#3A2545] shrink-0 transition-colors';
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-6 md:py-8 flex flex-col gap-8">

    <?php if (isset($_GET['agregado'])): ?>
        <div class="flex items-center gap-2 bg-[#5FA37A]/15 border border-[#5FA37A]/30 text-[#2E6B47] font-semibold text-sm px-4 py-3 rounded-2xl">
            <span class="material-symbols-outlined text-xl">check_circle</span>
            Producto agregado a tu carrito.
        </div>
    <?php elseif (isset($_GET['agotado'])): ?>
        <div class="flex items-center gap-2 bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D] font-semibold text-sm px-4 py-3 rounded-2xl">
            <span class="material-symbols-outlined text-xl">production_quantity_limits</span>
            Ese producto ya no tiene unidades disponibles.
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="flex items-center gap-2 bg-[#D9534F]/10 border border-[#D9534F]/30 text-[#A5312D] font-semibold text-sm px-4 py-3 rounded-2xl">
            <span class="material-symbols-outlined text-xl">error</span>
            Ese producto ya no está disponible.
        </div>
    <?php endif; ?>

    <!-- Banner de bienvenida -->
    <section class="w-full bg-[#F1E2F6] rounded-2xl md:rounded-3xl p-6 md:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm relative overflow-hidden border border-[#EED8F2]">
        <div class="flex items-center gap-4 z-10">
            <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-[#8A5AAE] shadow-sm shrink-0">
                <span class="material-symbols-outlined text-3xl">bakery_dining</span>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-[#3A2545] tracking-tight">
                    Postres artesanales recién horneados
                </h1>
                <p class="text-sm text-[#6E5A7A] mt-0.5">
                    Recetas de la casa, hechas por encargo y entregadas en el horario que elijas.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-full shadow-sm z-10 shrink-0 border border-[#EED8F2]">
            <span class="material-symbols-outlined text-[#8A5AAE]">local_shipping</span>
            <div class="flex flex-col">
                <span class="text-xs font-bold text-[#3A2545] flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-[#5FA37A]"></span> Envíos en Bogotá
                </span>
                <span class="text-[11px] text-[#6E5A7A]">Elige día y franja al pedir</span>
            </div>
        </div>
        <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-[#FAD3E1]/40 rounded-full blur-2xl pointer-events-none"></div>
    </section>

    <!-- Búsqueda, orden y filtros -->
    <section class="flex flex-col gap-6">

        <form method="get" action="catalogo.php" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <input type="hidden" name="categoria" value="<?= $idCat ?: '' ?>">
            <input type="hidden" name="sabor" value="<?= $idSabor ?: '' ?>">

            <div class="relative w-full md:max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#8A5AAE]">
                    <span class="material-symbols-outlined text-2xl">search</span>
                </div>
                <input type="search" name="q" value="<?= htmlspecialchars($q) ?>"
                       placeholder="Buscar por nombre o descripción..."
                       class="w-full pl-12 pr-6 py-3.5 bg-white rounded-full border border-[#EED8F2] focus:border-[#8A5AAE] focus:ring-4 focus:ring-[#8A5AAE]/20 text-[#3A2545] placeholder-[#6E5A7A] text-base shadow-sm transition-all outline-none">
            </div>

            <div class="flex items-center gap-3 self-end md:self-auto shrink-0 w-full sm:w-auto justify-end">
                <label class="text-xs font-bold text-[#6E5A7A] whitespace-nowrap" for="orden">Ordenar por:</label>
                <div class="relative">
                    <select id="orden" name="orden" onchange="this.form.submit()"
                            class="appearance-none bg-white border border-[#EED8F2] text-[#3A2545] font-semibold text-sm py-2.5 pl-4 pr-10 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8A5AAE] cursor-pointer">
                        <option value="nombre" <?= $orden === 'nombre' ? 'selected' : '' ?>>Nombre (A-Z)</option>
                        <option value="menor"  <?= $orden === 'menor'  ? 'selected' : '' ?>>Menor precio</option>
                        <option value="mayor"  <?= $orden === 'mayor'  ? 'selected' : '' ?>>Mayor precio</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[#8A5AAE] text-xl">keyboard_arrow_down</span>
                </div>
            </div>
        </form>

        <!-- Categorías -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
            <span class="text-xs font-bold text-[#6E5A7A] pr-2 hidden sm:inline">Categoría:</span>
            <a href="<?= urlCatalogo(['categoria' => null, 'limite' => null]) ?>"
               class="<?= $idCat === 0 ? $chipActivo : $chipNormal ?>">Todas</a>
            <?php foreach ($categorias as $c): ?>
                <a href="<?= urlCatalogo(['categoria' => $c['id'], 'limite' => null]) ?>"
                   class="<?= $idCat === (int)$c['id'] ? $chipActivo : $chipNormal ?>">
                    <?= htmlspecialchars($c['nombre']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Sabores -->
        <div class="flex flex-col gap-2">
            <span class="text-xs font-bold text-[#6E5A7A]">Filtrar por sabor:</span>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= urlCatalogo(['sabor' => null, 'limite' => null]) ?>"
                   class="px-4 py-2 rounded-full font-semibold text-xs transition-all <?= $idSabor === 0 ? 'bg-[#8A5AAE] text-white shadow-sm' : 'bg-[#F1E2F6] hover:bg-[#E7D0EF] text-[#6E5A7A]' ?>">
                    Todos
                </a>
                <?php foreach ($sabores as $s): ?>
                    <a href="<?= urlCatalogo(['sabor' => $s['id'], 'limite' => null]) ?>"
                       class="px-4 py-2 rounded-full font-semibold text-xs transition-all <?= $idSabor === (int)$s['id'] ? 'bg-[#8A5AAE] text-white shadow-sm' : 'bg-[#F1E2F6] hover:bg-[#E7D0EF] text-[#6E5A7A]' ?>">
                        <?= htmlspecialchars($s['nombre']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Tarjetas de producto: una sola plantilla que se repite por cada fila de la base de datos -->
    <section>
        <?php if (!$productos): ?>
            <div class="bg-white rounded-2xl border border-[#EED8F2] shadow-lila-soft p-10 text-center">
                <span class="material-symbols-outlined text-5xl text-[#C9A0DC]">search_off</span>
                <h2 class="text-lg font-bold text-[#3A2545] mt-3">No encontramos postres con esos filtros</h2>
                <p class="text-sm text-[#6E5A7A] mt-1">Prueba con otra búsqueda o quita algún filtro.</p>
                <a href="catalogo.php"
                   class="inline-block mt-5 px-6 py-2.5 rounded-full bg-[#8A5AAE] hover:bg-[#79479e] text-white font-semibold text-sm transition-colors">
                    Ver todo el catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($productos as $p):
                    $tieneFoto = !empty($p['imagen']);
                    $foto = $tieneFoto
                        ? '/Dulce_Micro/img_productos/' . rawurlencode($p['imagen'])
                        : $logoTienda;
                    // NUEVO: bandera de sin stock, usada en el badge y en el botón de abajo
                    $sinStock = (int)$p['stock'] <= 0;
                ?>
                    <article class="bg-white rounded-2xl p-4 flex flex-col justify-between shadow-lila-soft hover:shadow-lila-hover hover:-translate-y-1 transition-all duration-200 border border-[#EED8F2] group <?= $sinStock ? 'opacity-70' : '' ?>">
                        <div>
                            <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-[#FDF3F8] mb-4">
                                <img src="<?= htmlspecialchars($foto) ?>"
                                     alt="<?= htmlspecialchars($p['nombre_producto']) ?>"
                                     loading="lazy"
                                     class="w-full h-full <?= $tieneFoto ? 'object-cover' : 'object-contain p-10 opacity-80' ?> group-hover:scale-105 transition-transform duration-300">
                                <?php if (!empty($p['nombre_sabor']) && !$sinStock): ?>
                                    <span class="absolute top-3 left-3 bg-[#E685A8] text-[#3A2545] font-bold text-xs px-3 py-1 rounded-full shadow-sm">
                                        <?= htmlspecialchars($p['nombre_sabor']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($sinStock): ?>
                                    <span class="absolute top-3 right-3 bg-[#D9534F] text-white font-bold text-xs px-3 py-1 rounded-full shadow-sm">
                                        Agotado
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="font-bold text-lg text-[#3A2545] line-clamp-2 mb-1">
                                <?= htmlspecialchars($p['nombre_producto']) ?>
                            </h3>
                            <?php if (!empty($p['descripcion'])): ?>
                                <p class="text-sm text-[#6E5A7A] line-clamp-2 mb-3">
                                    <?= htmlspecialchars($p['descripcion']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="pt-3 border-t border-[#F1E2F6] flex items-center justify-between gap-2 mt-2">
                            <div>
                                <span class="text-[11px] text-[#6E5A7A] block font-bold">Precio</span>
                                <span class="font-extrabold text-lg text-[#3A2545]">
                                    $ <?= number_format((float)$p['precio_base'], 0, ',', '.') ?> COP
                                </span>
                            </div>

                            <?php if ($sinStock): ?>
                                <button type="button" disabled
                                        class="px-4 py-2.5 rounded-full bg-[#E2D6DE] text-[#8f7f8a] font-semibold text-sm cursor-not-allowed flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-lg">block</span>
                                    <span>Agotado</span>
                                </button>
                            <?php else: ?>
                                <form method="post" action="agregar_carrito.php">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                                    <button type="submit"
                                            class="px-4 py-2.5 rounded-full bg-[#8A5AAE] hover:bg-[#79479e] active:scale-95 text-white font-semibold text-sm shadow-sm transition-all flex items-center gap-1.5 focus:outline-none">
                                        <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                                        <span>Agregar</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Cargar más -->
    <?php if ($productos): ?>
        <div class="flex flex-col items-center justify-center pt-2 pb-4 gap-3">
            <?php if (count($productos) < $totalProductos): ?>
                <a href="<?= urlCatalogo(['limite' => $limite + 8]) ?>"
                   class="px-8 py-3.5 rounded-full border-2 border-[#8A5AAE] bg-white text-[#8A5AAE] hover:bg-[#F1E2F6] active:scale-95 transition-all shadow-sm flex items-center gap-2 font-bold text-sm">
                    <span class="material-symbols-outlined text-xl">autorenew</span>
                    <span>Cargar más postres</span>
                </a>
            <?php endif; ?>
            <span class="text-sm text-[#6E5A7A]">
                Mostrando <?= count($productos) ?> de <?= $totalProductos ?> creaciones artesanales
            </span>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
