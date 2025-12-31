<?php
require_once("libs/lib.php");
require_once("libs/services/content.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$movies = getMovies($user, $pwd, 1000);
$series = getSeries($user, $pwd, 1000);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <link rel="stylesheet" href="./styles/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/nouislider.min.css">
    <link rel="stylesheet" href="./styles/ionicons.min.css">
    <link rel="stylesheet" href="./styles/photoswipe.css">
    <link rel="stylesheet" href="./styles/glightbox.css">
    <link rel="stylesheet" href="./styles/default-skin.css">
    <link rel="stylesheet" href="./styles/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/select2.min.css">
    <link rel="stylesheet" href="./styles/listings.css">
    <link rel="stylesheet" href="./styles/main.css">
    <link rel="stylesheet" href="./styles/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/home/home.css">
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
                                <a href="./home.php" class="header__nav-link header__nav-link--active">Inicio</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="./channels.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="filmes.php" class="header__nav-link">Películas</a>
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

<?php include_once __DIR__ . '/partials/search_modal.php'; ?>

    <section class="home">
        <div class="owl-carousel home__bg">
            <?php
            $slider_items = [];
            foreach($movies as $row) {
                $slider_items[] = [
                    'img' => $row['stream_icon']
                ];
            }
            foreach($series as $row) {
                $slider_items[] = [
                    'img' => $row['cover']
                ];
            }
            shuffle($slider_items);
            foreach(array_slice($slider_items,0,6) as $item) {
                ?>
                <div class="item home__cover" data-bg="<?php echo $item['img']; ?>"></div>
            <?php } ?>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="home__title">POPULARES <b>AHORA</b></h1>
                    <button class="home__nav home__nav--prev" type="button">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <button class="home__nav home__nav--next" type="button">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                <div class="col-12">
                    <div class="owl-carousel home__carousel">
                        <?php
                        $carousel_items = [];
                        foreach($movies as $row) {
                            $carousel_items[] = [
                                'type' => 'movie',
                                'id' => $row['stream_id'],
                                'name' => $row['name'],
                                'img' => $row['stream_icon'],
                                'stream_type' => $row['stream_type'],
                                'year' => isset($row['year']) ? $row['year'] : 'N/A',
                                'rating' => isset($row['rating_5based']) ? $row['rating_5based'] : 'N/A'
                            ];
                        }
                        foreach($series as $row) {
                            $carousel_items[] = [
                                'type' => 'serie',
                                'id' => $row['series_id'],
                                'name' => $row['name'],
                                'img' => $row['cover'],
                                'year' => isset($row['year']) ? $row['year'] : (isset($row['releaseDate']) ? substr($row['releaseDate'], 0, 4) : 'N/A'),
                                'rating' => isset($row['rating']) ? $row['rating'] : (isset($row['rating_5based']) ? $row['rating_5based'] : 'N/A')
                            ];
                        }
                        shuffle($carousel_items);
                        foreach(array_slice($carousel_items,0,12) as $item) {
                            if($item['type'] == 'movie') {
                                $url = "filme.php?stream={$item['id']}&streamtipo=movie";
                            } else {
                                $url = "serie.php?stream={$item['id']}&streamtipo=serie";
                            }
                        ?>
                        <div class="item">
                           <div class="card card--big">
                              <div class="card__cover">
                                    <img loading="lazy" src="<?php echo $item['img']; ?>" alt="">
                                    <a href="<?php echo $url; ?>" class="card__play">
                                       <i class="fas fa-play"></i>
                                    </a>
                              </div>
                              <div class="card__content">
                                    <h3 class="card__title" style="margin-top:0;">
                                       <a href="<?php echo $url; ?>">
                                        <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $item['name']),30); ?>
                                       </a>
                                    </h3>
                                    <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                                       <?php echo $item['year']; ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo $item['rating']; ?>
                                    </span>
                              </div>
                           </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="content__head">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between" style="margin-top:30px;">
                            <h1 class="home__title" style="margin:0;">RECOMENDACIONES</h1>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <ul class="nav nav-tabs content__tabs" id="content__tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#movies" role="tab" aria-controls="movies" aria-selected="true">PELÍCULAS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#series" role="tab" aria-controls="series" aria-selected="false">SERIES</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#estrenos" role="tab" aria-controls="estrenos" aria-selected="false">ESTRENOS</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#recientes" role="tab" aria-controls="recientes" aria-selected="false">RECIÉN AGREGADOS</a>
                                </li>
                            </ul>
                            <div class="tabs-actions">
                                <button class="refresh-btn" data-type="movie" type="button" title="Actualizar Películas" id="refresh-movies-btn" style="display:none;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="refresh-btn" data-type="series" type="button" title="Actualizar Series" id="refresh-series-btn" style="display:none;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <select id="recientes-type" class="form-control" style="display:none; width: auto; max-width: 200px;">
                                    <option value="movie">Películas</option>
                                    <option value="series">Series</option>
                                </select>
                                <select id="estrenos-type" class="form-control" style="display:none; width: auto; max-width: 200px;">
                                    <option value="movie">Películas</option>
                                    <option value="series">Series</option>
                                </select>
                            </div>
                        </div>
                        <div class="content__mobile-tabs" id="content__mobile-tabs">
                            <div class="content__mobile-tabs-btn dropdown-toggle" role="navigation" id="mobile-tabs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <input type="button" value="Películas">
                                <span></span>
                            </div>
                            <div class="content__mobile-tabs-menu dropdown-menu" aria-labelledby="mobile-tabs">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" id="movies-tab" data-toggle="tab" href="#movies" role="tab" aria-controls="movies" aria-selected="true">PELÍCULAS</a></li>
                                    <li class="nav-item"><a class="nav-link" id="series-tab" data-toggle="tab" href="#series" role="tab" aria-controls="series" aria-selected="false">SERIES</a></li>
                                    <li class="nav-item"><a class="nav-link" id="estrenos-tab" data-toggle="tab" href="#estrenos" role="tab" aria-controls="estrenos" aria-selected="false">ESTRENOS</a></li>
                                    <li class="nav-item"><a class="nav-link" id="recientes-tab" data-toggle="tab" href="#recientes" role="tab" aria-controls="recientes" aria-selected="false">RECIÉN AGREGADOS</a></li>
                                </ul>
                            </div>
                            <div class="tabs-actions-mobile">
                                <button class="refresh-btn" data-type="movie" type="button" title="Actualizar Películas" id="refresh-movies-btn-mobile" style="display:none;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="refresh-btn" data-type="series" type="button" title="Actualizar Series" id="refresh-series-btn-mobile" style="display:none;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <select id="recientes-type-mobile" class="form-control" style="display:none; width: auto; max-width: 140px;">
                                    <option value="movie">Películas</option>
                                    <option value="series">Series</option>
                                </select>
                                <select id="estrenos-type-mobile" class="form-control" style="display:none; width: auto; max-width: 140px;">
                                    <option value="movie">Películas</option>
                                    <option value="series">Series</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="movies" role="tabpanel" aria-labelledby="movies-tab">
                    <div class="row" id="movies-grid">
                        <?php
                        $movies_list = getMovies($user, $pwd, 16);
                        foreach($movies_list as $row) {
    $filme_nome = $row['name'];
    $filme_id = $row['stream_id'];
    $filme_img = $row['stream_icon'];
    $filme_rat = $row['rating_5based'];
    $filme_ano = isset($row['year']) ? $row['year'] : 'N/A';
?>
<div class="col-6 col-sm-4 col-lg-3 col-xl-3">
    <div class="card">
        <div class="card__cover">
            <img loading="lazy" src="<?php echo $filme_img; ?>" alt="">
            <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=movie" class="card__play">
                <i class="fas fa-play"></i>
            </a>
        </div>
        <div class="card__content">
            <h3 class="card__title" style="margin-top:0;">
                <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=movie">
                <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome),30); ?>
                </a>
            </h3>
            <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                <?php echo $filme_ano; ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo $filme_rat; ?>
            </span>
        </div>
    </div>
