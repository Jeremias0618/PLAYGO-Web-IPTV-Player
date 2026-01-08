<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function getSeriesEpisodesImages($tmdb_id, $episodios) {
    if (!$tmdb_id || !is_array($episodios)) {
        return [];
    }
    
    $cache_dir = __DIR__ . '/../../assets/tmdb_cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    
    $tmdb_episodios_imgs = [];
    $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
    
    foreach ($episodios as $season_num => $eps) {
        $tmdb_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season_num?api_key=" . TMDB_API_KEY . "&language=" . $language;
        $tmdb_json = @file_get_contents($tmdb_url);
        $tmdb_data = json_decode($tmdb_json, true);
        
        if (!empty($tmdb_data['episodes'])) {
            foreach ($tmdb_data['episodes'] as $ep) {
                if (!empty($ep['still_path'])) {
                    $img_url = "https://image.tmdb.org/t/p/w500" . $ep['still_path'];
                    $img_local = $cache_dir . "{$tmdb_id}_{$season_num}_{$ep['episode_number']}.jpg";
                    $img_local_url = "assets/tmdb_cache/{$tmdb_id}_{$season_num}_{$ep['episode_number']}.jpg";
                    
                    if (!file_exists($img_local)) {
                        $img_data = @file_get_contents($img_url);
                        if ($img_data) {
                            file_put_contents($img_local, $img_data);
                        }
                    }
                    
                    if (file_exists($img_local)) {
                        $tmdb_episodios_imgs[$season_num][$ep['episode_number']] = $img_local_url;
                    } else {
                        $tmdb_episodios_imgs[$season_num][$ep['episode_number']] = $img_url;
                    }
                }
            }
        }
    }
    
    return $tmdb_episodios_imgs;
}

