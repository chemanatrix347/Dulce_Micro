<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('LOGIN');
    session_start();
}
require_once __DIR__ . '/../modelo/pedidocompleto.php';

if (empty($_SESSION['id_usuario']) || (isset($_SESSION['ultima_actividad']) && time() - $_SESSION['ultima_actividad'] > 900)) {
    header('Location: ../vista/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
$token = $_SESSION['csrf_token'] ?? '';
if (!$token || !is_string($_POST['csrf_token'] ?? null) || !hash_equals($token, $_POST['csrf_token'])) {
    http_response_code(403);
    exit('Recarga la pantalla de pedidos y vuelve a intentarlo.');
}

function pedidoEntero($campo) {
    $valor = filter_var($_POST[$campo] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($valor === false) {
        throw new InvalidArgumentException('Revisa la cantidad y las opciones seleccionadas.');
    }
    return $valor;
}
function pedidoFecha($campo) {
    $valor = $_POST[$campo] ?? '';
    $fecha = is_string($valor) ? DateTimeImmutable::createFromFormat('!Y-m-d', $valor) : false;
    if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
        throw new InvalidArgumentException('Revisa las fechas del pedido.');
    }
    return $valor;
}

$vista = in_array($_POST['vista'] ?? '', ['hoy', 'proximas', 'todos'], true) ? $_POST['vista'] : 'todos';
try {
    $modelo = new PedidoCompleto();
    $accion = $_POST['accion'] ?? '';
    switch ($accion) {
        case 'crear':
        case 'actualizar':
            $fecha = pedidoFecha('fecha_pedido');
            $entrega = pedidoFecha('fecha_estimada_entrega');
            if ($entrega < $fecha) {
                throw new InvalidArgumentException('La entrega debe ser el mismo día del pedido o después.');
            }
            $campos = [pedidoEntero('id_cliente'), $fecha, $entrega, pedidoEntero('id_producto'),
                pedidoEntero('cantidad'), pedidoEntero('id_estado_pedido'), pedidoEntero('id_metodo_pago'),
                pedidoFecha('fecha_pago'), pedidoEntero('id_repostera_asignada')];
            $ok = $accion === 'crear' ? $modelo->crear_pedido(...$campos)
                : $modelo->actualizar_pedido(pedidoEntero('id'), ...$campos);
            $mensaje = $accion === 'crear' ? 'Pedido creado.' : 'Cambios guardados.';
            if ($accion === 'crear') {
                $vista = 'todos';
            }
            break;
        case 'estado':
            $ok = $modelo->cambiar_estado(pedidoEntero('id'), pedidoEntero('anterior'), pedidoEntero('nuevo'));
            $mensaje = 'Estado del pedido actualizado.';
            break;
        case 'eliminar':
            $ok = $modelo->eliminar_pedido(pedidoEntero('id'));
            $mensaje = 'Pedido retirado de la lista.';
            break;
        default:
            throw new InvalidArgumentException('Acción de pedido desconocida.');
    }
    $_SESSION['pedidos_aviso'] = $ok ? ['success', $mensaje] : ['warning', 'No se aplicó el cambio. Revisa el pedido y su estado actual.'];
} catch (InvalidArgumentException $e) {
    $_SESSION['pedidos_aviso'] = ['warning', $e->getMessage()];
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['pedidos_aviso'] = ['danger', 'No se pudo guardar el cambio. Revisa los datos e inténtalo de nuevo.'];
}
header('Location: ../vista/pedidocompleto.php?vista=' . $vista, true, 303);
exit;