</div>
<?php } ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="series" role="tabpanel" aria-labelledby="series-tab">
                    <div class="row" id="series-grid">
                        <?php
                        $series_list = getSeries($user, $pwd, 16);
                        foreach($series_list as $row) {
    $serie_nome = $row['name'];
    $serie_id = $row['series_id'];
    $serie_img = $row['cover'];
    $serie_ano = isset($row['year']) ? $row['year'] : (isset($row['releaseDate']) ? substr($row['releaseDate'], 0, 4) : 'N/A');
    $serie_rat = isset($row['rating']) ? $row['rating'] : (isset($row['rating_5based']) ? $row['rating_5based'] : 'N/A');
?>
<div class="col-6 col-sm-4 col-lg-3 col-xl-3">
    <div class="card">
        <div class="card__cover">
            <img loading="lazy" src="<?php echo $serie_img; ?>" alt="">
            <a href="serie.php?stream=<?php echo $serie_id; ?>&streamtipo=serie" class="card__play">
                <i class="fas fa-play"></i>
            </a>
        </div>
        <div class="card__content">
            <h3 class="card__title" style="margin-top:0;">
                <a href="serie.php?stream=<?php echo $serie_id; ?>&streamtipo=serie">
        <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $serie_nome),30); ?>
                </a>
            </h3>
            <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                <?php echo $serie_ano; ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo $serie_rat; ?>
            </span>
        </div>
    </div>
