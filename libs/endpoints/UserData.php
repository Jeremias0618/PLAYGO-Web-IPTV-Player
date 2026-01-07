<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    if (!defined('IP')) {
        require_once(__DIR__ . '/../lib.php');
    }
    
    if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
        http_response_code(401);
        echo json_encode(['success'=>false, 'error'=>'no_user']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success'=>false, 'error'=>'method_not_allowed']);
        exit;
    }

    $user = $_COOKIE['xuserm'];
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $img = $_POST['img'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $rate = $_POST['rate'] ?? '';
    $tipo = $_POST['tipo'] ?? 'pelicula';

    $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
    $storageDir = __DIR__ . '/../../storage/users/' . $safeUser;
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    $historialFile = $storageDir . '/historial.json';
    $favoritosFile = $storageDir . '/favoritos.json';
    $progressFile = $storageDir . '/progress.json';
    $playlistsFile = $storageDir . '/playlists.json';

    function loadJsonFile($file, $default = []) {
        if (!file_exists($file)) {
            file_put_contents($file, json_encode($default));
            return $default;
        }
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        return $data !== null ? $data : $default;
    }

    function saveJsonFile($file, $data) {
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    if ($action == 'hist_add') {
        $historial = loadJsonFile($historialFile, []);
        $historial = array_filter($historial, function($item) use ($id, $tipo) {
            return !($item['id'] == $id && $item['tipo'] == $tipo);
        });
        array_unshift($historial, [
            'id' => $id,
            'tipo' => $tipo,
            'nombre' => $nombre,
            'img' => $img,
            'ano' => $ano,
            'rate' => $rate,
            'fecha' => date('Y-m-d H:i:s')
        ]);
        $historial = array_slice($historial, 0, 30);
        saveJsonFile($historialFile, $historial);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'fav_add') {
        $favoritos = loadJsonFile($favoritosFile, []);
        foreach ($favoritos as $fav) {
            if ($fav['id'] == $id && $fav['tipo'] == $tipo) {
                echo json_encode(['success'=>true]);
                exit;
            }
        }
        $favoritos[] = [
            'id' => $id,
            'tipo' => $tipo,
            'nombre' => $nombre,
            'img' => $img,
            'ano' => $ano,
            'rate' => $rate,
            'fecha' => date('Y-m-d H:i:s')
        ];
        saveJsonFile($favoritosFile, $favoritos);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'fav_remove') {
        $favoritos = loadJsonFile($favoritosFile, []);
        $favoritos = array_filter($favoritos, function($fav) use ($id, $tipo) {
            return !($fav['id'] == $id && $fav['tipo'] == $tipo);
        });
        saveJsonFile($favoritosFile, array_values($favoritos));
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'fav_check') {
        $favoritos = loadJsonFile($favoritosFile, []);
        $is_fav = false;
        foreach ($favoritos as $fav) {
            if ($fav['id'] == $id && $fav['tipo'] == $tipo) {
                $is_fav = true;
                break;
            }
        }
        echo json_encode(['success'=>true, 'is_fav'=>$is_fav]);
        exit;
    }

    if ($action == 'get_historial') {
        $historial = loadJsonFile($historialFile, []);
        echo json_encode(['success'=>true, 'historial'=>$historial]);
        exit;
    }

    if ($action == 'get_favoritos') {
        $favoritos = loadJsonFile($favoritosFile, []);
        echo json_encode(['success'=>true, 'favoritos'=>$favoritos]);
        exit;
    }

    if ($action == 'progress_save') {
        $time = isset($_POST['time']) ? (int)$_POST['time'] : 0;
        $progress = loadJsonFile($progressFile, []);
        $key = $tipo . '_' . $id;
        $progress[$key] = [
            'id' => $id,
            'tipo' => $tipo,
            'time' => $time,
            'updated' => date('Y-m-d H:i:s')
        ];
        saveJsonFile($progressFile, $progress);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'progress_get') {
        $progress = loadJsonFile($progressFile, []);
        $key = $tipo . '_' . $id;
        $time = isset($progress[$key]) ? (int)$progress[$key]['time'] : 0;
        echo json_encode(['success'=>true, 'time'=>$time]);
        exit;
    }

    if ($action == 'progress_remove') {
        $progress = loadJsonFile($progressFile, []);
        $key = $tipo . '_' . $id;
        if (isset($progress[$key])) {
            unset($progress[$key]);
            saveJsonFile($progressFile, $progress);
        }
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'playlist_list') {
        $playlists = loadJsonFile($playlistsFile, []);
        if (empty($playlists)) {
            $playlists = ['VER MÁS TARDE' => []];
            saveJsonFile($playlistsFile, $playlists);
        }
        echo json_encode(['success'=>true, 'playlists'=>$playlists]);
        exit;
    }

    if ($action == 'playlist_create') {
        $playlistName = trim($_POST['playlist_name'] ?? '');
        if (empty($playlistName)) {
            echo json_encode(['success'=>false, 'error'=>'empty_name']);
            exit;
        }
        $playlists = loadJsonFile($playlistsFile, []);
        if (empty($playlists)) {
            $playlists = ['VER MÁS TARDE' => []];
        }
        if (isset($playlists[$playlistName])) {
            echo json_encode(['success'=>false, 'error'=>'playlist_exists']);
            exit;
        }
        $playlists[$playlistName] = [];
        saveJsonFile($playlistsFile, $playlists);
        echo json_encode(['success'=>true, 'playlists'=>$playlists]);
        exit;
    }

    if ($action == 'playlist_add') {
        $playlistName = trim($_POST['playlist_name'] ?? '');
        if (empty($playlistName)) {
            $playlistName = 'VER MÁS TARDE';
        }
        $playlists = loadJsonFile($playlistsFile, []);
        if (empty($playlists)) {
            $playlists = ['VER MÁS TARDE' => []];
        }
        if (!isset($playlists[$playlistName])) {
            $playlists[$playlistName] = [];
        }
        $exists = false;
        foreach ($playlists[$playlistName] as $item) {
            if ($item['id'] == $id && $item['tipo'] == $tipo) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $playlists[$playlistName][] = [
                'id' => $id,
                'tipo' => $tipo,
                'nombre' => $nombre,
                'img' => $img,
                'ano' => $ano,
                'rate' => $rate,
                'fecha' => date('Y-m-d H:i:s')
            ];
            saveJsonFile($playlistsFile, $playlists);
        }
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'playlist_remove') {
        $playlistName = trim($_POST['playlist_name'] ?? '');
        if (empty($playlistName)) {
            echo json_encode(['success'=>false, 'error'=>'empty_name']);
            exit;
        }
        $playlists = loadJsonFile($playlistsFile, []);
        if (isset($playlists[$playlistName])) {
            $playlists[$playlistName] = array_filter($playlists[$playlistName], function($item) use ($id, $tipo) {
                return !($item['id'] == $id && $item['tipo'] == $tipo);
            });
            $playlists[$playlistName] = array_values($playlists[$playlistName]);
            saveJsonFile($playlistsFile, $playlists);
        }
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'playlist_check') {
        $playlistName = trim($_POST['playlist_name'] ?? '');
        if (empty($playlistName)) {
            $playlistName = 'VER MÁS TARDE';
        }
        $playlists = loadJsonFile($playlistsFile, []);
        if (empty($playlists)) {
            $playlists = ['VER MÁS TARDE' => []];
        }
        $isInPlaylist = false;
        if (isset($playlists[$playlistName])) {
            foreach ($playlists[$playlistName] as $item) {
                if ($item['id'] == $id && $item['tipo'] == $tipo) {
                    $isInPlaylist = true;
                    break;
                }
            }
        }
        echo json_encode(['success'=>true, 'is_in_playlist'=>$isInPlaylist]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success'=>false, 'error'=>'invalid_action']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'error' => $e->getMessage(), 
        'file' => basename($e->getFile()), 
        'line' => $e->getLine()
    ]);
    exit;
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'error' => $e->getMessage(), 
        'file' => basename($e->getFile()), 
        'line' => $e->getLine()
    ]);
    exit;
}

