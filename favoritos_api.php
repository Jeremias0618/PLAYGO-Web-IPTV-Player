<?php
require_once("connection.php");

session_start();
header('Content-Type: application/json');

$usuario_id = isset($_COOKIE['xuserid']) ? intval($_COOKIE['xuserid']) : 0;
if (!$usuario_id) {
    echo json_encode(['success' => false, 'msg' => 'No autenticado']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$img = $_POST['img'] ?? '';
$ano = $_POST['ano'] ?? '';
$rate = $_POST['rate'] ?? '';

if ($action === 'fav_add') {
    $stmt = $pdo->prepare("INSERT INTO favoritos (usuario_id, contenido_id, tipo, nombre, imagen, ano, rate)
        VALUES (:usuario_id, :contenido_id, :tipo, :nombre, :imagen, :ano, :rate)
        ON CONFLICT (usuario_id, contenido_id, tipo) DO NOTHING");
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':contenido_id' => $id,
        ':tipo' => $tipo,
        ':nombre' => $nombre,
        ':imagen' => $img,
        ':ano' => $ano,
        ':rate' => $rate
    ]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'fav_remove') {
    $stmt = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id=:usuario_id AND contenido_id=:contenido_id AND tipo=:tipo");
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':contenido_id' => $id,
        ':tipo' => $tipo
    ]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'fav_check') {
    $stmt = $pdo->prepare("SELECT 1 FROM favoritos WHERE usuario_id=:usuario_id AND contenido_id=:contenido_id AND tipo=:tipo");
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':contenido_id' => $id,
        ':tipo' => $tipo
    ]);
    $is_fav = $stmt->fetch() ? true : false;
    echo json_encode(['success' => true, 'is_fav' => $is_fav]);
    exit;
}

echo json_encode(['success' => false, 'msg' => 'Acción no válida']);