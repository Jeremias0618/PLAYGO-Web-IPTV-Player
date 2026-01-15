<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

require_once(__DIR__ . '/../services/collection.php');

function getCollectionData($saga_id, $user, $pwd) {
    $saga_actual = loadSagaFromJson($saga_id);
    
    if (!$saga_actual || empty($saga_actual['items'])) {
        return null;
    }
    
    $items = $saga_actual['items'];
    
    usort($items, function($a, $b) {
        $orderA = isset($a['order']) ? intval($a['order']) : 999;
        $orderB = isset($b['order']) ? intval($b['order']) : 999;
        return $orderA <=> $orderB;
    });
    
    $items_data = fetchItemsInParallel($items, $user, $pwd);
    
    $peliculas = [];
    foreach($items_data as $item_data) {
        $pelicula = processItemData($item_data, $user, $pwd);
        if ($pelicula) {
            $peliculas[] = $pelicula;
        }
    }
    
    $backdrop_fondo = getCollectionBackdrop($peliculas, $user, $pwd);
    
    return [
        'saga' => $saga_actual,
        'peliculas' => $peliculas,
        'backdrop_fondo' => $backdrop_fondo
    ];
}

