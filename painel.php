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
        /* MODAL BUSCADOR MEJORADO */
        .modal-buscador-bg {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: linear-gradient(135deg, rgba(15,32,39,0.95) 0%, rgba(44,83,100,0.95) 100%);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal-buscador-bg.active {
            display: flex;
            opacity: 1;
        }
        .modal-buscador {
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 20px;
            padding: 0;
            max-width: 800px;
            width: 90vw;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8);
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            border: 1px solid rgba(229,9,20,0.3);
        }
        .modal-buscador-bg.active .modal-buscador {
            transform: scale(1);
        }
        .modal-buscador-header {
            background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
            padding: 20px 24px;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }
        .modal-buscador-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        .modal-buscador-title {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .modal-buscador-close {
            position: absolute;
            top: 20px;
            right: 24px;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 2;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-buscador-close:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        .modal-buscador-body {
            padding: 24px;
            max-height: calc(85vh - 100px);
            overflow-y: auto;
        }
        .modal-buscador-inputbox {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 4px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .modal-buscador-inputbox input {
            flex: 1;
            background: transparent;
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 1.1rem;
            margin: 0;
            outline: none;
        }
        .modal-buscador-inputbox input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        .modal-buscador-inputbox button {
            background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 16px 24px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 4px;
            white-space: nowrap;
        }
        .modal-buscador-inputbox button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(229,9,20,0.4);
        }
        .modal-buscador-inputbox button:active {
            transform: translateY(0);
        }
        .modal-buscador-section {
            margin-bottom: 32px;
        }
        .modal-buscador-section h3 {
            color: #e50914;
            font-size: 1.2rem;
            margin-bottom: 20px;
            margin-top: 0;
            font-weight: 700;
            text-align: left;
            padding-left: 8px;
            border-left: 4px solid #e50914;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-buscador-section h3::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #e50914;
            border-radius: 50%;
        }
        .modal-buscador-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 16px;
            justify-content: start;
        }
        .modal-buscador-card {
            text-align: center;
            transition: transform 0.2s ease;
        }
        .modal-buscador-card:hover {
            transform: translateY(-4px);
        }
        .modal-buscador-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .modal-buscador-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 12px;
            background: #232027;
            margin-bottom: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        .modal-buscador-card:hover img {
            border-color: #e50914;
            box-shadow: 0 8px 24px rgba(229,9,20,0.3);
        }
        .modal-buscador-card span {
            color: #fff;
            font-size: 0.9rem;
            display: block;
            font-weight: 500;
            line-height: 1.3;
            padding: 0 4px;
            word-break: break-word;
        }
        .modal-buscador-empty {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255,255,255,0.7);
        }
        .modal-buscador-empty i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        .modal-buscador-empty p {
            font-size: 1.1rem;
            margin: 0;
        }
        .modal-buscador-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .filter-btn.active {
            background: #e50914;
            border-color: #e50914;
        }
        .filter-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        .filter-btn.active:hover {
            background: #c8008f;
        }
        
        /* Estilos específicos para desktop del buscador */
        @media (min-width: 1200px) {
            .modal-buscador {
                max-width: 700px !important;
                width: 700px !important;
            }
        }
        
        @media (min-width: 768px) and (max-width: 1199px) {
            .modal-buscador {
                max-width: 750px !important;
                width: 85vw !important;
            }
        }
        
        /* Estilos específicos para móviles del buscador */
        @media (max-width: 600px) {
            .modal-buscador {
                width: 98vw !important;
                max-width: 98vw !important;
                border-radius: 16px !important;
                margin: 10px;
            }
            .modal-buscador-header {
                padding: 16px 20px;
                border-radius: 16px 16px 0 0;
            }
            .modal-buscador-title {
                font-size: 1.2rem;
            }
            .modal-buscador-body {
                padding: 20px 16px;
            }
            .modal-buscador-inputbox {
                flex-direction: column;
                gap: 12px;
                padding: 8px;
            }
            .modal-buscador-inputbox input {
                width: 100%;
                font-size: 1rem;
                padding: 14px 16px;
            }
            .modal-buscador-inputbox button {
                width: 100%;
                padding: 14px 20px;
                font-size: 1rem;
            }
            .modal-buscador-filters {
                justify-content: center;
                gap: 8px;
            }
            .filter-btn {
                padding: 10px 14px;
                font-size: 0.9rem;
                min-width: 80px;
            }
            .modal-buscador-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 12px;
            }
            .modal-buscador-card img {
                height: 140px;
            }
            .modal-buscador-card span {
                font-size: 0.85rem;
                line-height: 1.2;
            }
            .modal-buscador-close {
                top: 16px;
                right: 16px;
                width: 32px;
                height: 32px;
                font-size: 1rem;
            }
        }
        
        /* Animaciones adicionales para el buscador */
        .modal-buscador-card {
            animation: fadeInUp 0.4s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .modal-buscador-card:nth-child(1) { animation-delay: 0.1s; }
        .modal-buscador-card:nth-child(2) { animation-delay: 0.15s; }
        .modal-buscador-card:nth-child(3) { animation-delay: 0.2s; }
        .modal-buscador-card:nth-child(4) { animation-delay: 0.25s; }
        .modal-buscador-card:nth-child(5) { animation-delay: 0.3s; }
        .modal-buscador-card:nth-child(6) { animation-delay: 0.35s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mejoras para el estado de carga */
        .modal-buscador-loading {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255,255,255,0.7);
        }
        
        .modal-buscador-loading i {
            font-size: 2rem;
            margin-bottom: 16px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

<!-- MODAL BUSCADOR MEJORADO -->
<div class="modal-buscador-bg" id="modalBuscador">
    <div class="modal-buscador">
        <div class="modal-buscador-header">
            <h2 class="modal-buscador-title">
                <i class="fas fa-search"></i> Buscador PLAYGO
            </h2>
            <button class="modal-buscador-close" id="closeSearchModal" title="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-buscador-body">
            <form id="modalBuscadorForm" autocomplete="off" onsubmit="return false;">
                <div class="modal-buscador-inputbox">
                    <input type="text" id="modalBuscadorInput" placeholder="Buscar películas, series o canales..." autofocus>
                    <button type="button" id="modalBuscadorBtn">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
            
            <!-- Filtros de búsqueda -->
            <div class="modal-buscador-filters" id="searchFilters">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-th-large"></i> Todo
                </button>
                <button class="filter-btn" data-filter="movies">
                    <i class="fas fa-film"></i> Películas
                </button>
                <button class="filter-btn" data-filter="series">
                    <i class="fas fa-tv"></i> Series
                </button>
                <button class="filter-btn" data-filter="channels">
                    <i class="fas fa-broadcast-tower"></i> TV
                </button>
            </div>
            
            <div id="modalBuscadorResults"></div>
        </div>
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

    // MODAL BUSCADOR MEJORADO
    const openSearchModal = document.getElementById('openSearchModal');
    const closeSearchModal = document.getElementById('closeSearchModal');
    const modalBuscador = document.getElementById('modalBuscador');
    const modalBuscadorInput = document.getElementById('modalBuscadorInput');
    const modalBuscadorBtn = document.getElementById('modalBuscadorBtn');
    const modalBuscadorResults = document.getElementById('modalBuscadorResults');
    const searchFilters = document.getElementById('searchFilters');

    let currentFilter = 'all';
    let searchTimeout;

    function showModalBuscador() {
        modalBuscador.classList.add('active');
        setTimeout(() => { 
            modalBuscadorInput.focus();
            modalBuscadorInput.select();
        }, 300);
    }
    
    function hideModalBuscador() {
        modalBuscador.classList.remove('active');
        setTimeout(() => {
            modalBuscadorInput.value = '';
            modalBuscadorResults.innerHTML = '';
            resetFilters();
        }, 300);
    }
    
    function resetFilters() {
        currentFilter = 'all';
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector('[data-filter="all"]').classList.add('active');
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

    // Función para normalizar texto (remover tildes y caracteres especiales)
    function normalizarTexto(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remover diacríticos (tildes, diéresis, etc.)
            .replace(/[^a-z0-9\s]/g, ' ') // Remover caracteres especiales, mantener solo letras, números y espacios
            .replace(/\s+/g, ' ') // Normalizar espacios múltiples
            .trim();
    }

    function renderBuscadorResults(query) {
        query = query.trim();
        let queryNormalizado = normalizarTexto(query);
        let html = '';
        let totalResults = 0;
        
        // Aplicar filtro
        let showMovies = currentFilter === 'all' || currentFilter === 'movies';
        let showSeries = currentFilter === 'all' || currentFilter === 'series';
        let showChannels = currentFilter === 'all' || currentFilter === 'channels';
        
        // Películas
        if (showMovies) {
            let pelis = peliculas.filter(p => {
                let nombreNormalizado = normalizarTexto(p.nombre);
                return nombreNormalizado.includes(queryNormalizado) || 
                       p.nombre.toLowerCase().includes(query.toLowerCase());
            });
            if (pelis.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-film"></i> PELÍCULAS (${pelis.length})</h3>
                    <div class="modal-buscador-grid">`;
                pelis.slice(0,12).forEach(p => {
                    html += `<div class="modal-buscador-card">
                        <a href="filme.php?stream=${p.id}&streamtipo=movie">
                            <img src="${p.img}" alt="${p.nombre}" onerror="this.src='img/logo.png'">
                            <span>${p.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += pelis.length;
            }
        }
        
        // Series
        if (showSeries) {
            let sers = series.filter(s => {
                let nombreNormalizado = normalizarTexto(s.nombre);
                return nombreNormalizado.includes(queryNormalizado) || 
                       s.nombre.toLowerCase().includes(query.toLowerCase());
            });
            if (sers.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-tv"></i> SERIES (${sers.length})</h3>
                    <div class="modal-buscador-grid">`;
                sers.slice(0,12).forEach(s => {
                    html += `<div class="modal-buscador-card">
                        <a href="serie.php?stream=${s.id}&streamtipo=serie">
                            <img src="${s.img}" alt="${s.nombre}" onerror="this.src='img/logo.png'">
                            <span>${s.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += sers.length;
            }
        }
        
        // Canales
        if (showChannels) {
            let chans = canales.filter(c => {
                let nombreNormalizado = normalizarTexto(c.nombre);
                return nombreNormalizado.includes(queryNormalizado) || 
                       c.nombre.toLowerCase().includes(query.toLowerCase());
            });
            if (chans.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-broadcast-tower"></i> TV EN VIVO (${chans.length})</h3>
                    <div class="modal-buscador-grid">`;
                chans.slice(0,12).forEach(c => {
                    html += `<div class="modal-buscador-card">
                        <a href="canal.php?stream=${c.id}">
                            <img src="${c.img}" alt="${c.nombre}" onerror="this.src='img/logo.png'">
                            <span>${c.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += chans.length;
            }
        }
        
        if (!html && query.length > 0) {
            html = `<div class="modal-buscador-empty">
                <i class="fas fa-search"></i>
                <p>No se encontraron resultados para "${query}"</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Intenta con otros términos o cambia el filtro</p>
            </div>`;
        } else if (query.length > 0) {
            html = `<div style="text-align: center; margin-bottom: 20px; color: rgba(255,255,255,0.7);">
                <i class="fas fa-info-circle"></i> Se encontraron ${totalResults} resultados
            </div>` + html;
        }
        
        modalBuscadorResults.innerHTML = html;
    }

    // Filtros de búsqueda
    searchFilters.addEventListener('click', function(e) {
        if (e.target.classList.contains('filter-btn')) {
            // Remover clase active de todos los botones
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            // Agregar clase active al botón clickeado
            e.target.classList.add('active');
            currentFilter = e.target.getAttribute('data-filter');
            
            // Re-renderizar resultados si hay una búsqueda activa
            let query = modalBuscadorInput.value.trim();
            if (query.length > 1) {
                renderBuscadorResults(query);
            }
        }
    });

    // Buscar con debounce para mejor performance
    modalBuscadorInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        let q = this.value;
        
        if (q.length > 1) {
            searchTimeout = setTimeout(() => {
                renderBuscadorResults(q);
            }, 300);
        } else {
            modalBuscadorResults.innerHTML = '';
        }
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