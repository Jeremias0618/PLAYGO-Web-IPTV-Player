<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);

$page_start_time = microtime(true);
error_log("[collection.php] PAGE_START - Saga ID: " . (isset($_GET['saga']) ? $_GET['saga'] : 'none'));

$saga_id = isset($_GET['saga']) ? $_GET['saga'] : '';

$sagasFile = __DIR__ . '/storage/sagas.json';
$saga_actual = null;

$load_start = microtime(true);
if (file_exists($sagasFile)) {
    $content = file_get_contents($sagasFile);
    $sagasData = json_decode($content, true) ?: [];
    
    foreach ($sagasData as $saga) {
        if (isset($saga['id']) && (string)$saga['id'] === (string)$saga_id) {
            $saga_actual = [
                'id' => $saga['id'],
                'nombre' => $saga['title'] ?? '',
                'imagen' => $saga['image'] ?? '',
                'items' => $saga['items'] ?? []
            ];
            break;
        }
    }
}
$load_time = (microtime(true) - $load_start) * 1000;
error_log("[collection.php] LOAD_SAGA - Time: " . number_format($load_time, 2) . "ms - Items: " . (isset($saga_actual['items']) ? count($saga_actual['items']) : 0));

if(!$saga_actual || empty($saga_actual['items'])) {
    error_log("[collection.php] REDIRECT - No saga found or empty items");
    header("Location: sagas.php");
    exit;
}

function getTmdbInfo($title, $year = '', $type = 'movie') {
    $start_time = microtime(true);
    error_log("[collection.php] getTmdbInfo START - Title: $title, Type: $type, Year: $year");
    
    if (!defined('TMDB_API_KEY') || empty(TMDB_API_KEY)) {
        error_log("[collection.php] getTmdbInfo SKIP - No TMDB_API_KEY defined");
        return null;
    }
    
    $query = urlencode($title);
    $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
    $search_type = ($type === 'series') ? 'tv' : 'movie';
    $tmdb_search_url = "https://api.themoviedb.org/3/search/{$search_type}?api_key=" . TMDB_API_KEY . "&language=" . $language . "&query=$query";
    if (!empty($year)) {
        $tmdb_search_url .= "&first_air_date_year=" . urlencode(substr($year, 0, 4));
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tmdb_search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $tmdb_search_json = @curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    $search_time = (microtime(true) - $start_time) * 1000;
    error_log("[collection.php] getTmdbInfo SEARCH - Time: " . number_format($search_time, 2) . "ms");
    
    if ($curl_error) {
        error_log("[collection.php] getTmdbInfo ERROR - CURL: $curl_error");
        return null;
    }
    
    $tmdb_search_data = @json_decode($tmdb_search_json, true);
    if (empty($tmdb_search_data['results'][0])) {
        error_log("[collection.php] getTmdbInfo NO_RESULTS - No results found for: $title");
        return null;
    }
    
    $result = $tmdb_search_data['results'][0];
    
    // Solo obtener detalles si realmente necesitamos información adicional
    if (!empty($result['id'])) {
        $tmdb_id = $result['id'];
        $detail_url = "https://api.themoviedb.org/3/{$search_type}/{$tmdb_id}?api_key=" . TMDB_API_KEY . "&language=" . $language . "&append_to_response=credits,videos";
        
        $detail_start = microtime(true);
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $detail_url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $tmdb_detail_json = @curl_exec($ch2);
        curl_close($ch2);
        
        $detail_time = (microtime(true) - $detail_start) * 1000;
        error_log("[collection.php] getTmdbInfo DETAIL - Time: " . number_format($detail_time, 2) . "ms");
        
        $tmdb_detail = @json_decode($tmdb_detail_json, true);
        if ($tmdb_detail) {
            if (!empty($tmdb_detail['overview'])) $result['overview'] = $tmdb_detail['overview'];
            if (!empty($tmdb_detail['vote_average'])) $result['vote_average'] = $tmdb_detail['vote_average'];
            if (!empty($tmdb_detail['release_date'])) $result['release_date'] = $tmdb_detail['release_date'];
            if (!empty($tmdb_detail['first_air_date'])) $result['first_air_date'] = $tmdb_detail['first_air_date'];
            if (!empty($tmdb_detail['poster_path'])) $result['poster_path'] = $tmdb_detail['poster_path'];
            if (!empty($tmdb_detail['genres']) && is_array($tmdb_detail['genres'])) {
                $result['genres'] = array_map(function($g) { return $g['name']; }, $tmdb_detail['genres']);
            }
            if (!empty($tmdb_detail['credits']['cast']) && is_array($tmdb_detail['credits']['cast'])) {
                $cast_names = array_slice(array_map(function($c) { return $c['name']; }, $tmdb_detail['credits']['cast']), 0, 5);
                $result['cast'] = implode(', ', $cast_names);
            }
            if (!empty($tmdb_detail['videos']['results']) && is_array($tmdb_detail['videos']['results'])) {
                foreach ($tmdb_detail['videos']['results'] as $video) {
                    if (isset($video['type']) && $video['type'] === 'Trailer' && isset($video['key'])) {
                        $result['youtube_key'] = $video['key'];
                        break;
                    }
                }
            }
        }
    }
    
    $total_time = (microtime(true) - $start_time) * 1000;
    error_log("[collection.php] getTmdbInfo END - Total Time: " . number_format($total_time, 2) . "ms");
    
    return $result;
}

$peliculas = [];
$items = $saga_actual['items'];

usort($items, function($a, $b) {
    $orderA = isset($a['order']) ? intval($a['order']) : 999;
    $orderB = isset($b['order']) ? intval($b['order']) : 999;
    return $orderA <=> $orderB;
});

$items_count = count($items);
error_log("[collection.php] PROCESSING_ITEMS - Total: $items_count");

// Array para almacenar información de llamadas API para JavaScript
$api_calls_log = [];

// Preparar todas las llamadas para ejecutarlas en paralelo
$parallel_start = microtime(true);
$multi_handle = curl_multi_init();
$curl_handles = [];
$items_data = [];

$item_index = 0;
foreach($items as $item) {
    $item_index++;
    $vod_id = isset($item['id']) ? $item['id'] : null;
    $item_type = isset($item['type']) ? $item['type'] : 'movie';
    
    if (!$vod_id) {
        error_log("[collection.php] ITEM_$item_index SKIP - No VOD ID");
        continue;
    }
    
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
    error_log("[collection.php] ITEM_$item_index XTREAM_API_CALL - URL: " . str_replace($pwd, '***', $url_info));
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_info);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_multi_add_handle($multi_handle, $ch);
    
    $curl_handles[$item_index] = $ch;
    $items_data[$item_index] = [
        'item' => $item,
        'vod_id' => $vod_id,
        'item_type' => $item_type,
        'start_time' => microtime(true)
    ];
}

