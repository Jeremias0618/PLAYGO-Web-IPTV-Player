<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

require_once(__DIR__ . '/../services/collection.php');

function getCollectionData($saga_id, $user, $pwd) {
    $page_start_time = microtime(true);
    error_log("[collection.php] ========== PAGE LOAD START ==========");
    error_log("[collection.php] Saga ID: {$saga_id}");
    
    $load_start = microtime(true);
    $saga_actual = loadSagaFromJson($saga_id);
    $load_time = (microtime(true) - $load_start) * 1000;
    error_log("[collection.php] loadSagaFromJson completed in " . number_format($load_time, 2) . "ms");
    
    if (!$saga_actual || empty($saga_actual['items'])) {
        error_log("[collection.php] ERROR: No saga found or empty items");
        return null;
    }
    
    $items = $saga_actual['items'];
    error_log("[collection.php] Total items in saga: " . count($items));
    
    $sort_start = microtime(true);
    usort($items, function($a, $b) {
        $orderA = isset($a['order']) ? intval($a['order']) : 999;
        $orderB = isset($b['order']) ? intval($b['order']) : 999;
        return $orderA <=> $orderB;
    });
    $sort_time = (microtime(true) - $sort_start) * 1000;
    error_log("[collection.php] Items sorted in " . number_format($sort_time, 2) . "ms");
    
    $fetch_start = microtime(true);
    $items_data = fetchItemsInParallel($items, $user, $pwd);
    $fetch_time = (microtime(true) - $fetch_start) * 1000;
    error_log("[collection.php] fetchItemsInParallel completed in " . number_format($fetch_time, 2) . "ms");
    error_log("[collection.php] Items data received: " . count($items_data));
    
    $process_start = microtime(true);
    $peliculas = [];
    $item_num = 0;
    foreach($items_data as $item_index => $item_data) {
        $item_num++;
        $item_process_start = microtime(true);
        $item_name = isset($item_data['item']['title']) ? $item_data['item']['title'] : "Item #{$item_index}";
        error_log("[collection.php] Processing item #{$item_num} ({$item_name})");
        
        $pelicula = processItemData($item_data, $user, $pwd);
        $item_process_time = (microtime(true) - $item_process_start) * 1000;
        
        if ($pelicula) {
            $peliculas[] = $pelicula;
            error_log("[collection.php] Item #{$item_num} ({$item_name}) - Processed successfully in " . number_format($item_process_time, 2) . "ms");
            error_log("[collection.php] Item #{$item_num} - Final data: Name=" . ($pelicula['name'] ?? 'N/A') . ", Year=" . ($pelicula['year'] ?? 'N/A') . ", Rating=" . ($pelicula['rating'] ?? 'N/A') . ", Plot=" . (!empty($pelicula['plot']) ? 'YES' : 'NO'));
        } else {
            error_log("[collection.php] Item #{$item_num} ({$item_name}) - WARNING: processItemData returned null");
        }
    }
    $process_time = (microtime(true) - $process_start) * 1000;
    error_log("[collection.php] All items processed in " . number_format($process_time, 2) . "ms");
    error_log("[collection.php] Total peliculas ready: " . count($peliculas));
    
    $backdrop_start = microtime(true);
    $backdrop_fondo = getCollectionBackdrop($peliculas, $user, $pwd);
    $backdrop_time = (microtime(true) - $backdrop_start) * 1000;
    error_log("[collection.php] getCollectionBackdrop completed in " . number_format($backdrop_time, 2) . "ms");
    
    $total_page_time = (microtime(true) - $page_start_time) * 1000;
    error_log("[collection.php] ========== PAGE LOAD END ==========");
    error_log("[collection.php] Total page generation time: " . number_format($total_page_time, 2) . "ms");
    error_log("[collection.php] Breakdown: Load=" . number_format($load_time, 2) . "ms, Sort=" . number_format($sort_time, 2) . "ms, Fetch=" . number_format($fetch_time, 2) . "ms, Process=" . number_format($process_time, 2) . "ms, Backdrop=" . number_format($backdrop_time, 2) . "ms");
    
    return [
        'saga' => $saga_actual,
        'peliculas' => $peliculas,
        'backdrop_fondo' => $backdrop_fondo
    ];
}

