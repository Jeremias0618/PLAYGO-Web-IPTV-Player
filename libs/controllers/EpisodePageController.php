<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getSeriesData')) {
    require_once(__DIR__ . '/SeriesDetail.php');
}

function getEpisodePageData($user, $pwd, $serie_id, $episode_id) {
    $seriesData = getSeriesData($user, $pwd, $serie_id);
    
    if (!$seriesData || empty($seriesData['episodes'])) {
        return null;
    }
    
    $ep_data = null;
    foreach ($seriesData['episodes'] as $temporada) {
        foreach ($temporada as $ep) {
            if ($ep['id'] == $episode_id) {
                $ep_data = $ep;
                break 2;
            }
        }
    }
    
    if (!$ep_data) {
        return null;
    }
    
    $tmdb_id = $seriesData['tmdb_id'];
    $season = isset($ep_data['season']) ? intval($ep_data['season']) : '';
    $ep_number = isset($ep_data['episode_num']) ? intval($ep_data['episode_num']) : '';
    
    $ep_still = '';
    $tmdb_episode_data = null;
    if ($tmdb_id && $season && $ep_number) {
        $still_filename = "{$tmdb_id}_{$season}_{$ep_number}.jpg";
        $still_local_path = __DIR__ . "/../../assets/tmdb_cache/$still_filename";
        $still_local_url = "assets/tmdb_cache/$still_filename";
        
        if (file_exists($still_local_path)) {
            $ep_still = $still_local_url;
        }
        
        $tmdb_ep_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season/episode/$ep_number?api_key=" . TMDB_API_KEY . "&language=" . (defined('LANGUAGE') ? LANGUAGE : 'es-ES');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tmdb_ep_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $tmdb_ep_json = @curl_exec($ch);
        curl_close($ch);
        
        $tmdb_episode_data = json_decode($tmdb_ep_json, true);
        if ($tmdb_episode_data && !empty($tmdb_episode_data['still_path']) && !file_exists($still_local_path)) {
            $ep_still_url = "https://image.tmdb.org/t/p/w780" . $tmdb_episode_data['still_path'];
            $img_data = @file_get_contents($ep_still_url);
            if ($img_data) {
                @file_put_contents($still_local_path, $img_data);
                $ep_still = $still_local_url;
            } else {
                $ep_still = $ep_still_url;
            }
        }
    }
    
    $wallpaper_img = '';
    if (!empty($seriesData['wallpaper_tmdb'])) {
        $wallpaper_img = $seriesData['wallpaper_tmdb'];
    } elseif (!empty($seriesData['backdrop'])) {
        $wallpaper_img = $seriesData['backdrop'];
    }
    
    $ep_backdrop = '';
    if (!empty($ep_still)) {
        $ep_backdrop = $ep_still;
    } elseif (!empty($ep_data['info']['backdrop_path'])) {
        $ep_backdrop = $ep_data['info']['backdrop_path'];
        if (!preg_match('/^https?:\/\//', $ep_backdrop) && !str_starts_with($ep_backdrop, '/')) {
            $ep_backdrop = 'assets/tmdb_cache/' . $ep_backdrop;
        }
    } elseif (!empty($wallpaper_img)) {
        $ep_backdrop = $wallpaper_img;
    }
    
    $ep_poster = '';
    if (!empty($ep_still)) {
        $ep_poster = $ep_still;
    } elseif (!empty($ep_data['info']['movie_image'])) {
        $ep_poster = $ep_data['info']['movie_image'];
    } else {
        $ep_poster = $seriesData['poster_img'];
    }
    
    $ep_ext = $ep_data['container_extension'] ?? 'ts';
    $video_url = IP . "/series/$user/$pwd/$episode_id.$ep_ext";
    
    $prev_ep_id = null;
    $next_ep_id = null;
    $found = false;
    foreach ($seriesData['episodes'] as $temporada) {
        foreach ($temporada as $ep) {
            if ($found && !$next_ep_id) {
                $next_ep_id = $ep['id'];
                break 2;
            }
            if ($ep['id'] == $episode_id) {
                $found = true;
            }
            if (!$found) {
                $prev_ep_id = $ep['id'];
            }
        }
    }
    
    $ep_name = $ep_data['title'] ?? 'Episodio';
    $ep_title_limpio = limpiar_titulo_episodio($ep_name);
    
    $ep_rating = $ep_data['info']['rating'] ?? '';
    if ($tmdb_episode_data && !empty($tmdb_episode_data['vote_average']) && empty($ep_rating)) {
        $ep_rating = $tmdb_episode_data['vote_average'];
    }
    
    $ep_dur = $ep_data['info']['duration'] ?? '';
    $ep_dur_secs = $ep_data['info']['duration_secs'] ?? '';
    $duration_source = '';
    
    if (empty($ep_dur) || $ep_dur === '00:00:00' || $ep_dur === '00:00') {
        if (!empty($ep_dur_secs) && is_numeric($ep_dur_secs) && intval($ep_dur_secs) > 0) {
            $duration_source = 'Xtream API (duration_secs)';
            $seconds = intval($ep_dur_secs);
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            if ($hours > 0) {
                $ep_dur = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
            } else {
                $ep_dur = sprintf("%02d:%02d", $minutes, $secs);
            }
        } elseif ($tmdb_episode_data && !empty($tmdb_episode_data['runtime']) && is_numeric($tmdb_episode_data['runtime'])) {
            $duration_source = 'TMDB API (runtime)';
            $runtime_minutes = intval($tmdb_episode_data['runtime']);
            $hours = floor($runtime_minutes / 60);
            $minutes = $runtime_minutes % 60;
            if ($hours > 0) {
                $ep_dur = sprintf("%02d:%02d:%02d", $hours, $minutes, 0);
            } else {
                $ep_dur = sprintf("%02d:%02d", $minutes, 0);
            }
        } else {
            $duration_source = 'Ninguna (requiere obtener del player)';
        }
    } elseif (!empty($ep_dur)) {
        $duration_source = 'Xtream API (duration)';
        if (is_numeric($ep_dur)) {
            $seconds = intval($ep_dur);
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            if ($hours > 0) {
                $ep_dur = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
            } else {
                $ep_dur = sprintf("%02d:%02d", $minutes, $secs);
            }
        }
    } else {
        $duration_source = 'Ninguna (requiere obtener del player)';
    }
    
    $episodes_progress = [];
    $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
    $progressFile = __DIR__ . '/../../storage/users/' . $safeUser . '/progress.json';
    
    if (file_exists($progressFile)) {
        $progressData = json_decode(file_get_contents($progressFile), true);
        if (is_array($progressData)) {
            foreach ($progressData as $key => $progressItem) {
                if (isset($progressItem['type']) && $progressItem['type'] === 'serie' && isset($progressItem['episode']['id'])) {
                    $ep_id_progress = $progressItem['episode']['id'];
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
                        $episodes_progress[$ep_id_progress] = [
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
        'serie_id' => $serie_id,
        'episode_id' => $episode_id,
        'tmdb_id' => $tmdb_id,
        'serie_nome' => $seriesData['name'],
        'poster_img' => $seriesData['poster_img'],
        'wallpaper_img' => $wallpaper_img,
        'sinopsis' => $seriesData['plot'],
        'genero' => $seriesData['genre'],
        'ano' => $seriesData['year'],
        'nota' => $seriesData['rating'],
        'cast' => $seriesData['cast'],
        'diretor' => $seriesData['director'],
        'youtube_id' => $seriesData['youtube_id'],
        'episodios' => $seriesData['episodes'],
        'ep_data' => $ep_data,
        'ep_name' => $ep_name,
        'ep_title_limpio' => $ep_title_limpio,
        'ep_num' => $ep_data['episode_num'] ?? '',
        'ep_plot' => $ep_data['info']['plot'] ?? '',
        'ep_rating' => $ep_rating,
        'ep_dur' => $ep_dur,
        'ep_dur_source' => $duration_source,
        'ep_poster' => $ep_poster,
        'ep_backdrop' => $ep_backdrop,
        'ep_ext' => $ep_ext,
        'video_url' => $video_url,
        'prev_ep_id' => $prev_ep_id,
        'next_ep_id' => $next_ep_id,
        'episodes_progress' => $episodes_progress
    ];
}

