<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("LOGIN");
    session_start();
}

$tiempo_limite = 900;
if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > $tiempo_limite)) {
    session_unset();
    session_destroy();
    header("Location: /Dulce_Micro/vista/login.php?expirada=1");
    exit();
}
$_SESSION['ultima_actividad'] = time();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---- Avatar actual del usuario ----
require_once __DIR__ . '/../../config/conexion.php';
$conn = (new conexion())->conn;
$stmtAvatar = $conn->prepare("SELECT avatar FROM usuarios WHERE id_usuario = ?");
$stmtAvatar->execute([$_SESSION['id_usuario']]);
$avatarActual = $stmtAvatar->fetchColumn() ?: 'undraw_profile_man1.svg';

$avataresDisponibles = [
    'undraw_profile_man1.svg',
    'undraw_profile_man2.svg',
    'undraw_profile_woman1.svg',
    'undraw_profile_woman2.svg',
];

// Para resaltar el enlace activo en el sidebar
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dulce Micro</title>
    <link rel="icon" type="image/png" href="/Dulce_Micro/img_logos/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/223d4f4ee1.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --dm-rosa: #f7a8c4;
            --dm-morado: #c9a0dc;
            --dm-rosa-fuerte: #e685a8;
            --dm-morado-fuerte: #a879c9;
        }
        body { background-color: #fdf3f8; }
        #wrapper { display: flex; align-items: stretch; min-height: 100vh; }

        /* ---- Sidebar ---- */
        .sidebar {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 224px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--dm-rosa) 0%, var(--dm-morado) 100%);
        }
        .sidebar .sidebar-brand {
            padding: 1.2rem 1rem;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,.2);
        }
        .sidebar .sidebar-brand img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: .6rem;
            background: #fff;
            object-fit: cover;
        }
        .sidebar .sidebar-heading {
            color: rgba(255,255,255,.75);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08rem;
            padding: 1rem 1rem .25rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.9);
            padding: .6rem 1rem;
            font-size: .9rem;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link i { width: 1.4rem; text-align: center; margin-right: .4rem; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.15); color: #fff; }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.25);
            color: #fff;
            border-left-color: #fff;
            font-weight: 600;
        }

        /* ---- Topbar ---- */
        .topbar {
            background: linear-gradient(90deg, var(--dm-rosa) 0%, var(--dm-morado) 100%);
            box-shadow: 0 .15rem 1.75rem rgba(0,0,0,.06);
        }
        .topbar .btn-outline-light {
            background: var(--dm-morado-fuerte);
            border-color: var(--dm-morado-fuerte);
            color: #fff;
        }
        .topbar .btn-outline-light:hover {
            background: var(--dm-rosa-fuerte);
            border-color: var(--dm-rosa-fuerte);
            color: #fff;
        }

        #content-wrapper {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;

    background-color: #d9b6e5;
    background-image:
        radial-gradient(circle at 10% 15%, rgba(247,168,196,.35) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(168,121,201,.30) 0%, transparent 22%),
        linear-gradient(135deg, #e8c8ed 0%, #d5aee0 50%, #c494d2 100%);

    background-attachment: fixed;
}
    </style>
</head>
<body>