// Ejecutar todas las llamadas en paralelo
$running = null;
do {
    curl_multi_exec($multi_handle, $running);
    curl_multi_select($multi_handle, 0.1);
} while ($running > 0);

$parallel_time = (microtime(true) - $parallel_start) * 1000;
error_log("[collection.php] PARALLEL_API_CALLS - Total Time: " . number_format($parallel_time, 2) . "ms for " . count($curl_handles) . " calls");

// Procesar resultados
foreach($curl_handles as $item_index => $ch) {
    $item_start = $items_data[$item_index]['start_time'];
    $item = $items_data[$item_index]['item'];
    $vod_id = $items_data[$item_index]['vod_id'];
    $item_type = $items_data[$item_index]['item_type'];
    
    $res_info = curl_multi_getcontent($ch);
    $xtream_time = (microtime(true) - $item_start) * 1000;
    
    $xtream_success = !empty($res_info);
    $data_info = json_decode($res_info, true);
    $xtream_has_data = !empty($data_info);
    
    error_log("[collection.php] ITEM_$item_index XTREAM_API_RESPONSE - Time: " . number_format($xtream_time, 2) . "ms, Success: " . ($xtream_success ? 'YES' : 'NO') . ", Has Data: " . ($xtream_has_data ? 'YES' : 'NO'));
    
    curl_multi_remove_handle($multi_handle, $ch);
    curl_close($ch);
    
    // Usar información del JSON primero (más rápido)
    $movie_name = isset($item['title']) ? $item['title'] : '';
    $stream_icon = isset($item['poster']) ? $item['poster'] : '';
    
    // Registrar llamada para JavaScript
    $api_calls_log[] = [
        'item_index' => $item_index,
        'vod_id' => $vod_id,
        'api' => 'Xtream UI',
        'endpoint' => 'get_vod_info',
        'time_ms' => round($xtream_time, 2),
        'success' => $xtream_success,
        'has_data' => $xtream_has_data,
        'movie_name' => $movie_name
    ];
    
    // Inicializar variables con valores por defecto
    $rating = '';
    $year = '';
    $duration = '';
    $country = '';
    $cast = '';
    $plot = '';
    $genre = '';
    $trailer = '';
    $info_data = [];
    $movie_data = [];
    
    if ($item_type === 'movie' && !empty($data_info['movie_data'])) {
        $movie_data = $data_info['movie_data'];
        $info_data = isset($data_info['info']) ? $data_info['info'] : [];
        // Actualizar nombre si viene de Xtream y es diferente
        if (!empty($movie_data['name']) && $movie_data['name'] !== $movie_name) {
            $movie_name = $movie_data['name'];
        }
    } elseif ($item_type === 'series' && !empty($data_info['info'])) {
        $info_data = $data_info['info'];
        if (!empty($info_data['name']) && $info_data['name'] !== $movie_name) {
            $movie_name = $info_data['name'];
        }
    }
    
    // Extraer información de Xtream (sobrescribe valores del JSON si están disponibles)
    if (!empty($info_data['movie_image']) && empty($stream_icon)) {
        $stream_icon = $info_data['movie_image'];
    }
    $rating = isset($info_data['rating']) ? $info_data['rating'] : '';
    $year = isset($info_data['releasedate']) ? substr($info_data['releasedate'], 0, 4) : '';
    $duration = isset($info_data['duration']) ? $info_data['duration'] : '';
    $country = isset($info_data['country']) ? $info_data['country'] : '';
    $cast = isset($info_data['cast']) ? $info_data['cast'] : '';
    $plot = isset($info_data['plot']) ? $info_data['plot'] : '';
    $genre = isset($info_data['genre']) ? $info_data['genre'] : '';
    $trailer = isset($info_data['youtube_trailer']) ? $info_data['youtube_trailer'] : '';
    
    $youtube_id = '';
    if (!empty($trailer)) {
        if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
            $youtube_id = $trailer;
        } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
            $youtube_id = $matches[1];
        }
    }
    
    $needs_tmdb = false;
    
    if (empty($plot) && empty($stream_icon) && empty($rating) && empty($year) && !empty($movie_name)) {
        $needs_tmdb = true;
        error_log("[collection.php] ITEM_$item_index TMDB ENABLED - Missing all critical info");
    }
    
    if ($needs_tmdb) {
        $tmdb_start = microtime(true);
        error_log("[collection.php] ITEM_$item_index TMDB_API_CALL - Title: $movie_name, Year: $year, Type: $item_type");
        
        $tmdb_info = getTmdbInfo($movie_name, $year, $item_type);
        $tmdb_time = (microtime(true) - $tmdb_start) * 1000;
        $tmdb_success = !empty($tmdb_info);
        
        error_log("[collection.php] ITEM_$item_index TMDB_API_RESPONSE - Time: " . number_format($tmdb_time, 2) . "ms, Success: " . ($tmdb_success ? 'YES' : 'NO') . " - Missing fields: $missing_fields");
        
        // Registrar llamada para JavaScript
        $api_calls_log[] = [
            'item_index' => $item_index,
            'vod_id' => $vod_id,
            'api' => 'TMDB',
            'endpoint' => 'search/' . ($item_type === 'series' ? 'tv' : 'movie'),
            'time_ms' => round($tmdb_time, 2),
            'success' => $tmdb_success,
            'has_data' => $tmdb_success,
            'movie_name' => $movie_name
        ];
        
        if ($tmdb_info) {
            if (empty($plot) && !empty($tmdb_info['overview'])) {
                $plot = $tmdb_info['overview'];
            }
            if (empty($rating) && !empty($tmdb_info['vote_average'])) {
                $rating = number_format($tmdb_info['vote_average'], 1);
            }
            if (empty($year)) {
                $release_date = !empty($tmdb_info['release_date']) ? $tmdb_info['release_date'] : (!empty($tmdb_info['first_air_date']) ? $tmdb_info['first_air_date'] : '');
                if ($release_date) {
                    $year = substr($release_date, 0, 4);
                }
            }
            if (empty($stream_icon) && !empty($tmdb_info['poster_path'])) {
                $stream_icon = 'https://image.tmdb.org/t/p/w500' . $tmdb_info['poster_path'];
            }
            if (empty($cast) && !empty($tmdb_info['cast'])) {
                $cast = $tmdb_info['cast'];
            }
            if (empty($genre) && !empty($tmdb_info['genres']) && is_array($tmdb_info['genres'])) {
                $genre = implode(', ', $tmdb_info['genres']);
            }
            if (empty($youtube_id) && !empty($tmdb_info['youtube_key'])) {
                $youtube_id = $tmdb_info['youtube_key'];
            }
        }
    }
    
    $item_time = (microtime(true) - $item_start) * 1000;
    error_log("[collection.php] ITEM_$item_index END - Processing Time: " . number_format($item_time, 2) . "ms");
    
    $peliculas[] = [
        'stream_id' => $vod_id,
        'name' => $movie_name,
        'stream_icon' => $stream_icon,
        'rating' => $rating,
        'rating_5based' => $rating,
        'year' => $year,
        'stream_type' => $item_type,
        'duration' => $duration,
        'country' => $country,
        'cast' => $cast,
        'plot' => $plot,
        'genre' => $genre,
        'youtube_id' => $youtube_id
    ];
}

