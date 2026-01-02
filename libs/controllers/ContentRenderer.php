<?php

if (!function_exists('limitar_texto')) {
    require_once(__DIR__ . '/../lib.php');
}

function renderMoviesGrid($items) {
    $html = '';
    foreach($items as $row) {
        $filme_nome = $row['name'];
        $filme_id = $row['stream_id'];
        $filme_img = $row['stream_icon'];
        $filme_rat = $row['rating_5based'];
        $filme_ano = isset($row['year']) ? $row['year'] : 'N/A';
        
        $html .= '<div class="col-6 col-sm-4 col-lg-3 col-xl-3">';
        $html .= '<div class="card">';
        $html .= '<div class="card__cover">';
        $html .= '<img loading="lazy" src="' . htmlspecialchars($filme_img) . '" alt="">';
        $html .= '<a href="movie.php?stream=' . $filme_id . '&streamtipo=movie" class="card__play">';
        $html .= '<i class="fas fa-play"></i>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '<div class="card__content">';
        $html .= '<h3 class="card__title" style="margin-top:0;">';
        $html .= '<a href="movie.php?stream=' . $filme_id . '&streamtipo=movie">';
        $html .= htmlspecialchars(limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome), 30));
        $html .= '</a>';
        $html .= '</h3>';
        $html .= '<span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">';
        $html .= htmlspecialchars($filme_ano) . ' &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> ' . htmlspecialchars($filme_rat);
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    return $html;
}

function renderSeriesGrid($items) {
    $html = '';
    foreach($items as $row) {
        $serie_nome = $row['name'];
        $serie_id = $row['series_id'];
        $serie_img = $row['cover'];
        $serie_ano = isset($row['year']) ? $row['year'] : (isset($row['releaseDate']) ? substr($row['releaseDate'], 0, 4) : 'N/A');
        $serie_rat = isset($row['rating']) ? $row['rating'] : (isset($row['rating_5based']) ? $row['rating_5based'] : 'N/A');
        
        $html .= '<div class="col-6 col-sm-4 col-lg-3 col-xl-3">';
        $html .= '<div class="card">';
        $html .= '<div class="card__cover">';
        $html .= '<img loading="lazy" src="' . htmlspecialchars($serie_img) . '" alt="">';
        $html .= '<a href="serie.php?stream=' . $serie_id . '&streamtipo=serie" class="card__play">';
        $html .= '<i class="fas fa-play"></i>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '<div class="card__content">';
        $html .= '<h3 class="card__title" style="margin-top:0;">';
        $html .= '<a href="serie.php?stream=' . $serie_id . '&streamtipo=serie">';
        $html .= htmlspecialchars(limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $serie_nome), 30));
        $html .= '</a>';
        $html .= '</h3>';
        $html .= '<span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">';
        $html .= htmlspecialchars($serie_ano) . ' &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> ' . htmlspecialchars($serie_rat);
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    return $html;
}

