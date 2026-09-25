<?php
include __DIR__ . '/vista/partials/header.php';

require_once __DIR__ . "/config/conexion.php";
$conn = (new conexion())->conn;

// ---- KPIs ----
$totalPedidos = $conn->query("SELECT COUNT(*) FROM pedido_completo WHERE estado = 1")->fetchColumn();

$ingresosMes = $conn->query(
    "SELECT COALESCE(SUM(monto),0) FROM pagos
     WHERE estado = 1 AND MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())"
)->fetchColumn();

$totalClientes = $conn->query("SELECT COUNT(*) FROM clientes WHERE estado = 1")->fetchColumn();

$usuariosActivos = $conn->query("SELECT COUNT(*) FROM usuarios WHERE estado != 'INACTIVO'")->fetchColumn();

// ---- Grafica: pedidos por estado ----
$stmt = $conn->query(
    "SELECT ep.estado_pedido AS nombre, COUNT(pc.id_pedido) AS total
     FROM estado_pedido ep
     LEFT JOIN pedido_completo pc ON pc.id_estado_pedido = ep.id_estado_pedido AND pc.estado = 1
     WHERE ep.estado = 1
     GROUP BY ep.id_estado_pedido, ep.estado_pedido"
);
$chartLabels = [];
$chartData = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $chartLabels[] = $row['nombre'];
    $chartData[] = (int) $row['total'];
}

$modulos = [
    "Roles" => "roles.php",
    "Sabor" => "sabor.php",
    "UnidadMedida" => "unidadmedida.php",
    "Tortas" => "tortas.php",
    "Decoracion" => "decoracion.php",
    "PostreIndividual" => "postresindividuales.php",
    "TipoDocumento" => "tipodocumento.php",
    "Cliente" => "clientes.php",
    "CategoriaPostre" => "categoriapostre.php",
    "Contabilidad" => "contabilidad.php",
    "EstadoPedido" => "estadopedido.php",
    "PedidoCompleto" => "pedidocompleto.php",
    "Repostera" => "reposteras.php",
    "Pago" => "pagos.php",
    "EstadoPago" => "estadopagos.php",
    "MetodoPago" => "metodospago.php",
    "Usuario" => "usuarios.php",
    "Inventario" => "inventario.php",
    "Tamaño" => "tamano.php",
    "MateriaPrima" => "materiaprima.php",
    "GastoDecoracion" => "gastodecoracion.php",
    "TablaMaestra" => "tablamaestra.php",
    "Receta" => "receta.php",
    "Producción" => "produccion.php",
    "Informe" => "informes.php",

    // Módulo principal de productos
    "Producto" => "producto.php",
];
?>

<h1 class="mb-4">Dashboard Gráfica</h1>

<div class="row g-3 mb-4">
    
<!-- PEDIDOS TOTALES -->
<div class="col-md-3">
    <a href="vista/pedidocompleto.php" class="text-decoration-none">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <div class="text-uppercase small">Pedidos totales</div>
                <div class="fs-2 fw-bold">
                    <?= htmlspecialchars($totalPedidos) ?>
                </div>
                <div class="mt-2">
                    <small>Ver pedidos →</small>
                </div>
            </div>
        </div>
    </a>
</div>

<!-- INGRESOS DEL MES -->
<div class="col-md-3">
    <a href="vista/contabilidad.php" class="text-decoration-none">
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <div class="text-uppercase small">Ingresos del mes</div>
                <div class="fs-2 fw-bold">
                    $<?= number_format($ingresosMes, 2) ?>
                </div>
                <div class="mt-2">
                    <small>Ver contabilidad →</small>
                </div>
            </div>
        </div>
    </a>
</div>

<!-- CLIENTES -->
<div class="col-md-3">
    <a href="vista/clientes.php" class="text-decoration-none">
        <div class="card text-bg-info h-100">
            <div class="card-body">
                <div class="text-uppercase small">Clientes registrados</div>
                <div class="fs-2 fw-bold">
                    <?= htmlspecialchars($totalClientes) ?>
                </div>
                <div class="mt-2">
                    <small>Ver clientes →</small>
                </div>
            </div>
        </div>
    </a>
</div>

<!-- USUARIOS -->
<div class="col-md-3">
    <a href="vista/usuarios.php" class="text-decoration-none">
        <div class="card text-bg-warning h-100">
            <div class="card-body">
                <div class="text-uppercase small">Usuarios activos</div>
                <div class="fs-2 fw-bold">
                    <?= htmlspecialchars($usuariosActivos) ?>
                </div>
                <div class="mt-2">
                    <small>Ver usuarios →</small>
                </div>
            </div>
        </div>
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Pedidos por estado</h5>
        <canvas id="graficaPedidos" height="90"></canvas>
    </div>
</div>


<h2 class="mb-3">Modulos</h2>
<div class="row row-cols-1 row-cols-md-3 g-3">
    <?php foreach ($modulos as $nombre => $archivo): ?>
    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?= $nombre ?></h5>
                <a href="vista/<?= $archivo ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-arrow-right"></i> Entrar
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
const coloresEstados = {
    'Pendiente': '#dc3545',      // Rojo
    'En preparación': '#fd7e14', // Naranja
    'Preparación': '#fd7e14',    // Naranja
    'Completado': '#0d6efd',     // Azul
    'Enviado': '#6f42c1',        // Morado
    'Entregado': '#198754'       // Verde
};

// Asigna un color dependiendo del estado
const coloresGrafica = <?= json_encode($chartLabels) ?>.map(estado => {
    return coloresEstados[estado] ?? '#6c757d';
});

new Chart(document.getElementById('graficaPedidos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Pedidos',
            data: <?= json_encode($chartData) ?>,
            backgroundColor: coloresGrafica,
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php include __DIR__ . '/vista/partials/footer.php'; ?>