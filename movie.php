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
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($filme); ?></title>
    <script>
window.movieId = <?php echo json_encode($id); ?>;
window.movieYoutubeId = <?php echo json_encode($youtube_id); ?>;
window.movieKey = "movie_time_<?php echo $id; ?>";
window.movieTitle = <?php echo json_encode($filme); ?>;
window.movieName = <?php echo json_encode($filme); ?>;
window.movieImg = <?php echo json_encode($poster_tmdb ?: $poster_img); ?>;
window.movieYear = <?php echo json_encode($ano); ?>;
window.movieRating = <?php echo json_encode($nota); ?>;
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
                        <?php echo $plot; ?>
    </div>
                    <div style="display: flex; gap: 16px; margin-top: 20px;">
                        <?php if (!empty($youtube_id)): ?>
                            <button id="btnTrailer" style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#ff0000 60%,#c80000 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                <i class="fab fa-youtube" style="font-size:1.5rem;"></i>
                                <span>Tráiler</span>
        </button>
                                        <?php endif; ?>

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
                                        'play', 'rewind', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'
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
                <?php
                $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams&category_id=$idcategoria";
                $resposta = apixtream($url);
                $output = json_decode($resposta, true);
                
                if (!is_array($output)) {
                    $output = [];
                }
                
                shuffle($output);
                $i = 1;
                $recomendadas_indices = array_rand($output, min(6, count($output)));
                if (!is_array($recomendadas_indices)) {
                    $recomendadas_indices = [$recomendadas_indices];
                }
                
                foreach($recomendadas_indices as $index) {
                    $row = $output[$index];
                    $filme_id = $row['stream_id'];
                    
                    if ($filme_id == $id) {
                        continue;
                    }
                    
                    $filme_nome = $row['name'];
                    $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome);
                    $filme_type = $row['stream_type'];
                    $filme_img = $row['stream_icon'];
                    $filme_rat = isset($row['rating']) ? $row['rating'] : '';
                    $filme_ano = isset($row['year']) ? $row['year'] : '';
                ?>
                <div class="col-4 col-sm-4 col-lg-2">
    <div class="card">
        <div class="card__cover">
                            <img loading="lazy" src="<?php echo $filme_img; ?>" alt="">
                            <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                                <i class="fas fa-play"></i>
            </a>
        </div>
        <div class="card__content">
                            <h3 class="card__title">
                                <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
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
<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap.bundle.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js"></script>
<script src="./scripts/vendors/jquery.mousewheel.min.js"></script>
<script src="./scripts/vendors/jquery.mcustomscrollbar.min.js"></script>
<script src="./scripts/vendors/wnumb.js"></script>
<script src="./scripts/vendors/nouislider.min.js"></script>
<script src="./scripts/vendors/jquery.morelines.min.js"></script>
<script src="./scripts/vendors/photoswipe.min.js"></script>
<script src="./scripts/vendors/photoswipe-ui-default.min.js"></script>
<script src="./scripts/vendors/glightbox.min.js"></script>
<script src="./scripts/vendors/jBox.all.min.js"></script>
<script src="./scripts/vendors/select2.min.js"></script>
<script src="./scripts/core/main.js"></script>
<script src="./scripts/movie/trailer.js"></script>
<script src="./scripts/movie/fullscreen.js"></script>
<script src="./scripts/movie/resume.js"></script>
<script src="./scripts/movie/favorites.js"></script>
<script src="./scripts/movie/history.js"></script>

</body>
</html>
