<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getSeriesData')) {
    require_once(__DIR__ . '/SeriesDetail.php');
}

if (!function_exists('getSeriesEpisodesImages')) {
    require_once(__DIR__ . '/../services/series-episodes.php');
}

function getSeriePageData($user, $pwd, $id) {
    $seriesData = getSeriesData($user, $pwd, $id);
    
    if (!$seriesData) {
        return null;
    }
    
    $tmdb_id = $seriesData['tmdb_id'];
    $episodios = $seriesData['episodes'];
    
    $tmdb_episodios_imgs = getSeriesEpisodesImages($tmdb_id, $episodios);
    
    $total_temporadas = is_array($episodios) ? count($episodios) : 0;
    $total_episodios = 0;
    if (is_array($episodios)) {
        foreach ($episodios as $eps) {
            $total_episodios += is_array($eps) ? count($eps) : 0;
        }
    }
    
    $episodes_progress = [];
    $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
    $progressFile = __DIR__ . '/../../storage/users/' . $safeUser . '/progress.json';
    
    if (file_exists($progressFile)) {
        $progressData = json_decode(file_get_contents($progressFile), true);
        if (is_array($progressData)) {
            foreach ($progressData as $key => $progressItem) {
                if (isset($progressItem['type']) && $progressItem['type'] === 'serie' && isset($progressItem['episode']['id'])) {
                    $episode_id = $progressItem['episode']['id'];
                    $time = isset($progressItem['episode']['time']) ? (int)$progressItem['episode']['time'] : 0;
                    $duration = isset($progressItem['episode']['duration']) ? $progressItem['episode']['duration'] : '';
                    
                    $duration_seconds = 0;
                    if (!empty($duration)) {
                        $duration_parts = explode(':', $duration);
                        if (count($duration_parts) == 3) {
                            $duration_seconds = intval($duration_parts[0]) * 3600 + intval($duration_parts[1]) * 60 + intval($duration_parts[2]);
                        } elseif (count($duration_parts) == 2) {
                            $duration_seconds = intval($duration_parts[0]) * 60 + intval($duration_parts[1]);
                        }
                    }
                    
                    if ($duration_seconds > 0 && $time > 0) {
                        $percentage = ($time / $duration_seconds) * 100;
                        $episodes_progress[$episode_id] = [
                            'time' => $time,
                            'duration' => $duration,
                            'duration_seconds' => $duration_seconds,
                            'percentage' => min(100, max(0, round($percentage, 2))),
                            'watched' => $percentage >= 80
                        ];
                    }
                }
            }
        }
    }
    
    return [
        'id' => $seriesData['id'],
        'serie_nome' => $seriesData['name'],
        'poster_img' => $seriesData['poster_img'],
        'poster_tmdb' => $seriesData['poster_tmdb'],
        'wallpaper_tmdb' => $seriesData['wallpaper_tmdb'],
        'backdrop' => $seriesData['backdrop'],
        'sinopsis' => $seriesData['plot'],
        'genero' => $seriesData['genre'],
        'ano' => $seriesData['year'],
        'pais' => $seriesData['country'],
        'nota' => $seriesData['rating'],
        'cast' => $seriesData['cast'],
        'diretor' => $seriesData['director'],
        'duracao' => $seriesData['duration'],
        'youtube_id' => $seriesData['youtube_id'],
        'tmdb_id' => $tmdb_id,
        'episodios' => $episodios,
        'tmdb_episodios_imgs' => $tmdb_episodios_imgs,
        'total_temporadas' => $total_temporadas,
        'total_episodios' => $total_episodios,
        'episodes_progress' => $episodes_progress
    ];
}

