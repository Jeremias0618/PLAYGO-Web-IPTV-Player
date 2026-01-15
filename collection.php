<?php
require_once("libs/lib.php");
require_once("libs/controllers/Collection.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);
$saga_id = isset($_GET['saga']) ? $_GET['saga'] : '';

$collection_data = getCollectionData($saga_id, $user, $pwd);

if (!$collection_data) {
    header("Location: sagas.php");
    exit;
}

$saga_actual = $collection_data['saga'];
$peliculas_pagina = $collection_data['peliculas'];
$backdrop_fondo = $collection_data['backdrop_fondo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/vendors/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/vendors/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/vendors/ionicons.min.css">
    <link rel="stylesheet" href="./styles/vendors/photoswipe.css">
    <link rel="stylesheet" href="./styles/vendors/glightbox.css">
    <link rel="stylesheet" href="./styles/vendors/default-skin.css">
    <link rel="stylesheet" href="./styles/vendors/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/vendors/select2.min.css">
    <link rel="stylesheet" href="./styles/core/main.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/collection/layout.css">
    <link rel="stylesheet" href="./styles/collection/items.css">
    <link rel="stylesheet" href="./styles/collection/buttons.css">
    <link rel="stylesheet" href="./styles/collection/modal.css">
    <link rel="stylesheet" href="./styles/collection/search.css">
    <link rel="stylesheet" href="./styles/collection/pagination.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($saga_actual['nombre']); ?></title>
<?php if($backdrop_fondo): ?>
<style>
body {
    background: url('<?php echo htmlspecialchars($backdrop_fondo, ENT_QUOTES); ?>') no-repeat center center fixed !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    background: rgba(0,0,0,0.75);
    pointer-events: none;
}
</style>
<?php endif; ?>
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
                                <a href="series.php" class="header__nav-link">Series</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="sagas.php" class="header__nav-link header__nav-link--active">Sagas</a>
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
<div id="bg-overlay"></div>
<?php include_once __DIR__ . '/libs/views/search.php'; ?>
<div style="width:100%;text-align:center;margin:120px 0 15px 0;">
    <h2 style="font-size:2.5rem;font-weight:800;letter-spacing:2px;color:#fff;display:inline-block;padding:10px 40px;border-radius:12px;"><?php echo htmlspecialchars($saga_actual['nombre']); ?></h2>
</div>
<section class="section details">
    <div class="container" style="margin-top: 0;">
        <div class="row">
<?php
if ($peliculas_pagina && is_array($peliculas_pagina)) {
    foreach($peliculas_pagina as $index) {
        $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $index['name']);
        $filme_type = $index['stream_type'];
        $filme_id = $index['stream_id'];
        $filme_img = $index['stream_icon'];
        $filme_rat = isset($index['rating']) ? $index['rating'] : '';
        $filme_ano = isset($index['year']) ? $index['year'] : '';
        $filme_duration = isset($index['duration']) ? $index['duration'] : '';
        $filme_country = isset($index['country']) ? $index['country'] : '';
        $filme_cast = isset($index['cast']) ? $index['cast'] : '';
        $filme_plot = isset($index['plot']) ? $index['plot'] : '';
        $filme_genre = isset($index['genre']) ? $index['genre'] : '';
        $youtube_id = isset($index['youtube_id']) ? $index['youtube_id'] : '';
        
        if (empty($youtube_id) && isset($index['stream_id'])) {
            $vod_id_temp = $index['stream_id'];
            $url_info_temp = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id_temp";
            $res_info_temp = apixtream($url_info_temp);
            $data_info_temp = json_decode($res_info_temp, true);
            
            if (!empty($data_info_temp) && isset($data_info_temp['info'])) {
                if (!empty($data_info_temp['info']['youtube_trailer'])) {
                    $trailer_temp = $data_info_temp['info']['youtube_trailer'];
                    if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer_temp)) {
                        $youtube_id = $trailer_temp;
                    } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer_temp, $matches)) {
                        $youtube_id = $matches[1];
                    }
                }
            }
        }
        
        $has_trailer = !empty($youtube_id);
