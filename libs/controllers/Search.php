<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function getSearchData($user, $pwd) {
    $moviesUrl = IP . "/player_api.php?username={$user}&password={$pwd}&action=get_vod_streams";
    $seriesUrl = IP . "/player_api.php?username={$user}&password={$pwd}&action=get_series";
    $channelsUrl = IP . "/player_api.php?username={$user}&password={$pwd}&action=get_live_streams";

    $moviesResponse = apixtream($moviesUrl);
    $seriesResponse = apixtream($seriesUrl);
    $channelsResponse = apixtream($channelsUrl);

    $movies = json_decode($moviesResponse, true) ?: [];
    $series = json_decode($seriesResponse, true) ?: [];
    $channels = json_decode($channelsResponse, true) ?: [];

    $moviesData = array_map(function ($item) {
        return [
            'id' => $item['stream_id'] ?? null,
            'nombre' => $item['name'] ?? '',
            'img' => $item['stream_icon'] ?? ''
        ];
    }, $movies);

    $seriesData = array_map(function ($item) {
        return [
            'id' => $item['series_id'] ?? null,
            'nombre' => $item['name'] ?? '',
            'img' => $item['cover'] ?? ''
        ];
    }, $series);

    $channelsData = array_map(function ($item) {
        return [
            'id' => $item['stream_id'] ?? null,
            'nombre' => $item['name'] ?? '',
            'img' => $item['stream_icon'] ?? '',
            'tipo' => $item['stream_type'] ?? ''
        ];
    }, $channels);

    return [
        'peliculas' => $moviesData,
        'series' => $seriesData,
        'canales' => $channelsData
    ];
}