// Cerrar el multi handle después de procesar todos los resultados
if (isset($multi_handle)) {
    curl_multi_close($multi_handle);
}

$output = $peliculas;

$backdrop_fondo = '';
if (!empty($peliculas) && is_array($peliculas) && count($peliculas) > 0) {
    $pelicula_aleatoria = $peliculas[array_rand($peliculas)];
    if (isset($pelicula_aleatoria['stream_id'])) {
    $vod_id = $pelicula_aleatoria['stream_id'];
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
    $res_info = apixtream($url_info);
    $data_info = json_decode($res_info, true);
    if (
        isset($data_info['info']['backdrop_path']) &&
        is_array($data_info['info']['backdrop_path']) &&
        count($data_info['info']['backdrop_path']) > 0
    ) {
        $backdrop_fondo = $data_info['info']['backdrop_path'][0];
        } elseif (isset($pelicula_aleatoria['stream_icon']) && !empty($pelicula_aleatoria['stream_icon'])) {
            $backdrop_fondo = $pelicula_aleatoria['stream_icon'];
        }
    }
}

$peliculas_pagina = $peliculas;

$page_total_time = (microtime(true) - $page_start_time) * 1000;

// Calcular estadísticas de llamadas API
$total_api_calls = count($api_calls_log);
$xtream_calls = array_filter($api_calls_log, function($call) { return $call['api'] === 'Xtream UI'; });
$tmdb_calls = array_filter($api_calls_log, function($call) { return $call['api'] === 'TMDB'; });

$xtream_total_time = array_sum(array_column($xtream_calls, 'time_ms'));
$tmdb_total_time = array_sum(array_column($tmdb_calls, 'time_ms'));

error_log("[collection.php] PAGE_END - Total Time: " . number_format($page_total_time, 2) . "ms");
error_log("[collection.php] API_CALLS_SUMMARY - Total Calls: $total_api_calls");
error_log("[collection.php] API_CALLS_SUMMARY - Xtream UI: " . count($xtream_calls) . " calls, Total Time: " . number_format($xtream_total_time, 2) . "ms, Avg: " . (count($xtream_calls) > 0 ? number_format($xtream_total_time / count($xtream_calls), 2) : 0) . "ms");
error_log("[collection.php] API_CALLS_SUMMARY - TMDB: " . count($tmdb_calls) . " calls, Total Time: " . number_format($tmdb_total_time, 2) . "ms, Avg: " . (count($tmdb_calls) > 0 ? number_format($tmdb_total_time / count($tmdb_calls), 2) : 0) . "ms");
error_log("[collection.php] API_CALLS_SUMMARY - Other Processing: " . number_format($page_total_time - $xtream_total_time - $tmdb_total_time, 2) . "ms");

