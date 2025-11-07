<?php
// filepath: c:\xampp\htdocs\MAXGO\db\base.php
header('Content-Type: application/json');
session_start();

$user = $_COOKIE['xuserm'] ?? '';
if (!$user) { echo json_encode(['success'=>false, 'error'=>'no_user']); exit; }

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$img = $_POST['img'] ?? '';
$ano = $_POST['ano'] ?? '';
$rate = $_POST['rate'] ?? '';
$tipo = $_POST['tipo'] ?? 'pelicula';

$datafile = __DIR__ . '/data.json';
if (!file_exists($datafile)) file_put_contents($datafile, json_encode(['historial'=>[], 'favoritos'=>[]]));

$data = json_decode(file_get_contents($datafile), true);

// HISTORIAL
if ($action == 'hist_add') {
    if (!isset($data['historial'][$user])) $data['historial'][$user] = [];
    // Elimina duplicados
    $data['historial'][$user] = array_filter($data['historial'][$user], function($item) use ($id, $tipo) {
        return !($item['id'] == $id && $item['tipo'] == $tipo);
    });
    // Agrega al inicio
    array_unshift($data['historial'][$user], [
        'id' => $id,
        'tipo' => $tipo,
        'nombre' => $nombre,
        'img' => $img,
        'ano' => $ano,
        'rate' => $rate,
        'fecha' => date('Y-m-d H:i:s')
    ]);
    // Limita a 30
    $data['historial'][$user] = array_slice($data['historial'][$user], 0, 30);
    file_put_contents($datafile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo json_encode(['success'=>true]);
    exit;
}

// FAVORITOS
if ($action == 'fav_add') {
    if (!isset($data['favoritos'][$user])) $data['favoritos'][$user] = [];
    // Evita duplicados
    foreach ($data['favoritos'][$user] as $fav) {
        if ($fav['id'] == $id && $fav['tipo'] == $tipo) {
            echo json_encode(['success'=>true]);
            exit;
        }
    }
    $data['favoritos'][$user][] = [
        'id' => $id,
        'tipo' => $tipo,
        'nombre' => $nombre,
        'img' => $img,
        'ano' => $ano,
        'rate' => $rate,
        'fecha' => date('Y-m-d H:i:s')
    ];
    file_put_contents($datafile, json_encode($data));
    echo json_encode(['success'=>true]);
    exit;
}
if ($action == 'fav_remove') {
    if (isset($data['favoritos'][$user])) {
        $data['favoritos'][$user] = array_filter($data['favoritos'][$user], function($fav) use ($id, $tipo) {
            return !($fav['id'] == $id && $fav['tipo'] == $tipo);
        });
        file_put_contents($datafile, json_encode($data));
    }
    echo json_encode(['success'=>true]);
    exit;
}
if ($action == 'fav_check') {
    $is_fav = false;
    if (isset($data['favoritos'][$user])) {
        foreach ($data['favoritos'][$user] as $fav) {
            if ($fav['id'] == $id && $fav['tipo'] == $tipo) $is_fav = true;
        }
    }
    echo json_encode(['success'=>true, 'is_fav'=>$is_fav]);
    exit;
}
if ($action == 'get_historial') {
    echo json_encode(['success'=>true, 'historial'=>$data['historial'][$user] ?? []]);
    exit;
}
if ($action == 'get_favoritos') {
    echo json_encode(['success'=>true, 'favoritos'=>$data['favoritos'][$user] ?? []]);
    exit;
}

echo json_encode(['success'=>false, 'error'=>'no_action']);