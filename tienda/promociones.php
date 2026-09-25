<?php
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/config_tienda.php';
require_once __DIR__ . '/fidelizacion.php';

$conn = (new conexion())->conn;

// Día de la semana de hoy: 1 = lunes ... 7 = domingo
$diaSemanaHoy = (int) date('N');
$promo2Activa = in_array($diaSemanaHoy, [2, 3, 4], true); // martes, miércoles, jueves

// Si hay un cliente en sesión, revisamos si estamos en su mes de cumpleaños
// para resaltarle el Combo Cumpleaños Mágico.
$esMesCumple = false;
if (!empty($_SESSION['id_cliente'])) {
    $st = $conn->prepare('SELECT fecha_nacimiento FROM clientes WHERE id_cliente = ?');
    $st->execute([(int) $_SESSION['id_cliente']]);
    $fnac = $st->fetchColumn();
    if ($fnac) {
        $esMesCumple = (new DateTime($fnac))->format('m') === date('m');
    }
}

$tituloPagina = 'Promociones';
$paginaActiva = 'promociones';

require_once __DIR__ . '/partials/header.php';
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-8 md:py-10">

    <!-- ================= ENCABEZADO PROMOCIONES ================= -->
    <section class="w-full flex flex-col gap-5">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">

            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E685A8]/20 text-[#8A5AAE] text-xs font-bold shadow-sm mb-2 border border-[#E685A8]/30">

                    <span class="material-symbols-outlined text-sm text-[#E685A8]">
                        local_offer
                    </span>

                    Promociones Especiales
                </span>

                <h1 class="text-2xl md:text-3xl font-bold text-[#3A2545] tracking-tight flex items-center gap-2">
                    Combos y Descuentos de Temporada
                    <span class="text-xl">✨</span>
                </h1>

                <p class="text-sm text-[#6E5A7A] mt-1">
                    Aprovecha ofertas exclusivas por tiempo limitado en repostería artesanal horneada hoy.
                </p>
            </div>

            <span class="text-xs font-semibold text-[#8A5AAE] bg-[#F1E2F6] px-3.5 py-1.5 rounded-full shrink-0 border border-[#EED8F2]">
                Válido hasta agotar existencias
            </span>

        </div>


        <!-- ================= TARJETAS DE PROMOCIONES ================= -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-2">


            <!-- =====================================================
                 PROMOCIÓN 1
                 COMBO CUMPLEAÑOS MÁGICO
                 (se resalta si es el mes de cumpleaños del cliente logueado)
            ====================================================== -->
            <article
                class="relative bg-gradient-to-br from-white to-[#FDF3F8]
                       rounded-2xl p-5
                       border-2 <?= $esMesCumple ? 'border-[#E685A8] ring-2 ring-[#E685A8]/40' : 'border-[#E685A8]' ?>
                       shadow-lila-soft
                       hover:shadow-lila-hover
                       hover:-translate-y-1
                       transition-all duration-200
                       flex flex-col justify-between
                       overflow-hidden group">

                <!-- Decoración -->
                <div class="absolute -right-6 -bottom-6 w-24 h-24
                            bg-[#E685A8]/10 rounded-full blur-xl
                            pointer-events-none">
                </div>


                <div>

                    <!-- Etiquetas -->
                    <div class="flex items-center justify-between gap-2 mb-3">

                        <span class="bg-[#E685A8] text-white font-extrabold text-xs px-3 py-1 rounded-full shadow-sm">
                            20% OFF
                        </span>

                        <span class="text-[11px] font-bold text-[#8A5AAE] uppercase tracking-wider">
                            Combo Dulce
                        </span>

                    </div>

                    <?php if ($esMesCumple): ?>
                        <div class="mb-3 inline-flex items-center gap-1.5 bg-[#FADBE8] text-[#8A5AAE] text-[11px] font-bold px-3 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm">celebration</span>
                            ¡Es tu mes! Este combo es para ti 🎂
                        </div>
                    <?php endif; ?>

                    <!-- Título -->
                    <h2 class="text-lg font-bold text-[#3A2545]
                               group-hover:text-[#8A5AAE]
                               transition-colors mb-1.5">

                        Combo Cumpleaños Mágico

                    </h2>


                    <!-- Descripción -->
                    <p class="text-xs text-[#6E5A7A] mb-4 leading-relaxed">

                        Torta Frutos Rojos (1 lb) + 6 Cupcakes artesanales
                        decorados con crema ligera.

                    </p>


                    <!-- Cupón -->
                    <div class="bg-white/80 rounded-xl p-2.5
                                border border-[#EED8F2]
                                flex items-center justify-between
                                gap-2 mb-4">

                        <span class="text-xs text-[#6E5A7A]">
                            Cupón:
                        </span>

                        <span class="font-mono font-bold text-xs
                                     bg-[#F1E2F6]
                                     text-[#8A5AAE]
                                     px-2.5 py-1 rounded
                                     border border-[#8A5AAE]/20
                                     tracking-wider">

                            DULCECUMPLE

                        </span>

                    </div>

                </div>


                <!-- Precio y botón -->
                <div class="pt-3 border-t border-[#F1E2F6]
                            flex items-center justify-between
                            gap-3">

                    <div>

                        <span class="text-xs text-[#6E5A7A]
                                     line-through block leading-none">

                            $ 75.000 COP

                        </span>

                        <span class="font-extrabold text-lg text-[#3A2545]">

                            $ 60.000 COP

                        </span>

                    </div>


                    <a href="/Dulce_Micro/tienda/catalogo.php"
                       class="px-4 py-2 rounded-full
                              bg-[#8A5AAE]
                              hover:bg-[#79479e]
                              active:scale-95
                              text-white font-semibold text-xs
                              shadow-sm transition-all
                              flex items-center gap-1">

                        <span>
                            Aprovechar Promo
                        </span>

                        <span class="material-symbols-outlined text-sm">
                            arrow_forward
                        </span>

                    </a>

                </div>

            </article>



            <!-- =====================================================
                 PROMOCIÓN 2
                 2x1 EN POSTRES EN VASO
                 (solo activa martes, miércoles y jueves)
            ====================================================== -->
            <article
                class="relative bg-gradient-to-br from-white to-[#F1E2F6]
                       rounded-2xl p-5
                       border border-[#EED8F2]
                       shadow-lila-soft
                       hover:shadow-lila-hover
                       hover:-translate-y-1
                       transition-all duration-200
                       flex flex-col justify-between
                       overflow-hidden group
                       <?= $promo2Activa ? '' : 'opacity-60 grayscale-[30%]' ?>">

                <!-- Decoración -->
                <div class="absolute -right-6 -bottom-6 w-24 h-24
                            bg-[#8A5AAE]/10 rounded-full blur-xl
                            pointer-events-none">
                </div>


                <div>

                    <!-- Etiquetas -->
                    <div class="flex items-center justify-between gap-2 mb-3">

                        <span class="bg-[#8A5AAE]
                                     text-white font-extrabold text-xs
                                     px-3 py-1 rounded-full shadow-sm
                                     flex items-center gap-1">

                            <span class="material-symbols-outlined text-xs">
                                favorite
                            </span>

                            Favorito

                        </span>


                        <span class="text-[11px] font-bold text-[#E685A8]
                                     uppercase tracking-wider">

                            2x1 Martes a Jueves

                        </span>

                    </div>

                    <?php if ($promo2Activa): ?>
                        <div class="mb-3 inline-flex items-center gap-1.5 bg-[#5FA37A]/15 text-[#2E6B47] text-[11px] font-bold px-3 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Disponible hoy
                        </div>
                    <?php else: ?>
                        <div class="mb-3 inline-flex items-center gap-1.5 bg-[#D9534F]/10 text-[#A5312D] text-[11px] font-bold px-3 py-1 rounded-full">
                            <span class="material-symbols-outlined text-sm">schedule</span>
                            Vuelve martes a jueves
                        </div>
                    <?php endif; ?>

                    <!-- Título -->
                    <h2 class="text-lg font-bold text-[#3A2545]
                               group-hover:text-[#8A5AAE]
                               transition-colors mb-1.5">

                        2x1 en Postres en Vaso

                    </h2>


                    <!-- Descripción -->
                    <p class="text-xs text-[#6E5A7A] mb-4 leading-relaxed">

                        Válido en Tiramisú de café huilense y Cheesecake
                        frío de maracuyá artesanal.

                    </p>


                    <!-- Información -->
                    <div class="bg-white/80 rounded-xl p-2.5
                                border border-[#EED8F2]
                                flex items-center gap-2 mb-4
                                text-xs text-[#6E5A7A]">

                        <span class="material-symbols-outlined
                                     text-[#8A5AAE] text-base">

                            calendar_month

                        </span>

                        <span>
                            Aplica automáticamente en carrito
                        </span>

                    </div>

                </div>


                <!-- Precio y botón -->
                <div class="pt-3 border-t border-[#F1E2F6]
                            flex items-center justify-between
                            gap-3">

                    <div>

                        <span class="text-[11px] text-[#6E5A7A]
                                     block uppercase font-bold">

                            2 unidades

                        </span>

                        <span class="font-extrabold text-lg text-[#3A2545]">

                            $ 39.000 COP

                        </span>

                    </div>

                    <?php if ($promo2Activa): ?>
                        <a href="/Dulce_Micro/tienda/catalogo.php"
                           class="px-4 py-2 rounded-full
                                  bg-[#8A5AAE]
                                  hover:bg-[#79479e]
                                  active:scale-95
                                  text-white font-semibold text-xs
                                  shadow-sm transition-all
                                  flex items-center gap-1">

                            <span>
                                Ver Promo
                            </span>

                            <span class="material-symbols-outlined text-sm">
                                arrow_forward
                            </span>

                        </a>
                    <?php else: ?>
                        <span class="px-4 py-2 rounded-full
                                     border border-[#8A5AAE]/30
                                     text-[#8A5AAE]/60
                                     font-semibold text-xs
                                     cursor-not-allowed
                                     flex items-center gap-1">

                            No disponible hoy
                        </span>
                    <?php endif; ?>

                </div>

            </article>



            <!-- =====================================================
                 PROMOCIÓN 3
                 ENVÍO GRATIS
                 (sin restricción de día, aplica siempre por monto de compra)
            ====================================================== -->
            <article
                class="relative bg-gradient-to-br from-white to-[#FDF3F8]
                       rounded-2xl p-5
                       border border-[#EED8F2]
                       shadow-lila-soft
                       hover:shadow-lila-hover
                       hover:-translate-y-1
                       transition-all duration-200
                       flex flex-col justify-between
                       overflow-hidden group">

                <!-- Decoración -->
                <div class="absolute -right-6 -bottom-6 w-24 h-24
                            bg-[#E685A8]/10 rounded-full blur-xl
                            pointer-events-none">
                </div>


                <div>

                    <!-- Etiquetas -->
                    <div class="flex items-center justify-between gap-2 mb-3">

                        <span class="bg-white text-[#8A5AAE]
                                     font-bold text-xs px-3 py-1
                                     rounded-full shadow-sm
                                     border border-[#EED8F2]
                                     flex items-center gap-1">

                            <span class="material-symbols-outlined
                                         text-xs text-[#8A5AAE]">

                                bolt

                            </span>

                            Envío Express

                        </span>


                        <span class="text-[11px] font-bold
                                     text-[#8A5AAE]
                                     uppercase tracking-wider">

                            Sin Costo Extra

                        </span>

                    </div>


                    <!-- Título -->
                    <h2 class="text-lg font-bold text-[#3A2545]
                               group-hover:text-[#8A5AAE]
                               transition-colors mb-1.5">

                        Envío Gratis en Tu Pedido

                    </h2>


                    <!-- Descripción -->
                    <p class="text-xs text-[#6E5A7A]
                              mb-4 leading-relaxed">

                        Por compras superiores a $80.000 COP en toda
                        la cobertura de Bogotá y municipios de la Sabana.

                    </p>


                    <!-- Información -->
                    <div class="bg-white/80 rounded-xl p-2.5
                                border border-[#EED8F2]
                                flex items-center gap-2 mb-4
                                text-xs text-[#6E5A7A]">

                        <span class="material-symbols-outlined
                                     text-[#8A5AAE] text-base">

                            check_circle

                        </span>

                        <span>
                            Cupón automático al finalizar pedido
                        </span>

                    </div>

                </div>


                <!-- Precio y botón -->
                <div class="pt-3 border-t border-[#F1E2F6]
                            flex items-center justify-between
                            gap-3">

                    <div>

                        <span class="text-[11px] text-[#6E5A7A]
                                     block uppercase font-bold">

                            Ahorro estimado

                        </span>

                        <span class="font-extrabold text-lg text-[#E685A8]">

                            Hasta $ 12.000

                        </span>

                    </div>


                    <a href="/Dulce_Micro/tienda/catalogo.php"
                       class="px-4 py-2 rounded-full
                              border border-[#8A5AAE]
                              text-[#8A5AAE]
                              hover:bg-[#8A5AAE]
                              hover:text-white
                              active:scale-95
                              font-semibold text-xs
                              transition-all
                              flex items-center gap-1">

                        <span>
                            Comprar Ahora
                        </span>

                        <span class="material-symbols-outlined text-sm">
                            shopping_basket
                        </span>

                    </a>

                </div>

            </article>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