$page_total_time = (microtime(true) - $page_start_time) * 1000;
error_log("[collection.php] PAGE_END - Total Time: " . number_format($page_total_time, 2) . "ms - Items Processed: " . count($peliculas_pagina));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/vendors/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/vendors/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/vendors/ionicons.min.css">
    <link rel="stylesheet" href="./styles/vendors/photoswipe.css">
    <link rel="stylesheet" href="./styles/vendors/glightbox.css">
    <link rel="stylesheet" href="./styles/vendors/default-skin.css">
    <link rel="stylesheet" href="./styles/vendors/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/vendors/select2.min.css">
    <link rel="stylesheet" href="./styles/core/main.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($saga_actual['nombre']); ?></title>
    <style>
    html, body {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
}
    .custom-paginator {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 40px 0 30px 0;
        padding: 0;
        list-style: none;
        background: #292933;
        border-radius: 6px;
        min-height: 48px;
        box-shadow: 0 2px 8px #0002;
        overflow: hidden;
        width: fit-content;
        min-width: 340px;
    }
    .custom-paginator li {
        margin: 0;
        display: flex;
        align-items: center;
    }
    .custom-paginator a, .custom-paginator span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 48px;
        color: #bfc1c8;
        background: transparent;
        border: none;
        font-size: 1.15rem;
        font-weight: 400;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
        cursor: pointer;
        outline: none;
        border-radius: 0;
    }
    .custom-paginator .active a,
    .custom-paginator .active span {
        background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);
        color: #fff;
        font-weight: 600;
        border-radius: 0;
    }
    .custom-paginator li:not(.active):hover a {
        background: #23232b;
        color: #fff;
    }
    .custom-paginator .disabled span {
        color: #555;
        cursor: default;
        background: transparent;
    }
    .custom-paginator .arrow {
        font-size: 1.3rem;
        color: #bfc1c8;
        padding: 0 10px;
    }
    .custom-paginator .arrow:hover {
        color: #fff;
        background: #23232b;
    }
    .header__content {
        background: #000 !important;
        border-radius: 12px;
        padding: 12px 24px;
    }
    .header__wrap {
        background: #000 !important;
    }
    .navbar-overlay {
        display: none !important;
    }
    .bg-animate {
        animation: navbarBgMove 8s linear infinite alternate;
        background-size: 200% 100%;
    }
    @keyframes navbarBgMove {
        0% { background-position: 0% 50%; }
        100% { background-position: 100% 50%; }
    }
    .modal-buscador-bg {
        display: none;
        position: fixed;
        z-index: 999999 !important;
        left: 0; top: 0;
        width: 100vw; height: 100vh;
        background: linear-gradient(135deg, rgba(15,32,39,0.95) 0%, rgba(44,83,100,0.95) 100%);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal-buscador-bg.active {
        display: flex;
        opacity: 1;
    }
    .modal-buscador {
        background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
        border-radius: 20px;
        padding: 0;
        max-width: 800px;
        width: 90vw;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.8);
        position: relative;
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid rgba(229,9,20,0.3);
    }
    .modal-buscador-bg.active .modal-buscador {
        transform: scale(1);
    }
    
    .modal-buscador-bg,
    .modal-buscador-bg * {
        z-index: 999999 !important;
    }
    
    .modal-buscador-bg {
        z-index: 999999 !important;
    }
    
    .modal-buscador {
        z-index: 999999 !important;
    }
    .modal-buscador-header {
        background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
        padding: 20px 24px;
        border-radius: 20px 20px 0 0;
        position: relative;
        overflow: hidden;
    }
    .modal-buscador-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
    .modal-buscador-title {
        color: #fff;
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .modal-buscador-close {
        position: absolute;
        top: 20px;
        right: 24px;
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.2);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 2;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-buscador-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }
    .modal-buscador-body {
        padding: 24px;
        max-height: calc(85vh - 100px);
        overflow-y: auto;
    }
    .modal-buscador-inputbox {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
        background: rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 4px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .modal-buscador-inputbox input {
        flex: 1;
        background: transparent;
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 1.1rem;
        margin: 0;
        outline: none;
    }
    .modal-buscador-inputbox input::placeholder {
        color: rgba(255,255,255,0.6);
    }
    .modal-buscador-inputbox button {
        background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: 16px 24px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-left: 4px;
        white-space: nowrap;
    }
    .modal-buscador-inputbox button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(229,9,20,0.4);
    }
    .modal-buscador-inputbox button:active {
        transform: translateY(0);
    }
    .modal-buscador-section {
        margin-bottom: 32px;
    }
    .modal-buscador-section h3 {
        color: #e50914;
        font-size: 1.2rem;
        margin-bottom: 20px;
        margin-top: 0;
        font-weight: 700;
        text-align: left;
        padding-left: 8px;
        border-left: 4px solid #e50914;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modal-buscador-section h3::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #e50914;
        border-radius: 50%;
    }
    .modal-buscador-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 16px;
        justify-content: start;
    }
    .modal-buscador-card {
        text-align: center;
        transition: transform 0.2s ease;
    }
    .modal-buscador-card:hover {
        transform: translateY(-4px);
    }
    .modal-buscador-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .modal-buscador-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 12px;
        background: #232027;
        margin-bottom: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    .modal-buscador-card:hover img {
        border-color: #e50914;
        box-shadow: 0 8px 24px rgba(229,9,20,0.3);
    }
    .modal-buscador-card span {
        color: #fff;
        font-size: 0.9rem;
        display: block;
        font-weight: 500;
        line-height: 1.3;
        padding: 0 4px;
        word-break: break-word;
    }
    .modal-buscador-empty {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255,255,255,0.7);
    }
    .modal-buscador-empty i {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    .modal-buscador-empty p {
        font-size: 1.1rem;
        margin: 0;
    }
    .modal-buscador-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filter-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-btn.active {
        background: #e50914;
        border-color: #e50914;
    }
    .filter-btn:hover {
        background: rgba(255,255,255,0.2);
    }
    .filter-btn.active:hover {
        background: #c8008f;
    }
    
    @media (min-width: 1200px) {
        .modal-buscador {
            max-width: 700px !important;
            width: 700px !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 1199px) {
        .modal-buscador {
            max-width: 750px !important;
            width: 85vw !important;
        }
    }
    
    @media (max-width: 600px) {
        .modal-buscador {
            width: 98vw !important;
            max-width: 98vw !important;
            border-radius: 16px !important;
            margin: 10px;
        }
        .modal-buscador-header {
            padding: 16px 20px;
            border-radius: 16px 16px 0 0;
        }
        .modal-buscador-title {
            font-size: 1.2rem;
        }
        .modal-buscador-body {
            padding: 20px 16px;
        }
        .modal-buscador-inputbox {
            flex-direction: column;
            gap: 12px;
            padding: 8px;
        }
        .modal-buscador-inputbox input {
            width: 100%;
            font-size: 1rem;
            padding: 14px 16px;
        }
        .modal-buscador-inputbox button {
            width: 100%;
            padding: 14px 20px;
            font-size: 1rem;
        }
        .modal-buscador-filters {
            justify-content: center;
            gap: 8px;
        }
        .filter-btn {
            padding: 10px 14px;
            font-size: 0.9rem;
            min-width: 80px;
        }
        .modal-buscador-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
        }
        .modal-buscador-card img {
            height: 140px;
        }
        .modal-buscador-card span {
            font-size: 0.85rem;
            line-height: 1.2;
        }
        .modal-buscador-close {
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }
    }
    
    #bg-overlay {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.78);
        z-index: 0;
        pointer-events: none;
    }
    body > *:not(#bg-overlay) {
        position: relative;
        z-index: 1;
    }
    .header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #000 !important;
        z-index: 2000 !important;
        box-shadow: none !important;
        border: none !important;
    }
    .header__wrap {
        background: #000 !important;
        border-radius: 0 !important;
    }
    .header__content {
        background: #000 !important;
        border-radius: 12px;
        padding: 12px 24px;
    }
    body {
        padding-top: 90px;
        overflow-x: hidden !important;
    }
    html {
        overflow-x: hidden !important;
    }
        .card__cover img {
    width: 100%;
    height: 440px;
    object-fit: cover;
    border-radius: 10px;
    background: #232027;
    display: block;
}
@media (max-width: 600px) {
  .card__cover img {
    height: 260px !important;
  }
}

            @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-right: auto !important;
            }
            .header__content {
                padding-left: 4px !important;
            }
        }

        @media (max-width: 600px) {
  .modal-buscador {
    max-width: 98vw !important;
    width: 98vw !important;
    min-width: 0 !important;
    padding: 18px 6px 16px 6px !important;
    border-radius: 14px !important;
    box-shadow: 0 4px 24px #000a !important;
    top: 10vw !important;
    left: 1vw !important;
    right: 1vw !important;
    position: relative !important;
  }
  .modal-buscador-inputbox {
    flex-direction: column !important;
    gap: 10px;
    margin-bottom: 18px !important;
  }
  .modal-buscador-inputbox input {
    font-size: 1.15rem !important;
    padding: 12px 10px !important;
    border-radius: 8px !important;
    width: 100% !important;
    margin-right: 0 !important;
  }
  .modal-buscador-inputbox button {
    width: 100% !important;
    font-size: 1.13rem !important;
    padding: 12px 0 !important;
    border-radius: 8px !important;
    margin-top: 4px;
  }
  .modal-buscador-close {
    top: 10px !important;
    right: 12px !important;
    font-size: 2.1rem !important;
    padding: 0 8px !important;
  }
  .modal-buscador-section h3 {
    font-size: 1.08rem !important;
    margin-bottom: 10px !important;
  }
  .modal-buscador-grid {
    gap: 10px !important;
  }
  .modal-buscador-card {
    width: 90px !important;
  }
  .modal-buscador-card img {
    height: 110px !important;
    border-radius: 8px !important;
  }
  .modal-buscador-card span {
    font-size: 0.93rem !important;
  }
}

        @media (min-width: 601px) {
            .header__logo img {
                width: 240px !important;
                height: 80px !important;
                max-width: none !important;
                object-fit: contain;
            }
        }
        @media (max-width: 600px) {
            .header__logo img {
                width: 240px !important;
                height: 60px !important;
                max-width: 100% !important;
                object-fit: contain;
            }
        }
        
        .modal-buscador-bg {
            z-index: 999999 !important;
            position: fixed !important;
        }
        
        .modal-buscador-bg * {
            z-index: 999999 !important;
        }
        
        .modal-buscador {
            z-index: 999999 !important;
            position: relative !important;
        }
    .collection-list-item {
        background: rgba(24, 24, 24, 0.9);
        border-radius: 12px;
        margin-bottom: 24px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .collection-list-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.5);
    }
    .collection-poster {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 8px;
        background: #232027;
    }
    .collection-info-title {
        color: #fff;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 12px;
        margin-top: 0;
    }
    .collection-info-genres {
        list-style: none;
        padding: 0;
        margin: 0 0 16px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .collection-info-genres li {
        background: rgba(255,255,255,0.1);
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    .collection-info-meta {
        list-style: none;
        padding: 0;
        margin: 0 0 16px 0;
        color: #fff;
    }
    .collection-info-meta li {
        margin-bottom: 8px;
        font-size: 1rem;
    }
    .collection-info-meta li strong {
        color: #e50914;
        margin-right: 8px;
    }
    .collection-info-plot {
        color: rgba(255,255,255,0.8);
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    .collection-buttons {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .collection-btn-trailer {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(90deg, #ff0000 60%, #c80000 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 22px;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        transition: all 0.2s;
        text-decoration: none;
    }
    .collection-btn-trailer:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255,0,0,0.4);
    }
    .collection-btn-fav {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(90deg, #232027 60%, #444 100%);
        color: #ffd700;
        border: none;
        border-radius: 8px;
        padding: 10px 22px;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        transition: all 0.2s;
    }
    .collection-btn-fav:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255,215,0,0.3);
    }
    .collection-btn-fav.favorito-active {
        background: linear-gradient(90deg, #ffd700 60%, #ffed4e 100%);
        color: #000;
    }
    #trailerModal {
        z-index: 99999 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        overflow-y: auto !important;
        display: none;
    }
    #trailerModal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    #trailerModal .modal-dialog {
        max-width: 900px;
        margin: 2rem auto;
        z-index: 100000 !important;
        position: relative;
    }
    #trailerModal .modal-content {
        background: #000;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.9);
    }
    #trailerModal .modal-header {
        border-bottom: 1px solid #333;
        padding: 16px 20px;
        background: #000;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    #trailerModal .modal-title {
        color: #fff;
        font-weight: 600;
        margin: 0;
    }
    #trailerModal .modal-body {
        padding: 0;
        background: #000;
    }
    #trailerModal .ratio {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
    }
    #trailerModal .ratio iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    #trailerModal .btn-close {
        filter: invert(1);
        opacity: 0.8;
        cursor: pointer;
        background: transparent;
        border: none;
        font-size: 1.5rem;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #trailerModal .btn-close:hover {
        opacity: 1;
    }
    #trailerModal .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    .modal-backdrop.show {
        z-index: 99998 !important;
        background-color: rgba(0, 0, 0, 0.75) !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
    }
    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }
    @media (max-width: 768px) {
        .collection-poster {
            height: 300px;
            margin-bottom: 20px;
        }
        .collection-info-title {
            font-size: 1.4rem;
        }
        .collection-buttons {
            flex-direction: column;
        }
        .collection-btn-trailer,
        .collection-btn-fav {
            width: 100%;
            justify-content: center;
        }
    }
    .section.details {
        position: relative;
        }
    </style>
