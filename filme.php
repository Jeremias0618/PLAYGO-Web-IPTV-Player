<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$id = trim($_REQUEST['stream']);
$tipo = trim($_REQUEST['streamtipo']);

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$id";
$resposta = apixtream($url);
$output = json_decode($resposta,true);

$backdrop = '';
if (!empty($output['info']['backdrop_path']) && is_array($output['info']['backdrop_path'])) {
    $backdrop = $output['info']['backdrop_path'][0];
}
$poster_img = $output['info']['movie_image']; // Para portada principal y ficha
$repro_img = $backdrop ?: $poster_img;        // Para el poster del reproductor
$filme = $output['movie_data']['name'];
$filme = preg_replace('/\s*\(\d{4}\)$/', '', $filme);

$tmdb_api_key = "eae5dbe11c2b8d96808af6b5e0fec463";
$tmdb_id = $output['info']['tmdb_id'] ?? null;
$ano = $output['info']['releasedate'] ?? '';

if (!$tmdb_id && !empty($filme)) {
    $query = urlencode($filme);
    $tmdb_search_url = "https://api.themoviedb.org/3/search/movie?api_key=$tmdb_api_key&language=es-ES&query=$query";
    if (!empty($ano)) {
        $tmdb_search_url .= "&year=" . urlencode(substr($ano,0,4));
    }
    $tmdb_search_json = @file_get_contents($tmdb_search_url);
    $tmdb_search_data = json_decode($tmdb_search_json, true);
    if (!empty($tmdb_search_data['results'][0]['id'])) {
        $tmdb_id = $tmdb_search_data['results'][0]['id'];
    }
}

$tmdb_backdrops = [];
$tmdb_posters = [];
$wallpaper_tmdb = '';
$poster_tmdb = '';

if ($tmdb_id) {
    $tmdb_images_url = "https://api.themoviedb.org/3/movie/$tmdb_id/images?api_key=$tmdb_api_key";
    $tmdb_images_json = @file_get_contents($tmdb_images_url);
    $tmdb_images_data = json_decode($tmdb_images_json, true);

    // --- BACKDROPS (wallpapers) ---
    if (!empty($tmdb_images_data['backdrops'])) {
        foreach ($tmdb_images_data['backdrops'] as $img) {
            if (!empty($img['file_path']) && $img['iso_639_1'] === 'es') {
                $tmdb_backdrops[] = "https://image.tmdb.org/t/p/original" . $img['file_path'];
            }
        }
        // Si no hay en español, usa cualquiera
        if (empty($tmdb_backdrops)) {
            foreach ($tmdb_images_data['backdrops'] as $img) {
                if (!empty($img['file_path'])) {
                    $tmdb_backdrops[] = "https://image.tmdb.org/t/p/original" . $img['file_path'];
                }
            }
        }
    }
    if (!empty($tmdb_backdrops)) {
        $wallpaper_tmdb = $tmdb_backdrops[array_rand($tmdb_backdrops)];
    }

    // --- POSTERS ---
    if (!empty($tmdb_images_data['posters'])) {
        foreach ($tmdb_images_data['posters'] as $img) {
            if (!empty($img['file_path']) && $img['iso_639_1'] === 'es') {
                $tmdb_posters[] = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
            }
        }
        // Si no hay en español, usa cualquiera
        if (empty($tmdb_posters)) {
            foreach ($tmdb_images_data['posters'] as $img) {
                if (!empty($img['file_path'])) {
                    $tmdb_posters[] = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
                }
            }
        }
    }
    if (!empty($tmdb_posters)) {
        $poster_tmdb = $tmdb_posters[array_rand($tmdb_posters)];
    }
}

