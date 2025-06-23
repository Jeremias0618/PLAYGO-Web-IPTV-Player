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
            top: 0px;
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
            .owl-carousel.home__carousel .card.card--big {
                width: 160px !important;
                min-width: 160px !important;
            }
            .owl-carousel.home__carousel .card.card--big .card__cover img {
                height: 240px !important;
            }
            .owl-carousel.home__carousel .owl-item {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
        }
        @media (max-width: 600px) {
            /* Para tarjetas de "Recién Agregados" */
            .content .card {
                width: 180px !important;
                min-width: 180px !important;
                margin-left: auto;
                margin-right: auto;
            }
            .content .card__cover img {
                height: 270px !important;
                width: 100% !important;
                object-fit: cover;
            }
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
        .owl-carousel.home__carousel .card.card--big {
            width: 240px;      /* Más ancho si lo deseas */
            min-width: 240px;
            max-width: 100%;
            margin: 0;         /* Elimina el margen aquí */
            display: inline-block;
            vertical-align: top;
            background: #181818;
            border-radius: 12px;
        }

        .owl-carousel.home__carousel .owl-item {
            padding-left: 20px;
            padding-right: 20px;
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
            'id'=>$p['stream_id'],
            'nombre'=>$p['name'],
            'img'=>$p['stream_icon']
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
                        <a href="filme.php?stream=${p.id}&streamtipo=movie">
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
                        <a href="serie.php?stream=${s.id}&streamtipo=serie">
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
                    <a href="canal.php?stream=${c.id}">
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
            e.preventDefault();
            let q = modalBuscadorInput.value;
            if (q.length > 1) renderBuscadorResults(q);
        }
    });

    </script>
    
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