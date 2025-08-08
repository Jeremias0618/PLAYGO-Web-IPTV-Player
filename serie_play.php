<?php
require_once("libs/lib.php");

// Validar sesión
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$serie_id = trim($_GET['serie_id'] ?? '');
$ep_id = trim($_GET['ep_id'] ?? '');

// Obtener info de la serie y episodio
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$serie_id";
$resposta = apixtream($url);
$output = json_decode($resposta,true);

$serie_nome = $output['info']['name'] ?? '';
$poster_img = $output['info']['cover'] ?? '';
$wallpaper_img = '';
if (!empty($output['info']['backdrop_path'])) {
    if (is_array($output['info']['backdrop_path'])) {
        $wallpaper_img = $output['info']['backdrop_path'][0];
    } else {
        $wallpaper_img = $output['info']['backdrop_path'];
    }
}
$sinopsis = $output['info']['plot'] ?? '';
$genero = $output['info']['genre'] ?? '';
$ano = $output['info']['releaseDate'] ?? '';
$nota = $output['info']['rating'] ?? '';
$cast = $output['info']['cast'] ?? '';
$diretor = $output['info']['director'] ?? '';
$episodios = $output['episodes'] ?? [];

$ep_data = null;
foreach ($episodios as $temporada) {
    foreach ($temporada as $ep) {
        if ($ep['id'] == $ep_id) {
            $ep_data = $ep;
            break 2;
        }
    }
}
$ep_name = $ep_data['title'] ?? 'Episodio';
$ep_num = $ep_data['episode_num'] ?? '';
$ep_plot = $ep_data['info']['plot'] ?? '';
$ep_dur = $ep_data['info']['duration'] ?? '';
$ep_img = $ep_data['info']['movie_image'] ?? $poster_img;
$ep_ext = $ep_data['container_extension'] ?? 'ts';

// Construir la URL del stream del episodio
$season_num = $ep_data['season'] ?? '';
$episode_num = $ep_data['episode_num'] ?? '';
$video_url = IP . "/series/$user/$pwd/$ep_id.$ep_ext";

// Buscar fondo del capítulo (still local de tmdb_cache o TMDB)
$ep_still = '';
$tmdb_id = $output['info']['tmdb_id'] ?? null;
$season = isset($ep_data['season']) ? intval($ep_data['season']) : '';
$ep_number = isset($ep_data['episode_num']) ? intval($ep_data['episode_num']) : '';
if ($tmdb_id && $season && $ep_number) {
    $still_filename = "{$tmdb_id}_{$season}_{$ep_number}.jpg";
    $still_local_path = __DIR__ . "/tmdb_cache/$still_filename";
    $still_local_url = "tmdb_cache/$still_filename";
    if (file_exists($still_local_path)) {
        $ep_still = $still_local_url;
    } else {
        // Si no existe local, intenta TMDB directo y descarga si existe
        $tmdb_api_key = "eae5dbe11c2b8d96808af6b5e0fec463";
        $tmdb_still_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season/episode/$ep_number?api_key=$tmdb_api_key&language=es-ES";
        $tmdb_still_json = @file_get_contents($tmdb_still_url);
        $tmdb_still_data = json_decode($tmdb_still_json, true);
        if (!empty($tmdb_still_data['still_path'])) {
            $ep_still = "https://image.tmdb.org/t/p/w780" . $tmdb_still_data['still_path'];
            // Descargar y guardar localmente para próximas veces
            $img_data = @file_get_contents($ep_still);
            if ($img_data) {
                @file_put_contents($still_local_path, $img_data);
                $ep_still = $still_local_url;
            }
        }
    }
}

// Fallback: backdrop_path o wallpaper_img
$ep_backdrop = '';
if (!empty($ep_still)) {
    $ep_backdrop = $ep_still;
} elseif (!empty($ep_data['info']['backdrop_path'])) {
    $ep_backdrop = $ep_data['info']['backdrop_path'];
    if (!preg_match('/^https?:\/\//', $ep_backdrop) && !str_starts_with($ep_backdrop, '/')) {
        $ep_backdrop = 'tmdb_cache/' . $ep_backdrop;
    }
} elseif (!empty($wallpaper_img)) {
    $ep_backdrop = $wallpaper_img;
}
$ep_poster = '';
if (!empty($ep_still)) {
    $ep_poster = $ep_still; // Imagen del capítulo (local o TMDB)
} elseif (!empty($ep_data['info']['movie_image'])) {
    $ep_poster = $ep_data['info']['movie_image']; // Imagen del episodio
} else {
    $ep_poster = $poster_img; // Póster de la serie
}

