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
    $backdrop = $_POST['backdrop'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $rate = $_POST['rate'] ?? '';
    $tipo = $_POST['tipo'] ?? 'pelicula';
    $duration = $_POST['duration'] ?? '';

    $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
    $storageDir = __DIR__ . '/../../storage/users/' . $safeUser;
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    $historyFile = $storageDir . '/history.json';
    $favoritesFile = $storageDir . '/favorites.json';
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
        $history = loadJsonFile($historyFile, []);
        $history = array_filter($history, function($item) use ($id, $tipo) {
            return !($item['id'] == $id && $item['type'] == $tipo);
        });
        array_unshift($history, [
            'id' => $id,
            'type' => $tipo,
            'name' => $nombre,
            'img' => $img,
            'backdrop' => $backdrop,
            'year' => $ano,
            'rating' => $rate,
            'date' => date('Y-m-d H:i:s')
        ]);
        $history = array_slice($history, 0, 30);
        saveJsonFile($historyFile, $history);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'fav_add') {
        $favorites = loadJsonFile($favoritesFile, []);
        foreach ($favorites as $fav) {
            if ($fav['id'] == $id && $fav['type'] == $tipo) {
                echo json_encode(['success'=>true]);
                exit;
            }
        }
        $favorites[] = [
            'id' => $id,
            'type' => $tipo,
            'name' => $nombre,
            'img' => $img,
            'backdrop' => $backdrop,
            'year' => $ano,
            'rating' => $rate,
            'date' => date('Y-m-d H:i:s')
        ];
        saveJsonFile($favoritesFile, $favorites);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'fav_remove') {
        $favorites = loadJsonFile($favoritesFile, []);
        $favorites = array_filter($favorites, function($fav) use ($id, $tipo) {
            return !($fav['id'] == $id && $fav['type'] == $tipo);
        });
        saveJsonFile($favoritesFile, array_values($favorites));
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'fav_check') {
        $favorites = loadJsonFile($favoritesFile, []);
        $is_fav = false;
        foreach ($favorites as $fav) {
            if ($fav['id'] == $id && $fav['type'] == $tipo) {
                $is_fav = true;
                break;
            }
        }
        echo json_encode(['success'=>true, 'is_fav'=>$is_fav]);
        exit;
    }

    if ($action == 'get_historial') {
        $history = loadJsonFile($historyFile, []);
        echo json_encode(['success'=>true, 'historial'=>$history]);
        exit;
    }

    if ($action == 'get_favoritos') {
        $favorites = loadJsonFile($favoritesFile, []);
        echo json_encode(['success'=>true, 'favoritos'=>$favorites]);
        exit;
    }

    if ($action == 'progress_save') {
        $time = isset($_POST['time']) ? (int)$_POST['time'] : 0;
        $progress = loadJsonFile($progressFile, []);
        $key = $tipo . '_' . $id;
        
        if ($tipo == 'serie') {
            $serie_id = $_POST['serie_id'] ?? '';
            $serie_name = $_POST['serie_name'] ?? '';
            $serie_img = $_POST['serie_img'] ?? '';
            $serie_backdrop = $_POST['serie_backdrop'] ?? '';
            
            $progressData = [
                'type' => $tipo,
                'serie' => [
                    'id' => $serie_id,
                    'name' => $serie_name,
                    'img' => $serie_img,
                    'backdrop' => $serie_backdrop
                ],
                'episode' => [
                    'id' => $id,
                    'name' => $nombre,
                    'img' => $img,
                    'time' => $time,
                    'duration' => $duration
                ],
                'updated' => date('Y-m-d H:i:s')
            ];
        } else {
            $progressData = [
                'id' => $id,
                'type' => $tipo,
                'name' => $nombre,
                'img' => $img,
                'backdrop' => $backdrop,
                'time' => $time,
                'duration' => $duration,
                'updated' => date('Y-m-d H:i:s')
            ];
        }
        
        $progress[$key] = $progressData;
        saveJsonFile($progressFile, $progress);
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action == 'progress_get') {
        $progress = loadJsonFile($progressFile, []);
        $key = $tipo . '_' . $id;
        $time = 0;
        
        if (isset($progress[$key])) {
            $progressItem = $progress[$key];
            if ($tipo == 'serie' && isset($progressItem['episode']['time'])) {
                $time = (int)$progressItem['episode']['time'];
            } elseif (isset($progressItem['time'])) {
                $time = (int)$progressItem['time'];
            }
        }
        
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
                'type' => $tipo,
                'name' => $nombre,
                'img' => $img,
                'backdrop' => $backdrop,
                'year' => $ano,
                'rating' => $rate,
                'date' => date('Y-m-d H:i:s')
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
                return !($item['id'] == $id && $item['type'] == $tipo);
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
                if ($item['id'] == $id && $item['type'] == $tipo) {
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

