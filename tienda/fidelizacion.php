<?php
/**
 * tienda/fidelizacion.php
 *
 * Funciones de fidelización de Dulce_Micro:
 *  - Cupón automático de cumpleaños (uno por cliente, una vez al año)
 *  - Puntos Dulces (acumulación y consulta de saldo)
 *
 * Inclúyelo donde lo necesites con:
 *   require_once __DIR__ . '/fidelizacion.php';
 */

/**
 * Si hoy es el cumpleaños del cliente y todavía no se le generó el cupón
 * de este año, lo crea y deja un mensaje flash para mostrarlo en pantalla.
 */
function verificarCuponCumpleanos(PDO $conn, int $idCliente): void
{
    $st = $conn->prepare('SELECT fecha_nacimiento FROM clientes WHERE id_cliente = ?');
    $st->execute([$idCliente]);
    $fechaNacimiento = $st->fetchColumn();

    if (!$fechaNacimiento) {
        return; // el cliente no ha registrado su fecha de cumpleaños
    }

    $hoy        = new DateTime();
    $nacimiento = new DateTime($fechaNacimiento);

    if ($hoy->format('m-d') !== $nacimiento->format('m-d')) {
        return; // hoy no es su cumpleaños
    }

    // ¿Ya se le generó el cupón de cumpleaños este año?
    $st = $conn->prepare(
        "SELECT COUNT(*) FROM cupones
          WHERE id_cliente = ? AND motivo = 'Cumpleaños'
            AND YEAR(fecha_generacion) = YEAR(CURDATE())"
    );
    $st->execute([$idCliente]);
    if ((int) $st->fetchColumn() > 0) {
        return; // ya lo tiene, no se duplica
    }

    $codigo = 'DULCECUMPLE' . date('Y') . '-' . $idCliente;
    $expira = (new DateTime())->modify('+30 days')->format('Y-m-d');

    $st = $conn->prepare(
        "INSERT INTO cupones (id_cliente, codigo, tipo_descuento, valor, motivo, fecha_generacion, fecha_expiracion)
         VALUES (?, ?, 'porcentaje', 20, 'Cumpleaños', CURDATE(), ?)"
    );
    $st->execute([$idCliente, $codigo, $expira]);

    $_SESSION['flash_cumple'] = [
        'codigo' => $codigo,
        'expira' => $expira,
    ];
}

/**
 * Suma Puntos Dulces al cliente (por ejemplo, después de confirmar un pago)
 * y deja registro en el historial. Regla: 1 punto por cada $1.000 COP comprados.
 */
function sumarPuntosDulces(PDO $conn, int $idCliente, float $montoCompra, string $descripcion = 'Compra en Dulce_Micro'): int
{
    $puntos = (int) floor($montoCompra / 1000);
    if ($puntos <= 0) {
        return 0;
    }

    $conn->prepare('UPDATE clientes SET puntos_dulces = puntos_dulces + ? WHERE id_cliente = ?')
         ->execute([$puntos, $idCliente]);

    $conn->prepare(
        "INSERT INTO movimientos_puntos_dulces (id_cliente, puntos, tipo, descripcion)
         VALUES (?, ?, 'ganados', ?)"
    )->execute([$idCliente, $puntos, $descripcion]);

    return $puntos;
}

/** Devuelve el saldo actual de Puntos Dulces del cliente. */
function obtenerPuntosDulces(PDO $conn, int $idCliente): int
{
    $st = $conn->prepare('SELECT puntos_dulces FROM clientes WHERE id_cliente = ?');
    $st->execute([$idCliente]);
    return (int) ($st->fetchColumn() ?: 0);
}
