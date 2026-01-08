<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function getSeriesData($user, $pwd, $id) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$id";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (empty($output)) {
        return null;
    }
    
    $backdrop = '';
    if (!empty($output['info']['backdrop_path']) && is_array($output['info']['backdrop_path'])) {
        $backdrop = $output['info']['backdrop_path'][0];
    }
    
    $poster_img = $output['info']['cover'] ?? '';
    $serie_nome = preg_replace('/\s*\(\d{4}\)$/', '', $output['info']['name'] ?? '');
    $sinopsis = $output['info']['plot'] ?? '';
    $genero = $output['info']['genre'] ?? '';
    $ano = $output['info']['releaseDate'] ?? '';
    $pais = $output['info']['country'] ?? '';
    $nota = $output['info']['rating'] ?? '';
    $cast = $output['info']['cast'] ?? '';
    $diretor = $output['info']['director'] ?? '';
    $duracao = $output['info']['duration'] ?? '';
    $trailer = $output['info']['youtube_trailer'] ?? '';
    $youtube_id = '';
    if (!empty($trailer)) {
        if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
            $youtube_id = $trailer;
        } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
            $youtube_id = $matches[1];
        }
    }
    $episodios = $output['episodes'] ?? [];
    
    $tmdb_id = $output['info']['tmdb_id'] ?? null;
    
    if (!$tmdb_id && !empty($serie_nome)) {
        $query = urlencode($serie_nome);
        $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
        $tmdb_search_url = "https://api.themoviedb.org/3/search/tv?api_key=" . TMDB_API_KEY . "&language=" . $language . "&query=$query";
        if (!empty($ano)) {
            $tmdb_search_url .= "&first_air_date_year=" . urlencode(substr($ano,0,4));
        }
        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'ignore_errors' => true
            ]
        ]);
        $tmdb_search_json = @file_get_contents($tmdb_search_url, false, $context);
        $tmdb_search_data = json_decode($tmdb_search_json, true);
        if (!empty($tmdb_search_data['results'][0]['id'])) {
            $tmdb_id = $tmdb_search_data['results'][0]['id'];
        }
    }
    
    $wallpaper_tmdb = '';
    $poster_tmdb = '';
    
    $tmdb_series_data = null;
    $tmdb_videos = null;
    
    $needs_backdrop = empty($backdrop);
    $needs_poster = empty($poster_img);
    
    $needs_tmdb_data = empty($diretor) || 
                       empty($cast) || 
                       empty($sinopsis) || 
                       empty($youtube_id);
    
    $tmdb_critical = $needs_backdrop || $needs_poster;
    
    if ($tmdb_id && ($tmdb_critical || $needs_tmdb_data)) {
        $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
        $append_params = [];
        if ($needs_backdrop || $needs_poster) {
            $append_params[] = 'images';
        }
        if ($needs_tmdb_data) {
            $append_params[] = 'credits';
            $append_params[] = 'videos';
        }
        $append_str = !empty($append_params) ? '&append_to_response=' . implode(',', $append_params) : '';
        $tmdb_series_url = "https://api.themoviedb.org/3/tv/$tmdb_id?api_key=" . TMDB_API_KEY . "&language=" . $language . $append_str;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tmdb_series_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $tmdb_critical ? 2 : 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $tmdb_critical ? 2 : 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        $tmdb_series_json = @curl_exec($ch);
        curl_close($ch);
        
        $tmdb_series_data = json_decode($tmdb_series_json, true);
        
        if (!empty($tmdb_series_data['videos'])) {
            $tmdb_videos = $tmdb_series_data['videos'];
        }
        
        if ($needs_backdrop && !empty($tmdb_series_data['images']['backdrops'])) {
            $langCode = defined('LANGUAGE') ? substr(LANGUAGE, 0, 2) : 'es';
            foreach ($tmdb_series_data['images']['backdrops'] as $img) {
                if (!empty($img['file_path'])) {
                    if ($img['iso_639_1'] === $langCode || empty($img['iso_639_1'])) {
                        $wallpaper_tmdb = "https://image.tmdb.org/t/p/original" . $img['file_path'];
                        break;
                    }
                }
            }
            if (empty($wallpaper_tmdb)) {
                foreach ($tmdb_series_data['images']['backdrops'] as $img) {
                    if (!empty($img['file_path'])) {
                        $wallpaper_tmdb = "https://image.tmdb.org/t/p/original" . $img['file_path'];
                        break;
                    }
                }
            }
        }
        
        if ($needs_poster && !empty($tmdb_series_data['images']['posters'])) {
            $langCode = defined('LANGUAGE') ? substr(LANGUAGE, 0, 2) : 'es';
            foreach ($tmdb_series_data['images']['posters'] as $img) {
                if (!empty($img['file_path'])) {
                    if ($img['iso_639_1'] === $langCode || empty($img['iso_639_1'])) {
                        $poster_tmdb = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
                        break;
                    }
                }
            }
            if (empty($poster_tmdb)) {
                foreach ($tmdb_series_data['images']['posters'] as $img) {
                    if (!empty($img['file_path'])) {
                        $poster_tmdb = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
                        break;
                    }
                }
            }
        }
    }
    
    if (empty($youtube_id) && !empty($tmdb_videos['results'])) {
        foreach ($tmdb_videos['results'] as $video) {
            if (isset($video['type']) && $video['type'] === 'Trailer' && isset($video['site']) && $video['site'] === 'YouTube' && !empty($video['key'])) {
                $youtube_id = $video['key'];
                break;
            }
        }
    }
    
    if (empty($diretor) && !empty($tmdb_series_data['created_by']) && count($tmdb_series_data['created_by']) > 0) {
        $diretor = $tmdb_series_data['created_by'][0]['name'];
    }
    
    if (empty($cast) && !empty($tmdb_series_data['credits']['cast'])) {
        $cast_array = [];
        $max_cast = min(5, count($tmdb_series_data['credits']['cast']));
        for ($i = 0; $i < $max_cast; $i++) {
            if (!empty($tmdb_series_data['credits']['cast'][$i]['name'])) {
                $cast_array[] = $tmdb_series_data['credits']['cast'][$i]['name'];
            }
        }
        $cast = implode(', ', $cast_array);
    }
    
    if (empty($sinopsis) && !empty($tmdb_series_data['overview'])) {
        $sinopsis = $tmdb_series_data['overview'];
    }
    
    if (empty($genero) && !empty($tmdb_series_data['genres'])) {
        $genres_array = [];
        foreach ($tmdb_series_data['genres'] as $genre) {
            if (!empty($genre['name'])) {
                $genres_array[] = $genre['name'];
            }
        }
        $genero = implode(', ', $genres_array);
    }
    
    if (empty($nota) && !empty($tmdb_series_data['vote_average'])) {
        $nota = number_format($tmdb_series_data['vote_average'], 2);
    }
    
    if (empty($ano) && !empty($tmdb_series_data['first_air_date'])) {
        $ano = $tmdb_series_data['first_air_date'];
    }
    
    if (empty($pais) && !empty($tmdb_series_data['origin_country']) && is_array($tmdb_series_data['origin_country'])) {
        $countries_array = [];
        foreach ($tmdb_series_data['origin_country'] as $country_code) {
            $countries_array[] = $country_code;
        }
        $pais = implode(', ', $countries_array);
    }
    
    return [
        'id' => $id,
        'name' => $serie_nome,
        'poster_img' => $poster_img,
        'poster_tmdb' => $poster_tmdb,
        'wallpaper_tmdb' => $wallpaper_tmdb,
        'backdrop' => $backdrop,
        'plot' => $sinopsis,
        'genre' => $genero,
        'year' => $ano,
        'country' => $pais,
        'rating' => $nota,
        'cast' => $cast,
        'director' => $diretor,
        'duration' => $duracao,
        'youtube_id' => $youtube_id,
        'tmdb_id' => $tmdb_id,
        'episodes' => $episodios
    ];
}

