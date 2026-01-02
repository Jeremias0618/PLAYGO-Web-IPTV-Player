<?php

require_once(__DIR__ . '/../lib.php');

function getMoviesData($user, $pwd, $categoryId = null) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams".($categoryId ? "&category_id=$categoryId" : "");
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    return $output;
}

function getMovieBackdrop($user, $pwd, $movies) {
    if (empty($movies) || !is_array($movies)) {
        return '';
    }
    
    $pelicula_aleatoria = $movies[array_rand($movies)];
    $vod_id = $pelicula_aleatoria['stream_id'];
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
    $res_info = apixtream($url_info);
    $data_info = json_decode($res_info, true);
    
    if (
        isset($data_info['info']['backdrop_path']) &&
        is_array($data_info['info']['backdrop_path']) &&
        count($data_info['info']['backdrop_path']) > 0
    ) {
        return $data_info['info']['backdrop_path'][0];
    }
    
    return '';
}

function filterMoviesByGenre($movies, $genre) {
    if (empty($genre)) {
        return $movies;
    }
    
    return array_filter($movies, function($p) use ($genre) {
        if (!isset($p['genre'])) return false;
        $gs = is_array($p['genre']) ? $p['genre'] : explode(',', $p['genre']);
        foreach ($gs as $gg) {
            if (trim($gg) == $genre) return true;
        }
        return false;
    });
}

function filterMoviesByRating($movies, $min, $max) {
    if ($min === null || $max === null) {
        return $movies;
    }
    
    $min = floatval($min);
    $max = floatval($max);
    
    return array_filter($movies, function($p) use ($min, $max) {
        $r = isset($p['rating_5based']) ? floatval($p['rating_5based'])*2 : (isset($p['rating']) ? floatval($p['rating']) : 0);
        return $r >= $min && $r <= $max;
    });
}

function filterMoviesByYear($movies, $min, $max) {
    if ($min === null || $max === null || $min === '' || $max === '') {
        return $movies;
    }
    
    $min = intval($min);
    $max = intval($max);
    
    $filtered = array_filter($movies, function($p) use ($min, $max) {
        $y = 0;
        
        if (isset($p['year']) && !empty($p['year'])) {
            if (is_numeric($p['year'])) {
                $y = intval($p['year']);
            } else {
                $y = intval(substr($p['year'], 0, 4));
            }
        } elseif (isset($p['releaseDate']) && !empty($p['releaseDate'])) {
            $y = intval(substr($p['releaseDate'], 0, 4));
        } elseif (isset($p['releasedate']) && !empty($p['releasedate'])) {
            $y = intval(substr($p['releasedate'], 0, 4));
        } elseif (isset($p['name']) && !empty($p['name'])) {
            if (preg_match('/\((\d{4})\)/', $p['name'], $matches)) {
                $y = intval($matches[1]);
            }
        }
        
        if ($y == 0) {
            return false;
        }
        
        return $y >= $min && $y <= $max;
    });
    
    return array_values($filtered);
}

function sortMovies($movies, $order, $direction = 'asc') {
    if (empty($order)) {
        return $movies;
    }
    
    $sorted = $movies;
    $dir = ($direction === 'desc') ? -1 : 1;
    
    if ($order == 'nombre') {
        usort($sorted, function($a, $b) use ($dir) {
            $result = strcmp($a['name'], $b['name']);
            return $result * $dir;
        });
    } elseif ($order == 'año') {
        usort($sorted, function($a, $b) use ($dir) {
            $yearA = 0;
            $yearB = 0;
            
            if (isset($a['year']) && !empty($a['year'])) {
                $yearA = is_numeric($a['year']) ? intval($a['year']) : intval(substr($a['year'], 0, 4));
            } elseif (isset($a['releaseDate']) && !empty($a['releaseDate'])) {
                $yearA = intval(substr($a['releaseDate'], 0, 4));
            } elseif (isset($a['releasedate']) && !empty($a['releasedate'])) {
                $yearA = intval(substr($a['releasedate'], 0, 4));
            } elseif (isset($a['name']) && preg_match('/\((\d{4})\)/', $a['name'], $matches)) {
                $yearA = intval($matches[1]);
            }
            
            if (isset($b['year']) && !empty($b['year'])) {
                $yearB = is_numeric($b['year']) ? intval($b['year']) : intval(substr($b['year'], 0, 4));
            } elseif (isset($b['releaseDate']) && !empty($b['releaseDate'])) {
                $yearB = intval(substr($b['releaseDate'], 0, 4));
            } elseif (isset($b['releasedate']) && !empty($b['releasedate'])) {
                $yearB = intval(substr($b['releasedate'], 0, 4));
            } elseif (isset($b['name']) && preg_match('/\((\d{4})\)/', $b['name'], $matches)) {
                $yearB = intval($matches[1]);
            }
            
            return ($yearB <=> $yearA) * $dir;
        });
    } elseif ($order == 'rating') {
        usort($sorted, function($a, $b) use ($dir) {
            $ra = isset($a['rating_5based']) ? floatval($a['rating_5based'])*2 : (isset($a['rating']) ? floatval($a['rating']) : 0);
            $rb = isset($b['rating_5based']) ? floatval($b['rating_5based'])*2 : (isset($b['rating']) ? floatval($b['rating']) : 0);
            return ($rb <=> $ra) * $dir;
        });
    } elseif ($order == 'recientes') {
        usort($sorted, function($a, $b) use ($dir) {
            $timeA = isset($a['added']) ? intval($a['added']) : 0;
            $timeB = isset($b['added']) ? intval($b['added']) : 0;
            return ($timeB <=> $timeA) * $dir;
        });
    } elseif ($order == 'antiguas') {
        usort($sorted, function($a, $b) use ($dir) {
            $timeA = isset($a['added']) ? intval($a['added']) : 0;
            $timeB = isset($b['added']) ? intval($b['added']) : 0;
            return ($timeA <=> $timeB) * $dir;
        });
    }
    
    return $sorted;
}

function getMoviesGenres($movies) {
    $generos = [];
    
    if (!is_array($movies)) {
        return $generos;
    }
    
    foreach ($movies as $pelicula) {
        if (!empty($pelicula['genre'])) {
            $gs = is_array($pelicula['genre']) ? $pelicula['genre'] : explode(',', $pelicula['genre']);
            foreach ($gs as $g) {
                $g = trim($g);
                if ($g && !in_array($g, $generos)) {
                    $generos[] = $g;
                }
            }
        }
    }
    
    sort($generos);
    return $generos;
}

function getPopularMovies($movies, $limit = 6) {
    if (!is_array($movies)) {
        return [];
    }
    
    $populares = $movies;
    usort($populares, function($a, $b) {
        return floatval($b['rating']) <=> floatval($a['rating']);
    });
    
    return array_slice($populares, 0, $limit);
}

function paginateMovies($movies, $page, $perPage = 48) {
    $total = is_array($movies) ? count($movies) : 0;
    $totalPages = ceil($total / $perPage);
    $page = max(1, intval($page));
    $start = ($page - 1) * $perPage;
    $paginated = ($movies && is_array($movies)) ? array_slice(array_values($movies), $start, $perPage) : [];
    
    return [
        'items' => $paginated,
        'total' => $total,
        'totalPages' => max(1, $totalPages),
        'currentPage' => $page
    ];
}

