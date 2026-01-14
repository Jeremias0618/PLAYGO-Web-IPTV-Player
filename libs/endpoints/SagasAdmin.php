<?php

require_once(__DIR__ . '/../lib.php');

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!defined('SAGAS_ADMIN_ENABLED') || SAGAS_ADMIN_ENABLED !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json');

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'collect_movies') {
    require_once(__DIR__ . '/../services/movies.php');
    
    $movies = getMoviesData($user, $pwd, null);
    
    $moviesData = array_map(function($movie) {
        return [
            'id' => $movie['stream_id'] ?? null,
            'name' => $movie['name'] ?? '',
            'poster' => $movie['stream_icon'] ?? ($movie['cover'] ?? '')
        ];
    }, $movies);
    
    echo json_encode([
        'success' => true,
        'movies' => $moviesData,
        'total' => count($moviesData)
    ]);
    exit;
}

if ($action === 'collect_series') {
    require_once(__DIR__ . '/../services/series.php');
    
    $series = getSeriesData($user, $pwd, null);
    
    $seriesData = array_map(function($serie) {
        return [
            'id' => $serie['series_id'] ?? null,
            'name' => $serie['name'] ?? '',
            'poster' => $serie['cover'] ?? ($serie['stream_icon'] ?? '')
        ];
    }, $series);
    
    echo json_encode([
        'success' => true,
        'series' => $seriesData,
        'total' => count($seriesData)
    ]);
    exit;
}

if ($action === 'check_title') {
    $sagaTitle = trim($_POST['title'] ?? $_GET['title'] ?? '');
    $sagaId = $_POST['saga_id'] ?? $_GET['saga_id'] ?? null;
    
    if (empty($sagaTitle)) {
        echo json_encode(['exists' => false]);
        exit;
    }
    
    $sagasFile = __DIR__ . '/../../storage/sagas.json';
    
    if (file_exists($sagasFile)) {
        $content = file_get_contents($sagasFile);
        $sagas = json_decode($content, true) ?: [];
    } else {
        $sagas = [];
    }
    
    $exists = false;
    $sagaIdClean = $sagaId ? trim((string)$sagaId) : null;
    
    foreach ($sagas as $saga) {
        $existingId = $saga['id'] ?? '';
        $existingIdClean = trim((string)$existingId);
        
        if ($sagaIdClean && $existingIdClean === $sagaIdClean) {
            continue;
        }
        if (isset($saga['title']) && strtolower(trim($saga['title'])) === strtolower($sagaTitle)) {
            $exists = true;
            break;
        }
    }
    
    echo json_encode(['exists' => $exists]);
    exit;
}

if ($action === 'get_sagas') {
    $sagasFile = __DIR__ . '/../../storage/sagas.json';
    
    if (file_exists($sagasFile)) {
        $content = file_get_contents($sagasFile);
        $sagas = json_decode($content, true) ?: [];
    } else {
        $sagas = [];
    }
    
    echo json_encode([
        'success' => true,
        'sagas' => $sagas
    ]);
    exit;
}