<?php if($backdrop_fondo): ?>
<style>
body {
    background: url('<?php echo htmlspecialchars($backdrop_fondo, ENT_QUOTES); ?>') no-repeat center center fixed !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    background: rgba(0,0,0,0.75);
    pointer-events: none;
}
</style>
<?php endif; ?>
</head>

<body class="body">

<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="login.php">
                            <img src="assets/logo/logo.png" alt="">
                        </a>
                        <ul class="header__nav d-flex align-items-center mb-0">
                            <li class="header__nav-item">
                                <a href="./home.php" class="header__nav-link">Inicio</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="./channels.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="movies.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link">Series</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="sagas.php" class="header__nav-link header__nav-link--active">Sagas</a>
                            </li>
                        </ul>
                        <div class="header__auth d-flex align-items-center">
                            <button class="header__search-btn" type="button" id="openSearchModal">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="profile.php">
                                <button class="header__signout-btn" type="button">
                                    <i class="fas fa-user"></i>
                                </button>
                            </a>
                        </div>
                        <button class="header__btn" type="button">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div id="bg-overlay"></div>

<?php include_once __DIR__ . '/libs/views/search.php'; ?>

<div style="width:100%;text-align:center;margin:120px 0 15px 0;">
    <h2 style="font-size:2.5rem;font-weight:800;letter-spacing:2px;color:#fff;display:inline-block;padding:10px 40px;border-radius:12px;"><?php echo htmlspecialchars($saga_actual['nombre']); ?></h2>
