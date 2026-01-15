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

function getTmdbInfo($title, $year = '', $type = 'movie', $needs_cast = false, $needs_trailer = false) {
    if (!defined('TMDB_API_KEY') || empty(TMDB_API_KEY)) {
        return null;
    }
    
    $clean_title = preg_replace('/\s*\(\d{4}\)\s*$/', '', $title);
    $clean_title = trim($clean_title);
    
    if (empty($year) && preg_match('/\((\d{4})\)/', $title, $matches)) {
        $year = $matches[1];
    }
    
    $query = urlencode($clean_title);
    $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
    $search_type = ($type === 'series') ? 'tv' : 'movie';
    $tmdb_search_url = "https://api.themoviedb.org/3/search/{$search_type}?api_key=" . TMDB_API_KEY . "&language=" . $language . "&query=$query";
    if (!empty($year)) {
        $year_param = ($type === 'series') ? 'first_air_date_year' : 'year';
        $tmdb_search_url .= "&{$year_param}=" . urlencode(substr($year, 0, 4));
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tmdb_search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
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
        $append_params = [];
        if ($needs_cast) {
            $append_params[] = 'credits';
        }
        if ($needs_trailer) {
            $append_params[] = 'videos';
        }
        $append_str = !empty($append_params) ? '&append_to_response=' . implode(',', $append_params) : '';
        $detail_url = "https://api.themoviedb.org/3/{$search_type}/{$tmdb_id}?api_key=" . TMDB_API_KEY . "&language=" . $language . $append_str;
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $detail_url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 2);
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
            if ($needs_cast && !empty($tmdb_detail['credits']['cast']) && is_array($tmdb_detail['credits']['cast'])) {
                $cast_array = [];
                $max_cast = min(5, count($tmdb_detail['credits']['cast']));
                for ($i = 0; $i < $max_cast; $i++) {
                    if (!empty($tmdb_detail['credits']['cast'][$i]['name'])) {
                        $cast_array[] = $tmdb_detail['credits']['cast'][$i]['name'];
                    }
                }
                $result['cast'] = implode(', ', $cast_array);
            }
            if ($needs_trailer && !empty($tmdb_detail['videos']['results']) && is_array($tmdb_detail['videos']['results'])) {
                foreach ($tmdb_detail['videos']['results'] as $video) {
                    if (isset($video['type']) && $video['type'] === 'Trailer' && isset($video['site']) && $video['site'] === 'YouTube' && !empty($video['key'])) {
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
        $item_name = isset($item['title']) ? $item['title'] : 'Unknown';
        $item_type_raw = isset($item['type']) ? $item['type'] : 'movie';
        $item_type = strtolower(trim($item_type_raw));
        
        if (!$vod_id) {
            continue;
        }
        
        if ($item_type === 'series' || $item_type === 'serie') {
            $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$vod_id";
        } else {
            $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
        }
        
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
            'item_type' => $item_type,
            'item_name' => $item_name
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
    $item_type_raw = $item_data['item_type'];
    $item_type = strtolower(trim($item_type_raw));
    $data_info = $item_data['data_info'];
    
    $movie_name = isset($item['title']) ? $item['title'] : '';
    $stream_icon = isset($item['poster']) ? $item['poster'] : '';
    
    $rating = '';
    $year = '';
    $duration = '';
    $country = '';
    $plot = '';
    $genre = '';
    $cast = '';
    $info_data = [];
    $movie_data = [];
    
    $has_xtream_data = !empty($data_info);
    
    if ($has_xtream_data && $item_type === 'movie' && !empty($data_info['movie_data'])) {
        $movie_data = $data_info['movie_data'];
        $info_data = isset($data_info['info']) ? $data_info['info'] : [];
        if (!empty($movie_data['name']) && $movie_data['name'] !== $movie_name) {
            $movie_name = $movie_data['name'];
        }
    } elseif ($has_xtream_data && ($item_type === 'series' || $item_type === 'serie') && !empty($data_info['info'])) {
        $info_data = $data_info['info'];
        if (!empty($info_data['name']) && $info_data['name'] !== $movie_name) {
            $movie_name = $info_data['name'];
        }
    }
    
    if ($has_xtream_data) {
        if ($item_type === 'movie') {
            if (!empty($info_data['movie_image']) && empty($stream_icon)) {
                $stream_icon = $info_data['movie_image'];
            }
            $rating = isset($info_data['rating']) ? $info_data['rating'] : '';
            $year = isset($info_data['releasedate']) ? substr($info_data['releasedate'], 0, 4) : '';
            $duration = isset($info_data['duration']) ? $info_data['duration'] : '';
            $country = isset($info_data['country']) ? $info_data['country'] : '';
            $plot = isset($info_data['plot']) ? $info_data['plot'] : '';
            $genre = isset($info_data['genre']) ? $info_data['genre'] : '';
        } else {
            if (!empty($info_data['cover']) && empty($stream_icon)) {
                $stream_icon = $info_data['cover'];
            }
            
            if (!empty($info_data['name'])) {
                $movie_name = preg_replace('/\s*\(\d{4}\)$/', '', $info_data['name']);
            }
            
            $rating = $info_data['rating'] ?? '';
            $plot = $info_data['plot'] ?? '';
            $genre = $info_data['genre'] ?? '';
            $country = $info_data['country'] ?? '';
            $cast = $info_data['cast'] ?? '';
            $duration = $info_data['duration'] ?? '';
            
            $year = '';
            $releaseDate = $info_data['releaseDate'] ?? '';
            if (!empty($releaseDate)) {
                $year = substr($releaseDate, 0, 4);
            }
            
            if (empty($duration) && isset($info_data['episode_run_time'])) {
                if (is_array($info_data['episode_run_time']) && !empty($info_data['episode_run_time'][0])) {
                    $duration_secs = intval($info_data['episode_run_time'][0]);
                    if ($duration_secs > 0) {
                        $hours = floor($duration_secs / 3600);
                        $minutes = floor(($duration_secs % 3600) / 60);
                        $seconds = $duration_secs % 60;
                        if ($hours > 0) {
                            $duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        } else {
                            $duration = sprintf("%02d:%02d", $minutes, $seconds);
                        }
                    }
                } elseif (is_numeric($info_data['episode_run_time'])) {
                    $duration_secs = intval($info_data['episode_run_time']);
                    if ($duration_secs > 0) {
                        $hours = floor($duration_secs / 3600);
                        $minutes = floor(($duration_secs % 3600) / 60);
                        $seconds = $duration_secs % 60;
                        if ($hours > 0) {
                            $duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        } else {
                            $duration = sprintf("%02d:%02d", $minutes, $seconds);
                        }
                    }
                }
            }
        }
    }
    
    if (empty($year) && preg_match('/\((\d{4})\)/', $movie_name, $matches)) {
        $year = $matches[1];
    }
    
    $result = [
        'stream_id' => $vod_id,
        'name' => $movie_name,
        'stream_icon' => $stream_icon,
        'rating' => $rating,
        'rating_5based' => $rating,
        'year' => $year,
        'stream_type' => $item_type,
        'duration' => $duration,
        'country' => $country,
        'plot' => $plot,
        'genre' => $genre,
        'cast' => $cast
    ];
    
    return $result;
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
