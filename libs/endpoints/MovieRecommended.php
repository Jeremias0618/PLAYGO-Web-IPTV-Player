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
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $current_id = isset($_POST['current_id']) ? (int)$_POST['current_id'] : 0;

    if ($category_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid category_id']);
        exit;
    }

    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams&category_id=$category_id";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        $output = [];
    }
    
    shuffle($output);
    $recomendadas_indices = array_rand($output, min(6, count($output)));
    if (!is_array($recomendadas_indices)) {
        $recomendadas_indices = [$recomendadas_indices];
    }
    
    $html = '';
    foreach($recomendadas_indices as $index) {
        $row = $output[$index];
        $filme_id = $row['stream_id'];
        
        if ($filme_id == $current_id) {
            continue;
        }
        
        $filme_nome = $row['name'];
        $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome);
        $filme_type = $row['stream_type'];
        $filme_img = $row['stream_icon'];
        $filme_rat = isset($row['rating']) ? $row['rating'] : '';
        $filme_ano = isset($row['year']) ? $row['year'] : '';
        
        $html .= '<div class="col-4 col-sm-4 col-lg-2">';
        $html .= '<div class="card">';
        $html .= '<div class="card__cover">';
        $html .= '<img loading="lazy" src="' . htmlspecialchars($filme_img) . '" alt="">';
        $html .= '<a href="movie.php?stream=' . $filme_id . '&streamtipo=' . $filme_type . '" class="card__play">';
        $html .= '<i class="fas fa-play"></i>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '<div class="card__content">';
        $html .= '<h3 class="card__title">';
        $html .= '<a href="movie.php?stream=' . $filme_id . '&streamtipo=' . $filme_type . '">';
        $html .= htmlspecialchars($filme_nome);
        $html .= '</a>';
        $html .= '</h3>';
        $html .= '<span class="card__rate">';
        if ($filme_ano) {
            $html .= htmlspecialchars($filme_ano);
        }
        if ($filme_rat !== '') {
            $html .= ' <i class="fa-solid fa-star"></i> ' . htmlspecialchars($filme_rat);
        }
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }

    echo json_encode(['html' => $html]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(), 
        'file' => basename($e->getFile()), 
        'line' => $e->getLine()
    ]);
    exit;
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(), 
        'file' => basename($e->getFile()), 
        'line' => $e->getLine()
    ]);
    exit;
}

