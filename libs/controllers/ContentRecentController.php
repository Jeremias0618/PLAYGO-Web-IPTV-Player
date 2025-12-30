<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}
require_once(__DIR__ . '/../services/content.php');
require_once(__DIR__ . '/ContentRenderer.php');

function handleRecentContentRequest() {
    if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    $user = $_COOKIE['xuserm'];
    $pwd = $_COOKIE['xpwdm'];
    $type = isset($_POST['type']) ? $_POST['type'] : 'movie';
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 16;

    header('Content-Type: application/json');

    $items = getRecentContent($user, $pwd, $type, $limit);
    
    if ($type === 'movie' || $type === 'movies') {
        $html = renderMoviesGrid($items);
    } else {
        $html = renderSeriesGrid($items);
    }

    echo json_encode(['html' => $html]);
    exit;
}

