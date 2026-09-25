<?php
/**
 * Ajustes y funciones compartidas por carrito, checkout, pedidos y factura.
 * Cambia aquí los valores propios de tu negocio.
 */
date_default_timezone_set('America/Bogota');

/* ---------------- Datos del negocio (salen en la factura) ---------------- */
const NEGOCIO_NOMBRE   = 'Dulce Micro';
const NEGOCIO_SLOGAN   = 'Repostería artesanal';
const NEGOCIO_NIT      = '';    // opcional, ej: '900.123.456-7'. Vacío = no se muestra
const NEGOCIO_CONTACTO = '';    // opcional, ej: 'WhatsApp +57 300 000 0000'
const WHATSAPP_TIENDA  = '573104746216';    // solo dígitos con indicativo, ej: '573001234567'. Vacío = sin botón

/* ---------------- Envío y entregas ---------------- */
const ENVIO_COSTO        = 8000;     // costo de envío en COP
const ENVIO_GRATIS_DESDE = 150000;   // envío gratis desde este subtotal
const MAX_POR_PRODUCTO   = 20;       // máximo de unidades de un mismo producto
const DIAS_ANTICIPACION  = 1;        // primer día disponible: 1 = mañana
const DIAS_VISIBLES      = 8;        // cuántos días se ofrecen para entrega
const CIUDADES_ENTREGA   = ['Bogotá D.C.'];   // agrega aquí las ciudades donde entregas

const FRANJAS = [
    'manana' => ['Mañana', '9:00 AM - 1:00 PM'],
    'tarde'  => ['Tarde',  '2:00 PM - 6:00 PM'],
];

/* ---------------- Pagos ----------------
 * true  = MODO DE PRUEBA: los pagos electrónicos (PSE, Nequi, tarjetas...) se
 *         marcan como "Completado" sin cobrar nada. Sirve para demostrar el flujo.
 * false = todos los pagos quedan "Pendiente" hasta que alguien los confirme
 *         en el panel. Úsalo cuando no haya pasarela de pagos real.
 * El pago en efectivo siempre queda "Pendiente" (se cobra al entregar). */
const MODO_PAGO_SIMULADO = true;

/* IDs de tus tablas estado_pedido y estado_pagos (verificados en phpMyAdmin) */
const ESTADO_PEDIDO_PENDIENTE = 1;
const ESTADO_PAGO_PENDIENTE   = 1;
const ESTADO_PAGO_COMPLETADO  = 3;

const DIAS_SEMANA = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const MESES       = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
const DIAS_LARGOS  = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const MESES_LARGOS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

/** 48000 -> "$ 48.000 COP" */
function formatoCOP(float $valor): string
{
    return '$ ' . number_format($valor, 0, ',', '.') . ' COP';
}

/** "2026-09-25" -> "Viernes, 25 de Septiembre de 2026" */
function fechaLarga(string $ymd): string
{
    $d = DateTime::createFromFormat('Y-m-d', $ymd);
    if (!$d) {
        return $ymd;
    }
    return DIAS_LARGOS[(int)$d->format('w')] . ', ' . $d->format('j') . ' de '
         . MESES_LARGOS[(int)$d->format('n') - 1] . ' de ' . $d->format('Y');
}

/** Costo de envío según el subtotal. */
function costoEnvio(float $subtotal): int
{
    return $subtotal >= ENVIO_GRATIS_DESDE ? 0 : ENVIO_COSTO;
}

/** Días ofrecidos para la entrega, empezando en DIAS_ANTICIPACION. */
function diasDisponibles(): array
{
    $dias = [];
    $hoy  = new DateTime('today');
    for ($i = DIAS_ANTICIPACION; $i < DIAS_ANTICIPACION + DIAS_VISIBLES; $i++) {
        $d = (clone $hoy)->modify("+$i day");
        $diaSemana = DIAS_SEMANA[(int)$d->format('w')];
        $dias[] = [
            'valor'    => $d->format('Y-m-d'),
            'etiqueta' => $i === 1 ? 'Mañana' : $diaSemana,
            'numero'   => $d->format('j'),
            'mes'      => MESES[(int)$d->format('n') - 1],
        ];
    }
    return $dias;
}

/**
 * Título, descripción e ícono con que se muestra cada método de pago.
 * Los métodos salen de tu tabla metodos_pago: si agregas uno nuevo, aparece solo
 * (con un ícono genérico si no está en esta lista).
 */
function infoMetodoPago(string $nombre): array
{
    $n = mb_strtolower($nombre);
    return match (true) {
        str_contains($n, 'efectivo')      => ['Pago contra entrega', 'Pagas en efectivo cuando recibes tu pedido', 'payments'],
        str_contains($n, 'nequi')         => [$nombre, 'Pago con tu billetera Nequi', 'smartphone'],
        str_contains($n, 'pse')           => [$nombre, 'Débito desde cualquier banco en Colombia', 'account_balance'],
        str_contains($n, 'tarjeta')       => [$nombre, 'Visa, Mastercard y otras', 'credit_card'],
        str_contains($n, 'transferencia'),
        str_contains($n, 'bancolombia')   => [$nombre, 'Transferencia a la cuenta de la tienda', 'account_balance_wallet'],
        default                           => [$nombre, 'Pago en línea', 'payments'],
    };
}

/**
 * Lee de la base los productos del carrito (id => cantidad) con su precio actual.
 * Devuelve ['items' => [...], 'subtotal' => float, 'unidades' => int, 'quitados' => bool].
 * Los productos que ya no existen o están inactivos se sacan del carrito.
 */
function cargarItemsCarrito(PDO $conn, array $carrito): array
{
    $resultado = ['items' => [], 'subtotal' => 0.0, 'unidades' => 0, 'quitados' => false];
    if (!$carrito) {
        return $resultado;
    }

    $ids    = array_map('intval', array_keys($carrito));
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt   = $conn->prepare(
        "SELECT id_producto, nombre_producto, precio_base, imagen,
                id_categoria_postre, id_tamano, id_sabor
           FROM productos
          WHERE estado = 1 AND id_producto IN ($marcas)"
    );
    $stmt->execute($ids);

    $filas = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $filas[(int)$f['id_producto']] = $f;
    }

    foreach ($carrito as $id => $cantidad) {
        $id       = (int)$id;
        $cantidad = max(1, min((int)$cantidad, MAX_POR_PRODUCTO));

        if (!isset($filas[$id])) {
            unset($_SESSION['carrito'][$id]);
            $resultado['quitados'] = true;
            continue;
        }

        $f     = $filas[$id];
        $linea = round((float)$f['precio_base'] * $cantidad, 2);

        $resultado['items'][] = [
            'id'        => $id,
            'nombre'    => $f['nombre_producto'],
            'precio'    => (float)$f['precio_base'],
            'imagen'    => $f['imagen'],
            'cantidad'  => $cantidad,
            'subtotal'  => $linea,
            'categoria' => $f['id_categoria_postre'],
            'tamano'    => $f['id_tamano'],
            'sabor'     => $f['id_sabor'],
        ];
        $resultado['subtotal'] += $linea;
        $resultado['unidades'] += $cantidad;
    }
    return $resultado;
}
