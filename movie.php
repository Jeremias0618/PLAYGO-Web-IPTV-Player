<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

session_write_close();

require_once(__DIR__ . '/libs/controllers/Movie.php');

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$id = trim($_REQUEST['stream']);
$tipo = trim($_REQUEST['streamtipo']);

$movieData = getMovieData($user, $pwd, $id);

if (!$movieData) {
    header("Location: movies.php");
    exit;
}

$filme = $movieData['name'];
$poster_img = $movieData['poster_img'];
$repro_img = $movieData['repro_img'];
$wallpaper_tmdb = $movieData['wallpaper_tmdb'];
$poster_tmdb = $movieData['poster_tmdb'];
$backdrop = $movieData['backdrop'];
$idcategoria = $movieData['category_id'];
$exts = $movieData['container_extension'];
$youtube_id = $movieData['youtube_id'];
$diretor = $movieData['director'];
$cast = $movieData['cast'];
$plot = $movieData['plot'];
$genero = $movieData['genre'];
$duracao = $movieData['duration'];
$pais = $movieData['country'];
$nota = $movieData['rating'];
$ano = $movieData['year'];

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
    <link rel="stylesheet" href="./styles/movie/resume-notification.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($filme); ?></title>
    <script>
window.movieId = <?php echo json_encode($id); ?>;
window.movieYoutubeId = <?php echo json_encode($youtube_id); ?>;
window.movieKey = "movie_time_<?php echo $id; ?>";
window.movieTipo = <?php echo json_encode($tipo); ?>;
window.movieTitle = <?php echo json_encode($filme); ?>;
window.movieName = <?php echo json_encode($filme); ?>;
window.movieImg = <?php echo json_encode($poster_tmdb ?: $poster_img); ?>;
window.movieBackdrop = <?php echo json_encode($wallpaper_tmdb ?: ($backdrop ?: $poster_img)); ?>;
window.movieYear = <?php echo json_encode($ano); ?>;
window.movieRating = <?php echo json_encode($nota); ?>;
window.movieDuration = <?php echo json_encode($duracao); ?>;
</script>
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
                                <a href="movies.php" class="header__nav-link header__nav-link--active">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link">Series</a>
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
<div class="details__bg" data-bg="<?php echo $wallpaper_tmdb ?: ($backdrop ?: $poster_img); ?>"></div>
    <div class="container top-margin">
        <div class="row">
            <div class="col-12">
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
                        <div class="plot-text-wrapper">
                            <div class="plot-text collapsed" id="plotText"><?php echo htmlspecialchars($plot); ?></div>
                            <button type="button" class="plot-toggle-btn" id="plotToggleBtn" style="display: none;">
                                <span class="plot-toggle-text">Ver más</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div style="display: flex; gap: 16px; margin-top: 20px;">
                        <?php if (!empty($youtube_id)): ?>
                            <button id="btnTrailer" style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#ff0000 60%,#c80000 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                <i class="fab fa-youtube" style="font-size:1.5rem;"></i>
                                <span>Tráiler</span>
        </button>
                                        <?php endif; ?>

                    <button id="btnFavorito"
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
                            $ext = strtolower($exts);
                            if ($ext == 'mp4'): ?>
                                <video
                                    id="plyr-video"
                                    playsinline
                                    webkit-playsinline
                                    controls
                                    width="100%"
                                    height="450"
                                    poster="<?php echo $wallpaper_tmdb ?: $repro_img; ?>"
                                    x-webkit-airplay="allow"
                            >
                                    <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.mp4" type="video/mp4" />
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
                                        poster="<?php echo $wallpaper_tmdb ?: $repro_img; ?>"
                                        style="background:#000;display:block;margin:auto;width:100%;max-width:1100px;height:600px;object-fit:contain;"
                                        x-webkit-airplay="allow"
                                    >
                                        <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.<?php echo $exts; ?>" type="video/<?php echo htmlspecialchars($exts); ?>" />
                                        <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.<?php echo $exts; ?>" />
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
</section>
<?php if (!empty($youtube_id)): ?>
<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<section class="content">
    <div class="container" style="margin-top: 30px;">
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-12">
            <div class="row">
                <div class="col-12">
                        <h2 class="section__title section__title--sidebar">Usuarios también vieron</h2>
                </div>
                <div id="recomendadas-container" class="row">
                    <div class="col-12 text-center" style="color: #fff; padding: 40px;">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
                </div>
                </div>
            </div>
        </div>