// Aquí agregas este bloque:
$ep_img_from_get = trim($_GET['ep_img'] ?? '');
if ($ep_img_from_get) {
    $ep_poster = $ep_img_from_get;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PLAYGO - <?php echo htmlspecialchars($serie_nome); ?> - <?php echo htmlspecialchars($ep_name); ?></title>
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
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
        .mobile-menu {
        display: none;
        }
        #mobileMenuOverlay {
        display: none;
        }
            body {
                color: #fff;
        <?php if (!empty($wallpaper_img)): ?>
                background: linear-gradient(180deg,rgba(24,24,24,0.80) 0%,rgba(24,24,24,0.80) 100%), url('<?php echo $wallpaper_img; ?>') center center/cover no-repeat;
                background-attachment: fixed;
        <?php else: ?>
                background: #181818;
        <?php endif; ?>
            }
        .header__content { background: #000 !important; border-radius: 12px; padding: 12px 24px; }
        .header__wrap { background: #000 !important; }
        .navbar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 80px; z-index: 1; pointer-events: none; background: linear-gradient(90deg, #0f2027 0%, #2c5364 100%); opacity: 0.85; transition: opacity 0.4s; }
        .bg-animate { animation: navbarBgMove 8s linear infinite alternate; background-size: 200% 100%; }
        @keyframes navbarBgMove { 0% { background-position: 0% 50%; } 100% { background-position: 100% 50%; } }
        .card__cover img { width: 100%; border-radius: 12px; box-shadow: 0 8px 32px #000a; }
        .card__content { color: #fff; }
        .card__rate { font-size: 1.1rem; margin-bottom: 10px; }
        .card__meta { list-style: none; padding: 0; margin: 0 0 10px 0; }
        .card__meta li { margin-bottom: 6px; }
        .card__description { color:rgb(255, 255, 255); margin-bottom: 10px; }
        .btn-back { background: #e50914; color: #fff; border: none; border-radius: 8px; padding: 8px 22px; font-size: 1.1rem; font-weight: 600; margin-bottom: 24px; transition: background 0.2s; text-decoration: none; display: inline-block; }
        .btn-back:hover { background: #fff; color: #e50914; }
        @media (max-width: 900px) {
            .details__title { font-size: 1.3rem; }
            .card__cover img { max-width: 320px; margin: 0 auto; }
        }

        .details__title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            text-transform: uppercase;
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
        }

        /* Fix para iOS - Ocultar header en pantalla completa */
        @supports (-webkit-touch-callout: none) {
            /* Solo para dispositivos iOS */
            video::-webkit-media-controls-fullscreen-button {
                display: block !important;
            }
            
            /* Ocultar header cuando el video está en pantalla completa */
            video:fullscreen ~ .header,
            video:fullscreen ~ .navbar-overlay,
            video:-webkit-full-screen ~ .header,
            video:-webkit-full-screen ~ .navbar-overlay,
            video:-moz-full-screen ~ .header,
            video:-moz-full-screen ~ .navbar-overlay {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }
            
            /* También ocultar cuando el body está en pantalla completa */
            :fullscreen .header,
            :fullscreen .navbar-overlay,
            :-webkit-full-screen .header,
            :-webkit-full-screen .navbar-overlay,
            :-moz-full-screen .header,
            :-moz-full-screen .navbar-overlay {
                display: none !important;
                opacity: 0 !important;
                visibility: hidden !important;
            }
        }
            /* Menú lateral móvil mejorado */
            @media (max-width: 600px) {
            .mobile-menu {
                display: none;
                position: fixed !important;
                top: 0;
                right: 0;
                width: 80vw;
                max-width: 320px;
                height: 100vh;
                background: #181818;
                z-index: 10000;
                padding: 32px 0 0 0;
                box-shadow: -2px 0 16px #000a;
                transition: transform 0.3s cubic-bezier(.4,2,.6,1), opacity 0.2s;
                transform: translateX(100%);
                opacity: 0;
            }
            .mobile-menu.active {
                display: block;
                transform: translateX(0);
                opacity: 1;
            }
            #mobileMenuOverlay {
                display: none;
                position: fixed;
                top: 0; left: 0;
                width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.65);
                z-index: 9999;
                transition: opacity 0.2s;
            }
            #mobileMenuOverlay.active {
                display: block;
                opacity: 1;
            }
            .mobile-menu ul {
                list-style: none;
                padding: 0 0 0 0;
                margin: 0;
            }
            .mobile-menu li {
                border-bottom: 1px solid #232027;
            }
            .mobile-menu a {
                display: block;
                color: #fff;
                text-decoration: none;
                font-size: 1.13rem;
                padding: 18px 28px;
                font-weight: 600;
                letter-spacing: 1px;
                transition: background 0.2s, color 0.2s;
            }
            .mobile-menu a:hover,
            .mobile-menu a:focus {
                background: #232027;
                color: #e50914;
            }
            .mobile-menu .close-menu {
                position: absolute;
                top: 18px;
                right: 18px;
                font-size: 2rem;
                color: #fff;
                background: none;
                border: none;
                z-index: 10100;
            }
            }

        /* Mejoras para el buscador en móvil */
        @media (max-width: 600px) {
            .modal-buscador {
                padding: 18px 6px 12px 6px;
                max-width: 98vw;
            }
            .modal-buscador-inputbox input {
                font-size: 1rem;
                padding: 10px 8px;
            }
            .modal-buscador-inputbox button {
                font-size: 1rem;
                padding: 8px 14px;
            }
            .modal-buscador-card {
                width: 90px;
            }
            .modal-buscador-card img {
                height: 110px;
            }
        }
        @media (max-width: 600px) {
        /* Centrar poster, título e info */
        .card--details .row {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
        }
        .card__cover {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }
        .card__content {
            align-items: center !important;
            text-align: center !important;
            display: flex;
            flex-direction: column;
        }
        .details__title {
            text-align: center !important;
            width: 100%;
            margin-top: 12px;
        }
        /* Ocultar meta info (Duración, Director, Reparto) */
        .card__meta {
            display: none !important;
        }
        }
        @media (min-width: 601px) {
            .header__logo img {
                width: 240px !important;
                height: 80px !important;
                max-width: none !important;
                object-fit: contain !important;
            }
        }
        @media (max-width: 600px) {
            .header__logo img {
                width: 120px !important;
                height: 40px !important;
                max-width: 100% !important;
                object-fit: contain !important;
            }
        }
    </style>
</head>
<body>
<!-- HEADER estilo filme.php -->
<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="index.php">
                            <img src="img/logo.png" alt="">
                        </a>
                        <ul class="header__nav d-flex align-items-center mb-0">
                            <li class="header__nav-item"><a href="./painel.php" class="header__nav-link">Inicio</a></li>
                            <li class="header__nav-item"><a href="./canais.php" class="header__nav-link">TV en Vivo</a></li>
                            <li class="header__nav-item"><a href="filmes.php" class="header__nav-link">Películas</a></li>
                            <li class="header__nav-item"><a href="series.php" class="header__nav-link">Series</a></li>
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

<!-- Menú lateral móvil -->
<nav class="mobile-menu" id="mobileMenu">
  <button class="close-menu" id="closeMobileMenu" aria-label="Cerrar menú">&times;</button>
  <ul>
    <li><a href="./painel.php">INICIO</a></li>
    <li><a href="./canais.php">TV EN VIVO</a></li>
    <li><a href="filmes.php">PELÍCULAS</a></li>
    <li><a href="series.php">SERIES</a></li>
    <li><a href="profile.php">PERFIL</a></li>
  </ul>
</nav>
<div id="mobileMenuOverlay"></div>

<!-- MODAL BUSCADOR igual a filme.php -->
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
    <div class="container" style="margin-top: 80px;">
        <div class="row">
            <div class="col-12 col-xl-12">
                <div class="card card--details">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-3">
                            <div class="card__cover">
                            <img src="<?php echo $poster_img; ?>" alt="">
                                </div>
                        </div>
                        <div class="col-12 col-sm-9 col-md-9 col-lg-9 col-xl-9">
                            <div class="card__content">
                            <h1 class="details__title">
                                <?php
                                    // Quitar el año del nombre de la serie si viene entre paréntesis
                                    $serie_nome_limpio = preg_replace('/\s*\(\d{4}\)$/', '', $serie_nome);

                                    // Limpiar el nombre del episodio, dejando solo el texto después del último guion
                                    $ep_title = $ep_data['title'] ?? '';
                                    $ep_title_limpio = trim(preg_replace('/^.*-\s*/', '', $ep_title));

                                    // Temporada y episodio
                                    $season = $ep_data['season'] ?? '';
                                    $ep_num = $ep_data['episode_num'] ?? '';

                                    // Mostrar título en formato deseado
                                    echo htmlspecialchars($serie_nome_limpio) . " | Temporada " . intval($season) . " Episodio " . intval($ep_num) . " - " . htmlspecialchars($ep_title_limpio);
                                ?>
                            </h1>
                                <span class="card__rate">
                                    <?php
                                    echo ($ano ? substr($ano, 0, 4) : '');
                                    if ($nota !== '') {
                                        echo ' &nbsp; <i class="fa-solid fa-star"></i> ' . $nota;
                                    }
                                    ?>
                                </span>
                                <ul class="card__meta">
                                    <li><span><strong>Duración:</strong></span> <?php echo $ep_dur; ?></li>
                                    <li><span><strong>Director:</strong></span> <?php echo $diretor; ?></li>
                                    <li><span><strong>Reparto:</strong></span> <?php echo $cast; ?></li>
                                </ul>
                                <div class="card__description card__description--details">
                                    <?php echo $ep_plot ?: $sinopsis; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($ep_ext === 'mp4'): ?>
                        <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
                        <div class="video-player-container" style="margin:32px auto 0 auto; max-width:1100px; width:100%; aspect-ratio:16/9; background:#000; border-radius:12px; overflow:hidden;">
                                <video id="player" playsinline webkit-playsinline controls poster="<?php echo htmlspecialchars($ep_poster); ?>" style="width:100%;height:100%;display:block;object-fit:contain;background:#000;" x-webkit-airplay="allow">
                                    <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4" />
                                </video>
                        </div>
                        <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const player = new Plyr('#player', {
                                    ratio: '16:9',
                                    controls: [
                                        'play-large', 'rewind', 'play', 'fast-forward', 'progress',
                                        'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'
                                    ]
                                });
                                window.player = player;
                            });
                        </script>
                    <?php else: ?>
                        <div class="video-player-container" style="margin:32px auto 0 auto; max-width:1100px; width:100%; aspect-ratio:16/9; background:#000; border-radius:12px; overflow:hidden;">
                        <video id="player" playsinline webkit-playsinline controls poster="<?php echo htmlspecialchars($ep_poster); ?>" style="width:100%;height:100%;display:block;object-fit:contain;background:#000;" x-webkit-airplay="allow">
                            <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/<?php echo htmlspecialchars($ep_ext); ?>" />
                            Tu navegador no soporta la reproducción de este formato.
                        </video>
                        </div>
                    <?php endif; ?>
                    <?php
// Buscar episodios anterior y siguiente
$prev_ep_id = null;
$next_ep_id = null;
$found = false;
foreach ($episodios as $temporada) {
    foreach ($temporada as $ep) {
        if ($found && !$next_ep_id) {
            $next_ep_id = $ep['id'];
            break 2;
        }
        if ($ep['id'] == $ep_id) {
            $found = true;
        }
        if (!$found) {
            $prev_ep_id = $ep['id'];
        }
    }
}
$serie_url = "serie.php?stream=" . urlencode($serie_id) . "&streamtipo=serie";
$prev_url = $prev_ep_id ? "serie_play.php?serie_id=" . urlencode($serie_id) . "&ep_id=" . urlencode($prev_ep_id) : "#";
$next_url = $next_ep_id ? "serie_play.php?serie_id=" . urlencode($serie_id) . "&ep_id=" . urlencode($next_ep_id) : "#";
?>

<div class="player-nav-btns" style="display:flex;justify-content:center;gap:32px;margin:32px 0;">
    <a href="<?php echo $prev_url; ?>" <?php if (!$prev_ep_id) echo 'style="pointer-events:none;opacity:0.5;"'; ?>>
        <div class="nav-btn">
            <svg width="40" height="40" viewBox="0 0 40 40"><circle cx="20" cy="20" r="18" fill="#f5f5f5"/><polyline points="24,12 16,20 24,28" fill="none" stroke="#333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
    </a>
    <a href="<?php echo $serie_url; ?>">
        <div class="nav-btn">
            <svg width="40" height="40" viewBox="0 0 40 40"><circle cx="20" cy="20" r="18" fill="#f5f5f5"/><line x1="14" y1="16" x2="26" y2="16" stroke="#333" stroke-width="3" stroke-linecap="round"/><line x1="14" y1="20" x2="26" y2="20" stroke="#333" stroke-width="3" stroke-linecap="round"/><line x1="14" y1="24" x2="26" y2="24" stroke="#333" stroke-width="3" stroke-linecap="round"/></svg>
        </div>
    </a>
    <a href="<?php echo $next_url; ?>" <?php if (!$next_ep_id) echo 'style="pointer-events:none;opacity:0.5;"'; ?>>
        <div class="nav-btn">
            <svg width="40" height="40" viewBox="0 0 40 40"><circle cx="20" cy="20" r="18" fill="#f5f5f5"/><polyline points="16,12 24,20 16,28" fill="none" stroke="#333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
    </a>
</div>

<div class="serie-episodios-list" style="margin: 0 auto 48px auto; max-width: 900px;">
    <?php
    // Solo mostrar episodios de la temporada actual
    $temporada_actual = $ep_data['season'] ?? '';
    if (isset($episodios[$temporada_actual])) {
        $eps = $episodios[$temporada_actual];
        foreach ($eps as $ep) {
            $is_active = ($ep['id'] == $ep_id);
            $ep_img = $ep['info']['movie_image'] ?? $poster_img;
            $ep_title = $ep['title'] ?? '';
            $ep_title_limpio = trim(preg_replace('/^.*-\s*/', '', $ep_title));
            $ep_num = $ep['episode_num'] ?? '';
            $ep_dur = $ep['info']['duration'] ?? '';
            $ep_plot = $ep['info']['plot'] ?? '';
            $ep_date = $ep['info']['release_date'] ?? '';
            // Formatear fecha en español
            $fecha = '';
            if ($ep_date) {
                setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
                $timestamp = strtotime($ep_date);
                $fecha = strftime('%e de %B de %Y', $timestamp);
                // Si strftime falla, usar date manual
                if (!$fecha || $fecha == '') {
                    $meses = [
                        '01'=>'enero','02'=>'febrero','03'=>'marzo','04'=>'abril','05'=>'mayo','06'=>'junio',
                        '07'=>'julio','08'=>'agosto','09'=>'septiembre','10'=>'octubre','11'=>'noviembre','12'=>'diciembre'
                    ];
                    $y = date('Y', $timestamp);
                    $m = $meses[date('m', $timestamp)];
                    $d = date('j', $timestamp);
                    $fecha = "$d de $m de $y";
                }
            }
            // Enlace al episodio
            $ep_url = "serie_play.php?serie_id=" . urlencode($serie_id) . "&ep_id=" . urlencode($ep['id']);
            if (!empty($ep_img)) {
                $ep_url .= "&ep_img=" . urlencode($ep_img);
            }
            ?>
            <a href="<?php echo $ep_url; ?>" class="episodio-card<?php if($is_active) echo ' active'; ?>">
                <div>
                    <img src="<?php echo htmlspecialchars($ep_img); ?>" alt="">
                </div>
                <div class="episodio-info">
                    <div class="episodio-title">
                        <span class="episodio-num">E<?php echo intval($ep_num); ?></span>
                        <?php echo htmlspecialchars($ep_title_limpio); ?>
                    </div>
                    <div class="episodio-meta">
                        <?php if($fecha): ?><span><i class="fa-regular fa-calendar"></i> <?php echo $fecha; ?></span><?php endif; ?>
                        <?php if($ep_dur): ?><span><i class="fa-regular fa-clock"></i> <?php echo $ep_dur; ?></span><?php endif; ?>
                    </div>
                    <div class="episodio-plot">
                        <?php echo htmlspecialchars($ep_plot); ?>
                    </div>
                </div>
            </a>
            <?php
        }
    }
    ?>
</div>
<style>
.serie-episodios-list {
    margin: 0 auto 48px auto;
    max-width: 1000px;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 28px;
}
.serie-episodios-list .episodio-card {
    display: flex;
    gap: 28px;
    background: rgba(30, 30, 36, 0.96);
    border-radius: 18px;
    box-shadow: 0 6px 32px #0007;
    align-items: stretch;
    min-height: 170px;
    transition: box-shadow 0.2s, transform 0.2s, background 0.2s;
    border: none;
    padding: 0;
    overflow: hidden;
    position: relative;
    text-decoration: none;
}
.serie-episodios-list .episodio-card.active {
    border-left: 6px solid #e50914;
    background: linear-gradient(90deg, #232027 80%, #e50914 120%);
    box-shadow: 0 10px 40px #e5091422;
    opacity: 1;
    pointer-events: none;
    transform: scale(1.02);
}
.serie-episodios-list .episodio-card:hover:not(.active) {
    box-shadow: 0 12px 40px #e5091440;
    background: linear-gradient(90deg, #232027 80%, #e50914 120%);
    transform: translateY(-2px) scale(1.015);
}
.serie-episodios-list .episodio-card > div:first-child {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    background: #181818;
}
.serie-episodios-list .episodio-card > div:first-child img {
    width: 180px !important;
    height: 110px !important;
    object-fit: cover;
    border-radius: 12px 0 0 12px;
    box-shadow: 0 2px 12px #0006;
    transition: filter 0.2s;
}
.serie-episodios-list .episodio-card.active > div:first-child img,
.serie-episodios-list .episodio-card:hover > div:first-child img {
    filter: brightness(1.08) saturate(1.1);
}
.serie-episodios-list .episodio-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 18px 24px 18px 0;
}
.serie-episodios-list .episodio-title {
    font-weight: 800;
    font-size: 1.22rem;
    color: #fff;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap; /* Permite que el texto pase abajo si no cabe */
    white-space: normal; /* Permite salto de línea */
}
.serie-episodios-list .episodio-num {
    background: #e50914;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 700;
    border-radius: 6px;
    padding: 2px 10px;
    margin-right: 8px;
    letter-spacing: 1px;
    flex-shrink: 0;
}

.serie-episodios-list .episodio-meta {
    color: #aaa;
    font-size: 1.01rem;
    margin-bottom: 7px;
    display: flex;
    gap: 18px;
    align-items: center;
}
.serie-episodios-list .episodio-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}
.serie-episodios-list .episodio-plot {
    display: -webkit-box;
    -webkit-line-clamp: 3; /* Máximo 2 líneas */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal;
    color: #e0e0e0;
    font-size: 1.04rem;
    line-height: 1.35;
    margin-top: 4px;
}
@media (max-width: 900px) {
    .serie-episodios-list {
        max-width: 100vw;
        padding: 0 4px;
        gap: 14px;
    }
    .serie-episodios-list .episodio-card {
        gap: 10px;
        min-height: 70px;
        border-radius: 10px;
    }
    .serie-episodios-list .episodio-card > div:first-child img {
        width: 90px !important;
        height: 55px !important;
        border-radius: 10px 0 0 10px;
    }
    .serie-episodios-list .episodio-info {
        padding: 10px 10px 10px 0;
    }
    .serie-episodios-list .episodio-title,
    .serie-episodios-list .episodio-meta,
    .serie-episodios-list .episodio-plot {
        font-size: 0.98rem;
    }
}
</style>

<style>
.player-nav-btns .nav-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    box-shadow: 0 4px 16px #0002;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    transition: box-shadow 0.2s, transform 0.2s;
}
.player-nav-btns .nav-btn:hover {
    box-shadow: 0 8px 24px #0003;
    transform: translateY(-3px) scale(1.07);
    background: #f0f0f0;
}
.player-nav-btns a[style*="pointer-events:none"] .nav-btn {
    cursor: not-allowed;
}
</style>
                </div>
            </div>
        </div>
    </div>
