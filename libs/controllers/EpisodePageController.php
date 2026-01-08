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
    if ($tmdb_id && $season && $ep_number) {
        $still_filename = "{$tmdb_id}_{$season}_{$ep_number}.jpg";
        $still_local_path = __DIR__ . "/../../assets/tmdb_cache/$still_filename";
        $still_local_url = "assets/tmdb_cache/$still_filename";
        
        if (file_exists($still_local_path)) {
            $ep_still = $still_local_url;
        } else {
            $tmdb_still_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season/episode/$ep_number?api_key=" . TMDB_API_KEY . "&language=" . (defined('LANGUAGE') ? LANGUAGE : 'es-ES');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tmdb_still_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $tmdb_still_json = @curl_exec($ch);
            curl_close($ch);
            
            $tmdb_still_data = json_decode($tmdb_still_json, true);
            if (!empty($tmdb_still_data['still_path'])) {
                $ep_still_url = "https://image.tmdb.org/t/p/w780" . $tmdb_still_data['still_path'];
                $img_data = @file_get_contents($ep_still_url);
                if ($img_data) {
                    @file_put_contents($still_local_path, $img_data);
                    $ep_still = $still_local_url;
                } else {
                    $ep_still = $ep_still_url;
                }
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
    $ep_title_limpio = trim(preg_replace('/^.*-\s*/', '', $ep_name));
    
    return [
        'serie_id' => $serie_id,
        'episode_id' => $episode_id,
        'serie_nome' => $seriesData['name'],
        'poster_img' => $seriesData['poster_img'],
        'wallpaper_img' => $wallpaper_img,
        'sinopsis' => $seriesData['plot'],
        'genero' => $seriesData['genre'],
        'ano' => $seriesData['year'],
        'nota' => $seriesData['rating'],
        'cast' => $seriesData['cast'],
        'diretor' => $seriesData['director'],
        'episodios' => $seriesData['episodes'],
        'ep_data' => $ep_data,
        'ep_name' => $ep_name,
        'ep_title_limpio' => $ep_title_limpio,
        'ep_num' => $ep_data['episode_num'] ?? '',
        'ep_plot' => $ep_data['info']['plot'] ?? '',
        'ep_dur' => $ep_data['info']['duration'] ?? '',
        'ep_poster' => $ep_poster,
        'ep_backdrop' => $ep_backdrop,
        'ep_ext' => $ep_ext,
        'video_url' => $video_url,
        'prev_ep_id' => $prev_ep_id,
        'next_ep_id' => $next_ep_id
    ];
}

