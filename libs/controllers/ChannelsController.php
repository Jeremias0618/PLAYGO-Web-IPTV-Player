<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getLiveCategories')) {
    require_once(__DIR__ . '/../services/live.php');
}

if (!function_exists('limitar_texto')) {
    require_once(__DIR__ . '/../lib.php');
}

function processChannelsData($streams) {
    $processed = [];
    
    foreach($streams as $stream) {
        $channelId = $stream['stream_id'];
        $defaultIcon = isset($stream['stream_icon']) ? $stream['stream_icon'] : '';
        $logo = getChannelLogo($channelId, $defaultIcon);
        $description = getChannelDescription($stream);
        
        $processed[] = [
            'id' => $channelId,
            'name' => $stream['name'],
            'type' => isset($stream['stream_type']) ? $stream['stream_type'] : 'live',
            'logo' => $logo,
            'description' => $description,
            'category_id' => isset($stream['category_id']) ? $stream['category_id'] : '',
            'category_name' => isset($stream['category_name']) ? $stream['category_name'] : ''
        ];
    }
    
    return $processed;
}

function getChannelsPageData($user, $pwd, $categoryId = null) {
    $categories = getLiveCategories($user, $pwd);
    $streams = getLiveStreams($user, $pwd, $categoryId);
    
    if (!function_exists('downloadChannelsImages')) {
        require_once(__DIR__ . '/../services/channel_images.php');
    }
    
    if (!empty($streams)) {
        downloadChannelsImages($streams, 20);
    }
    
    $processedStreams = processChannelsData($streams);
    
    return [
        'categories' => $categories,
        'streams' => $processedStreams
    ];
}

