<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function loadSagaFromJson($saga_id) {
    $sagasFile = __DIR__ . '/../../storage/sagas.json';
    
    if (!file_exists($sagasFile)) {
        return null;
    }
    
    $content = file_get_contents($sagasFile);
    $sagasData = json_decode($content, true) ?: [];
    
    foreach ($sagasData as $saga) {
        if (isset($saga['id']) && (string)$saga['id'] === (string)$saga_id) {
            return [
                'id' => $saga['id'],
                'nombre' => $saga['title'] ?? '',
                'imagen' => $saga['image'] ?? '',
                'items' => $saga['items'] ?? []
            ];
        }
    }
    
    return null;
}

function getTmdbInfo($title, $year = '', $type = 'movie') {
    if (!defined('TMDB_API_KEY') || empty(TMDB_API_KEY)) {
        return null;
    }
    
    $query = urlencode($title);
    $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
    $search_type = ($type === 'series') ? 'tv' : 'movie';
    $tmdb_search_url = "https://api.themoviedb.org/3/search/{$search_type}?api_key=" . TMDB_API_KEY . "&language=" . $language . "&query=$query";
    if (!empty($year)) {
        $tmdb_search_url .= "&first_air_date_year=" . urlencode(substr($year, 0, 4));
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tmdb_search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $tmdb_search_json = @curl_exec($ch);
    curl_close($ch);
    
    $tmdb_search_data = @json_decode($tmdb_search_json, true);
    if (empty($tmdb_search_data['results'][0])) {
        return null;
    }
    
    $result = $tmdb_search_data['results'][0];
    
    if (!empty($result['id'])) {
        $tmdb_id = $result['id'];
        $detail_url = "https://api.themoviedb.org/3/{$search_type}/{$tmdb_id}?api_key=" . TMDB_API_KEY . "&language=" . $language . "&append_to_response=credits,videos";
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $detail_url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $tmdb_detail_json = @curl_exec($ch2);
        curl_close($ch2);
        
        $tmdb_detail = @json_decode($tmdb_detail_json, true);
        if ($tmdb_detail) {
            if (!empty($tmdb_detail['overview'])) $result['overview'] = $tmdb_detail['overview'];
            if (!empty($tmdb_detail['vote_average'])) $result['vote_average'] = $tmdb_detail['vote_average'];
            if (!empty($tmdb_detail['release_date'])) $result['release_date'] = $tmdb_detail['release_date'];
            if (!empty($tmdb_detail['first_air_date'])) $result['first_air_date'] = $tmdb_detail['first_air_date'];
            if (!empty($tmdb_detail['poster_path'])) $result['poster_path'] = $tmdb_detail['poster_path'];
            if (!empty($tmdb_detail['genres']) && is_array($tmdb_detail['genres'])) {
                $result['genres'] = array_map(function($g) { return $g['name']; }, $tmdb_detail['genres']);
            }
            if (!empty($tmdb_detail['credits']['cast']) && is_array($tmdb_detail['credits']['cast'])) {
                $cast_names = array_slice(array_map(function($c) { return $c['name']; }, $tmdb_detail['credits']['cast']), 0, 5);
                $result['cast'] = implode(', ', $cast_names);
            }
            if (!empty($tmdb_detail['videos']['results']) && is_array($tmdb_detail['videos']['results'])) {
                foreach ($tmdb_detail['videos']['results'] as $video) {
                    if (isset($video['type']) && $video['type'] === 'Trailer' && isset($video['key'])) {
                        $result['youtube_key'] = $video['key'];
                        break;
                    }
                }
            }
        }
    }
    
    return $result;
}

function fetchItemsInParallel($items, $user, $pwd) {
    $multi_handle = curl_multi_init();
    $curl_handles = [];
    $items_data = [];
    
    $item_index = 0;
    foreach($items as $item) {
        $item_index++;
        $vod_id = isset($item['id']) ? $item['id'] : null;
        
        if (!$vod_id) {
            continue;
        }
        
        $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_multi_add_handle($multi_handle, $ch);
        
        $curl_handles[$item_index] = $ch;
        $items_data[$item_index] = [
            'item' => $item,
            'vod_id' => $vod_id,
            'item_type' => isset($item['type']) ? $item['type'] : 'movie',
            'start_time' => microtime(true)
        ];
    }
    
    $running = null;
    do {
        curl_multi_exec($multi_handle, $running);
        curl_multi_select($multi_handle, 0.1);
    } while ($running > 0);
    
    $results = [];
    foreach($curl_handles as $item_index => $ch) {
        $item = $items_data[$item_index]['item'];
        $vod_id = $items_data[$item_index]['vod_id'];
        $item_type = $items_data[$item_index]['item_type'];
        
        $res_info = curl_multi_getcontent($ch);
        $data_info = json_decode($res_info, true);
        
        curl_multi_remove_handle($multi_handle, $ch);
        curl_close($ch);
        
        $results[$item_index] = [
            'item' => $item,
            'vod_id' => $vod_id,
            'item_type' => $item_type,
            'data_info' => $data_info
        ];
    }
    
    curl_multi_close($multi_handle);
    
    return $results;
}

function processItemData($item_data, $user, $pwd) {
    $item = $item_data['item'];
    $vod_id = $item_data['vod_id'];
    $item_type = $item_data['item_type'];
    $data_info = $item_data['data_info'];
    
    $movie_name = isset($item['title']) ? $item['title'] : '';
    $stream_icon = isset($item['poster']) ? $item['poster'] : '';
    
    $rating = '';
    $year = '';
    $duration = '';
    $country = '';
    $cast = '';
    $plot = '';
    $genre = '';
    $trailer = '';
    $info_data = [];
    $movie_data = [];
    
    if ($item_type === 'movie' && !empty($data_info['movie_data'])) {
        $movie_data = $data_info['movie_data'];
        $info_data = isset($data_info['info']) ? $data_info['info'] : [];
        if (!empty($movie_data['name']) && $movie_data['name'] !== $movie_name) {
            $movie_name = $movie_data['name'];
        }
    } elseif ($item_type === 'series' && !empty($data_info['info'])) {
        $info_data = $data_info['info'];
        if (!empty($info_data['name']) && $info_data['name'] !== $movie_name) {
            $movie_name = $info_data['name'];
        }
    }
    
    if (!empty($info_data['movie_image']) && empty($stream_icon)) {
        $stream_icon = $info_data['movie_image'];
    }
    $rating = isset($info_data['rating']) ? $info_data['rating'] : '';
    $year = isset($info_data['releasedate']) ? substr($info_data['releasedate'], 0, 4) : '';
    $duration = isset($info_data['duration']) ? $info_data['duration'] : '';
    $country = isset($info_data['country']) ? $info_data['country'] : '';
    $cast = isset($info_data['cast']) ? $info_data['cast'] : '';
    $plot = isset($info_data['plot']) ? $info_data['plot'] : '';
    $genre = isset($info_data['genre']) ? $info_data['genre'] : '';
    $trailer = isset($info_data['youtube_trailer']) ? $info_data['youtube_trailer'] : '';
    
    $youtube_id = '';
    if (!empty($trailer)) {
        if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
            $youtube_id = $trailer;
        } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
            $youtube_id = $matches[1];
        }
    }
    
    $needs_tmdb = false;
    if (empty($plot) && empty($stream_icon) && empty($rating) && empty($year) && !empty($movie_name)) {
        $needs_tmdb = true;
    }
    
    if ($needs_tmdb) {
        $tmdb_info = getTmdbInfo($movie_name, $year, $item_type);
        
        if ($tmdb_info) {
            if (empty($plot) && !empty($tmdb_info['overview'])) {
                $plot = $tmdb_info['overview'];
            }
            if (empty($rating) && !empty($tmdb_info['vote_average'])) {
                $rating = number_format($tmdb_info['vote_average'], 1);
            }
            if (empty($year)) {
                $release_date = !empty($tmdb_info['release_date']) ? $tmdb_info['release_date'] : (!empty($tmdb_info['first_air_date']) ? $tmdb_info['first_air_date'] : '');
                if ($release_date) {
                    $year = substr($release_date, 0, 4);
                }
            }
            if (empty($stream_icon) && !empty($tmdb_info['poster_path'])) {
                $stream_icon = 'https://image.tmdb.org/t/p/w500' . $tmdb_info['poster_path'];
            }
            if (empty($cast) && !empty($tmdb_info['cast'])) {
                $cast = $tmdb_info['cast'];
            }
            if (empty($genre) && !empty($tmdb_info['genres']) && is_array($tmdb_info['genres'])) {
                $genre = implode(', ', $tmdb_info['genres']);
            }
            if (empty($youtube_id) && !empty($tmdb_info['youtube_key'])) {
                $youtube_id = $tmdb_info['youtube_key'];
            }
        }
    }
    
    return [
        'stream_id' => $vod_id,
        'name' => $movie_name,
        'stream_icon' => $stream_icon,
        'rating' => $rating,
        'rating_5based' => $rating,
        'year' => $year,
        'stream_type' => $item_type,
        'duration' => $duration,
        'country' => $country,
        'cast' => $cast,
        'plot' => $plot,
        'genre' => $genre,
        'youtube_id' => $youtube_id
    ];
}

function getCollectionBackdrop($peliculas, $user, $pwd) {
    if (empty($peliculas) || !is_array($peliculas) || count($peliculas) === 0) {
        return '';
    }
    
    $pelicula_aleatoria = $peliculas[array_rand($peliculas)];
    if (!isset($pelicula_aleatoria['stream_id'])) {
        return '';
    }
    
    $vod_id = $pelicula_aleatoria['stream_id'];
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
    $res_info = apixtream($url_info);
    $data_info = json_decode($res_info, true);
    
    if (isset($data_info['info']['backdrop_path']) && is_array($data_info['info']['backdrop_path']) && count($data_info['info']['backdrop_path']) > 0) {
        return $data_info['info']['backdrop_path'][0];
    } elseif (isset($pelicula_aleatoria['stream_icon']) && !empty($pelicula_aleatoria['stream_icon'])) {
        return $pelicula_aleatoria['stream_icon'];
    }
    
    return '';
}

