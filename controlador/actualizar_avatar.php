<?php
session_name("LOGIN");
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit();
}

$avataresPermitidos = [
    'undraw_profile_man1.svg',
    'undraw_profile_man2.svg',
    'undraw_profile_woman1.svg',
    'undraw_profile_woman2.svg',
];

$data = json_decode(file_get_contents('php://input'), true);
$avatar = $data['avatar'] ?? '';
$csrf   = $data['csrf_token'] ?? '';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token invalido']);
    exit();
}

if (!in_array($avatar, $avataresPermitidos, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Avatar no valido']);
    exit();
}

require_once __DIR__ . '/../config/conexion.php';
$conn = (new conexion())->conn;

$stmt = $conn->prepare("UPDATE usuarios SET avatar = ? WHERE id_usuario = ?");
$stmt->execute([$avatar, $_SESSION['id_usuario']]);

echo json_encode(['ok' => true]);