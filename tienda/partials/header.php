    <?php
    /**
     * Encabezado compartido de la tienda (catálogo, carrito, perfil, mis pedidos, confirmación).
     *
     * Antes de incluirlo, cada página puede definir:
     *   $tituloPagina  -> texto del <title>
     *   $paginaActiva  -> 'catalogo' | 'pedidos' | ''  (resalta el enlace del menú)
     */
    // Sesión propia de la tienda (distinta a la del panel interno), cookie segura y token CSRF
    require_once __DIR__ . '/../sesion.php';

    $tituloPagina  = $tituloPagina ?? 'Dulce Micro';
    $paginaActiva  = $paginaActiva ?? '';
    $totalCarrito  = array_sum($_SESSION['carrito'] ?? []);
    $nombreCliente = $_SESSION['cliente_nombre'] ?? null;   // se llenará con el login de clientes
    $inicial       = $nombreCliente ? mb_strtoupper(mb_substr($nombreCliente, 0, 1)) : '';
    $logoTienda    = '/Dulce_Micro/img_logos/logo%20principal.png';

    $claseActivo   = 'text-white font-bold border-b-2 border-white pb-1 drop-shadow-sm';
    $claseNormal   = 'text-white/90 hover:text-white transition-colors';
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($tituloPagina) ?> | Dulce Micro</title>
        <link rel="icon" type="image/png" href="/Dulce_Micro/img_logos/favicon.png">

        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">

        <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        borderRadius: { DEFAULT: '1rem', '2xl': '1.5rem', '3xl': '2rem' },
                        boxShadow: {
                            'lila-soft':  '0 4px 16px -2px rgba(138, 90, 174, 0.10)',
                            'lila-hover': '0 10px 24px -4px rgba(138, 90, 174, 0.18)'
                        }
                    }
                }
            }
        </script>
        <style>
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
                display: inline-block;
                vertical-align: middle;
                line-height: 1;
            }
            .custom-scrollbar::-webkit-scrollbar { height: 6px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #F1E2F6; border-radius: 9999px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #C9A0DC; border-radius: 9999px; }
        </style>
    </head>
    <body class="bg-[#FDF3F8] text-[#3A2545] font-['Plus_Jakarta_Sans',sans-serif] antialiased min-h-screen flex flex-col">

    <!-- ================= BARRA SUPERIOR ================= -->
    <header class="sticky top-0 z-50 w-full shadow-sm" style="background: linear-gradient(90deg, #941E4A 0%, #603380 100%);">
        <div class="max-w-7xl mx-auto px-6 md:px-8 h-20 flex items-center justify-between gap-4">

            <!-- Logo -->
            <a class="flex items-center gap-3 shrink-0 focus:outline-none" href="/Dulce_Micro/tienda/catalogo.php">
                <img src="<?= $logoTienda ?>" alt="Dulce Micro"
                    class="h-14 w-14 rounded-full bg-white object-cover shadow-sm">
                <span class="hidden sm:inline text-2xl font-extrabold tracking-tight text-white drop-shadow-sm">Dulce Micro</span>
            </a>

            <!-- Menú -->
            <nav class="hidden md:flex items-center gap-8 font-semibold text-sm">
                <a class="<?= $paginaActiva === 'catalogo' ? $claseActivo : $claseNormal ?>"
                href="/Dulce_Micro/tienda/catalogo.php">Catálogo</a>
                <?php if ($nombreCliente): ?>
                    <a class="<?= $paginaActiva === 'pedidos' ? $claseActivo : $claseNormal ?>"
                    href="/Dulce_Micro/tienda/mis_pedidos.php">Mis pedidos</a>
                <?php endif; ?>
                <a class="<?= $paginaActiva === 'promociones' ? $claseActivo : $claseNormal ?>"href="/Dulce_Micro/tienda/promociones.php">Promociones</a>
                <a class="<?= $claseNormal ?>" href="#">Sobre Nosotros</a>
                <a class="<?= $claseNormal ?>" href="#">Contacto</a>
            </nav>

            <!-- Carrito y usuario -->
            <div class="flex items-center gap-3 md:gap-4">
                <a href="/Dulce_Micro/tienda/carrito.php" aria-label="Carrito de compras"
                class="relative p-2.5 rounded-full text-white hover:bg-white/20 transition-all duration-200">
                    <span class="material-symbols-outlined text-2xl">shopping_bag</span>
                    <?php if ($totalCarrito > 0): ?>
                        <span class="absolute top-1 right-1 bg-[#E685A8] text-[#3A2545] text-xs font-bold min-w-5 h-5 px-1 rounded-full flex items-center justify-center shadow-md border border-white/40">
                            <?= (int)$totalCarrito ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="flex items-center gap-2 pl-2 border-l border-white/30">
                    <?php if ($nombreCliente): ?>
                        <a href="/Dulce_Micro/tienda/perfil.php"
                        class="flex items-center gap-2.5 py-1 px-3 rounded-full bg-white/20 hover:bg-white/30 transition-colors">
                            <span class="w-8 h-8 rounded-full bg-white text-[#8A5AAE] flex items-center justify-center font-bold text-sm shadow-sm">
                                <?= htmlspecialchars($inicial) ?>
                            </span>
                            <span class="hidden sm:inline font-semibold text-sm text-white">
                                Hola, <?= htmlspecialchars(explode(' ', $nombreCliente)[0]) ?>
                            </span>
                        </a>
                        <a href="/Dulce_Micro/tienda/logout.php" title="Cerrar sesión" aria-label="Cerrar sesión"
                        class="p-2 rounded-full text-white hover:bg-white/20 transition-all">
                            <span class="material-symbols-outlined text-[22px]">logout</span>
                        </a>
                    <?php else: ?>
                        <a href="/Dulce_Micro/tienda/login.php"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-[#8A5AAE] hover:shadow-md font-semibold text-sm transition-all">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                            <span>Ingresar</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <!-- ================= /BARRA SUPERIOR ================= -->
