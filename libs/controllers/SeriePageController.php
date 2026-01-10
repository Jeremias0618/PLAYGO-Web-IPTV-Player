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
    $progress_durations = [];
    
    if (file_exists($progressFile)) {
        $progressData = json_decode(file_get_contents($progressFile), true);
        if (is_array($progressData)) {
            foreach ($progressData as $key => $progressItem) {
                if (isset($progressItem['type']) && $progressItem['type'] === 'serie' && isset($progressItem['episode']['id'])) {
                    $episode_id = $progressItem['episode']['id'];
                    $time = isset($progressItem['episode']['time']) ? (int)$progressItem['episode']['time'] : 0;
                    $duration = isset($progressItem['episode']['duration']) ? $progressItem['episode']['duration'] : '';
                    
                    if (!empty($duration) && ($duration !== '00:00:00' && $duration !== '00:00')) {
                        $progress_durations[$episode_id] = $duration;
                    }
                    
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
    
    if (is_array($episodios) && $tmdb_id) {
        $runtime_dir = __DIR__ . '/../../assets/tmdb_runtime/';
        $cached_runtimes = [];
        if (is_dir($runtime_dir)) {
            foreach ($episodios as $temp_num => $temp_episodes) {
                if (!is_array($temp_episodes)) continue;
                foreach ($temp_episodes as $ep) {
                    $season = isset($ep['season']) ? intval($ep['season']) : '';
                    $ep_number = isset($ep['episode_num']) ? intval($ep['episode_num']) : '';
                    if ($season && $ep_number) {
                        $cache_filename = "{$tmdb_id}_{$season}_{$ep_number}.json";
                        $cache_path = $runtime_dir . $cache_filename;
                        if (file_exists($cache_path)) {
                            $cached_data = @json_decode(file_get_contents($cache_path), true);
                            if (isset($cached_data['runtime']) && is_numeric($cached_data['runtime']) && $cached_data['runtime'] > 0) {
                                $cache_key = "{$season}_{$ep_number}";
                                $cached_runtimes[$cache_key] = intval($cached_data['runtime']);
                            }
                        }
                    }
                }
            }
        }
        
        foreach ($episodios as $temp_num => &$temp_episodes) {
            if (!is_array($temp_episodes)) continue;
            
            foreach ($temp_episodes as &$ep) {
                $ep_id = $ep['id'] ?? '';
                $ep_dur = $ep['info']['duration'] ?? '';
                $ep_dur_secs = $ep['info']['duration_secs'] ?? '';
                $season = isset($ep['season']) ? intval($ep['season']) : '';
                $ep_number = isset($ep['episode_num']) ? intval($ep['episode_num']) : '';
                
                if (empty($ep_dur) || $ep_dur === '00:00:00' || $ep_dur === '00:00') {
                    if (isset($progress_durations[$ep_id]) && !empty($progress_durations[$ep_id])) {
                        $ep['info']['duration'] = $progress_durations[$ep_id];
                    } elseif (!empty($ep_dur_secs) && is_numeric($ep_dur_secs) && intval($ep_dur_secs) > 0) {
                        $seconds = intval($ep_dur_secs);
                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        $secs = $seconds % 60;
                        if ($hours > 0) {
                            $ep['info']['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
                        } else {
                            $ep['info']['duration'] = sprintf("%02d:%02d", $minutes, $secs);
                        }
                    } elseif ($tmdb_id && $season && $ep_number) {
                        $cache_key = "{$season}_{$ep_number}";
                        $runtime_minutes = isset($cached_runtimes[$cache_key]) ? $cached_runtimes[$cache_key] : getTmdbEpisodeRuntime($tmdb_id, $season, $ep_number);
                        if ($runtime_minutes && $runtime_minutes > 0) {
                            $hours = floor($runtime_minutes / 60);
                            $minutes = $runtime_minutes % 60;
                            if ($hours > 0) {
                                $ep['info']['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, 0);
                            } else {
                                $ep['info']['duration'] = sprintf("%02d:%02d", $minutes, 0);
                            }
                        }
                    }
                } elseif (!empty($ep_dur) && is_numeric($ep_dur)) {
                    $seconds = intval($ep_dur);
                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    $secs = $seconds % 60;
                    if ($hours > 0) {
                        $ep['info']['duration'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
                    } else {
                        $ep['info']['duration'] = sprintf("%02d:%02d", $minutes, $secs);
                    }
                }
            }
            unset($ep);
        }
        unset($temp_episodes);
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