</div>
<section class="section details">
    <div class="container" style="margin-top: 0;">
        <div class="row">
<?php
if ($peliculas_pagina && is_array($peliculas_pagina)) {
    foreach($peliculas_pagina as $index) {
        $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $index['name']);
        $filme_type = $index['stream_type'];
        $filme_id = $index['stream_id'];
        $filme_img = $index['stream_icon'];
        $filme_rat = isset($index['rating']) ? $index['rating'] : '';
        $filme_ano = isset($index['year']) ? $index['year'] : '';
        $filme_duration = isset($index['duration']) ? $index['duration'] : '';
        $filme_country = isset($index['country']) ? $index['country'] : '';
        $filme_cast = isset($index['cast']) ? $index['cast'] : '';
        $filme_plot = isset($index['plot']) ? $index['plot'] : '';
        $filme_genre = isset($index['genre']) ? $index['genre'] : '';
        $youtube_id = isset($index['youtube_id']) ? $index['youtube_id'] : '';
        
        if (empty($youtube_id) && isset($index['stream_id'])) {
            $vod_id_temp = $index['stream_id'];
            $url_info_temp = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id_temp";
            $res_info_temp = apixtream($url_info_temp);
            $data_info_temp = json_decode($res_info_temp, true);
            
            if (!empty($data_info_temp) && isset($data_info_temp['info'])) {
                if (!empty($data_info_temp['info']['youtube_trailer'])) {
                    $trailer_temp = $data_info_temp['info']['youtube_trailer'];
                    if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer_temp)) {
                        $youtube_id = $trailer_temp;
                    } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer_temp, $matches)) {
                        $youtube_id = $matches[1];
                    }
                }
            }
        }
        
        $has_trailer = !empty($youtube_id);
?>
            <div class="col-12">
                <div class="collection-list-item">
                    <div class="row">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                    <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                                <img loading="lazy" src="<?php echo htmlspecialchars($filme_img); ?>" alt="<?php echo htmlspecialchars($filme_nome); ?>" class="collection-poster">
                    </a>
            </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <h2 class="collection-info-title">
                                <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" style="color: #fff; text-decoration: none;">
                                    <?php echo htmlspecialchars($filme_nome); ?>
                                </a>
                            </h2>
                            <?php if (!empty($filme_genre)): ?>
                            <ul class="collection-info-genres">
<?php
                                $genres = is_array($filme_genre) ? $filme_genre : explode(',', $filme_genre);
                                foreach($genres as $g): 
                                    $genre_trimmed = trim($g);
                                    if (!empty($genre_trimmed)):
                                ?>
                                    <li><?php echo htmlspecialchars($genre_trimmed); ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                            <?php endif; ?>
                            <div style="margin-bottom: 16px;">
                                <span style="color: #fff; font-size: 1.1rem;">
                                    <?php echo $filme_ano; ?>
                                    <?php if (!empty($filme_rat)): ?>
                                        &nbsp; <i class="fa-solid fa-star" style="color: #e50914;"></i> <?php echo $filme_rat; ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <ul class="collection-info-meta">
                                <?php if (!empty($filme_duration)): 
                                    $duracao_formatted = $filme_duration;
                                    if (is_numeric($filme_duration)) {
                                        $hours = floor($filme_duration / 3600);
                                        $minutes = floor(($filme_duration % 3600) / 60);
                                        $seconds = $filme_duration % 60;
                                        if ($hours > 0) {
                                            $duracao_formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
} else {
                                            $duracao_formatted = sprintf("%02d:%02d", $minutes, $seconds);
                                        }
                                    }
                                ?>
                                <li><strong>Duración:</strong> <?php echo htmlspecialchars($duracao_formatted); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($filme_country)): ?>
                                <li><strong>País:</strong> <?php echo htmlspecialchars($filme_country); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($filme_cast)): ?>
                                <li><strong>Reparto:</strong> <?php echo htmlspecialchars($filme_cast); ?></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (!empty($filme_plot)): ?>
                            <div class="collection-info-plot">
                                <?php echo htmlspecialchars($filme_plot); ?>
        </div>
                            <?php endif; ?>
                            <div class="collection-buttons">
                                <button class="collection-btn-trailer" data-youtube-id="<?php echo $has_trailer ? htmlspecialchars($youtube_id) : ''; ?>" data-movie-title="<?php echo htmlspecialchars($filme_nome); ?>" <?php echo $has_trailer ? '' : 'disabled'; ?> style="<?php echo $has_trailer ? 'cursor:pointer;' : 'opacity:0.5;cursor:not-allowed;'; ?>">
                                    <i class="fab fa-youtube" style="font-size:1.5rem;"></i>
                                    <span>Tráiler</span>
                                </button>
                                <button class="collection-btn-fav" data-movie-id="<?php echo $filme_id; ?>" data-movie-name="<?php echo htmlspecialchars($filme_nome); ?>" data-movie-img="<?php echo htmlspecialchars($filme_img); ?>" data-movie-year="<?php echo htmlspecialchars($filme_ano); ?>" data-movie-rating="<?php echo htmlspecialchars($filme_rat); ?>">
                                    <i class="fa fa-star" style="font-size:1.4rem;"></i>
                                    <span class="fav-text-<?php echo $filme_id; ?>">Agregar a Favoritos</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    <?php
                    }
                    } else {
    echo '<div class="col-12" style="color:#fff;font-size:1.2rem;text-align:center;">No hay películas disponibles en esta saga.</div>';
                    }
                    ?>
            </div>
        </div>
