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
    
    $tmdb_backdrops = [];
    $tmdb_posters = [];
    $wallpaper_tmdb = '';
    $poster_tmdb = '';
    
    $tmdb_movie_data = null;
    $tmdb_videos = null;
    
    if ($tmdb_id) {
        $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
        $tmdb_movie_url = "https://api.themoviedb.org/3/movie/$tmdb_id?api_key=" . TMDB_API_KEY . "&language=" . $language . "&append_to_response=credits,videos,images";
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);
        $tmdb_movie_json = @file_get_contents($tmdb_movie_url, false, $context);
        $tmdb_movie_data = json_decode($tmdb_movie_json, true);
        
        if (!empty($tmdb_movie_data['videos'])) {
            $tmdb_videos = $tmdb_movie_data['videos'];
        }
        
        $tmdb_images_data = !empty($tmdb_movie_data['images']) ? $tmdb_movie_data['images'] : null;
        
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
    
    $trailer = $output['info']['youtube_trailer'] ?? '';
    $youtube_id = '';
    if (!empty($trailer)) {
        if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
            $youtube_id = $trailer;
        } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
            $youtube_id = $matches[1];
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
    
    $director = $output['info']['director'] ?? '';
    if (empty($director) && !empty($tmdb_movie_data['credits']['crew'])) {
        foreach ($tmdb_movie_data['credits']['crew'] as $crew) {
            if (isset($crew['job']) && $crew['job'] === 'Director') {
                $director = $crew['name'];
                break;
            }
        }
    }
    
    $cast = $output['info']['cast'] ?? '';
    if (empty($cast) && !empty($tmdb_movie_data['credits']['cast'])) {
        $cast_array = [];
        $max_cast = min(5, count($tmdb_movie_data['credits']['cast']));
        for ($i = 0; $i < $max_cast; $i++) {
            if (!empty($tmdb_movie_data['credits']['cast'][$i]['name'])) {
                $cast_array[] = $tmdb_movie_data['credits']['cast'][$i]['name'];
            }
        }
        $cast = implode(', ', $cast_array);
    }
    
    $plot = $output['info']['plot'] ?? '';
    if (empty($plot) && !empty($tmdb_movie_data['overview'])) {
        $plot = $tmdb_movie_data['overview'];
    }
    
    $genero = $output['info']['genre'] ?? '';
    if (empty($genero) && !empty($tmdb_movie_data['genres'])) {
        $genres_array = [];
        foreach ($tmdb_movie_data['genres'] as $genre) {
            if (!empty($genre['name'])) {
                $genres_array[] = $genre['name'];
            }
        }
        $genero = implode(', ', $genres_array);
    }
    
    $duracao = $output['info']['duration'] ?? '';
    if (empty($duracao) && !empty($tmdb_movie_data['runtime'])) {
        $runtime_minutes = intval($tmdb_movie_data['runtime']);
        $hours = floor($runtime_minutes / 60);
        $minutes = $runtime_minutes % 60;
        $duracao = sprintf('%02d:%02d:%02d', $hours, $minutes, 0);
    }
    
    $pais = $output['info']['country'] ?? '';
    if (empty($pais) && !empty($tmdb_movie_data['production_countries'])) {
        $countries_array = [];
        foreach ($tmdb_movie_data['production_countries'] as $country) {
            if (!empty($country['name'])) {
                $countries_array[] = $country['name'];
            }
        }
        $pais = implode(', ', $countries_array);
    }
    
    $nota = $output['info']['rating'] ?? '';
    if (empty($nota) && !empty($tmdb_movie_data['vote_average'])) {
        $nota = number_format($tmdb_movie_data['vote_average'], 2);
    }
    
    if (empty($ano) && !empty($tmdb_movie_data['release_date'])) {
        $ano = substr($tmdb_movie_data['release_date'], 0, 4);
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
        'director' => $director,
        'cast' => $cast,
        'plot' => $plot,
        'genre' => $genero,
        'duration' => $duracao,
        'country' => $pais,
        'rating' => $nota,
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

