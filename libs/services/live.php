<?php

require_once(__DIR__ . '/../lib.php');

function getLiveCategories($user, $pwd) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_categories";
    $res = apixtream($url);
    $categories = json_decode($res, true);
    
    if (!is_array($categories)) {
        return [];
    }
    
    return $categories;
}

function getLiveStreams($user, $pwd, $categoryId = null) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
    if ($categoryId) {
        $url .= "&category_id=" . urlencode($categoryId);
    }
    
    $res = apixtream($url);
    $streams = json_decode($res, true);
    
    if (!is_array($streams)) {
        return [];
    }
    
    return $streams;
}

function getChannelLogo($channelId, $defaultIcon) {
    global $customChannelLogos;
    
    if (!isset($customChannelLogos) || !is_array($customChannelLogos)) {
        return $defaultIcon;
    }
    
    if (isset($customChannelLogos[$channelId])) {
        $customPath = __DIR__ . '/../../' . $customChannelLogos[$channelId];
        if (file_exists($customPath)) {
            return $customChannelLogos[$channelId];
        }
    }
    
    return $defaultIcon;
}

function getChannelDescription($channelData) {
    if (!empty($channelData['plot'])) {
        return $channelData['plot'];
    } elseif (!empty($channelData['description'])) {
        return $channelData['description'];
    } elseif (!empty($channelData['category_name'])) {
        return 'Categoría: ' . $channelData['category_name'];
    }
    
    return '';
}