</section>

<div class="modal fade" id="trailerModal" tabindex="-1" role="dialog" aria-labelledby="trailerModalTitle" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trailerModalTitle">Tráiler</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar" tabindex="0"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footer__copyright">
                    &copy; <?php echo date('Y'); ?> <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="assets/logo/logo.png"> PLAYGO
                </div>
            </div>
        </div>
    </div>
</footer>
<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap.bundle.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js"></script>
<script src="./scripts/vendors/jquery.mousewheel.min.js"></script>
<script src="./scripts/vendors/jquery.mcustomscrollbar.min.js"></script>
<script src="./scripts/vendors/wnumb.js"></script>
<script src="./scripts/vendors/jquery.morelines.min.js"></script>
<script src="./scripts/vendors/photoswipe.min.js"></script>
<script src="./scripts/vendors/photoswipe-ui-default.min.js"></script>
<script src="./scripts/vendors/glightbox.min.js"></script>
<script src="./scripts/vendors/jBox.all.min.js"></script>
<script src="./scripts/vendors/select2.min.js"></script>
<script src="./scripts/vendors/jwplayer.js"></script>
<script src="./scripts/vendors/jwplayer.core.controls.js"></script>
<script src="./scripts/vendors/provider.hlsjs.js"></script>
<script src="./scripts/core/main.js"></script>

<script>
console.log('[collection.php] Page loaded - Saga ID: <?php echo htmlspecialchars($saga_id); ?>');
console.log('[collection.php] Items count: <?php echo count($peliculas_pagina); ?>');
console.log('[collection.php] Page generation time: <?php echo isset($page_total_time) ? number_format($page_total_time, 2) : 'N/A'; ?>ms');