$idcategoria = $output['movie_data']['category_id'];
$exts = $output['movie_data']['container_extension'];
$trailer = $output['info']['youtube_trailer'];
$youtube_id = '';
if (!empty($trailer)) {
    // Si es solo el ID (11 caracteres), úsalo directo
    if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
        $youtube_id = $trailer;
    } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
        $youtube_id = $matches[1];
    }
}
$diretor = $output['info']['director'];
$cast = $output['info']['cast'];
$plot = $output['info']['plot'];
$genero = $output['info']['genre'];
$duracao = $output['info']['duration'];
$pais = $output['info']['country'];
$nota = $output['info']['rating'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./css/owl.carousel.min.css">
    <link rel="stylesheet" href="./css/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./css/nouislider.min.css">
    <link rel="stylesheet" href="./css/ionicons.min.css">
    <link rel="stylesheet" href="./css/photoswipe.css">
    <link rel="stylesheet" href="./css/glightbox.css">
    <link rel="stylesheet" href="./css/default-skin.css">
    <link rel="stylesheet" href="./css/jBox.all.min.css">
    <link rel="stylesheet" href="./css/select2.min.css">
    <link rel="stylesheet" href="./css/listings.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <title>MAXGO - <?php echo htmlspecialchars($filme); ?></title>
    <style>
    .seasons__cover, .details__bg, .home__bg {
        filter: blur(0px) !important;
        opacity: 10%;
    }
    .header__content {
        background: #000 !important;
        border-radius: 12px;
        padding: 12px 24px;
    }
    .header__wrap {
        background: #000 !important;
    }
    .navbar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 80px;
        z-index: 1;
        pointer-events: none;
        background: linear-gradient(90deg, #0f2027 0%, #2c5364 100%);
        opacity: 0.85;
        transition: opacity 0.4s;
    }
    .bg-animate {
        animation: navbarBgMove 8s linear infinite alternate;
        background-size: 200% 100%;
    }
    @keyframes navbarBgMove {
        0% { background-position: 0% 50%; }
        100% { background-position: 100% 50%; }
    }
    /* MODAL BUSCADOR */
    .modal-buscador-bg {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.85);
        align-items: center;
        justify-content: center;
    }
    .modal-buscador-bg.active {
        display: flex;
    }
    .modal-buscador {
        background: #181818;
        border-radius: 18px;
        padding: 32px 24px 24px 24px;
        max-width: 900px;
        width: 98vw;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 8px 32px #000a;
        position: relative;
    }
    .modal-buscador-close {
        position: absolute;
        top: 16px;
        right: 18px;
        font-size: 1.8rem;
        color: #fff;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 2;
    }
    .modal-buscador-inputbox {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
    }
    .modal-buscador-inputbox input {
        flex: 1;
        background: #232027;
        border: none;
        color: #fff;
        border-radius: 8px;
        padding: 12px 18px;
        font-size: 1.2rem;
        margin-right: 12px;
    }
    .modal-buscador-inputbox input::placeholder {
        color: #aaa;
    }
    .modal-buscador-inputbox button {
        background: #e50914;
        border: none;
        color: #fff;
        border-radius: 8px;
        padding: 10px 22px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .modal-buscador-inputbox button:hover {
        background: #fff;
        color: #e50914;
    }
    .modal-buscador-section {
        margin-bottom: 32px;
    }
    .modal-buscador-section h3 {
        color: #e50914;
        font-size: 1.25rem;
        margin-bottom: 16px;
        margin-top: 0;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .modal-buscador-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
    }
    .modal-buscador-card {
        width: 110px;
        text-align: center;
    }
    .modal-buscador-card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        border-radius: 10px;
        background: #232027;
        margin-bottom: 7px;
        box-shadow: 0 2px 8px #0005;
    }
    .modal-buscador-card span {
        color: #fff;
        font-size: 0.98rem;
        display: block;
        margin-top: 2px;
        word-break: break-word;
    }

        @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            .header__content {
                padding-left: 4px !important;
            }
    
    /* Solo para móviles (Android, iOS, etc.) */
@media (max-width: 600px) {
    .modal-buscador {
        max-width: 98vw;
        width: 98vw;
        min-width: unset;
        padding: 18px 6px 12px 6px;
        border-radius: 12px;
        box-shadow: 0 4px 16px #000a;
    }
    .modal-buscador-inputbox input {
        font-size: 1rem;
        padding: 10px 10px;
    }
    .modal-buscador-inputbox button {
        font-size: 1rem;
        padding: 8px 12px;
    }
    .modal-buscador-section h3 {
        font-size: 1.05rem;
        margin-bottom: 10px;
    }
    .modal-buscador-grid {
        gap: 10px;
    }
    .modal-buscador-card {
        width: 90px;
    }
    .modal-buscador-card img {
        height: 100px;
    }
    .modal-buscador-card span {
        font-size: 0.85rem;
    }
}

