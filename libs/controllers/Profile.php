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
    
    $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
    $userDataFile = __DIR__ . '/../../storage/users/' . $safeUser . '/user_data.json';
    
    $memberSince = 'Desconocida';
    if (file_exists($userDataFile)) {
        $userDataContent = file_get_contents($userDataFile);
        $sessions = json_decode($userDataContent, true);
        if (is_array($sessions) && count($sessions) > 0) {
            $firstSession = $sessions[0];
            if (isset($firstSession['date'])) {
                $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $firstSession['date']);
                if ($dateObj) {
                    $memberSince = $dateObj->format('d/m/Y');
                }
            }
        }
    }
    
    return [
        'backdrop' => $backdrop,
        'username' => $user,
        'member_since' => $memberSince
    ];
}