// Mostrar información detallada de llamadas API
var apiCallsLog = <?php echo json_encode($api_calls_log ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>;

console.log('[collection.php] ========== API CALLS DETAILED LOG ==========');
console.log('[collection.php] Total API Calls: ' + apiCallsLog.length);

var xtreamCalls = apiCallsLog.filter(function(call) { return call.api === 'Xtream UI'; });
var tmdbCalls = apiCallsLog.filter(function(call) { return call.api === 'TMDB'; });

console.log('[collection.php] --- Xtream UI Calls: ' + xtreamCalls.length + ' ---');
xtreamCalls.forEach(function(call, index) {
    console.log('[collection.php] Xtream Call #' + (index + 1) + ':', {
        'Item': call.item_index + 1,
        'VOD ID': call.vod_id,
        'Movie': call.movie_name,
        'Time': call.time_ms + 'ms',
        'Success': call.success ? '✓' : '✗',
        'Has Data': call.has_data ? '✓' : '✗'
    });
});

if (tmdbCalls.length > 0) {
    console.log('[collection.php] --- TMDB Calls: ' + tmdbCalls.length + ' ---');
    tmdbCalls.forEach(function(call, index) {
        console.log('[collection.php] TMDB Call #' + (index + 1) + ':', {
            'Item': call.item_index + 1,
            'VOD ID': call.vod_id,
            'Movie': call.movie_name,
            'Endpoint': call.endpoint,
            'Time': call.time_ms + 'ms',
            'Success': call.success ? '✓' : '✗',
            'Has Data': call.has_data ? '✓' : '✗'
        });
    });
}

// Resumen de tiempos
var xtreamTotalTime = xtreamCalls.reduce(function(sum, call) { return sum + call.time_ms; }, 0);
var tmdbTotalTime = tmdbCalls.reduce(function(sum, call) { return sum + call.time_ms; }, 0);
var xtreamAvgTime = xtreamCalls.length > 0 ? (xtreamTotalTime / xtreamCalls.length).toFixed(2) : 0;
var tmdbAvgTime = tmdbCalls.length > 0 ? (tmdbTotalTime / tmdbCalls.length).toFixed(2) : 0;

console.log('[collection.php] --- API CALLS SUMMARY ---');
console.log('[collection.php] Xtream UI: ' + xtreamCalls.length + ' calls, Total: ' + xtreamTotalTime.toFixed(2) + 'ms, Avg: ' + xtreamAvgTime + 'ms');
if (tmdbCalls.length > 0) {
    console.log('[collection.php] TMDB: ' + tmdbCalls.length + ' calls, Total: ' + tmdbTotalTime.toFixed(2) + 'ms, Avg: ' + tmdbAvgTime + 'ms');
}
console.log('[collection.php] ============================================');

(function() {
    function initTrailerModal() {
        const trailerButtons = document.querySelectorAll('.collection-btn-trailer');
        const trailerModalElement = document.getElementById('trailerModal');
        const trailerIframe = document.getElementById('trailerIframe');
        const trailerModalTitle = document.getElementById('trailerModalTitle');

        if (!trailerModalElement || !trailerIframe) {
            console.error('Modal elements not found');
            return;
        }

        let trailerModal = null;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                trailerModal = new bootstrap.Modal(trailerModalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
            } catch (e) {
                console.error('Error creating Bootstrap modal:', e);
            }
        } else {
            console.warn('Bootstrap Modal not available, using jQuery fallback');
        }

        console.log('Found', trailerButtons.length, 'trailer buttons');
        
        trailerButtons.forEach(function(btn, index) {
            let youtubeId = btn.getAttribute('data-youtube-id');
            const isDisabled = btn.hasAttribute('disabled') || btn.disabled;
            console.log('Button', index, 'youtubeId:', youtubeId, 'disabled:', isDisabled, 'hasAttribute:', btn.hasAttribute('disabled'));
            
            if (!youtubeId || youtubeId === '' || youtubeId === 'null' || youtubeId === 'undefined') {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
                console.log('Button', index, 'disabled - no youtubeId');
            } else {
                btn.disabled = false;
                btn.removeAttribute('disabled');
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                console.log('Button', index, 'enabled - youtubeId:', youtubeId);
            }
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const currentYoutubeId = btn.getAttribute('data-youtube-id');
                const movieTitle = btn.getAttribute('data-movie-title');
                
                console.log('Button clicked!', {
                    disabled: btn.disabled,
                    youtubeId: currentYoutubeId,
                    hasModal: !!trailerModal
                });
                
                if (btn.disabled || !currentYoutubeId || currentYoutubeId === '' || currentYoutubeId === 'null' || currentYoutubeId === 'undefined') {
                    alert('Tráiler no disponible para esta película');
                    return;
                }
                
                if (currentYoutubeId && currentYoutubeId !== '' && currentYoutubeId !== 'null' && currentYoutubeId !== 'undefined') {
                    if (trailerModalTitle) {
                        trailerModalTitle.textContent = movieTitle ? movieTitle + ' - Tráiler' : 'Tráiler';
                    }
                    
                    const embedUrl = "https://www.youtube.com/embed/" + currentYoutubeId + "?autoplay=1&rel=0&modestbranding=1";
                    console.log('Setting iframe src to:', embedUrl);
                    trailerIframe.src = embedUrl;
                    
                    if (trailerModal) {
                        console.log('Using Bootstrap modal');
                        trailerModalElement.setAttribute('aria-hidden', 'false');
                        trailerModal.show();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        console.log('Using jQuery modal');
                        trailerModalElement.setAttribute('aria-hidden', 'false');
                        $(trailerModalElement).modal('show');
                    } else {
                        console.log('Using manual modal');
                        trailerModalElement.style.display = 'block';
                        trailerModalElement.classList.add('show');
                        trailerModalElement.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.style.zIndex = '9999';
                        backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.75)';
                        backdrop.style.position = 'fixed';
                        backdrop.style.top = '0';
                        backdrop.style.left = '0';
                        backdrop.style.width = '100%';
                        backdrop.style.height = '100%';
                        document.body.appendChild(backdrop);
                        
                        backdrop.addEventListener('click', function() {
                            closeTrailerModal();
                        });
                    }
                    
                    function closeTrailerModal() {
                        trailerModalElement.style.display = 'none';
                        trailerModalElement.classList.remove('show');
                        trailerModalElement.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        if (trailerIframe) {
                            trailerIframe.src = '';
                        }
                    }
                } else {
                    console.warn('No valid youtubeId found:', youtubeId);
                    alert('Tráiler no disponible para esta película');
                }
            });
        });

        if (trailerModalElement) {
            trailerModalElement.addEventListener('hidden.bs.modal', function () {
                trailerModalElement.setAttribute('aria-hidden', 'true');
                if (trailerIframe) {
                    trailerIframe.src = "";
                }
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
            });
            
            trailerModalElement.addEventListener('hide.bs.modal', function () {
                if (trailerIframe) {
                    trailerIframe.src = "";
                }
            });
            
            const closeBtn = trailerModalElement.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    if (trailerModal) {
                        trailerModal.hide();
                    } else if (typeof $ !== 'undefined' && $.fn.modal) {
                        $(trailerModalElement).modal('hide');
                    } else {
                        trailerModalElement.style.display = 'none';
                        trailerModalElement.classList.remove('show');
                        trailerModalElement.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        if (trailerIframe) {
                            trailerIframe.src = '';
                        }
                    }
                });
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrailerModal);
    } else {
        initTrailerModal();
    }
})();

document.addEventListener('DOMContentLoaded', function() {
    const favButtons = document.querySelectorAll('.collection-btn-fav');
    favButtons.forEach(function(btn) {
        const movieId = btn.getAttribute('data-movie-id');
        const favText = btn.querySelector('.fav-text-' + movieId);
        let isFav = false;

        fetch('libs/endpoints/UserData.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=fav_check&id=' + movieId + '&tipo=pelicula'
        })
        .then(res => res.json())
        .then(data => {
            if (data.is_fav) {
                isFav = true;
                if (favText) favText.textContent = 'Favorito';
                btn.classList.add('favorito-active');
            }
        })
        .catch(err => console.error('Error checking favorite:', err));

        btn.addEventListener('click', function() {
            const action = isFav ? 'fav_remove' : 'fav_add';
            const movieName = btn.getAttribute('data-movie-name');
            const movieImg = btn.getAttribute('data-movie-img');
            const movieYear = btn.getAttribute('data-movie-year');
            const movieRating = btn.getAttribute('data-movie-rating');

            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=' + action + '&id=' + movieId + '&nombre=' + encodeURIComponent(movieName) + '&img=' + encodeURIComponent(movieImg) + '&ano=' + encodeURIComponent(movieYear) + '&rate=' + encodeURIComponent(movieRating) + '&tipo=pelicula'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    isFav = !isFav;
                    if (favText) favText.textContent = isFav ? 'Favorito' : 'Agregar a Favoritos';
                    btn.classList.toggle('favorito-active', isFav);
                }
            })
            .catch(err => console.error('Error updating favorite:', err));
        });
    });
});
</script>

</body>
</html>

