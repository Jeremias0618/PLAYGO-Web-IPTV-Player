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

$ep_img_from_get = trim($_GET['ep_img'] ?? '');
if ($ep_img_from_get) {
    $ep_poster = $ep_img_from_get;
}

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
        window.episodeHistoryData = {
            serieId: <?php echo json_encode($serie_id); ?>,
            serieName: <?php echo json_encode($serie_nome_limpio); ?>,
            posterImg: <?php echo json_encode($poster_img); ?>,
            backdrop: <?php echo json_encode($ep_backdrop ?: $wallpaper_img); ?>,
            ano: <?php echo json_encode($ano); ?>,
            rate: <?php echo json_encode($nota); ?>
        };
    </script>
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
    <link rel="stylesheet" href="./styles/search/modal.css">
    <link rel="stylesheet" href="./styles/episode/layout.css">
    <link rel="stylesheet" href="./styles/episode/player.css">
    <link rel="stylesheet" href="./styles/episode/episodes.css">
    <link rel="stylesheet" href="./styles/episode/mobile.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <style>
        body {
            color: #fff;
            <?php if (!empty($wallpaper_img)): ?>
            background: linear-gradient(180deg,rgba(24,24,24,0.80) 0%,rgba(24,24,24,0.80) 100%), url('<?php echo htmlspecialchars($wallpaper_img); ?>') center center/cover no-repeat;
            background-attachment: fixed;
            <?php else: ?>
            background: #181818;
            <?php endif; ?>
        }
    </style>
</head>
<body>
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
                            <li class="header__nav-item"><a href="./home.php" class="header__nav-link">Inicio</a></li>
                            <li class="header__nav-item"><a href="./channels.php" class="header__nav-link">TV en Vivo</a></li>
                            <li class="header__nav-item"><a href="movies.php" class="header__nav-link">Películas</a></li>
                            <li class="header__nav-item"><a href="series.php" class="header__nav-link">Series</a></li>
                            <li class="header__nav-item"><a href="sagas.php" class="header__nav-link">Sagas</a></li>
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

<nav class="mobile-menu" id="mobileMenu">
    <button class="close-menu" id="closeMobileMenu" aria-label="Cerrar menú">&times;</button>
    <ul>
        <li><a href="./home.php">INICIO</a></li>
        <li><a href="./channels.php">TV EN VIVO</a></li>
        <li><a href="movies.php">PELÍCULAS</a></li>
        <li><a href="series.php">SERIES</a></li>
        <li><a href="profile.php">PERFIL</a></li>
    </ul>
</nav>
<div id="mobileMenuOverlay"></div>

<?php include_once __DIR__ . '/libs/views/search.php'; ?>

<section class="section details">
    <div class="container" style="margin-top: 80px;">
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
                                <span class="card__rate">
                                    <?php
                                    echo ($ano ? substr($ano, 0, 4) : '');
                                    if ($nota !== '') {
                                        echo ' &nbsp; <i class="fa-solid fa-star"></i> ' . $nota;
                                    }
                                    ?>
                                </span>
                                <ul class="card__meta">
                                    <li><span><strong>Duración:</strong></span> <?php echo htmlspecialchars($ep_dur); ?></li>
                                    <li><span><strong>Director:</strong></span> <?php echo htmlspecialchars($diretor); ?></li>
                                    <li><span><strong>Reparto:</strong></span> <?php echo htmlspecialchars($cast); ?></li>
                                </ul>
                                <div class="card__description card__description--details">
                                    <?php echo htmlspecialchars($ep_plot ?: $sinopsis); ?>
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

                    <div class="serie-episodios-list">
                        <?php
                        $temporada_actual = $ep_data['season'] ?? '';
                        if (isset($episodios[$temporada_actual])) {
                            $eps = $episodios[$temporada_actual];
                            foreach ($eps as $ep) {
                                $is_active = ($ep['id'] == $episode_id);
                                $ep_img = $ep['info']['movie_image'] ?? $poster_img;
                                $ep_title = $ep['title'] ?? '';
                                $ep_title_limpio = trim(preg_replace('/^.*-\s*/', '', $ep_title));
                                $ep_num = $ep['episode_num'] ?? '';
                                $ep_dur = $ep['info']['duration'] ?? '';
                                $ep_plot = $ep['info']['plot'] ?? '';
                                $ep_date = $ep['info']['release_date'] ?? '';
                                $fecha = '';
                                if ($ep_date) {
                                    $timestamp = strtotime($ep_date);
                                    $meses = [
                                        '01'=>'enero','02'=>'febrero','03'=>'marzo','04'=>'abril','05'=>'mayo','06'=>'junio',
                                        '07'=>'julio','08'=>'agosto','09'=>'septiembre','10'=>'octubre','11'=>'noviembre','12'=>'diciembre'
                                    ];
                                    $y = date('Y', $timestamp);
                                    $m = $meses[date('m', $timestamp)];
                                    $d = date('j', $timestamp);
                                    $fecha = "$d de $m de $y";
                                }
                                $ep_url = "episode.php?serie_id=" . urlencode($serie_id) . "&episode_id=" . urlencode($ep['id']);
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
                                            <?php if($ep_dur): ?><span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($ep_dur); ?></span><?php endif; ?>
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
                </div>
            </div>
        </div>
    </div>
</section>

<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap.bundle.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js"></script>
<script src="./scripts/vendors/jquery.mousewheel.min.js"></script>
<script src="./scripts/vendors/jquery.mcustomscrollbar.min.js"></script>
<script src="./scripts/vendors/wnumb.js"></script>
<script src="./scripts/vendors/jquery.morelines.min.js"></script>
<script src="./scripts/vendors/photoswipe.min.js"></script>
<script src="./scripts/vendors/photoswipe-ui-default.min.js"></script>
<script src="./scripts/vendors/glightbox.min.js"></script>
<script src="./scripts/vendors/jBox.all.min.js"></script>
<script src="./scripts/vendors/select2.min.js"></script>
<script src="./scripts/core/main.js"></script>
<script src="./scripts/search/modal.js"></script>
<script src="./scripts/episode/history.js"></script>
<script src="./scripts/episode/resume.js"></script>
<script src="./scripts/episode/mobile.js"></script>

</body>
</html>
