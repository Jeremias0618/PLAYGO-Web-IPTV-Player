<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function getMovieData($user, $pwd, $id) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$id";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (empty($output)) {
        return null;
    }
    
    $backdrop = '';
    if (!empty($output['info']['backdrop_path']) && is_array($output['info']['backdrop_path'])) {
        $backdrop = $output['info']['backdrop_path'][0];
    }
    
    $poster_img = $output['info']['movie_image'];
    $repro_img = $backdrop ?: $poster_img;
    $filme = $output['movie_data']['name'];
    $filme = preg_replace('/\s*\(\d{4}\)$/', '', $filme);
    
    $tmdb_id = $output['info']['tmdb_id'] ?? null;
    $ano = $output['info']['releasedate'] ?? '';
    
    if (!$tmdb_id && !empty($filme)) {
        $query = urlencode($filme);
        $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
        $tmdb_search_url = "https://api.themoviedb.org/3/search/movie?api_key=" . TMDB_API_KEY . "&language=" . $language . "&query=$query";
        if (!empty($ano)) {
            $tmdb_search_url .= "&year=" . urlencode(substr($ano,0,4));
        }
        $tmdb_search_json = @file_get_contents($tmdb_search_url);
        $tmdb_search_data = json_decode($tmdb_search_json, true);
        if (!empty($tmdb_search_data['results'][0]['id'])) {
            $tmdb_id = $tmdb_search_data['results'][0]['id'];
        }
    }
    
    $tmdb_backdrops = [];
    $tmdb_posters = [];
    $wallpaper_tmdb = '';
    $poster_tmdb = '';
    
    if ($tmdb_id) {
        $tmdb_images_url = "https://api.themoviedb.org/3/movie/$tmdb_id/images?api_key=" . TMDB_API_KEY;
        $tmdb_images_json = @file_get_contents($tmdb_images_url);
        $tmdb_images_data = json_decode($tmdb_images_json, true);
        
        if (!empty($tmdb_images_data['backdrops'])) {
            $langCode = defined('LANGUAGE') ? substr(LANGUAGE, 0, 2) : 'es';
            foreach ($tmdb_images_data['backdrops'] as $img) {
                if (!empty($img['file_path']) && $img['iso_639_1'] === $langCode) {
                    $tmdb_backdrops[] = "https://image.tmdb.org/t/p/original" . $img['file_path'];
                }
            }
            if (empty($tmdb_backdrops)) {
                foreach ($tmdb_images_data['backdrops'] as $img) {
                    if (!empty($img['file_path'])) {
                        $tmdb_backdrops[] = "https://image.tmdb.org/t/p/original" . $img['file_path'];
                    }
                }
            }
        }
        if (!empty($tmdb_backdrops)) {
            $wallpaper_tmdb = $tmdb_backdrops[array_rand($tmdb_backdrops)];
        }
        
        if (!empty($tmdb_images_data['posters'])) {
            $langCode = defined('LANGUAGE') ? substr(LANGUAGE, 0, 2) : 'es';
            foreach ($tmdb_images_data['posters'] as $img) {
                if (!empty($img['file_path']) && $img['iso_639_1'] === $langCode) {
                    $tmdb_posters[] = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
                }
            }
            if (empty($tmdb_posters)) {
                foreach ($tmdb_images_data['posters'] as $img) {
                    if (!empty($img['file_path'])) {
                        $tmdb_posters[] = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
                    }
                }
            }
        }
        if (!empty($tmdb_posters)) {
            $poster_tmdb = $tmdb_posters[array_rand($tmdb_posters)];
        }
    }
    
    $trailer = $output['info']['youtube_trailer'];
    $youtube_id = '';
    if (!empty($trailer)) {
        if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
            $youtube_id = $trailer;
        } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
            $youtube_id = $matches[1];
        }
    }
    
    return [
        'id' => $id,
        'name' => $filme,
        'poster_img' => $poster_img,
        'repro_img' => $repro_img,
        'wallpaper_tmdb' => $wallpaper_tmdb,
        'poster_tmdb' => $poster_tmdb,
        'backdrop' => $backdrop,
        'category_id' => $output['movie_data']['category_id'],
        'container_extension' => $output['movie_data']['container_extension'],
        'youtube_id' => $youtube_id,
        'director' => $output['info']['director'] ?? '',
        'cast' => $output['info']['cast'] ?? '',
        'plot' => $output['info']['plot'] ?? '',
        'genre' => $output['info']['genre'] ?? '',
        'duration' => $output['info']['duration'] ?? '',
        'country' => $output['info']['country'] ?? '',
        'rating' => $output['info']['rating'] ?? '',
        'year' => $ano
    ];
}

function getRecommendedMovies($user, $pwd, $categoryId, $currentMovieId) {
    require_once(__DIR__ . '/../services/movies.php');
    
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams&category_id=$categoryId";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    $filteredMovies = array_filter($output, function($movie) use ($currentMovieId) {
        return isset($movie['stream_id']) && $movie['stream_id'] != $currentMovieId;
    });
    
    if (empty($filteredMovies)) {
        return [];
    }
    
    $totalMovies = count($filteredMovies);
    $limit = min(20, $totalMovies);
    
    $randomIndices = [];
    if ($limit == 1) {
        $randomIndices = [array_rand($filteredMovies)];
    } else {
        $randomIndices = array_rand($filteredMovies, $limit);
        if (!is_array($randomIndices)) {
            $randomIndices = [$randomIndices];
        }
    }
    
    $moviesWithInfo = [];
    foreach ($randomIndices as $idx) {
        if (!isset($filteredMovies[$idx])) continue;
        $movie = $filteredMovies[$idx];
        $vod_id = $movie['stream_id'];
        $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
        $res_info = apixtream($url_info);
        $data_info = json_decode($res_info, true);
        
        if (isset($data_info['info']) && isset($data_info['movie_data'])) {
            $movie['genre'] = isset($data_info['info']['genre']) ? $data_info['info']['genre'] : '';
            $movie['rating'] = isset($data_info['info']['rating']) ? $data_info['info']['rating'] : '';
            $movie['rating_5based'] = isset($data_info['info']['rating_5based']) ? $data_info['info']['rating_5based'] : '';
        }
        $moviesWithInfo[] = $movie;
    }
    
    $recomendadas = getPopularMovies($moviesWithInfo, 6);
    
    if (empty($recomendadas) && !empty($moviesWithInfo)) {
        shuffle($moviesWithInfo);
        $recomendadas = array_slice($moviesWithInfo, 0, 6);
    }
    
    return $recomendadas;
}

