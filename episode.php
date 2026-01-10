<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

session_write_close();

require_once(__DIR__ . '/libs/controllers/EpisodePageController.php');

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$serie_id = trim($_GET['serie_id'] ?? '');
$episode_id = trim($_GET['episode_id'] ?? '');

$pageData = getEpisodePageData($user, $pwd, $serie_id, $episode_id);

if (!$pageData) {
    header("Location: series.php");
    exit;
}

extract($pageData, EXTR_SKIP);

$serie_nome_limpio = preg_replace('/\s*\(\d{4}\)$/', '', $serie_nome);
$ep_title_limpio = trim(preg_replace('/^.*-\s*/', '', $ep_name));
$season = $ep_data['season'] ?? '';
$ep_num = $ep_data['episode_num'] ?? '';
$ep_num_str = $ep_num ? str_pad(intval($ep_num), 2, '0', STR_PAD_LEFT) : '';
$season_str = $season ? str_pad(intval($season), 2, '0', STR_PAD_LEFT) : '';
$episodeTitle = "$serie_nome_limpio - T{$season_str}E{$ep_num_str} - $ep_title_limpio";
$episodeKey = "serie_time_$episode_id";

$serie_url = "serie.php?stream=" . urlencode($serie_id) . "&streamtipo=serie";
$prev_url = $prev_ep_id ? "episode.php?serie_id=" . urlencode($serie_id) . "&episode_id=" . urlencode($prev_ep_id) : "#";
$next_url = $next_ep_id ? "episode.php?serie_id=" . urlencode($serie_id) . "&episode_id=" . urlencode($next_ep_id) : "#";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./styles/vendors/bootstrap.min.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/vendors/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/vendors/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/vendors/nouislider.min.css">
    <link rel="stylesheet" href="./styles/vendors/ionicons.min.css">
    <link rel="stylesheet" href="./styles/vendors/photoswipe.css">
    <link rel="stylesheet" href="./styles/vendors/glightbox.css">
    <link rel="stylesheet" href="./styles/vendors/default-skin.css">
    <link rel="stylesheet" href="./styles/vendors/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/vendors/select2.min.css">
    <link rel="stylesheet" href="./styles/vendors/plyr.css">
    <link rel="stylesheet" href="./styles/core/main.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/movie/layout.css">
    <link rel="stylesheet" href="./styles/movie/modal.css">
    <link rel="stylesheet" href="./styles/movie/trailer.css">
    <link rel="stylesheet" href="./styles/movie/mobile.css">
    <link rel="stylesheet" href="./styles/movie/fullscreen.css">
    <link rel="stylesheet" href="./styles/episode/player.css">
    <link rel="stylesheet" href="./styles/episode/episodes.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($serie_nome); ?> - <?php echo htmlspecialchars($ep_name); ?></title>
    <script>
        window.episodeId = <?php echo json_encode($episode_id); ?>;
        window.episodeKey = "serie_time_<?php echo $episode_id; ?>";
        window.episodeTitle = <?php echo json_encode($episodeTitle); ?>;
        window.episodeTipo = "serie";
        window.episodeName = <?php echo json_encode($episodeTitle); ?>;
        window.episodeImg = <?php echo json_encode($ep_poster); ?>;
        window.episodeBackdrop = <?php echo json_encode($ep_backdrop ?: $wallpaper_img); ?>;
        window.episodeDuration = <?php echo json_encode($ep_dur); ?>;
        window.serieId = <?php echo json_encode($serie_id); ?>;
        window.serieName = <?php echo json_encode($serie_nome_limpio); ?>;
        window.serieImg = <?php echo json_encode($poster_img); ?>;
        window.serieBackdrop = <?php echo json_encode($wallpaper_img); ?>;
        window.serieYoutubeId = <?php echo json_encode($youtube_id); ?>;
        window.serieYear = <?php echo json_encode($ano); ?>;
        window.serieRating = <?php echo json_encode($nota); ?>;
        window.episodeHistoryData = {
            serieId: <?php echo json_encode($serie_id); ?>,
            serieName: <?php echo json_encode($serie_nome_limpio); ?>,
            posterImg: <?php echo json_encode($poster_img); ?>,
            backdrop: <?php echo json_encode($ep_backdrop ?: $wallpaper_img); ?>,
            ano: <?php echo json_encode($ano); ?>,
            rate: <?php echo json_encode($nota); ?>
        };
        
    </script>
    <style>
