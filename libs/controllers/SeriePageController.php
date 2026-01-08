<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

if (!function_exists('getSeriesData')) {
    require_once(__DIR__ . '/SeriesDetail.php');
}

if (!function_exists('getSeriesEpisodesImages')) {
    require_once(__DIR__ . '/../services/series-episodes.php');
}

function getSeriePageData($user, $pwd, $id) {
    $seriesData = getSeriesData($user, $pwd, $id);
    
    if (!$seriesData) {
        return null;
    }
    
    $tmdb_id = $seriesData['tmdb_id'];
    $episodios = $seriesData['episodes'];
    
    $tmdb_episodios_imgs = getSeriesEpisodesImages($tmdb_id, $episodios);
    
    $total_temporadas = is_array($episodios) ? count($episodios) : 0;
    $total_episodios = 0;
    if (is_array($episodios)) {
        foreach ($episodios as $eps) {
            $total_episodios += is_array($eps) ? count($eps) : 0;
        }
    }
    
    return [
        'id' => $seriesData['id'],
        'serie_nome' => $seriesData['name'],
        'poster_img' => $seriesData['poster_img'],
        'poster_tmdb' => $seriesData['poster_tmdb'],
        'wallpaper_tmdb' => $seriesData['wallpaper_tmdb'],
        'backdrop' => $seriesData['backdrop'],
        'sinopsis' => $seriesData['plot'],
        'genero' => $seriesData['genre'],
        'ano' => $seriesData['year'],
        'pais' => $seriesData['country'],
        'nota' => $seriesData['rating'],
        'cast' => $seriesData['cast'],
        'diretor' => $seriesData['director'],
        'duracao' => $seriesData['duration'],
        'youtube_id' => $seriesData['youtube_id'],
        'tmdb_id' => $tmdb_id,
        'episodios' => $episodios,
        'tmdb_episodios_imgs' => $tmdb_episodios_imgs,
        'total_temporadas' => $total_temporadas,
        'total_episodios' => $total_episodios
    ];
}

