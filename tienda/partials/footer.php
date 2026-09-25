<?php
// Pie de página compartido de la tienda. Cierra <body> y <html> abiertos en header.php.
$logoTienda = $logoTienda ?? '/Dulce_Micro/img_logos/logo%20principal.png';
?>

<!-- ================= PIE DE PÁGINA ================= -->
<footer class="w-full bg-[#F1E2F6] shadow-inner mt-12 border-t border-[#EED8F2]">
    <div class="w-full py-12 px-6 md:px-12 max-w-7xl mx-auto flex flex-col gap-8">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Marca -->
            <div class="flex flex-col gap-3 md:col-span-1">
                <div class="flex items-center gap-2">
                    <img src="<?= $logoTienda ?>" alt="Dulce Micro"
                         class="h-10 w-10 rounded-full bg-white object-cover shadow-sm">
                    <span class="text-xl font-extrabold text-[#3A2545]">Dulce Micro</span>
                </div>
                <p class="text-sm text-[#6E5A7A]">
                    Repostería fina y postres artesanales horneados con los mejores ingredientes de Colombia.
                </p>
            </div>

            <!-- Explorar -->
            <div class="flex flex-col gap-2.5">
                <h4 class="text-base font-bold text-[#3A2545]">Explorar</h4>
                <ul class="flex flex-col gap-2 text-sm">
                    <li><a class="text-[#8A5AAE] font-semibold hover:underline" href="/Dulce_Micro/tienda/catalogo.php">Catálogo Completo</a></li>
                    <li><a class="text-[#6E5A7A] hover:text-[#8A5AAE] transition-colors" href="/Dulce_Micro/tienda/promociones.php">Promociones de Temporada</a></li>
                    <li><a class="text-[#6E5A7A] hover:text-[#8A5AAE] transition-colors" href="#">Zonas de Entrega y Horarios</a></li>
                </ul>
            </div>

            <!-- Servicio -->
            <div class="flex flex-col gap-2.5">
                <h4 class="text-base font-bold text-[#3A2545]">Servicio</h4>
                <ul class="flex flex-col gap-2 text-sm">
                    <li><a class="text-[#6E5A7A] hover:text-[#8A5AAE] transition-colors" href="#">Métodos de Pago: PSE, Nequi, Daviplata</a></li>
                    <li><a class="text-[#6E5A7A] hover:text-[#8A5AAE] transition-colors" href="#">Soporte y Pedidos WhatsApp</a></li>
                    <li><a class="text-[#6E5A7A] hover:text-[#8A5AAE] transition-colors" href="#">Preguntas Frecuentes</a></li>
                </ul>
            </div>

            <!-- Transparencia -->
            <div class="flex flex-col gap-2.5">
                <h4 class="text-base font-bold text-[#3A2545]">Transparencia</h4>
                <ul class="flex flex-col gap-2 text-sm">
                    <li><a class="text-[#6E5A7A] hover:text-[#8A5AAE] transition-colors" href="#">Términos y Condiciones</a></li>
                </ul>
            </div>
        </div>

        <div class="pt-6 border-t border-[#EED8F2] flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-[#6E5A7A] text-center sm:text-left">
                © <?= date('Y') ?> Dulce Micro Repostería Artesanal.<span class="">•</span>Hecho con amor en Colombia.<span class="">•</span>Todos los derechos reservados.
            </p>
            <span class="text-xs font-semibold text-[#8A5AAE]">Bogotá D.C.</span>
        </div>
    </div>
</footer>
<!-- ================= /PIE DE PÁGINA ================= -->

</body>
</html>
