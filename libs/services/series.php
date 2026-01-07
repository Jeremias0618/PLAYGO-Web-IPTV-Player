<?php

require_once(__DIR__ . '/../lib.php');

function getSeriesData($user, $pwd, $categoryId = null) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series".($categoryId ? "&category_id=$categoryId" : "");
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    
    if (!is_array($output)) {
        return [];
    }
    
    return $output;
}

function getSeriesBackdrop($user, $pwd, $series) {
    if (empty($series) || !is_array($series)) {
        return '';
    }
    
    $serie_aleatoria = $series[array_rand($series)];
    $series_id = $serie_aleatoria['series_id'];
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$series_id";
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

function filterSeriesByGenre($series, $genre) {
    if (empty($genre)) {
        return $series;
    }
    
    return array_filter($series, function($s) use ($genre) {
        if (!isset($s['genre'])) return false;
        $gs = is_array($s['genre']) ? $s['genre'] : explode(',', $s['genre']);
        foreach ($gs as $gg) {
            if (trim($gg) == $genre) return true;
        }
        return false;
    });
}

function filterSeriesByRating($series, $min, $max) {
    if ($min === null || $max === null) {
        return $series;
    }
    
    $min = floatval($min);
    $max = floatval($max);
    
    return array_filter($series, function($s) use ($min, $max) {
        $r = isset($s['rating']) ? floatval($s['rating']) : 0;
        return $r >= $min && $r <= $max;
    });
}

function filterSeriesByYear($series, $min, $max) {
    if ($min === null || $max === null || $min === '' || $max === '') {
        return $series;
    }
    
    $min = intval($min);
    $max = intval($max);
    
    $filtered = array_filter($series, function($s) use ($min, $max) {
        $y = isset($s['releaseDate']) ? intval(substr($s['releaseDate'],0,4)) : (isset($s['year']) ? intval($s['year']) : 0);
        return $y >= $min && $y <= $max;
    });
    
    return array_values($filtered);
}

function sortSeries($series, $order, $direction = 'asc') {
    if (empty($order)) {
        return $series;
    }
    
    $sorted = $series;
    $dir = ($direction === 'desc') ? -1 : 1;
    
    if ($order == 'nombre') {
        usort($sorted, function($a, $b) use ($dir) {
            $result = strcmp($a['name'], $b['name']);
            return $result * $dir;
        });
    } elseif ($order == 'año') {
        usort($sorted, function($a, $b) use ($dir) {
            $ya = isset($a['releaseDate']) ? intval(substr($a['releaseDate'],0,4)) : (isset($a['year']) ? intval($a['year']) : 0);
            $yb = isset($b['releaseDate']) ? intval(substr($b['releaseDate'],0,4)) : (isset($b['year']) ? intval($b['year']) : 0);
            return ($yb <=> $ya) * $dir;
        });
    } elseif ($order == 'rating') {
        usort($sorted, function($a, $b) use ($dir) {
            $ra = isset($a['rating']) ? floatval($a['rating']) : 0;
            $rb = isset($b['rating']) ? floatval($b['rating']) : 0;
            return ($rb <=> $ra) * $dir;
        });
    } elseif ($order == 'recientes') {
        usort($sorted, function($a, $b) use ($dir) {
            $timeA = isset($a['last_modified']) ? intval($a['last_modified']) : (isset($a['added']) ? intval($a['added']) : 0);
            $timeB = isset($b['last_modified']) ? intval($b['last_modified']) : (isset($b['added']) ? intval($b['added']) : 0);
            return ($timeB <=> $timeA) * $dir;
        });
    } elseif ($order == 'antiguas') {
        usort($sorted, function($a, $b) use ($dir) {
            $timeA = isset($a['last_modified']) ? intval($a['last_modified']) : (isset($a['added']) ? intval($a['added']) : 0);
            $timeB = isset($b['last_modified']) ? intval($b['last_modified']) : (isset($b['added']) ? intval($b['added']) : 0);
            return ($timeA <=> $timeB) * $dir;
        });
    }
    
    return $sorted;
}

function getSeriesGenres($series) {
    $generos = [];
    
    if (!is_array($series)) {
        return $generos;
    }
    
    foreach ($series as $serie) {
        if (!empty($serie['genre'])) {
            $gs = is_array($serie['genre']) ? $serie['genre'] : explode(',', $serie['genre']);
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

function getPopularSeries($series, $limit = 6) {
    if (!is_array($series)) {
        return [];
    }
    
    $sorted = $series;
    usort($sorted, function($a, $b) {
        $ra = isset($a['rating']) ? floatval($a['rating']) : 0;
        $rb = isset($b['rating']) ? floatval($b['rating']) : 0;
        return $rb <=> $ra;
    });
    
    return array_slice($sorted, 0, $limit);
}

function paginateSeries($series, $page, $perPage = 48) {
    $total = is_array($series) ? count($series) : 0;
    $totalPages = ceil($total / $perPage);
    $page = max(1, intval($page));
    $start = ($page - 1) * $perPage;
    $paginated = ($series && is_array($series)) ? array_slice(array_values($series), $start, $perPage) : [];
    
    return [
        'items' => $paginated,
        'total' => $total,
        'totalPages' => max(1, $totalPages),
        'currentPage' => $page
    ];
}