</section>
<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap.bundle.min.js" defer></script>
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
<script src="./scripts/movie/trailer.js" defer></script>
<script src="./scripts/movie/fullscreen.js" defer></script>
<script src="./scripts/movie/resume.js"></script>
<script src="./scripts/movie/favorites.js"></script>
<script src="./scripts/movie/history.js"></script>
<script src="./scripts/movie/playlist.js"></script>
<script>
(function() {
    function loadRecomendadas() {
        if (typeof jQuery === 'undefined') {
            setTimeout(loadRecomendadas, 50);
            return;
        }
        
        const loadStart = performance.now();
        jQuery.ajax({
            url: 'libs/endpoints/MovieRecommended.php',
            method: 'POST',
            data: {
                category_id: <?php echo $idcategoria; ?>,
                current_id: <?php echo $id; ?>
            },
            success: function(response) {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                if (data.html) {
                    jQuery('#recomendadas-container').html(data.html);
                } else if (data.error) {
                    jQuery('#recomendadas-container').html('<div class="col-12 text-center" style="color: #ff4444; padding: 40px;">Error: ' + data.error + '</div>');
                }
            },
            error: function(xhr, status, error) {
                jQuery('#recomendadas-container').html('<div class="col-12 text-center" style="color: #ff4444; padding: 40px;">Error al cargar recomendaciones</div>');
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadRecomendadas);
    } else {
        loadRecomendadas();
    }
})();
</script>
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        
        const plotText = document.getElementById('plotText');
        const plotToggleBtn = document.getElementById('plotToggleBtn');
        const plotWrapper = document.querySelector('.plot-text-wrapper');
        
        if (!plotText || !plotToggleBtn || !plotWrapper) {
            return;
        }
        
        function calculateMaxHeight() {
            const posterCover = document.querySelector('.card__cover');
            const cardContent = document.querySelector('.card__content');
            const cardWrap = document.querySelector('.card__wrap');
            const cardMeta = document.querySelector('.card__meta');
            const buttonsContainer = document.querySelector('.card__content > div[style*="display: flex"]');
            
            if (!posterCover || !cardContent) {
                return null;
            }
            
            const posterHeight = posterCover.offsetHeight;
            
            let usedHeight = 0;
            
            if (cardWrap) {
                usedHeight += cardWrap.offsetHeight;
                const cardWrapMargin = window.getComputedStyle(cardWrap).marginBottom;
                usedHeight += parseInt(cardWrapMargin) || 0;
            }
            
            if (cardMeta) {
                usedHeight += cardMeta.offsetHeight;
                const cardMetaMargin = window.getComputedStyle(cardMeta).marginBottom;
                usedHeight += parseInt(cardMetaMargin) || 0;
            }
            
            if (buttonsContainer) {
                usedHeight += buttonsContainer.offsetHeight;
                const buttonsMargin = window.getComputedStyle(buttonsContainer).marginTop;
                usedHeight += parseInt(buttonsMargin) || 0;
            }
            
            const plotWrapperMargin = window.getComputedStyle(plotWrapper).marginTop;
            usedHeight += parseInt(plotWrapperMargin) || 0;
            
            const toggleBtnHeight = 40;
            const padding = 20;
            
            const availableHeight = posterHeight - usedHeight - toggleBtnHeight - padding;
            
            return Math.max(50, availableHeight);
        }
        
        function checkTextHeight() {
            const maxHeight = calculateMaxHeight();
            
            if (maxHeight === null) {
                return;
            }
            
            plotText.style.maxHeight = '';
            plotText.style.overflow = '';
            plotText.classList.remove('collapsed', 'expanded');
            
            const originalHeight = plotText.scrollHeight;
            
            if (originalHeight > maxHeight) {
                plotToggleBtn.style.display = 'flex';
                plotText.style.maxHeight = maxHeight + 'px';
                plotText.style.overflow = 'hidden';
                plotText.classList.add('collapsed');
                plotToggleBtn.classList.remove('expanded');
            } else {
                plotToggleBtn.style.display = 'none';
                plotText.classList.remove('collapsed');
            }
        }
        
        plotToggleBtn.addEventListener('click', function() {
            const toggleText = plotToggleBtn.querySelector('.plot-toggle-text');
            if (plotText.classList.contains('collapsed')) {
                plotText.style.maxHeight = '';
                plotText.style.overflow = '';
                plotText.classList.remove('collapsed');
                plotText.classList.add('expanded');
                plotToggleBtn.classList.add('expanded');
                if (toggleText) toggleText.textContent = 'Ver menos';
            } else {
                const maxHeight = calculateMaxHeight();
                if (maxHeight !== null) {
                    plotText.style.maxHeight = maxHeight + 'px';
                    plotText.style.overflow = 'hidden';
                }
                plotText.classList.remove('expanded');
                plotText.classList.add('collapsed');
                plotToggleBtn.classList.remove('expanded');
                if (toggleText) toggleText.textContent = 'Ver más';
            }
        });
        
        setTimeout(checkTextHeight, 100);
        
        window.addEventListener('resize', function() {
            setTimeout(checkTextHeight, 100);
        });
        
        const posterImg = document.querySelector('.card__cover img');
        if (posterImg) {
            posterImg.addEventListener('load', checkTextHeight);
            if (posterImg.complete) {
                checkTextHeight();
            }
        }
    });
})();
</script>

</body>
</html>