</section>

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

<script>
// MODAL BUSCADOR
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
openSearchModal.onclick = showModalBuscador;
closeSearchModal.onclick = hideModalBuscador;
window.addEventListener('keydown', function(e) {
    if (e.key === "Escape") hideModalBuscador();
});
modalBuscador.addEventListener('click', function(e) {
    if (e.target === modalBuscador) hideModalBuscador();
});

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
        "id" => $p['stream_id'],
        "nombre" => $p['name'],
        "img" => $p['stream_icon'],
        "tipo" => "pelicula"
    ];
},$peliculas)).";\n";
// Series
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
$resposta = apixtream($url);
$series = json_decode($resposta,true);
echo "series = ".json_encode(array_map(function($s){
    return [
        "id" => $s['series_id'],
        "nombre" => $s['name'],
        "img" => $s['cover'],
        "tipo" => "serie"
    ];
},$series)).";\n";
// Canales
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
$resposta = apixtream($url);
$canales = json_decode($resposta,true);
echo "canales = ".json_encode(array_map(function($c){
    return [
        "id" => $c['stream_id'],
        "nombre" => $c['name'],
        "img" => $c['stream_icon'],
        "tipo" => "canal"
    ];
},$canales)).";\n";
?>

function renderBuscadorResults(query) {
    query = query.trim().toLowerCase();
    let html = '';
    // Películas
    let pelis = peliculas.filter(p => p.nombre && p.nombre.toLowerCase().includes(query));
    if (pelis.length > 0) {
        html += '<div class="modal-buscador-section"><h3>Películas</h3><div class="modal-buscador-grid">';
        pelis.slice(0,8).forEach(p => {
            html += `<a class="modal-buscador-card" href="filme.php?stream=${p.id}&streamtipo=movie">
                        <img src="${p.img}" alt="">
                        <span>${p.nombre}</span>
                    </a>`;
        });
        html += '</div></div>';
    }
    // Series
    let sers = series.filter(s => s.nombre && s.nombre.toLowerCase().includes(query));
    if (sers.length > 0) {
        html += '<div class="modal-buscador-section"><h3>Series</h3><div class="modal-buscador-grid">';
        sers.slice(0,8).forEach(s => {
            html += `<a class="modal-buscador-card" href="serie.php?stream=${s.id}">
                        <img src="${s.img}" alt="">
                        <span>${s.nombre}</span>
                    </a>`;
        });
        html += '</div></div>';
    }
    // Canales
    let chans = canales.filter(c => c.nombre && c.nombre.toLowerCase().includes(query));
    if (chans.length > 0) {
        html += '<div class="modal-buscador-section"><h3>Canales en Vivo</h3><div class="modal-buscador-grid">';
        chans.slice(0,8).forEach(c => {
            html += `<a class="modal-buscador-card" href="canal.php?stream=${c.id}">
                        <img src="${c.img}" alt="">
                        <span>${c.nombre}</span>
                    </a>`;
        });
        html += '</div></div>';
    }
    if (!html && query.length > 0) {
        html = '<div style="color:#fff;font-size:1.1rem;padding:20px;">No se encontraron resultados.</div>';
    }
    modalBuscadorResults.innerHTML = html;
}