/* Mejor estilo responsive para botones Tráiler, Favoritos y Saga en móviles */
@media (max-width: 600px) {
    .card__content > div[style*="display: flex"] {
        flex-direction: column !important;
        gap: 10px !important;
        margin-top: 18px !important;
    }
    #btnTrailer,
    #btnFavorito,
    a[href="predator.php"] {
        width: 100% !important;
        min-width: 0 !important;
        justify-content: center !important;
        align-items: center !important;
        font-size: 1.08rem !important;
        padding: 13px 0 !important;
        border-radius: 12px !important;
        margin-bottom: 0 !important;
        box-shadow: 0 2px 12px #0002;
        transition: background 0.18s, color 0.18s;
        gap: 10px !important;
    }
    #btnTrailer i,
    #btnFavorito i,
    a[href="predator.php"] i {
        font-size: 1.35rem !important;
        margin-right: 8px !important;
    }
    #btnTrailer span,
    #btnFavorito span,
    a[href="predator.php"] span {
        font-size: 1.08rem !important;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    #btnTrailer {
        background: linear-gradient(90deg,#ff0000 60%,#c80000 100%) !important;
        color: #fff !important;
    }
    #btnFavorito {
        background: linear-gradient(90deg,#232027 60%,#444 100%) !important;
        color: #ffd700 !important;
    }
    a[href="predator.php"] {
        background: linear-gradient(90deg,#0f2027 60%,#2c5364 100%) !important;
        color: #fff !important;
        text-decoration: none !important;
    }
    #btnTrailer:active,
    #btnFavorito:active,
    a[href="predator.php"]:active {
        opacity: 0.85;
    }
}

    @media (max-width: 600px) {
        /* Centrar título */
        .details__title {
            text-align: center !important;
            width: 100%;
            display: block;
        }
        /* Centrar poster */
        .card__cover {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 18px auto;
        }
        .card__cover img {
            margin: 0 auto;
            display: block;
            max-width: 90vw;
            height: auto;
        }
        /* Centrar info debajo del poster */
        .card__content {
            text-align: center !important;
            align-items: center !important;
            justify-content: center !important;
            display: flex;
            flex-direction: column;
        }
        .card__meta {
            justify-content: center !important;
            text-align: center !important;
            margin: 0 auto 10px auto;
            padding: 0;
        }
        .card__description--details {
            text-align: center !important;
        }
    }

    @media (max-width: 600px) {
        /* Solo mostrar Duración en móviles */
        .card__meta li:nth-child(2),
        .card__meta li:nth-child(3) {
            display: none !important;
        }
    }
    @media (max-width: 600px) {
        /* Centrar título */
        .details__title {
            text-align: center !important;
            width: 100%;
            display: block;
            font-weight: 700 !important; /* Hace la letra más gruesa */
        }
    @media (max-width: 600px) {
        .card__wrap {
            margin-top: 4px !important; /* Reduce el espacio arriba del año/rating */
            margin-bottom: 0 !important;
        }
    }
    
    @media (max-width: 600px) {
        .content .card__title {
            max-width: 110px;
            margin-left: auto;
            margin-right: auto;
            overflow: hidden;
        }
        .content .card__title a {
            display: -webkit-box;
            -webkit-line-clamp: 2;      /* Máximo 2 líneas */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 2.3em;          /* Ajusta según tamaño de fuente */
            font-size: 0.95rem;
            line-height: 1.2;
            color: #fff;
            text-decoration: none;
            word-break: break-word;
            width: 100%;
            max-width: 110px;
        }
        .content .card {
            max-width: 110px;
            margin-left: auto;
            margin-right: auto;
        }
    }    
    </style>
</head>
<body class="body">
<!-- HEADER estilo painel.php -->
<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="index.php">
                            <img src="img/logo.png" alt="" height="48px">
                        </a>
                        <ul class="header__nav d-flex align-items-center mb-0">
                            <li class="header__nav-item">
                                <a href="./painel.php" class="header__nav-link">Inicio</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="./canais.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="filmes.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link">Series</a>
                            </li>
                        </ul>
                        <div class="header__auth d-flex align-items-center">
                            <button class="header__search-btn" type="button" id="openSearchModal">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="profile.php">
                                <button class="header__signout-btn" type="button">
                                    <i class="fas fa-user"></i>
                                </button>
                            </a>
                        </div>
                        <button class="header__btn" type="button">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- MODAL BUSCADOR -->
<div class="modal-buscador-bg" id="modalBuscador">
    <div class="modal-buscador">
        <button class="modal-buscador-close" id="closeSearchModal" title="Cerrar">&times;</button>
        <form id="modalBuscadorForm" autocomplete="off" onsubmit="return false;">
            <div class="modal-buscador-inputbox">
                <input type="text" id="modalBuscadorInput" placeholder="Buscar películas, series o canales en vivo..." autofocus>
                <button type="button" id="modalBuscadorBtn">Buscar</button>
            </div>
        </form>
        <div id="modalBuscadorResults"></div>
    </div>
</div>
<section class="section details">
<div class="details__bg" data-bg="<?php echo $wallpaper_tmdb ?: ($backdrop ?: $poster_img); ?>"></div>
    <div class="container top-margin">
        <div class="row">
            <div class="col-12">
                <!-- Solo el título, sin año -->
                <h1 class="details__title"><?php echo htmlspecialchars($filme); ?><br/>
                    <ul class="card__list">
                        <?php foreach (explode(',', $genero) as $g): ?>
                            <li><?php echo htmlspecialchars(trim($g)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </h1>
            </div>
            <div class="col-12 col-xl-12">
                <div class="card card--details">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-3">
                            <div class="card__cover">
<img src="<?php echo $poster_tmdb ?: $poster_img; ?>" alt="">
                            </div>
                        </div>
                        <div class="col-12 col-sm-9 col-md-9 col-lg-9 col-xl-9">
                            <div class="card__content">
                           <div class="card__wrap">
                              <span class="card__rate">
                                 <?php
                                 // Mostrar solo el año, estrella y calificación
                                 echo ($ano ? substr($ano, 0, 4) : '');
                                 if ($nota !== '') {
                                       echo ' &nbsp; <i class="fa-solid fa-star"></i> ' . $nota;
                                 }
                                 ?>
                              </span>
                           </div>
                                <ul class="card__meta">
                                    <li><span><strong>Duración:</strong></span> <?php echo $duracao; ?></li>
                                    <li><span><strong>País:</strong></span> <a href="#"><?php echo $pais; ?></a></li>
                                    <li><span><strong>Reparto:</strong></span> <?php echo $cast; ?></li>
                                </ul>
                    <div class="card__description card__description--details">
                        <?php echo $plot; ?>
                    </div>
                    <!-- Botón Tráiler YouTube -->
                    <div style="display: flex; gap: 16px; margin-top: 20px;">
                        <?php if (!empty($youtube_id)): ?>
                            <button id="btnTrailer" style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#ff0000 60%,#c80000 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                <i class="fab fa-youtube" style="font-size:1.5rem;"></i>
                                <span>Tráiler</span>
                                            </button>
                                        <?php endif; ?>

                    <!-- Botón Favoritos -->
                    <button id="btnFavorito"
                        style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#232027 60%,#444 100%);color:#ffd700;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                        <i class="fa fa-star" style="font-size:1.4rem;"></i>
                        <span id="favText">Agregar a Favoritos</span>
                    </button>
                    <?php
                    $saga_predator_ids = [7716, 2693, 2690, 2689, 2692, 2691, 2450, 2449];
                    if (in_array((int)$id, $saga_predator_ids)): ?>
                        <a href="predator.php" style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#0f2027 60%,#2c5364 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;text-decoration:none;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                            <i class="fa fa-film" style="font-size:1.4rem;"></i>
                            <span>Saga Predator</span>
                        </a>
                    <?php endif; ?>
                    <!-- Modal Trailer -->
<div id="trailerModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.92);z-index:99999;align-items:center;justify-content:center;">
    <div style="position:relative;max-width:900px;width:95vw;">
        <button id="closeTrailerModal" style="position:absolute;top:-38px;right:-8px;background:none;border:none;color:#fff;font-size:2.5rem;cursor:pointer;z-index:2;">&times;</button>
        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;box-shadow:0 8px 32px #000a;">
            <iframe id="trailerIframe" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:12px;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    <div class="row top-margin-sml">
                        <div class="col-12">
                            <div class="alert alert-danger" id="player__error" style="display: none;"></div>
                            <div id="player_row">
                                <div id="now__playing__player"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Reproductor Plyr con botones avanzar/retroceder -->
                    <div class="row">
                        <div class="col-12">
                            <?php
                            // Detectar extensión y usar el reproductor adecuado
                            $ext = strtolower($exts);
                            if ($ext == 'mp4'): ?>
                                <!-- Plyr CSS -->
                                <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
                                <video
                                    id="plyr-video"
                                    playsinline
                                    controls
                                    width="100%"
                                    height="450"
                                    poster="<?php echo $wallpaper_tmdb ?: $repro_img; ?>"
                            >
                                    <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.mp4" type="video/mp4" />
                                </video>
                                <!-- Plyr JS -->
                                <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
                                <script>
                                const player = new Plyr('#plyr-video', {
                                    controls: [
                                        'play-large', 'play', 'rewind', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'
                                    ],
                                    seekTime: 10,
                                });
                                window.player = player; // <-- AGREGA ESTA LÍNEA
                                </script>
                            <?php else: ?>
                                <!-- Reproductor HTML5 nativo ajustando tamaño automáticamente y más grande -->
                                <div style="width:100%;max-width:1100px;margin:auto;">
                                    <video
                                        controls
                                        poster="<?php echo $wallpaper_tmdb ?: $repro_img; ?>"
                                        style="background:#000;display:block;margin:auto;width:100%;max-width:1100px;height:600px;object-fit:contain;"
                                    >
                                        <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.<?php echo $exts; ?>" type="video/<?php echo htmlspecialchars($exts); ?>" />
                                        <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.<?php echo $exts; ?>" />
                                        Tu navegador no soporta este formato de video.
                                    </video>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Fin reproductor -->
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container" style="margin-top: 30px;">
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-12">
                        <h2 class="section__title section__title--sidebar">Usuarios también vieron</h2>
                    </div>
                    <?php
                    // Sugerencias
                $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams&category_id=$idcategoria";
                $resposta = apixtream($url);
                $output = json_decode($resposta,true);
                shuffle($output);
                $i = 1;
                foreach(array_rand($output,6) as $index) {
                    $row = $output[$index];
                    $filme_nome = $row['name'];
                    $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome);
                    $filme_type = $row['stream_type'];
                    $filme_id = $row['stream_id'];
                    $filme_img = $row['stream_icon'];
                    $filme_rat = isset($row['rating']) ? $row['rating'] : '';
                    $filme_ano = isset($row['year']) ? $row['year'] : '';
                ?>
                <div class="col-4 col-sm-4 col-lg-2">
                    <div class="card">
                        <div class="card__cover">
                            <img loading="lazy" src="<?php echo $filme_img; ?>" alt="">
                            <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title">
                                <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                                    <?php echo $filme_nome; ?>
                                </a>
                            </h3>
                            <span class="card__rate">
                                <?php
                                if ($filme_ano) {
                                    echo $filme_ano;
                                }
                                if ($filme_rat !== '') {
                                    echo ' <i class="fa-solid fa-star"></i> ' . $filme_rat;
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php $i++; } ?>
                </div>
            </div>
        </div>
    </div>
</section>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footer__copyright">
                    &copy; 2021 <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="img/logo.png"> v1.1.8
                </div>
            </div>
        </div>
    </div>
</footer>
<script type="text/javascript" src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1"></script>
<script src="./js/jquery-3.5.1.min.js"></script>
<script src="./js/bootstrap.bundle.min.js"></script>
<script src="./js/owl.carousel.min.js"></script>
<script src="./js/jquery.mousewheel.min.js"></script>
<script src="./js/jquery.mcustomscrollbar.min.js"></script>
<script src="./js/wnumb.js"></script>
<script src="./js/nouislider.min.js"></script>
<script src="./js/jquery.morelines.min.js"></script>
<script src="./js/photoswipe.min.js"></script>
<script src="./js/photoswipe-ui-default.min.js"></script>
<script src="./js/glightbox.min.js"></script>
<script src="./js/jBox.all.min.js"></script>
<script src="./js/select2.min.js"></script>
<script src="./js/main.js"></script>
<script>
// MODAL BUSCADOR igual que painel.php
document.addEventListener('DOMContentLoaded', function() {
    const openSearchModal = document.getElementById('openSearchModal');
    const closeSearchModal = document.getElementById('closeSearchModal');
    const modalBuscador = document.getElementById('modalBuscador');
    const modalBuscadorInput = document.getElementById('modalBuscadorInput');
    const modalBuscadorBtn = document.getElementById('modalBuscadorBtn');
    const modalBuscadorResults = document.getElementById('modalBuscadorResults');

    function showModalBuscador() {
        modalBuscador.classList.add('active');
        setTimeout(() => { modalBuscadorInput.focus(); }, 200);
    }
    function hideModalBuscador() {
        modalBuscador.classList.remove('active');
        modalBuscadorInput.value = '';
        modalBuscadorResults.innerHTML = '';
    }
    if(openSearchModal) openSearchModal.onclick = showModalBuscador;
    if(closeSearchModal) closeSearchModal.onclick = hideModalBuscador;
    window.addEventListener('keydown', function(e) {
        if (e.key === "Escape") hideModalBuscador();
    });
    if(modalBuscador) {
        modalBuscador.addEventListener('click', function(e) {
            if (e.target === modalBuscador) hideModalBuscador();
        });
    }

    // Cargar datos para búsqueda (películas, series, canales)
    let peliculas = [];
    let series = [];
    let canales = [];
    <?php
    // Películas
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
    $resposta = apixtream($url);
    $peliculas = json_decode($resposta,true);
    echo "peliculas = ".json_encode(array_map(function($p){
        return [
            'id'=>$p['stream_id'],
            'nombre'=>$p['name'],
            'img'=>$p['stream_icon'],
            'tipo'=>$p['stream_type']
        ];
    },$peliculas)).";\n";
    // Series
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
    $resposta = apixtream($url);
    $series = json_decode($resposta,true);
    echo "series = ".json_encode(array_map(function($s){
        return [
            'id'=>$s['series_id'],
            'nombre'=>$s['name'],
            'img'=>$s['cover']
        ];
    },$series)).";\n";
    // Canales
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
    $resposta = apixtream($url);
    $canales = json_decode($resposta,true);
    echo "canales = ".json_encode(array_map(function($c){
        return [
            'id'=>$c['stream_id'],
            'nombre'=>$c['name'],
            'img'=>$c['stream_icon'],
            'tipo'=>$c['stream_type']
        ];
    },$canales)).";\n";
    ?>

    function renderBuscadorResults(query) {
        query = query.trim().toLowerCase();
        let html = '';
        // Películas
        let pelis = peliculas.filter(p => p.nombre.toLowerCase().includes(query));
        if (pelis.length > 0) {
            html += `<div class="modal-buscador-section"><h3>PELICULAS</h3><div class="modal-buscador-grid">`;
            pelis.slice(0,12).forEach(p => {
                html += `<div class="modal-buscador-card">
                    <a href="filme.php?sessao=<?php echo gerar_hash(256); ?>&stream=${p.id}&streamtipo=${p.tipo}">
                        <img src="${p.img}" alt="${p.nombre}">
                        <span>${p.nombre}</span>
                    </a>
                </div>`;
            });
            html += `</div></div>`;
        }
        // Series
        let sers = series.filter(s => s.nombre.toLowerCase().includes(query));
        if (sers.length > 0) {
            html += `<div class="modal-buscador-section"><h3>SERIES</h3><div class="modal-buscador-grid">`;
            sers.slice(0,12).forEach(s => {
                html += `<div class="modal-buscador-card">
                    <a href="serie.php?sessao=<?php echo gerar_hash(256); ?>&stream=${s.id}&serie=${encodeURIComponent(s.nombre)}&img=${encodeURIComponent(s.img)}">
                        <img src="${s.img}" alt="${s.nombre}">
                        <span>${s.nombre}</span>
                    </a>
                </div>`;
            });
            html += `</div></div>`;
        }
        // Canales
        let chans = canales.filter(c => c.nombre.toLowerCase().includes(query));
        if (chans.length > 0) {
            html += `<div class="modal-buscador-section"><h3>TV EN VIVO</h3><div class="modal-buscador-grid">`;
            chans.slice(0,12).forEach(c => {
                html += `<div class="modal-buscador-card">
                    <a href="canal.php?sessao=<?php echo gerar_hash(256); ?>&stream=${c.id}&streamtipo=${c.tipo}&canal=${encodeURIComponent(c.nombre)}&img=${encodeURIComponent(c.img)}">
                        <img src="${c.img}" alt="${c.nombre}">
                        <span>${c.nombre}</span>
                    </a>
                </div>`;
            });
            html += `</div></div>`;
        }
        if (!html && query.length > 0) {
            html = `<div style="color:#fff;text-align:center;margin-top:30px;">Sin resultados.</div>`;
        }
        modalBuscadorResults.innerHTML = html;
    }

    if(modalBuscadorInput) {
        modalBuscadorInput.addEventListener('input', function() {
            let q = this.value;
            if (q.length > 1) renderBuscadorResults(q);
            else modalBuscadorResults.innerHTML = '';
        });
    }
    if(modalBuscadorBtn) {
        modalBuscadorBtn.addEventListener('click', function() {
            let q = modalBuscadorInput.value;
            if (q.length > 1) renderBuscadorResults(q);
        });
    }
    if(modalBuscadorInput) {
        modalBuscadorInput.addEventListener('keydown', function(e){
            if(e.key === "Enter") {
                e.preventDefault();
                let q = modalBuscadorInput.value;
                if (q.length > 1) renderBuscadorResults(q);
            }
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTrailer = document.getElementById('btnTrailer');
    const trailerModal = document.getElementById('trailerModal');
    const closeTrailerModal = document.getElementById('closeTrailerModal');
    const trailerIframe = document.getElementById('trailerIframe');
    const youtubeId = "<?php echo $youtube_id; ?>";
    if(btnTrailer && youtubeId) {
        btnTrailer.onclick = function() {
            trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
            trailerModal.style.display = "flex";
        }
    }
    if(closeTrailerModal) {
        closeTrailerModal.onclick = function() {
            trailerModal.style.display = "none";
            trailerIframe.src = "";
        }
    }
    if(trailerModal) {
        trailerModal.addEventListener('click', function(e){
            if(e.target === trailerModal){
                trailerModal.style.display = "none";
                trailerIframe.src = "";
            }
        });
    }
    window.addEventListener('keydown', function(e){
        if(e.key === "Escape" && trailerModal.style.display === "flex"){
            trailerModal.style.display = "none";
            trailerIframe.src = "";
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const movieKey = "movie_time_<?php echo $id; ?>";
    const movieTitle = "<?php echo addslashes($filme); ?>";

    function showResumeNotification(time, onAccept, onCancel) {
        if (document.getElementById('resumeNotifBg')) return;
        const bg = document.createElement('div');
        bg.id = 'resumeNotifBg';
        bg.style.position = 'fixed';
        bg.style.left = '0';
        bg.style.top = '0';
        bg.style.width = '100vw';
        bg.style.height = '100vh';
        bg.style.background = 'rgba(0,0,0,0.85)';
        bg.style.zIndex = '999999';
        bg.style.display = 'flex';
        bg.style.alignItems = 'center';
        bg.style.justifyContent = 'center';

        const notif = document.createElement('div');
        notif.id = 'resumeNotif';
        notif.style.background = '#232027';
        notif.style.color = '#fff';
        notif.style.padding = '32px 38px';
        notif.style.borderRadius = '16px';
        notif.style.boxShadow = '0 8px 32px #000a';
        notif.style.fontSize = '1.18rem';
        notif.style.display = 'flex';
        notif.style.flexDirection = 'column';
        notif.style.alignItems = 'center';
        notif.style.gap = '28px';
        notif.style.maxWidth = '90vw';
        notif.style.textAlign = 'center';

        const min = Math.floor(time/60);
        const sec = Math.floor(time%60).toString().padStart(2,'0');
        notif.innerHTML = `
            <span style="font-size:1.15rem;">¿Deseas continuar viendo <b>${movieTitle}</b> desde el minuto <b>${min}:${sec}</b>?</span>
            <div style="display:flex;gap:18px;">
                <button id="resumeAccept" style="background:#e50914;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;cursor:pointer;margin-right:10px;">Aceptar</button>
                <button id="resumeCancel" style="background:#444;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;cursor:pointer;">Cancelar</button>
            </div>
        `;
        bg.appendChild(notif);
        document.body.appendChild(bg);

        document.getElementById('resumeAccept').onclick = function() {
            bg.remove();
            onAccept();
        };
        document.getElementById('resumeCancel').onclick = function() {
            bg.remove();
            if (onCancel) onCancel();
        };
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === "Escape") {
                bg.remove();
                document.removeEventListener('keydown', escListener);
                if (onCancel) onCancel();
            }
        });
    }

    // Esperar a que Plyr esté listo
    if (document.getElementById('plyr-video') && window.Plyr) {
        // Espera a que window.player esté definido
        let plyrInterval = setInterval(function() {
            if (window.player && typeof window.player.on === "function") {
                clearInterval(plyrInterval);
                const plyrPlayer = window.player;
                plyrPlayer.on('timeupdate', function() {
                    localStorage.setItem(movieKey, Math.floor(plyrPlayer.currentTime));
                });
                plyrPlayer.on('ended', function() {
                    localStorage.removeItem(movieKey);
                });
                const lastTime = parseInt(localStorage.getItem(movieKey) || "0");
                if (lastTime > 10) {
                    plyrPlayer.pause();
                    showResumeNotification(lastTime, function() {
                        plyrPlayer.currentTime = lastTime;
                        plyrPlayer.play();
                    });
                }
            }
        }, 200);
    }
    // HTML5 player
    else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
        const video = document.querySelector('video');
        video.addEventListener('timeupdate', function() {
            localStorage.setItem(movieKey, Math.floor(video.currentTime));
        });
        video.addEventListener('ended', function() {
            localStorage.removeItem(movieKey);
        });
        const lastTime = parseInt(localStorage.getItem(movieKey) || "0");
        if (lastTime > 10) {
            video.pause();
            showResumeNotification(lastTime, function() {
                video.currentTime = lastTime;
                video.play();
            });
        }
    }
});
</script>

<script>
window['__onGCastApiAvailable'] = function(isAvailable) {
  if (isAvailable) {
    cast.framework.CastContext.getInstance().setOptions({
      receiverApplicationId: chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID,
      autoJoinPolicy: chrome.cast.AutoJoinPolicy.ORIGIN_SCOPED
    });
  }
};

document.addEventListener('DOMContentLoaded', function() {
    // Crear botón de Cast
    const castBtn = document.createElement('google-cast-button');
    castBtn.style.setProperty('--connected-color', '#e50914');
    castBtn.style.setProperty('--disconnected-color', '#fff');
    castBtn.style.width = '42px';
    castBtn.style.height = '42px';
    castBtn.style.marginLeft = '10px';
    castBtn.title = "Transmitir a Chromecast";

    // Insertar el botón en el contenedor de botones
    let insertTarget = document.querySelector('.card__content > div[style*="display: flex"]');
    if (insertTarget) {
        insertTarget.appendChild(castBtn);
    } else {
        // Si no existe, insértalo al inicio de .card__content
        let cardContent = document.querySelector('.card__content');
        if (cardContent) cardContent.insertBefore(castBtn, cardContent.firstChild);
    }

    // Lanzar video al Chromecast cuando se seleccione un dispositivo
    if (window.cast && cast.framework) {
        cast.framework.CastContext.getInstance().addEventListener(
            cast.framework.CastContextEventType.SESSION_STATE_CHANGED,
            function(event) {
                if (event.sessionState === cast.framework.SessionState.SESSION_STARTED) {
                    lanzarVideoACast();
                }
            }
        );
    }

    function lanzarVideoACast() {
        let video = document.getElementById('plyr-video') || document.querySelector('video');
        if (!video) return;
        let src = video.currentSrc || video.src;
        if (!src) return;
        let mediaInfo = new chrome.cast.media.MediaInfo(src, 'video/mp4');
        mediaInfo.metadata = new chrome.cast.media.GenericMediaMetadata();
        mediaInfo.metadata.title = document.title;
        let request = new chrome.cast.media.LoadRequest(mediaInfo);
        let session = cast.framework.CastContext.getInstance().getCurrentSession();
        if (session) session.loadMedia(request);
    }

    // Mostrar advertencia si no es HTTPS ni localhost
    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
        castBtn.style.display = 'none';
        console.warn('Chromecast solo funciona en HTTPS o localhost.');
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFav = document.getElementById('btnFavorito');
    const favText = document.getElementById('favText');
    let isFav = false;

    // Consultar estado inicial
    fetch('db/base.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=fav_check&id=<?php echo $id; ?>&tipo=pelicula`
    })
    .then(res => res.json())
    .then(data => {
        if (data.is_fav) {
            isFav = true;
            favText.textContent = 'Favorito';
            btnFav.classList.add('favorito-active');
        }
    });

    btnFav.addEventListener('click', function() {
        const action = isFav ? 'fav_remove' : 'fav_add';
        fetch('db/base.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=${action}&id=<?php echo $id; ?>&nombre=<?php echo urlencode($filme); ?>&img=<?php echo urlencode($poster_tmdb ?: $poster_img); ?>&ano=<?php echo urlencode($ano); ?>&rate=<?php echo urlencode($nota); ?>&tipo=pelicula`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                isFav = !isFav;
                favText.textContent = isFav ? 'Favorito' : 'Agregar a Favoritos';
                btnFav.classList.toggle('favorito-active', isFav);
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let historialGuardado = false;
    function guardarHistorial() {
        if (historialGuardado) return;
        historialGuardado = true;
        fetch('db/base.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=hist_add&id=<?php echo $id; ?>&nombre=<?php echo urlencode($filme); ?>&img=<?php echo urlencode($poster_tmdb ?: $poster_img); ?>&ano=<?php echo urlencode($ano); ?>&rate=<?php echo urlencode($nota); ?>&tipo=pelicula`
        });
    }

    // Plyr
    if (window.player && typeof window.player.on === "function") {
        window.player.on('play', guardarHistorial);
    }
    // HTML5 player (cuando NO es Plyr)
    else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
        const video = document.querySelector('video');
        video.addEventListener('play', guardarHistorial);
    }
});
</script>

</body>
</html>