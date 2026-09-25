    <?php
    session_name("LOGIN");
    session_start();

    require_once __DIR__ . "/../modelo/estadopedido.php";

    $estadoPedido = new EstadoPedido();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificacion CSRF: el token del formulario debe coincidir con el de la sesion
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            die("Token de seguridad invalido. Recarga la pagina e intenta de nuevo.");
        }

        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear') {
            $estadoPedido->crear_estado_pedido($_POST['estado_pedido']);
        } elseif ($accion === 'actualizar') {
            $estadoPedido->actualizar_estado_pedido($_POST['id'], $_POST['estado_pedido']);
        } elseif ($accion === 'eliminar') {
            $estadoPedido->eliminar_estado_pedido($_POST['id']);
        }

        header("Location: ../vista/estadopedido.php");
        exit;
    }
    ?>
