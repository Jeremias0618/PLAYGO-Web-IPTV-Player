<?php

require_once(__DIR__ . '/../lib.php');

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!defined('SAGAS_ADMIN_ENABLED') || SAGAS_ADMIN_ENABLED !== false) {
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
    $sagaTitle = $_POST['title'] ?? '';
    $sagaItems = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
    $sagaMovies = isset($_POST['movies']) ? json_decode($_POST['movies'], true) : [];
    $sagaImage = $_POST['image'] ?? '';
    
    if (empty($sagaTitle)) {
        echo json_encode(['error' => 'Invalid title']);
        exit;
    }
    
    if (empty($sagaItems) && empty($sagaMovies)) {
        echo json_encode(['error' => 'No items selected']);
        exit;
    }
    
    $items = !empty($sagaItems) ? $sagaItems : (is_array($sagaMovies) ? array_map(function($m) {
        return array_merge($m, ['type' => 'movie']);
    }, $sagaMovies) : []);
    
    $sagasFile = __DIR__ . '/../../storage/sagas.json';
    
    if (file_exists($sagasFile)) {
        $content = file_get_contents($sagasFile);
        $sagas = json_decode($content, true) ?: [];
    } else {
        $sagas = [];
    }
    
    $sagaId = strtoupper(str_replace([' ', '-'], '_', preg_replace('/[^a-zA-Z0-9\s\-]/', '', $sagaTitle)));
    
    $newSaga = [
        'id' => $sagaId,
        'title' => $sagaTitle,
        'image' => $sagaImage,
        'items' => $items,
        'movies' => $items,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $sagas[] = $newSaga;
    
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

