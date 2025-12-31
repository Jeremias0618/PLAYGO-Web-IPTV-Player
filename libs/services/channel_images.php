<?php

require_once(__DIR__ . '/../lib.php');

function downloadChannelImage($imageUrl, $channelId) {
    if (empty($imageUrl) || empty($channelId)) {
        return false;
    }
    
    $channelsDir = __DIR__ . '/../../assets/channels/';
    
    if (!is_dir($channelsDir)) {
        if (!mkdir($channelsDir, 0755, true)) {
            return false;
        }
    }
    
    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (empty($extension)) {
        $extension = 'jpg';
    }
    
    $filename = 'channel_' . $channelId . '.' . $extension;
    $filepath = $channelsDir . $filename;
    
    if (file_exists($filepath)) {
        return 'assets/channels/' . $filename;
    }
    
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $imageData !== false && !empty($imageData)) {
        if (file_put_contents($filepath, $imageData) !== false) {
            return 'assets/channels/' . $filename;
        }
    }
    
    return false;
}

function getLocalChannelImage($channelId, $remoteUrl) {
    $channelsDir = __DIR__ . '/../../assets/channels/';
    
    if (!is_dir($channelsDir)) {
        return $remoteUrl;
    }
    
    $files = glob($channelsDir . 'channel_' . $channelId . '.*');
    
    if (!empty($files) && file_exists($files[0])) {
        $filename = basename($files[0]);
        return 'assets/channels/' . $filename;
    }
    
    return $remoteUrl;
}

function downloadChannelsImages($streams, $limit = 50) {
    $downloaded = 0;
    $failed = 0;
    $skipped = 0;
    $processed = 0;
    
    foreach($streams as $stream) {
        if ($processed >= $limit) {
            break;
        }
        
        $channelId = isset($stream['stream_id']) ? $stream['stream_id'] : null;
        $imageUrl = isset($stream['stream_icon']) ? $stream['stream_icon'] : '';
        
        if (empty($channelId) || empty($imageUrl)) {
            continue;
        }
        
        $localPath = getLocalChannelImage($channelId, $imageUrl);
        
        if (strpos($localPath, 'assets/channels/') === 0) {
            $skipped++;
            continue;
        }
        
        $result = downloadChannelImage($imageUrl, $channelId);
        $processed++;
        
        if ($result !== false) {
            $downloaded++;
        } else {
            $failed++;
        }
        
        if ($processed % 10 === 0) {
            usleep(50000);
        }
    }
    
    return [
        'downloaded' => $downloaded,
        'failed' => $failed,
        'skipped' => $skipped,
        'processed' => $processed
    ];
}