if ($action === 'save_saga') {
    function getRandomSagaWallpaper() {
        $wallpapers = [
            'assets/image/wallpaper_02.webp',
            'assets/image/wallpaper_03.webp',
            'assets/image/wallpaper_04.webp',
            'assets/image/wallpaper_05.webp',
            'assets/image/wallpaper_channels.webp'
        ];
        return $wallpapers[array_rand($wallpapers)];
    }
    
    $sagaTitle = trim($_POST['title'] ?? '');
    $sagaItems = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
    $sagaImage = $_POST['image'] ?? '';
    $sagaId = isset($_POST['saga_id']) && $_POST['saga_id'] !== '' && $_POST['saga_id'] !== 'null' && $_POST['saga_id'] !== 'undefined' ? trim((string)$_POST['saga_id']) : null;
    
    if (empty($sagaTitle)) {
        echo json_encode(['error' => 'El título es requerido']);
        exit;
    }
    
    if (empty($sagaItems) || !is_array($sagaItems)) {
        echo json_encode(['error' => 'No hay items seleccionados']);
        exit;
    }
    
    $sagasFile = __DIR__ . '/../../storage/sagas.json';
    
    if (file_exists($sagasFile)) {
        $content = file_get_contents($sagasFile);
        $sagas = json_decode($content, true) ?: [];
    } else {
        $sagas = [];
    }
    
    foreach ($sagas as $existingSaga) {
        $existingId = isset($existingSaga['id']) ? trim((string)$existingSaga['id']) : '';
        
        if ($sagaId && $existingId === $sagaId) {
            continue;
        }
        
        $existingTitle = isset($existingSaga['title']) ? trim($existingSaga['title']) : '';
        if ($existingTitle && strtolower($existingTitle) === strtolower($sagaTitle)) {
            echo json_encode(['error' => 'Ya existe una saga con ese nombre']);
            exit;
        }
    }
    
    $items = array_map(function($item) {
        return [
            'id' => $item['id'] ?? null,
            'title' => $item['title'] ?? '',
            'poster' => $item['poster'] ?? '',
            'type' => $item['type'] ?? 'movie',
            'order' => intval($item['order'] ?? 0)
        ];
    }, $sagaItems);
    
    usort($items, function($a, $b) {
        return ($a['order'] ?? 0) - ($b['order'] ?? 0);
    });
    
    if ($sagaId) {
        $sagaIndex = -1;
        foreach ($sagas as $index => $saga) {
            $sagaIdFromFile = isset($saga['id']) ? trim((string)$saga['id']) : '';
            if ($sagaIdFromFile === $sagaId) {
                $sagaIndex = $index;
                break;
            }
        }
        
        if ($sagaIndex >= 0) {
            if (empty($sagaImage)) {
                $existingImage = $sagas[$sagaIndex]['image'] ?? '';
                if (empty($existingImage)) {
                    $sagaImage = getRandomSagaWallpaper();
                } else {
                    $sagaImage = $existingImage;
                }
            }
            
            $sagas[$sagaIndex] = [
                'id' => $sagaId,
                'title' => $sagaTitle,
                'image' => $sagaImage,
                'items' => $items,
                'created_at' => $sagas[$sagaIndex]['created_at'] ?? date('Y-m-d H:i:s')
            ];
            $newSaga = $sagas[$sagaIndex];
        } else {
            echo json_encode(['error' => 'Saga no encontrada']);
            exit;
        }
    } else {
        if (empty($sagaImage)) {
            $sagaImage = getRandomSagaWallpaper();
        }
        
        $maxId = 0;
        foreach ($sagas as $saga) {
            if (isset($saga['id']) && is_numeric($saga['id'])) {
                $id = intval($saga['id']);
                if ($id > $maxId) {
                    $maxId = $id;
                }
            }
        }
        
        $newSagaId = $maxId + 1;
        
        $newSaga = [
            'id' => (string)$newSagaId,
            'title' => $sagaTitle,
            'image' => $sagaImage,
            'items' => $items,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $sagas[] = $newSaga;
    }
    
    if (!is_dir(dirname($sagasFile))) {
        mkdir(dirname($sagasFile), 0755, true);
    }
    
    file_put_contents($sagasFile, json_encode($sagas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo json_encode([
        'success' => true,
        'saga' => $newSaga
    ]);
    exit;
}

if ($action === 'delete_saga') {
    $sagaId = $_POST['saga_id'] ?? '';
    
    if (empty($sagaId)) {
        echo json_encode(['error' => 'Invalid saga ID']);
        exit;
    }
    
    $sagasFile = __DIR__ . '/../../storage/sagas.json';
    
    if (!file_exists($sagasFile)) {
        echo json_encode(['error' => 'Sagas file not found']);
        exit;
    }
    
    $content = file_get_contents($sagasFile);
    $sagas = json_decode($content, true) ?: [];
    
    $sagas = array_filter($sagas, function($saga) use ($sagaId) {
        return ($saga['id'] ?? '') !== $sagaId;
    });
    
    $sagas = array_values($sagas);
    
    file_put_contents($sagasFile, json_encode($sagas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'get_items_posters') {
    $itemsJson = $_POST['items'] ?? $_GET['items'] ?? '[]';
    $items = json_decode($itemsJson, true) ?: [];
    
    if (empty($items) || !is_array($items)) {
        echo json_encode(['success' => true, 'posters' => []]);
        exit;
    }
    
    require_once(__DIR__ . '/../services/movies.php');
    require_once(__DIR__ . '/../services/series.php');
    
    $posters = [];
    
    foreach ($items as $item) {
        $itemId = $item['id'] ?? null;
        $itemType = $item['type'] ?? 'movie';
        
        if (!$itemId) {
            continue;
        }
        
        $poster = '';
        
        if ($itemType === 'movie') {
            $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$itemId";
            $response = apixtream($url);
            $data = json_decode($response, true);
            
            if ($data && isset($data['info'])) {
                $poster = $data['info']['movie_image'] ?? $data['info']['stream_icon'] ?? '';
            }
        } else if ($itemType === 'series') {
            $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$itemId";
            $response = apixtream($url);
            $data = json_decode($response, true);
            
            if ($data && isset($data['info'])) {
                $poster = $data['info']['cover'] ?? $data['info']['stream_icon'] ?? '';
            }
        }
        
        $posters[] = [
            'id' => $itemId,
            'type' => $itemType,
            'poster' => $poster
        ];
    }
    
    echo json_encode([
        'success' => true,
        'posters' => $posters
    ]);
    exit;
}

if ($action === 'upload_image') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Upload failed']);
        exit;
    }
    
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['error' => 'File too large']);
        exit;
    }
    
    $sagaTitle = $_POST['saga_title'] ?? 'SAGA';
    $sagaId = strtoupper(str_replace([' ', '-'], '_', preg_replace('/[^a-zA-Z0-9\s\-]/', '', $sagaTitle)));
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $sagaId . '.' . $extension;
    $uploadDir = __DIR__ . '/../../assets/image/sagas/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $imageUrl = 'assets/image/sagas/' . $filename;
        echo json_encode([
            'success' => true,
            'image' => $imageUrl
        ]);
    } else {
        echo json_encode(['error' => 'Failed to save file']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);

