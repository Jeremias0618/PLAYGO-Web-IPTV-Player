<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getChannelLogo')) {
    require_once(__DIR__ . '/../services/live.php');
}

function getChannelData($user, $pwd, $streamId) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
    $resposta = apixtream($url);
    $canales = json_decode($resposta, true);
    
    if (!is_array($canales)) {
        return null;
    }
    
    foreach($canales as $c) {
        if($c['stream_id'] == $streamId) {
            return $c;
        }
    }
    
    return null;
}

function getChannelInfo($canalData) {
    if (!empty($canalData['plot'])) {
        return $canalData['plot'];
    } elseif (!empty($canalData['description'])) {
        return $canalData['description'];
    } elseif (!empty($canalData['category_name'])) {
        return 'Categoría: ' . $canalData['category_name'];
    }
    return '';
}

function getChannelEPG($user, $pwd, $streamId) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_short_epg&stream_id=$streamId";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (isset($output['epg_listings'])) {
        return $output['epg_listings'];
    }
    
    return [];
}

function getRecommendedChannels($user, $pwd, $currentStreamId, $categoryId = null, $limit = 5) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams".($categoryId ? "&category_id=$categoryId" : "");
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    $output = array_filter($output, function($c) use ($currentStreamId) { 
        return $c['stream_id'] != $currentStreamId; 
    });
    
    shuffle($output);
    return array_slice($output, 0, $limit);
}

function limpiarFechaEPG($texto) {
    $partes = explode('-', $texto, 2);
    return trim($partes[0]);
}