<div id="wrapper">

    <!-- ===== Sidebar ===== -->
    <ul class="sidebar">
        <a class="sidebar-brand" href="/Dulce_Micro/index.php">
            <img src="/Dulce_Micro/img_logos/logo principal.png"
                 alt="Logotipo de Dulce Micro: una porción de pastel con glaseado morado, chispas de colores y un corazón rosado encima, sobre un plato blanco, con el texto Dulce Micro en letras rosadas y moradas dentro de un círculo rosa.">
            Dulce Micro
        </a>

        <div class="sidebar-heading">Principal</div>
        <li><a class="nav-link <?= $paginaActual === 'index.php' ? 'active' : '' ?>" href="/Dulce_Micro/index.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>

        <div class="sidebar-heading">Ventas</div>
        <li><a class="nav-link <?= $paginaActual === 'pedidocompleto.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/pedidocompleto.php"><i class="fa-solid fa-cart-shopping"></i> Pedidos</a></li>
        <li><a class="nav-link <?= $paginaActual === 'nuevaventa.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/nuevaventa.php"><i class="fa-solid fa-cash-register"></i> Nueva Venta</a></li>
        <li><a class="nav-link <?= $paginaActual === 'clientes.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/clientes.php"><i class="fa-solid fa-users"></i> Clientes</a></li>
        <li><a class="nav-link <?= $paginaActual === 'contabilidad.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/contabilidad.php"><i class="fa-solid fa-coins"></i> Contabilidad</a></li>
        <li><a class="nav-link <?= $paginaActual === 'pagos.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/pagos.php"><i class="fa-solid fa-money-check-dollar"></i> Pagos</a></li>

        <div class="sidebar-heading">Catálogo</div>
        <li><a class="nav-link <?= $paginaActual === 'producto.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/producto.php"><i class="fa-solid fa-cake-candles"></i> Productos</a></li>
        <li><a class="nav-link <?= $paginaActual === 'tortas.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/tortas.php"><i class="fa-solid fa-birthday-cake"></i> Tortas</a></li>
        <li><a class="nav-link <?= $paginaActual === 'postresindividuales.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/postresindividuales.php"><i class="fa-solid fa-ice-cream"></i> Postres individuales</a></li>
        <li><a class="nav-link <?= $paginaActual === 'decoracion.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/decoracion.php"><i class="fa-solid fa-palette"></i> Decoración</a></li>
        <li><a class="nav-link <?= $paginaActual === 'sabor.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/sabor.php"><i class="fa-solid fa-mortar-pestle"></i> Sabores</a></li>

        <div class="sidebar-heading">Inventario</div>
        <li><a class="nav-link <?= $paginaActual === 'inventario.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/inventario.php"><i class="fa-solid fa-boxes-stacked"></i> Inventario</a></li>
        <li><a class="nav-link <?= $paginaActual === 'materiaprima.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/materiaprima.php"><i class="fa-solid fa-wheat-awn"></i> Materia prima</a></li>
        <li><a class="nav-link <?= $paginaActual === 'receta.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/receta.php"><i class="fa-solid fa-book-open"></i> Recetas</a></li>

        <div class="sidebar-heading">Administración</div>
<li><a class="nav-link <?= $paginaActual === 'usuarios.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/usuarios.php"><i class="fa-solid fa-user-gear"></i> Usuarios</a></li>
<li><a class="nav-link <?= $paginaActual === 'roles.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/roles.php"><i class="fa-solid fa-shield-halved"></i> Roles</a></li>
<li><a class="nav-link <?= $paginaActual === 'tablamaestra.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/tablamaestra.php"><i class="fa-solid fa-table-list"></i> Tabla maestra</a></li>
<li><a class="nav-link <?= $paginaActual === 'informes.php' ? 'active' : '' ?>" href="/Dulce_Micro/vista/informes.php"><i class="fa-solid fa-file-invoice-dollar"></i> Informes</a></li>
    </ul>
    <!-- ===== /Sidebar ===== -->

    <div id="content-wrapper">
        <div id="content">

            <!-- ===== Topbar ===== -->
            <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top">
                <div class="container-fluid justify-content-end">
                    <div class="d-flex align-items-center">
                        <img src="/Dulce_Micro/img/<?= htmlspecialchars($avatarActual) ?>"
                             id="avatarActualImg"
                             alt="Avatar"
                             class="rounded-circle me-2"
                             style="width:36px; height:36px; object-fit:cover; cursor:pointer; border:2px solid var(--dm-morado);"
                             data-bs-toggle="modal"
                             data-bs-target="#modalAvatar"
                             title="Cambiar foto de perfil">
                        <span class="me-3" style="color: rgba(255,255,255,.9);">Hola, <?= htmlspecialchars($_SESSION['Nombre']) ?></span>
                        <a href="/Dulce_Micro/vista/logout.php" class="btn btn-sm btn-outline-light">
                            <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesion
                        </a>
                    </div>
                </div>
            </nav>
            <!-- ===== /Topbar ===== -->

            <!-- Modal para elegir avatar -->
            <div class="modal fade" id="modalAvatar" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(90deg, var(--dm-rosa), var(--dm-morado)); color:#fff;">
                            <h5 class="modal-title">Elige tu foto de perfil</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body d-flex justify-content-around flex-wrap gap-3">
                            <?php foreach ($avataresDisponibles as $archivoAvatar): ?>
                                <img src="/Dulce_Micro/img/<?= htmlspecialchars($archivoAvatar) ?>"
                                     class="avatar-opcion rounded-circle <?= $archivoAvatar === $avatarActual ? 'border border-3 border-primary' : 'border' ?>"
                                     data-avatar="<?= htmlspecialchars($archivoAvatar) ?>"
                                     style="width:90px; height:90px; object-fit:cover; cursor:pointer;">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">