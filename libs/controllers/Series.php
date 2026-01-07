<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getSeriesData')) {
    require_once(__DIR__ . '/../services/series.php');
}

function getSeriesParams() {
    return [
        'id' => isset($_REQUEST['id']) ? trim($_REQUEST['id']) : null,
        'genero' => isset($_GET['genero']) ? $_GET['genero'] : null,
        'rating' => isset($_GET['rating']) ? $_GET['rating'] : null,
        'rating_min' => isset($_GET['rating_min']) ? $_GET['rating_min'] : null,
        'rating_max' => isset($_GET['rating_max']) ? $_GET['rating_max'] : null,
        'year' => isset($_GET['year']) ? $_GET['year'] : null,
        'year_min' => isset($_GET['year_min']) ? $_GET['year_min'] : null,
        'year_max' => isset($_GET['year_max']) ? $_GET['year_max'] : null,
        'orden' => isset($_GET['orden']) ? $_GET['orden'] : null,
        'orden_dir' => isset($_GET['orden_dir']) ? $_GET['orden_dir'] : 'asc',
        'pagina' => isset($_GET['pagina']) ? $_GET['pagina'] : 1
    ];
}

function hasActiveFilters($params) {
    return !empty($params['genero']) || 
           !empty($params['rating']) || 
           (!empty($params['rating_min']) && !empty($params['rating_max'])) ||
           !empty($params['year']) ||
           (!empty($params['year_min']) && !empty($params['year_max'])) ||
           !empty($params['orden']);
}

function getSeriesPageData($user, $pwd, $params = []) {
    $categoryId = isset($params['id']) ? trim($params['id']) : null;
    $genre = isset($params['genero']) && $params['genero'] != '' ? $params['genero'] : null;
    
    $ratingMin = null;
    $ratingMax = null;
    if (isset($params['rating']) && $params['rating'] !== '' && $params['rating'] !== null) {
        $rating = intval($params['rating']);
        $ratingMin = $rating;
        $ratingMax = $rating + 0.9;
    } elseif (isset($params['rating_min']) && $params['rating_min'] !== '' && isset($params['rating_max']) && $params['rating_max'] !== '') {
        $ratingMin = intval($params['rating_min']);
        $ratingMax = intval($params['rating_max']) + 0.9;
    }
    
    $yearMin = null;
    $yearMax = null;
    if (isset($params['year']) && $params['year'] !== '' && $params['year'] !== null) {
        $year = intval($params['year']);
        $yearMin = $year;
        $yearMax = $year;
    } elseif (isset($params['year_min']) && $params['year_min'] !== '' && isset($params['year_max']) && $params['year_max'] !== '') {
        $yearMin = intval($params['year_min']);
        $yearMax = intval($params['year_max']);
    }
    
    $order = isset($params['orden']) && $params['orden'] != '' ? $params['orden'] : null;
    $orderDir = isset($params['orden_dir']) && in_array($params['orden_dir'], ['asc', 'desc']) ? $params['orden_dir'] : 'asc';
    $page = isset($params['pagina']) ? max(1, intval($params['pagina'])) : 1;
    
    $series = getSeriesData($user, $pwd, $categoryId);
    
    if (!empty($genre)) {
        $series = filterSeriesByGenre($series, $genre);
    }
    
    if ($ratingMin !== null && $ratingMax !== null) {
        $series = filterSeriesByRating($series, $ratingMin, $ratingMax);
    }
    
    if ($yearMin !== null && $yearMax !== null) {
        $series = filterSeriesByYear($series, $yearMin, $yearMax);
    }
    
    if (!empty($order)) {
        $series = sortSeries($series, $order, $orderDir);
    }
    
    $genres = getSeriesGenres($series);
    $backdrop = getSeriesBackdrop($user, $pwd, $series);
    $popular = getPopularSeries($series, 6);
    $pagination = paginateSeries($series, $page, 48);
    
    return [
        'series' => $pagination['items'],
        'total' => $pagination['total'],
        'totalPages' => $pagination['totalPages'],
        'currentPage' => $pagination['currentPage'],
        'genres' => $genres,
        'backdrop' => $backdrop,
        'popular' => $popular,
        'allSeries' => $series
    ];
}

function getSeriesPageWithPopular($user, $pwd, $params = []) {
    $hasFilters = hasActiveFilters($params);
    $data = getSeriesPageData($user, $pwd, $params);
    
    if (!$hasFilters) {
        $allSeries = getSeriesData($user, $pwd, null);
        $populares = getPopularSeries($allSeries, 6);
    } else {
        $populares = [];
    }
    
    return [
        'series' => $data['series'],
        'totalPages' => $data['totalPages'],
        'currentPage' => $data['currentPage'],
        'genres' => $data['genres'],
        'backdrop' => $data['backdrop'],
        'popular' => $populares,
        'hasFilters' => $hasFilters
    ];
}

