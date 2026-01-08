<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function getSeriesEpisodesImages($tmdb_id, $episodios) {
    if (!$tmdb_id || !is_array($episodios) || empty($episodios)) {
        return [];
    }
    
    $cache_dir = __DIR__ . '/../../assets/tmdb_cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    
    $tmdb_episodios_imgs = [];
    $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
    
    foreach ($episodios as $season_num => $eps) {
        if (!is_array($eps) || empty($eps)) {
            continue;
        }
        
        $needs_api_call = false;
        $episode_numbers = [];
        foreach ($eps as $ep) {
            $ep_num = $ep['episode_num'] ?? '';
            if ($ep_num) {
                $episode_numbers[] = $ep_num;
                $img_local = $cache_dir . "{$tmdb_id}_{$season_num}_{$ep_num}.jpg";
                if (!file_exists($img_local)) {
                    $needs_api_call = true;
                }
            }
        }
        
        if (!$needs_api_call && !empty($episode_numbers)) {
            foreach ($episode_numbers as $ep_num) {
                $img_local = $cache_dir . "{$tmdb_id}_{$season_num}_{$ep_num}.jpg";
                $img_local_url = "assets/tmdb_cache/{$tmdb_id}_{$season_num}_{$ep_num}.jpg";
                if (file_exists($img_local)) {
                    $tmdb_episodios_imgs[$season_num][$ep_num] = $img_local_url;
                }
            }
            continue;
        }
        
        $tmdb_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season_num?api_key=" . TMDB_API_KEY . "&language=" . $language;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tmdb_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        $tmdb_json = @curl_exec($ch);
        curl_close($ch);
        
        $tmdb_data = json_decode($tmdb_json, true);
        
        if (!empty($tmdb_data['episodes'])) {
            foreach ($tmdb_data['episodes'] as $ep) {
                if (!empty($ep['still_path']) && !empty($ep['episode_number'])) {
                    $ep_num = $ep['episode_number'];
                    $img_url = "https://image.tmdb.org/t/p/w500" . $ep['still_path'];
                    $img_local = $cache_dir . "{$tmdb_id}_{$season_num}_{$ep_num}.jpg";
                    $img_local_url = "assets/tmdb_cache/{$tmdb_id}_{$season_num}_{$ep_num}.jpg";
                    
                    if (!file_exists($img_local)) {
                        $img_ch = curl_init();
                        curl_setopt($img_ch, CURLOPT_URL, $img_url);
                        curl_setopt($img_ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($img_ch, CURLOPT_TIMEOUT, 3);
                        curl_setopt($img_ch, CURLOPT_CONNECTTIMEOUT, 2);
                        curl_setopt($img_ch, CURLOPT_SSL_VERIFYPEER, false);
                        $img_data = @curl_exec($img_ch);
                        curl_close($img_ch);
                        if ($img_data) {
                            @file_put_contents($img_local, $img_data);
                        }
                    }
                    
                    if (file_exists($img_local)) {
                        $tmdb_episodios_imgs[$season_num][$ep_num] = $img_local_url;
                    } else {
                        $tmdb_episodios_imgs[$season_num][$ep_num] = $img_url;
                    }
                }
            }
        }
    }
    
    return $tmdb_episodios_imgs;
}

