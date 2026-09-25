<?php
/**
 * Sesión de la tienda. Todas las páginas y controladores la cargan con
 * require_once, en vez de repetir session_start() en cada archivo.
 *
 * Usa un nombre distinto al del panel interno ("LOGIN"), así un cliente
 * nunca comparte sesión con el personal.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_name('TIENDA');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,       // JavaScript no puede leer la cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

/*
 * 1.3 Expiración de sesión por inactividad (15 minutos).
 * Si pasó más tiempo desde la última acción, se destruye la sesión completa
 * (datos, token CSRF y cookie), igual que hace el panel interno. Se deja un
 * aviso para que login.php lo muestre si el cliente estaba en una página
 * que requiere sesión.
 */
const TIEMPO_LIMITE_SESION = 900; // 15 minutos, en segundos

if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > TIEMPO_LIMITE_SESION)) {
    $habiaCliente = !empty($_SESSION['id_cliente']);

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    session_name('TIENDA');
    session_start();
    if ($habiaCliente) {
        $_SESSION['aviso_sesion'] = 'expirada';
    }
}
$_SESSION['ultima_actividad'] = time();


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/** true si la petición es POST y trae el token CSRF correcto. */
function csrfValido(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '');
}

function clienteLogueado(): bool
{
    return !empty($_SESSION['id_cliente']);
}

/**
 * 1.2 RBAC (control de acceso por rol): esta tienda solo tiene el rol "cliente".
 * Toda página que muestre datos privados (checkout, perfil, pedidos, facturas)
 * llama a esta función al inicio. Si no hay un cliente autenticado, se corta
 * la ejecución y se redirige a login.php: nunca se llega a mostrar el HTML
 * ni a ejecutar la lógica de esa página. El personal del panel interno usa
 * una sesión totalmente distinta ("LOGIN"), así que ninguna de las dos partes
 * puede acceder a las páginas restringidas de la otra con su misma sesión.
 */
function exigirCliente(string $volver = ''): void
{
    if (!clienteLogueado()) {
        header('Location: /Dulce_Micro/tienda/login.php' . ($volver !== '' ? '?volver=' . urlencode($volver) : ''));
        exit;
    }
}
