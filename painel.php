<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="img/favicon.ico">
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
    <style>
        .home__bg { filter: blur(0px) !important; opacity: 10%; }
        .card__cover img { height: 400px; object-fit: cover; }
        .card.card--big .card__cover img { height: 420px; }
        .card__title { min-height: 48px; }
        .header__content {
            background: #000 !important;
            border-radius: 12px;
            padding: 12px 24px;
        }
        .header__wrap {
            background: #000 !important;
        }
        .card__content,
        .card.card--big .card__content,
        .card {
            background: transparent !important;
            box-shadow: none !important;
        }
        .card__rate {
            margin-bottom: 0;
        }
        .card__title {
            margin-bottom: 0 !important;
            min-height: unset !important;
            font-size: 1.05rem;
            line-height: 1.2;
        }
        .card__rate {
            margin-top: 2px !important;
            margin-bottom: 0 !important;
            font-size: 1.02rem;
            display: block;
        }
        .owl-carousel.home__carousel .card.card--big {
            width: 230px;
            min-width: 230px;
            max-width: 100%;
            margin: 0 22px;
            display: inline-block;
            vertical-align: top;
            background: #181818;
            border-radius: 12px;
        }
        .owl-carousel.home__carousel .owl-item {
            padding-left: 24px;
            padding-right: 24px;
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
        @media (max-width: 600px) {
            .owl-carousel.home__carousel .card.card--big {
                width: 180px;
                min-width: 180px;
                margin: 0 16px;
            }
            .owl-carousel.home__carousel .owl-item {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }
            .header__content {
                padding: 12px 24px;
                padding-left: 4px !important;
            }
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-right: auto;
            }
        }
        @media (min-width: 601px) {
            .header__logo img {
                width: 240px !important;
                height: 80px !important;
                max-width: none !important;
                object-fit: contain;
            }
        }
        @media (max-width: 600px) {
            .header__logo img {
                width: 240px !important;
                height: 60px !important;
                max-width: 100% !important;
                object-fit: contain;
            }
        }
    </style>
</head>
<body class="body">
    <!-- HEADER -->
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
                            <li class="header__nav-item">
                                <a href="./painel.php" class="header__nav-link header__nav-link--active">Inicio</a>
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

<?php include_once __DIR__ . '/partials/search_modal.php'; ?>

    <!-- SLIDER DESTACADOS -->
    <section class="home">
        <div class="owl-carousel home__bg">
            <?php
            // Slider de fondo (mantener igual)
            $url_movies = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
            $res_movies = apixtream($url_movies);
            $movies = json_decode($res_movies,true);

            $url_series = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
            $res_series = apixtream($url_series);
            $series = json_decode($res_series,true);

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
                        // Carrusel: mezclar películas y series y mostrar 12 aleatorios
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

    <!-- TABS PELÍCULAS Y SERIES -->
    <section class="content">
        <div class="content__head">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h1 class="home__title" style="margin-top:30px;">RECIÉN <b>AGREGADOS</b></h1>
                        <ul class="nav nav-tabs content__tabs" id="content__tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#movies" role="tab" aria-controls="movies" aria-selected="true">PELÍCULAS</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#series" role="tab" aria-controls="series" aria-selected="false">SERIES</a>
                            </li>
                        </ul>
                        <div class="content__mobile-tabs" id="content__mobile-tabs">
                            <div class="content__mobile-tabs-btn dropdown-toggle" role="navigation" id="mobile-tabs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <input type="button" value="Películas">
                                <span></span>
                            </div>
                            <div class="content__mobile-tabs-menu dropdown-menu" aria-labelledby="mobile-tabs">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" id="movies-tab" data-toggle="tab" href="#movies" role="tab" aria-controls="movies" aria-selected="true">PELÍCULAS</a></li>
                                    <li class="nav-item"><a class="nav-link" id="series-tab" data-toggle="tab" href="#series" role="tab" aria-controls="series" aria-selected="false">SERIES</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <!-- content tabs -->
            <div class="tab-content">
                <!-- MOVIES TAB -->
                <div class="tab-pane fade show active" id="movies" role="tabpanel" aria-labelledby="movies-tab">
                    <div class="row">
                        <?php
                        // Películas recientes
                        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
                        $resposta = apixtream($url);
                        $output = json_decode($resposta,true);
                        shuffle($output);
foreach(array_slice($output,0,16) as $row) {
    $filme_nome = $row['name'];
    $filme_id = $row['stream_id'];
    $filme_img = $row['stream_icon'];
    $filme_rat = $row['rating_5based']; // Puntuación
    $filme_ano = isset($row['year']) ? $row['year'] : 'N/A'; // Año de estreno
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
                <!-- SERIES TAB -->
                <div class="tab-pane fade" id="series" role="tabpanel" aria-labelledby="series-tab">
                    <div class="row">
                        <?php
                        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
                        $resposta = apixtream($url);
                        $output = json_decode($resposta,true);
                        shuffle($output);
foreach(array_slice($output,0,16) as $row) {
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
            </div>
        </div>
    </section>
    
    <!-- ...resto de painel.php igual... -->

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="footer__copyright">
                        &copy; 2024 <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="img/logo.png"> MAXGO
                    </div>
                </div>
            </div>
        </div>
    </footer>
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
    <script src="./js/jwplayer.js"></script>
    <script src="./js/jwplayer.core.controls.js"></script>
    <script src="./js/provider.hlsjs.js"></script>
    <script src="./js/main.js"></script>
    
<script>
$(document).ready(function(){
    var $carousel = $('.home__carousel');
    $carousel.owlCarousel({
        loop: true,
        margin: 20,
        nav: false, // Usamos botones personalizados
        dots: false,
        autoplay: false,
        autoplayTimeout: 0,
        autoplayHoverPause: false,
        rtl: false,
        smartSpeed: 1800,
        responsive:{
            0:{ items:1 },
            600:{ items:3 },
            1000:{ items:5 }
        }
    });

    // Botones personalizados
    $('.home__nav--prev').off('click').on('click', function(){
        $carousel.trigger('prev.owl.carousel');
    });
    $('.home__nav--next').off('click').on('click', function(){
        $carousel.trigger('next.owl.carousel');
    });

    // Movimiento automático (solo uno activo)
    if (window._carouselInterval) clearInterval(window._carouselInterval);
    window._carouselInterval = setInterval(function(){
        $carousel.trigger('next.owl.carousel', [1800]);
    }, 2000);
});
</script>
</body>
</html>