// Buscar al escribir
modalBuscadorInput.addEventListener('input', function() {
    let q = this.value;
    if (q.length > 1) renderBuscadorResults(q);
    else modalBuscadorResults.innerHTML = '';
});
// Buscar al hacer clic en botón
modalBuscadorBtn.addEventListener('click', function() {
    let q = modalBuscadorInput.value;
    if (q.length > 1) renderBuscadorResults(q);
});
// Enter en input
modalBuscadorInput.addEventListener('keydown', function(e){
    if(e.key === "Enter") {
        let q = modalBuscadorInput.value;
        if (q.length > 1) renderBuscadorResults(q);
        e.preventDefault();
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- GUARDAR EN HISTORIAL ---
    fetch('db/base.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=hist_add&id=<?php echo $serie_id; ?>&nombre=<?php echo urlencode(preg_replace('/\s*\(\d{4}\)$/', '', $serie_nome)); ?>&img=<?php echo urlencode($poster_img); ?>&ano=<?php echo urlencode($ano); ?>&rate=<?php echo urlencode($nota); ?>&tipo=serie`
    });

    // --- REANUDAR REPRODUCCIÓN ---
    const episodeKey = "serie_time_<?php echo $ep_id; ?>";
    // Formato: Fallout - T01E02 - El objetivo
    const serieNomeLimpio = "<?php echo addslashes(preg_replace('/\s*\(\d{4}\)$/', '', $serie_nome)); ?>";
    const epNum = "<?php echo str_pad(intval($ep_data['season'] ?? 1),2,'0',STR_PAD_LEFT); ?>";
    const epEpi = "<?php echo str_pad(intval($ep_data['episode_num'] ?? 1),2,'0',STR_PAD_LEFT); ?>";
    const epTitleLimpio = "<?php echo addslashes(trim(preg_replace('/^.*-\s*/', '', $ep_data['title'] ?? ''))); ?>";
    const episodeTitle = `${serieNomeLimpio} - T${epNum}E${epEpi} - ${epTitleLimpio}`;

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
            <span style="font-size:1.15rem;">¿Deseas continuar viendo <b>${episodeTitle}</b> desde el minuto <b>${min}:${sec}</b>?</span>
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

    // Plyr o HTML5
    let video = document.getElementById('player');
    if (window.player && typeof window.player.on === "function") {
        // Plyr
        window.player.on('timeupdate', function() {
            localStorage.setItem(episodeKey, Math.floor(window.player.currentTime));
        });
        window.player.on('ended', function() {
            localStorage.removeItem(episodeKey);
        });
        const lastTime = parseInt(localStorage.getItem(episodeKey) || "0");
        if (lastTime > 10) {
            window.player.pause();
            showResumeNotification(lastTime, function() {
                window.player.currentTime = lastTime;
                window.player.play();
            });
        }
    } else if (video) {
        // HTML5
        video.addEventListener('timeupdate', function() {
            localStorage.setItem(episodeKey, Math.floor(video.currentTime));
        });
        video.addEventListener('ended', function() {
            localStorage.removeItem(episodeKey);
        });
        const lastTime = parseInt(localStorage.getItem(episodeKey) || "0");
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
// Fix para iOS - Manejo de pantalla completa
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.header');
    const navbarOverlay = document.querySelector('.navbar-overlay');
    
    // Detectar si es iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    
    if (isIOS) {
        // Función para ocultar/mostrar header
        function toggleHeaderVisibility(isFullscreen) {
            if (isFullscreen) {
                if (header) header.style.display = 'none';
                if (navbarOverlay) navbarOverlay.style.display = 'none';
            } else {
                if (header) header.style.display = 'block';
                if (navbarOverlay) navbarOverlay.style.display = 'block';
            }
        }
        
        // Escuchar eventos de pantalla completa para video nativo
        document.addEventListener('webkitfullscreenchange', function() {
            toggleHeaderVisibility(!!document.webkitFullscreenElement);
        });
        
        document.addEventListener('fullscreenchange', function() {
            toggleHeaderVisibility(!!document.fullscreenElement);
        });
        
        // Para Plyr player
        if (window.player && typeof window.player.on === "function") {
            window.player.on('enterfullscreen', function() {
                toggleHeaderVisibility(true);
            });
            
            window.player.on('exitfullscreen', function() {
                toggleHeaderVisibility(false);
            });
        }
        
        // Para video HTML5 nativo
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('webkitbeginfullscreen', function() {
                toggleHeaderVisibility(true);
            });
            
            video.addEventListener('webkitendfullscreen', function() {
                toggleHeaderVisibility(false);
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const btnMenu = document.querySelector('.header__btn');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
  const closeMenuBtn = document.getElementById('closeMobileMenu');

  function openMenu() {
    mobileMenu.classList.add('active');
    mobileMenuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu() {
    mobileMenu.classList.remove('active');
    mobileMenuOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if(btnMenu && mobileMenu && mobileMenuOverlay) {
    btnMenu.addEventListener('click', openMenu);
    mobileMenuOverlay.addEventListener('click', closeMenu);
    if(closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
  }
});
</script>

</body>
</html>