</div>
<?php } ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="estrenos" role="tabpanel" aria-labelledby="estrenos-tab">
                    <div class="row" id="estrenos-grid">
                        <?php
                        $estrenos = getPremieres($user, $pwd, 'movie', 16);
                        foreach($estrenos as $row) {
                            $filme_nome = $row['name'];
                            $filme_id = $row['stream_id'];
                            $filme_img = $row['stream_icon'];
                            $filme_rat = $row['rating_5based'];
                            $filme_ano = isset($row['year']) ? $row['year'] : 'N/A';
                        ?>
                        <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
                            <div class="card">
                                <div class="card__cover">
                                    <img loading="lazy" src="<?php echo $filme_img; ?>" alt="">
                                    <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=movie" class="card__play">
                                        <i class="fas fa-play"></i>
                                    </a>
                                </div>
                                <div class="card__content">
                                    <h3 class="card__title" style="margin-top:0;">
                                        <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=movie">
                                        <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome),30); ?>
                                        </a>
                                    </h3>
                                    <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                                        <?php echo $filme_ano; ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo $filme_rat; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="recientes" role="tabpanel" aria-labelledby="recientes-tab">
                    <div class="row" id="recientes-grid">
                        <?php
                        $recientes = getRecentContent($user, $pwd, 'movie', 16);
                        foreach($recientes as $row) {
                            $filme_nome = $row['name'];
                            $filme_id = $row['stream_id'];
                            $filme_img = $row['stream_icon'];
                            $filme_rat = $row['rating_5based'];
                            $filme_ano = isset($row['year']) ? $row['year'] : 'N/A';
                        ?>
                        <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
                            <div class="card">
                                <div class="card__cover">
                                    <img loading="lazy" src="<?php echo $filme_img; ?>" alt="">
                                    <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=movie" class="card__play">
                                        <i class="fas fa-play"></i>
                                    </a>
                                </div>
                                <div class="card__content">
                                    <h3 class="card__title" style="margin-top:0;">
                                        <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=movie">
                                        <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome),30); ?>
                                        </a>
                                    </h3>
                                    <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                                        <?php echo $filme_ano; ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo $filme_rat; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
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
                        &copy; <?php echo date('Y'); ?> <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="assets/logo/logo.png"> PLAYGO
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="./scripts/jquery-3.5.1.min.js"></script>
    <script src="./scripts/bootstrap.bundle.min.js"></script>
    <script src="./scripts/owl.carousel.min.js"></script>
    <script src="./scripts/jquery.mousewheel.min.js"></script>
    <script src="./scripts/jquery.mcustomscrollbar.min.js"></script>
    <script src="./scripts/wnumb.js"></script>
    <script src="./scripts/nouislider.min.js"></script>
    <script src="./scripts/jquery.morelines.min.js"></script>
    <script src="./scripts/photoswipe.min.js"></script>
    <script src="./scripts/photoswipe-ui-default.min.js"></script>
    <script src="./scripts/glightbox.min.js"></script>
    <script src="./scripts/jBox.all.min.js"></script>
    <script src="./scripts/select2.min.js"></script>
    <script src="./scripts/jwplayer.js"></script>
    <script src="./scripts/jwplayer.core.controls.js"></script>
    <script src="./scripts/provider.hlsjs.js"></script>
    <script src="./scripts/main.js"></script>
    <script src="./scripts/home/home.js"></script>
</body>
</html>