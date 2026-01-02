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
    $ratingMin = isset($params['rating_min']) && $params['rating_min'] !== '' ? trim($params['rating_min']) : null;
    $ratingMax = isset($params['rating_max']) && $params['rating_max'] !== '' ? trim($params['rating_max']) : null;
    $yearMin = isset($params['year_min']) && $params['year_min'] !== '' ? trim($params['year_min']) : null;
    $yearMax = isset($params['year_max']) && $params['year_max'] !== '' ? trim($params['year_max']) : null;
    $order = isset($params['orden']) && $params['orden'] != '' ? $params['orden'] : null;
    $orderDir = isset($params['orden_dir']) && in_array($params['orden_dir'], ['asc', 'desc']) ? $params['orden_dir'] : 'asc';
    $page = isset($params['pagina']) ? max(1, intval($params['pagina'])) : 1;
    
    $movies = getMoviesData($user, $pwd, $categoryId);
    
    if (!empty($genre)) {
        $movies = filterMoviesByGenre($movies, $genre);
    }
    
    if ($ratingMin !== null && $ratingMin !== '' && $ratingMax !== null && $ratingMax !== '') {
        $movies = filterMoviesByRating($movies, $ratingMin, $ratingMax);
    }
    
    if ($yearMin !== null && $yearMin !== '' && $yearMax !== null && $yearMax !== '') {
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