?>
            <div class="col-12">
                <div class="collection-list-item">
                    <div class="row">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                                <img loading="lazy" src="<?php echo htmlspecialchars($filme_img); ?>" alt="<?php echo htmlspecialchars($filme_nome); ?>" class="collection-poster">
                            </a>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <h2 class="collection-info-title">
                                <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" style="color: #fff; text-decoration: none;">
                                    <?php echo htmlspecialchars($filme_nome); ?>
                                </a>
                            </h2>
                            <?php if (!empty($filme_genre)): ?>
                            <ul class="collection-info-genres">
<?php
                                $genres = is_array($filme_genre) ? $filme_genre : explode(',', $filme_genre);
                                foreach($genres as $g): 
                                    $genre_trimmed = trim($g);
                                    if (!empty($genre_trimmed)):
                                ?>
                                    <li><?php echo htmlspecialchars($genre_trimmed); ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                            <?php endif; ?>
                            <div style="margin-bottom: 16px;">
                                <span style="color: #fff; font-size: 1.1rem;">
                                    <?php echo $filme_ano; ?>
                                    <?php if (!empty($filme_rat)): ?>
                                        &nbsp; <i class="fa-solid fa-star" style="color: #e50914;"></i> <?php echo $filme_rat; ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <ul class="collection-info-meta">
                                <?php if (!empty($filme_duration)): 
                                    $duracao_formatted = $filme_duration;
                                    if (is_numeric($filme_duration)) {
                                        $hours = floor($filme_duration / 3600);
                                        $minutes = floor(($filme_duration % 3600) / 60);
                                        $seconds = $filme_duration % 60;
                                        if ($hours > 0) {
                                            $duracao_formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                        } else {
                                            $duracao_formatted = sprintf("%02d:%02d", $minutes, $seconds);
                                        }
                                    }
                                ?>
                                <li><strong>Duración:</strong> <?php echo htmlspecialchars($duracao_formatted); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($filme_country)): ?>
                                <li><strong>País:</strong> <?php echo htmlspecialchars($filme_country); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($filme_cast)): ?>
                                <li><strong>Reparto:</strong> <?php echo htmlspecialchars($filme_cast); ?></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (!empty($filme_plot)): ?>
                            <div class="collection-info-plot">
                                <?php echo htmlspecialchars($filme_plot); ?>
                            </div>
                            <?php endif; ?>
                            <div class="collection-buttons">
                                <button class="collection-btn-trailer" data-youtube-id="<?php echo $has_trailer ? htmlspecialchars($youtube_id) : ''; ?>" data-movie-title="<?php echo htmlspecialchars($filme_nome); ?>" <?php echo $has_trailer ? '' : 'disabled'; ?> style="<?php echo $has_trailer ? 'cursor:pointer;' : 'opacity:0.5;cursor:not-allowed;'; ?>">
                                    <i class="fab fa-youtube" style="font-size:1.5rem;"></i>
                                    <span>Tráiler</span>
                                </button>
                                <button class="collection-btn-fav" data-movie-id="<?php echo $filme_id; ?>" data-movie-name="<?php echo htmlspecialchars($filme_nome); ?>" data-movie-img="<?php echo htmlspecialchars($filme_img); ?>" data-movie-year="<?php echo htmlspecialchars($filme_ano); ?>" data-movie-rating="<?php echo htmlspecialchars($filme_rat); ?>">
                                    <i class="fa fa-star" style="font-size:1.4rem;"></i>
                                    <span class="fav-text-<?php echo $filme_id; ?>">Agregar a Favoritos</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                    <?php
                    }
                    } else {
    echo '<div class="col-12" style="color:#fff;font-size:1.2rem;text-align:center;">No hay películas disponibles en esta saga.</div>';
                    }
                    ?>
            </div>
        </div>
</section>
<div class="modal fade" id="trailerModal" tabindex="-1" role="dialog" aria-labelledby="trailerModalTitle" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trailerModalTitle">Tráiler</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar" tabindex="0"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footer__copyright">
                    &copy; <?php echo date('Y'); ?> <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="assets/logo/logo.png"> PLAYGO
                </div>
            </div>
        </div>
    </div>
</footer>
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
<script src="./scripts/vendors/jwplayer.js"></script>
<script src="./scripts/vendors/jwplayer.core.controls.js"></script>
<script src="./scripts/vendors/provider.hlsjs.js"></script>
<script src="./scripts/core/main.js"></script>
<script src="./scripts/collection/trailer.js"></script>
<script src="./scripts/collection/favorites.js"></script>
<script src="./scripts/collection/init.js"></script>
</body>
</html>
