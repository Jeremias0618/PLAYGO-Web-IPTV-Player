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
            $getYearMonth = function($item) {
                $year = 0;
                $month = 0;
                
                if (isset($item['releaseDate']) && !empty($item['releaseDate'])) {
                    $dateStr = $item['releaseDate'];
                    if (preg_match('/^(\d{4})-(\d{2})/', $dateStr, $matches)) {
                        $year = intval($matches[1]);
                        $month = intval($matches[2]);
                    } elseif (preg_match('/^(\d{4})/', $dateStr, $matches)) {
                        $year = intval($matches[1]);
                    }
                } elseif (isset($item['releasedate']) && !empty($item['releasedate'])) {
                    $dateStr = $item['releasedate'];
                    if (preg_match('/^(\d{4})-(\d{2})/', $dateStr, $matches)) {
                        $year = intval($matches[1]);
                        $month = intval($matches[2]);
                    } elseif (preg_match('/^(\d{4})/', $dateStr, $matches)) {
                        $year = intval($matches[1]);
                    }
                } elseif (isset($item['year']) && !empty($item['year'])) {
                    if (is_numeric($item['year'])) {
                        $year = intval($item['year']);
                    } else {
                        $yearStr = $item['year'];
                        if (preg_match('/^(\d{4})-(\d{2})/', $yearStr, $matches)) {
                            $year = intval($matches[1]);
                            $month = intval($matches[2]);
                        } elseif (preg_match('/^(\d{4})/', $yearStr, $matches)) {
                            $year = intval($matches[1]);
                        }
                    }
                } elseif (isset($item['name']) && preg_match('/\((\d{4})\)/', $item['name'], $matches)) {
                    $year = intval($matches[1]);
                }
                
                return ['year' => $year, 'month' => $month];
            };
            
            $dateA = $getYearMonth($a);
            $dateB = $getYearMonth($b);
            
            if ($dateA['year'] != $dateB['year']) {
                return ($dateB['year'] <=> $dateA['year']) * $dir;
            }
            
            if ($dateA['month'] == 0 && $dateB['month'] == 0) {
                return 0;
            }
            
            if ($dateA['month'] == 0) {
                return 1 * $dir;
            }
            
            if ($dateB['month'] == 0) {
                return -1 * $dir;
            }
            
            return ($dateB['month'] <=> $dateA['month']) * $dir;
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
    
    $generos = getMoviesGenres($movies);
    if (empty($generos)) {
        return [];
    }
    
    $populares = [];
    $generosUsados = [];
    
    shuffle($generos);
    
    foreach ($generos as $genero) {
        if (count($populares) >= $limit) {
            break;
        }
        
        $peliculasGenero = filterMoviesByGenre($movies, $genero);
        
        $peliculasFiltradas = array_filter($peliculasGenero, function($p) {
            $r = isset($p['rating_5based']) ? floatval($p['rating_5based'])*2 : (isset($p['rating']) ? floatval($p['rating']) : 0);
            return $r >= 5.0 && $r <= 10.0;
        });
        
        if (!empty($peliculasFiltradas)) {
            usort($peliculasFiltradas, function($a, $b) {
                $ra = isset($a['rating_5based']) ? floatval($a['rating_5based'])*2 : (isset($a['rating']) ? floatval($a['rating']) : 0);
                $rb = isset($b['rating_5based']) ? floatval($b['rating_5based'])*2 : (isset($b['rating']) ? floatval($b['rating']) : 0);
                return $rb <=> $ra;
            });
            
            $peliculasFiltradas = array_values($peliculasFiltradas);
            $peliculaAleatoria = $peliculasFiltradas[array_rand($peliculasFiltradas)];
            $populares[] = $peliculaAleatoria;
            $generosUsados[] = $genero;
        }
    }
    
    return $populares;
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

function getRandomMoviesByGenres($movies, $ratingMin = 4.0, $ratingMax = 10.0, $user = null, $pwd = null) {
    if (!is_array($movies) || empty($movies)) {
        return [];
    }
    
    $moviesWithRating = [];
    foreach ($movies as $movie) {
        $r = isset($movie['rating_5based']) ? floatval($movie['rating_5based'])*2 : (isset($movie['rating']) ? floatval($movie['rating']) : 0);
        if ($r >= $ratingMin && $r <= $ratingMax) {
            if (empty($movie['genre']) && $user && $pwd) {
                $vod_id = isset($movie['stream_id']) ? $movie['stream_id'] : null;
                if ($vod_id) {
                    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
                    $res_info = @apixtream($url_info);
                    $data_info = @json_decode($res_info, true);
                    if (isset($data_info['info']['genre']) && !empty($data_info['info']['genre'])) {
                        $movie['genre'] = $data_info['info']['genre'];
                    }
                }
            }
            $moviesWithRating[] = $movie;
        }
    }
    
    if (empty($moviesWithRating)) {
        return [];
    }
    
    $moviesByGenre = [];
    foreach ($moviesWithRating as $movie) {
        if (!isset($movie['genre']) || empty($movie['genre'])) {
            if (!isset($moviesByGenre['Sin género'])) {
                $moviesByGenre['Sin género'] = [];
            }
            $moviesByGenre['Sin género'][] = $movie;
            continue;
        }
        
        $genres = is_array($movie['genre']) ? $movie['genre'] : explode(',', $movie['genre']);
        foreach ($genres as $genre) {
            $genre = trim($genre);
            if (empty($genre)) {
                continue;
            }
            
            if (!isset($moviesByGenre[$genre])) {
                $moviesByGenre[$genre] = [];
            }
            
            $movieId = isset($movie['stream_id']) ? $movie['stream_id'] : null;
            $exists = false;
            foreach ($moviesByGenre[$genre] as $existingMovie) {
                if (isset($existingMovie['stream_id']) && $existingMovie['stream_id'] == $movieId) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $moviesByGenre[$genre][] = $movie;
            }
        }
    }
    
    if (empty($moviesByGenre)) {
        return [];
    }
    
    $allGenres = array_keys($moviesByGenre);
    shuffle($allGenres);
    
    $result = [];
    $addedMovieIds = [];
    
    foreach ($allGenres as $genre) {
        $genreMovies = $moviesByGenre[$genre];
        shuffle($genreMovies);
        
        foreach ($genreMovies as $movie) {
            $movieId = isset($movie['stream_id']) ? $movie['stream_id'] : null;
            if ($movieId && !in_array($movieId, $addedMovieIds)) {
                $result[] = $movie;
                $addedMovieIds[] = $movieId;
            } elseif (!$movieId) {
                $result[] = $movie;
            }
        }
    }
    
    shuffle($result);
    
    return array_values($result);
}

