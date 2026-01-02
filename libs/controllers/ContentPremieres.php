<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}
require_once(__DIR__ . '/../services/content.php');
require_once(__DIR__ . '/ContentRenderer.php');

function handlePremieresRequest() {
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
    $year = isset($_POST['year']) ? (int)$_POST['year'] : null;

    header('Content-Type: application/json');

    $items = getPremieres($user, $pwd, $type, $limit, $year);
    
    if (empty($items)) {
        $typeLabel = ($type === 'movie' || $type === 'movies') ? 'películas' : 'series';
        $html = '<div class="col-12 text-center" style="color: #fff; padding: 60px 20px;">';
        $html .= '<i class="fas fa-film" style="font-size: 3rem; color: rgba(255, 255, 255, 0.3); margin-bottom: 20px; display: block;"></i>';
        $html .= '<h3 style="color: #fff; margin-bottom: 10px; font-size: 1.5rem;">No se encontraron resultados</h3>';
        $html .= '<p style="color: rgba(255, 255, 255, 0.7); font-size: 1rem;">No hay ' . htmlspecialchars($typeLabel) . ' disponibles para el año ' . htmlspecialchars($year) . '.</p>';
        $html .= '</div>';
        echo json_encode(['html' => $html, 'empty' => true]);
        exit;
    }
    
    if ($type === 'movie' || $type === 'movies') {
        $html = renderMoviesGrid($items);
    } else {
        $html = renderSeriesGrid($items);
    }

    echo json_encode(['html' => $html, 'empty' => false]);
    exit;
}