body {
    background: linear-gradient(180deg,rgba(24,24,24,0.80) 0%,rgba(24,24,24,0.80) 100%), url('<?php echo htmlspecialchars($ep_backdrop ?: $wallpaper_img ?: $poster_img); ?>') center center/cover no-repeat;
    color: #fff;
    background-attachment: fixed;
}
    </style>
</head>
<body class="body">
 
<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="login.php">
                            <img src="assets/logo/logo.png" alt="">
                        </a>
                        <ul class="header__nav d-flex align-items-center mb-0">
                            <li class="header__nav-item">
                                <a href="./home.php" class="header__nav-link">Inicio</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="./channels.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="movies.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link header__nav-link--active">Series</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="sagas.php" class="header__nav-link">Sagas</a>
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
<?php include_once __DIR__ . '/libs/views/search.php'; ?>
<section class="section details">
    <div class="container top-margin">
        <div class="row">
            <div class="col-12 col-xl-12">
                <div class="card card--details">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-3">
                            <div class="card__cover">
                                <img src="<?php echo htmlspecialchars($poster_img); ?>" alt="">
                            </div>
                        </div>
                        <div class="col-12 col-sm-9 col-md-9 col-lg-9 col-xl-9">
                            <div class="card__content">
                                <h1 class="details__title">
                                    <?php echo htmlspecialchars($serie_nome_limpio) . " | Temporada " . intval($season) . " Episodio " . intval($ep_num) . " - " . htmlspecialchars($ep_title_limpio); ?>
                                </h1>
                                <div class="card__wrap">
                                    <span class="card__rate">
                                        <?php
                                        echo ($ano ? substr($ano, 0, 4) : '');
                                        if ($nota !== '') {
                                            echo ' &nbsp; <i class="fa-solid fa-star"></i> ' . $nota;
                                        }
                                        ?>
                                    </span>
                                </div>
                                <ul class="card__meta">
                                    <li><span><strong>Duración:</strong></span> <?php echo htmlspecialchars($ep_dur); ?></li>
                                    <li><span><strong>Director:</strong></span> <?php echo htmlspecialchars($diretor); ?></li>
                                    <li><span><strong>Reparto:</strong></span> <?php echo htmlspecialchars($cast); ?></li>
                                </ul>
                                <div class="card__description card__description--details">
                                    <?php echo htmlspecialchars($ep_plot ?: $sinopsis); ?>
                                </div>
                                <div style="display: flex; gap: 12px; margin-top: 20px;">
                                    <?php if (!empty($youtube_id)): ?>
                                        <button class="btn d-flex align-items-center" id="btnTrailer"
                                            style="font-weight:600;font-size:1.1rem;height:44px;background:linear-gradient(90deg,#ff0000 60%,#c80000 100%);color:#fff;border:none;border-radius:8px;padding:4px 22px;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                            <i class="fab fa-youtube" style="font-size:1.4rem;margin-right:8px;"></i> Ver Tráiler
                                        </button>
                                    <?php endif; ?>
                                    <button id="btnFavorito"
                                        class="btn d-flex align-items-center"
                                        style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#232027 60%,#444 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                        <i class="fa fa-star" style="font-size:1.4rem;color:#fff;"></i>
                                        <span id="favText" style="color:#fff;">Agregar a Favoritos</span>
                                    </button>
                                    <div style="position: relative; display: inline-block;">
                                        <button id="btnPlaylist"
                                            style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#232027 60%,#444 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                            <i class="fa fa-bookmark" style="font-size:1.4rem;color:#fff;"></i>
                                            <span style="color:#fff;">Guardar</span>
                                        </button>
                                        <div id="playlistTooltip" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; z-index: 10000;"></div>
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
            <div class="row">
                        <div class="col-12">
                        <?php
                            if ($ep_ext === 'mp4'): ?>
                                <video
                                    id="plyr-video"
                                    playsinline
                                    webkit-playsinline
                                    controls
                                    width="100%"
                                    height="450"
                                    poster="<?php echo htmlspecialchars($ep_poster); ?>"
                                    x-webkit-airplay="allow"
                            >
                                    <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4" />
                                </video>
                                <script src="./scripts/vendors/plyr.polyfilled.js"></script>
                                <script>
                                const player = new Plyr('#plyr-video', {
                                    controls: [
                                        'play-large', 'play', 'rewind', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'
                                    ],
                                    seekTime: 10,
                                });
                                window.player = player;
                                
                                </script>
                            <?php else: ?>
                                <div style="width:100%;max-width:1100px;margin:auto;">
                                    <video
                                        controls
                                        playsinline
                                        webkit-playsinline
                                        poster="<?php echo htmlspecialchars($ep_poster); ?>"
                                        style="background:#000;display:block;margin:auto;width:100%;max-width:1100px;height:600px;object-fit:contain;"
                                        x-webkit-airplay="allow"
                                    >
                                        <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/<?php echo htmlspecialchars($ep_ext); ?>" />
                                        <source src="<?php echo htmlspecialchars($video_url); ?>" />
                                        Tu navegador no soporta este formato de video.
                                    </video>
                </div>
                            <?php endif; ?>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>

                    <div class="player-nav-btns">
                        <a href="<?php echo $prev_url; ?>" <?php if (!$prev_ep_id) echo 'style="pointer-events:none;opacity:0.5;"'; ?> aria-label="Episodio anterior">
                            <div class="nav-btn">
                                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="20" cy="20" r="18" fill="#f5f5f5"/>
                                    <polyline points="24,12 16,20 24,28" fill="none" stroke="#333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </a>
                        <a href="<?php echo $serie_url; ?>" aria-label="Volver a la serie">
                            <div class="nav-btn">
                                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="20" cy="20" r="18" fill="#f5f5f5"/>
                                    <line x1="14" y1="16" x2="26" y2="16" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                                    <line x1="14" y1="20" x2="26" y2="20" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                                    <line x1="14" y1="24" x2="26" y2="24" stroke="#333" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </a>
                        <a href="<?php echo $next_url; ?>" <?php if (!$next_ep_id) echo 'style="pointer-events:none;opacity:0.5;"'; ?> aria-label="Episodio siguiente">
                            <div class="nav-btn">
                                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="20" cy="20" r="18" fill="#f5f5f5"/>
                                    <polyline points="16,12 24,20 16,28" fill="none" stroke="#333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </a>
                    </div>

                    <div class="serie-episodios-list">
                        <?php
                        $temporada_actual = $ep_data['season'] ?? '';
                        if (isset($episodios[$temporada_actual])) {
                            $eps = $episodios[$temporada_actual];
                            foreach ($eps as $ep) {
                                $is_active = ($ep['id'] == $episode_id);
                                $ep_num = $ep['episode_num'] ?? '';
                                
                                $ep_img = $poster_img;
                                if ($tmdb_id && $temporada_actual && $ep_num) {
                                    $still_filename = "{$tmdb_id}_{$temporada_actual}_{$ep_num}.jpg";
                                    $still_local_path = __DIR__ . "/assets/tmdb_cache/$still_filename";
                                    $still_local_url = "assets/tmdb_cache/$still_filename";
                                    if (file_exists($still_local_path)) {
                                        $ep_img = $still_local_url;
                                    } elseif (!empty($ep['info']['movie_image'])) {
                                        $ep_img = $ep['info']['movie_image'];
                                    }
                                } elseif (!empty($ep['info']['movie_image'])) {
                                    $ep_img = $ep['info']['movie_image'];
                                }
                                
                                $ep_title = $ep['title'] ?? '';
                                $ep_title_limpio = trim(preg_replace('/^.*-\s*/', '', $ep_title));
                                $ep_dur = $ep['info']['duration'] ?? '';
                                $ep_dur_secs = $ep['info']['duration_secs'] ?? '';
                                
                                if (empty($ep_dur) || $ep_dur === '00:00:00' || $ep_dur === '00:00') {
                                    if (!empty($ep_dur_secs) && is_numeric($ep_dur_secs) && intval($ep_dur_secs) > 0) {
                                        $seconds = intval($ep_dur_secs);
                                        $hours = floor($seconds / 3600);
                                        $minutes = floor(($seconds % 3600) / 60);
                                        $secs = $seconds % 60;
                                        if ($hours > 0) {
                                            $ep_dur = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
                                        } else {
                                            $ep_dur = sprintf("%02d:%02d", $minutes, $secs);
                                        }
                                    } elseif ($tmdb_id && $temporada_actual && $ep_num) {
                                        $tmdb_ep_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$temporada_actual/episode/$ep_num?api_key=" . TMDB_API_KEY . "&language=" . (defined('LANGUAGE') ? LANGUAGE : 'es-ES');
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $tmdb_ep_url);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        $tmdb_ep_json = @curl_exec($ch);
                                        curl_close($ch);
                                        $tmdb_ep_data = json_decode($tmdb_ep_json, true);
                                        if (!empty($tmdb_ep_data['runtime']) && is_numeric($tmdb_ep_data['runtime'])) {
                                            $runtime_minutes = intval($tmdb_ep_data['runtime']);
                                            $hours = floor($runtime_minutes / 60);
                                            $minutes = $runtime_minutes % 60;
                                            if ($hours > 0) {
                                                $ep_dur = sprintf("%02d:%02d:%02d", $hours, $minutes, 0);
                                            } else {
                                                $ep_dur = sprintf("%02d:%02d", $minutes, 0);
                                            }
                                        }
                                    }
                                } elseif (!empty($ep_dur) && is_numeric($ep_dur)) {
                                    $seconds = intval($ep_dur);
                                    $hours = floor($seconds / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    $secs = $seconds % 60;
                                    if ($hours > 0) {
                                        $ep_dur = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
                                    } else {
                                        $ep_dur = sprintf("%02d:%02d", $minutes, $secs);
                                    }
                                }
                                $ep_plot = $ep['info']['plot'] ?? '';
                                $ep_date = $ep['info']['release_date'] ?? '';
                                $fecha = '';
                                if ($ep_date) {
                                    $timestamp = strtotime($ep_date);
                                    if ($timestamp) {
                                        $fecha = date('Y-m-d', $timestamp);
                                    } else {
                                        $fecha = $ep_date;
                                    }
                                }
                                $ep_rating = $ep['info']['rating'] ?? '';
                                $ep_url = "episode.php?serie_id=" . urlencode($serie_id) . "&episode_id=" . urlencode($ep['id']);
                                
                                $ep_progress = isset($episodes_progress[$ep['id']]) ? $episodes_progress[$ep['id']] : null;
                                $ep_percentage = $ep_progress ? $ep_progress['percentage'] : 0;
                                $ep_watched = $ep_progress && $ep_progress['watched'];
                                ?>
                                <a href="<?php echo $ep_url; ?>" class="episodio-card<?php if($is_active) echo ' active'; ?>"<?php if($is_active) echo ' aria-current="page"'; ?>>
                                    <div class="episodio-image-wrapper">
                                        <img src="<?php echo htmlspecialchars($ep_img); ?>" alt="<?php echo htmlspecialchars($ep_title_limpio); ?>" loading="lazy">
                                        <?php if ($ep_watched): ?>
                                            <div class="episodio-watched-badge">
                                                <i class="fa-solid fa-check-circle"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($ep_progress && $ep_percentage > 0 && !$ep_watched): ?>
                                            <div class="episodio-progress-bar">
                                                <div class="episodio-progress-fill" style="width: <?php echo min(100, max(0, $ep_percentage)); ?>%"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="episodio-info">
                                        <div class="episodio-title">
                                            <span class="episodio-num">E<?php echo intval($ep_num); ?></span>
                                            <span><?php echo htmlspecialchars($ep_title_limpio); ?></span>
                                        </div>
                                        <div class="episodio-meta">
                                            <?php if($fecha): ?><span><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($fecha); ?></span><?php endif; ?>
                                            <?php if($ep_dur): ?><span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($ep_dur); ?></span><?php endif; ?>
                                            <?php if($ep_rating): ?><span><i class="fa-solid fa-star"></i> <?php echo htmlspecialchars($ep_rating); ?></span><?php endif; ?>
                                        </div>
                                        <?php if($ep_plot): ?>
                                        <div class="episodio-plot">
                                            <?php echo htmlspecialchars($ep_plot); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php if (!empty($youtube_id)): ?>
<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap-5.3.3.bundle.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js" defer></script>
<script src="./scripts/vendors/jquery.mousewheel.min.js" defer></script>
<script src="./scripts/vendors/jquery.mcustomscrollbar.min.js" defer></script>
<script src="./scripts/vendors/wnumb.js" defer></script>
<script src="./scripts/vendors/nouislider.min.js" defer></script>
<script src="./scripts/vendors/jquery.morelines.min.js" defer></script>
<script src="./scripts/vendors/photoswipe.min.js" defer></script>
<script src="./scripts/vendors/photoswipe-ui-default.min.js" defer></script>
<script src="./scripts/vendors/glightbox.min.js" defer></script>
<script src="./scripts/vendors/jBox.all.min.js" defer></script>
<script src="./scripts/vendors/select2.min.js" defer></script>
<script src="./scripts/core/main.js" defer></script>
<script src="./scripts/search/modal.js"></script>
<script src="./scripts/episode/history.js"></script>
<script src="./scripts/episode/resume.js"></script>
<script src="./scripts/episode/mobile.js"></script>
<?php if (!empty($youtube_id)): ?>
<script src="./scripts/serie/trailer.js"></script>
<?php endif; ?>
<script src="./scripts/serie/favorites.js"></script>
<script src="./scripts/serie/playlist.js"></script>

</body>
</html>
