<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getMoviesData')) {
    require_once(__DIR__ . '/../services/movies.php');
}

function getProfilePageData($user, $pwd) {
    $movies = getMoviesData($user, $pwd);
    $backdrop = getMovieBackdrop($user, $pwd, $movies);
    
    return [
        'backdrop' => $backdrop
    ];
}

