<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}
require_once(__DIR__ . '/../services/content.php');
require_once(__DIR__ . '/ContentRenderer.php');

function handleRefreshRequest() {
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

    if ($type === 'movie') {
        $items = getMovies($user, $pwd, $limit);
        $html = renderMoviesGrid($items);
    } elseif ($type === 'series') {
        $items = getSeries($user, $pwd, $limit);
        $html = renderSeriesGrid($items);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type']);
        exit;
    }

    echo json_encode(['html' => $html]);
    exit;
}

