<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../lib.php');
}

function getSagasAdminData() {
    $backdrop_fondo = 'assets/image/wallpaper_channels.webp';

    return [
        'backdrop' => $backdrop_fondo
    ];
}

