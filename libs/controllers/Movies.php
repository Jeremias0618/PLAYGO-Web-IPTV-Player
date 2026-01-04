<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getMoviesData')) {
    require_once(__DIR__ . '/../services/movies.php');
}

function getMoviesPageData($user, $pwd, $params = []) {
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
    
    $movies = getMoviesData($user, $pwd, $categoryId);
    
    if (!empty($genre)) {
        $movies = filterMoviesByGenre($movies, $genre);
    }
    
    if ($ratingMin !== null && $ratingMax !== null) {
        $movies = filterMoviesByRating($movies, $ratingMin, $ratingMax);
    }
    
    if ($yearMin !== null && $yearMax !== null) {
        $movies = filterMoviesByYear($movies, $yearMin, $yearMax);
    }
    
    if (!empty($order)) {
        $movies = sortMovies($movies, $order, $orderDir);
    }
    
    $genres = getMoviesGenres($movies);
    $backdrop = getMovieBackdrop($user, $pwd, $movies);
    $popular = getPopularMovies($movies, 6);
    $pagination = paginateMovies($movies, $page, 48);
    
    return [
        'movies' => $pagination['items'],
        'total' => $pagination['total'],
        'totalPages' => $pagination['totalPages'],
        'currentPage' => $pagination['currentPage'],
        'genres' => $genres,
        'backdrop' => $backdrop,
        'popular' => $popular,
        'allMovies' => $movies
    ];
}

