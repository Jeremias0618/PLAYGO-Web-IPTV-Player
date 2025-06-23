<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

// Obtener info de cuenta para la fecha de vencimiento
$url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_account_info";
$respuesta_info = apixtream($url_info);
$info = json_decode($respuesta_info, true);

$expira = 'Desconocida';
if (isset($info['user_info']['exp_date']) && is_numeric($info['user_info']['exp_date'])) {
    $expira = date('d/m/Y H:i', $info['user_info']['exp_date']);
}

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
$respuesta = apixtream($url);
$peliculas = json_decode($respuesta, true);

$wallpaper_url = '';
if ($peliculas && count($peliculas) > 0) {
    $random = $peliculas[array_rand($peliculas)];
    $wallpaper_url = isset($random['cover']) ? $random['cover'] : (isset($random['stream_icon']) ? $random['stream_icon'] : '');
}

// Generar recomendados aleatorios de películas y series
$recomendados = [];
if ($peliculas && count($peliculas) > 0) {
    foreach ($peliculas as $p) {
        $recomendados[] = [
            'id' => $p['stream_id'],
            'nombre' => $p['name'],
            'img' => !empty($p['cover']) ? $p['cover'] : (!empty($p['stream_icon']) ? $p['stream_icon'] : 'img/noimg.jpg'),
            'ano' => $p['year'] ?? '',
            'rate' => $p['rating'] ?? '0',
            'tipo' => 'movie'
        ];
    }
}

// Series
$url_series = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
$respuesta_series = apixtream($url_series);
$series = json_decode($respuesta_series, true);

if ($series && count($series) > 0) {
    foreach ($series as $s) {
        $recomendados[] = [
            'id' => $s['series_id'],
            'nombre' => $s['name'],
            'img' => !empty($s['cover']) ? $s['cover'] : (!empty($s['stream_icon']) ? $s['stream_icon'] : 'img/noimg.jpg'),
            'ano' => $s['year'] ?? '',
            'rate' => $s['rating'] ?? '0',
            'tipo' => 'serie'
        ];
    }
}

// Mezclar y tomar 8 aleatorios
shuffle($recomendados);
$recomendados = array_slice($recomendados, 0, 8);

// $expira = ...;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - MI PERFIL</title>
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
        .mobile-menu {
        display: none;
        }
        #mobileMenuOverlay {
        display: none;
        }
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&display=swap');
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            background: #181a20 url('<?php echo $wallpaper_url; ?>') center/cover no-repeat;
            background-blend-mode: multiply;
            color: #fff;
            overflow-x: hidden;
        }
        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 40px;
        }
        .main-content {
            padding-top: 140px;
        }
        .profile-section {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        /* NUEVO DISEÑO HORIZONTAL */
        .profile-card-horizontal {
            display: flex;
            flex-direction: row;
            align-items: center;
            background: rgba(24,26,32,0.92);
            border-radius: 32px;
            box-shadow: 0 8px 40px #e5091440, 0 2px 24px #000a;
            max-width: 1110px;
            margin: 0 auto 36px auto;
            padding: 38px 60px 28px 60px; 
            border: 2.5px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(18px);
            position: relative;
            gap: 38px;
        }
        .profile-avatar-horizontal {
            min-width: 130px;
            min-height: 130px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 40%, #e50914 60%, #fff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4.2rem;
            color: #fff;
            box-shadow: 0 0 40px 10px #e5091480, 0 2px 24px #000a;
            border: 7px solid #fff2;
            margin-right: 0;
        }
        .profile-info-horizontal {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .profile-info-row {
            display: grid;
            grid-template-columns: 160px 1fr 160px 1fr;
            align-items: center;
            gap: 18px 32px;
            margin-bottom: 2px;
        }
        .profile-label-h {
            color: #e50914;
            font-weight: 700;
            font-size: 1.08rem;
            letter-spacing: 1px;
            text-align: right;
            opacity: 0.95;
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }
        .profile-value-h {
            color: #fff;
            font-weight: 900;
            font-size: 1.45rem;
            background: rgba(30,30,36,0.92);
            padding: 8px 18px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #0003;
            text-align: left;
            font-family: 'Montserrat', Arial, sans-serif;
            letter-spacing: 1px;
            min-width: 0;
            word-break: break-all;
        }
        .profile-actions-horizontal {
            display: flex;
            gap: 22px;
            justify-content: flex-end;
            margin-top: 18px;
        }
        .profile-btn-h {
            background: linear-gradient(90deg, #e50914 60%, #ff6a00 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 13px 32px;
            font-size: 1.15rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 2px 12px #e5091440;
            transition: background 0.2s, transform 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-btn-h:hover {
            background: #fff;
            color: #e50914;
            transform: scale(1.07);
        }
        @media (max-width: 900px) {
            .profile-card-horizontal {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 6px 12px 6px;
                max-width: 98vw;
                gap: 18px;
            }
            .profile-avatar-horizontal {
                margin: 0 auto 8px auto;
                width: 80px;
                height: 80px;
                font-size: 2.2rem;
                min-width: 80px;
                min-height: 80px;
            }
            .profile-info-row {
                grid-template-columns: 90px 1fr 90px 1fr;
                gap: 6px 8px;
            }
            .profile-value-h {
                font-size: 1rem;
                padding: 7px 8px;
            }
            .profile-label-h {
                font-size: 0.98rem;
            }
            .profile-actions-horizontal {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
        }
        /* Carrusel estilo poster vertical tipo streaming */
        .carousel-wrapper {
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            padding: 0;
            margin-bottom: 38px;
            position: relative;
            overflow: visible;
        }
        .owl-carousel.profile-carousel .item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .movie-card {
            width: 210px;
            background: transparent;
            border: none;
            box-shadow: none;
            margin: 0 12px;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.18s;
            position: relative;
            overflow: hidden;
        }
        .movie-card:hover {
            transform: scale(1.04) translateY(-6px);
        }
        .movie-poster-wrap {
            position: relative;
            width: 210px;
            height: 315px;
            display: block;
        }
        .movie-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 0;
            background: #232027;
            box-shadow: 0 4px 24px #0008;
            transition: filter 0.2s;
        }
        .movie-poster-wrap:hover .movie-overlay {
            opacity: 1;
        }
        .movie-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(229,9,80,0.70) 0%, rgba(255,46,122,0.60) 100%);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.25s;
            z-index: 2;
        }
        .movie-play {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            width: 64px;
            height: 64px;
            color: #fff;
            font-size: 2.3rem;
            box-shadow: 0 2px 12px #e5091440;
            transition: background 0.2s, color 0.2s, transform 0.2s;
            text-decoration: none;
        }
        .movie-play:hover {
            background: #fff;
            color: #e50914;
            transform: scale(1.12);
        }
        .movie-info {
            width: 100%;
            padding: 0;
            text-align: left;
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .movie-title {
            color: #fff;
            font-size: 1.13rem;
            font-weight: 700;
            margin-bottom: 6px;
            margin-top: 0;
            min-height: 1.5em;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .movie-title a {
            color: #fff;
            text-decoration: none;
            transition: color 0.2s;
        }
        .movie-title a:hover {
            color: #e50914;
        }
        .movie-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 0;
        }
        .movie-year {
            color: #fff;
            font-size: 1.02rem;
            font-weight: 700;
        }
        .movie-rating {
            color: #ff2e7a;
            font-size: 1.02rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .movie-rating i {
            color: #ff2e7a;
            font-size: 1.1rem;
        }
        @media (max-width: 900px) {
            .movie-card, .movie-poster-wrap { width: 120px; }
            .movie-poster-wrap, .movie-cover { height: 180px; }
            .movie-title { font-size: 0.98rem; }
        }
        @media (max-width: 600px) {
            .movie-card, .movie-poster-wrap { width: 90px; }
            .movie-poster-wrap, .movie-cover { height: 135px; }
            .movie-title { font-size: 0.88rem; }
        }
        @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            .header__content {
                padding-left: 4px !important;
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

        @media (max-width: 600px) {
        .main-content {
            padding-top: 80px !important;
        }
        .profile-section {
            gap: 12px !important;
            margin-bottom: 18px !important;
            padding: 0 4px;
        }
        .profile-card-horizontal {
            flex-direction: column !important;
            align-items: center !important;
            padding: 16px 6px 10px 6px !important;
            max-width: 98vw !important;
            gap: 10px !important;
            border-radius: 18px !important;
            box-shadow: 0 4px 18px #e5091440, 0 1px 10px #000a !important;
        }
        .profile-avatar-horizontal {
            margin: 0 auto 8px auto !important;
            width: 64px !important;
            height: 64px !important;
            font-size: 2.1rem !important;
            min-width: 64px !important;
            min-height: 64px !important;
            border-width: 4px !important;
        }
        .profile-info-horizontal {
            gap: 6px !important;
            width: 100%;
        }
        .profile-info-row {
            grid-template-columns: 1fr 1fr !important;
            gap: 4px 4px !important;
            margin-bottom: 0 !important;
        }
        .profile-label-h {
            font-size: 0.92rem !important;
            padding-right: 2px;
            text-align: right !important;
            justify-content: flex-end !important;
        }
        .profile-value-h {
            font-size: 0.98rem !important;
            padding: 6px 6px !important;
            border-radius: 7px !important;
            text-align: left !important;
            min-width: 0 !important;
            word-break: break-all;
        }
        .profile-actions-horizontal {
            flex-direction: column !important;
            gap: 8px !important;
            align-items: stretch !important;
            margin-top: 10px !important;
        }
        .profile-btn-h {
            font-size: 1rem !important;
            padding: 10px 0 !important;
            border-radius: 8px !important;
        }
        .section-title-row {
            margin-top: 18px !important;
            margin-bottom: 8px !important;
            text-align: center !important;
        }
        .section-title {
            font-size: 1.1rem !important;
            letter-spacing: 1px;
        }
        .carousel-wrapper {
            margin-bottom: 18px !important;
            padding: 0 !important;
        }
        .movie-card, .movie-poster-wrap {
            width: 110px !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 165px !important;
            border-radius: 8px !important;
        }
        .movie-title {
            font-size: 0.92rem !important;
            margin-bottom: 2px !important;
            margin-top: 0 !important;
            text-align: center !important;
            max-width: 100%;
            white-space: normal !important;
        }
        .movie-meta {
            gap: 6px !important;
            font-size: 0.88rem !important;
            justify-content: center !important;
        }
        .carousel-nav {
            width: 32px !important;
            height: 32px !important;
            font-size: 1.2rem !important;
            top: 40% !important;
        }
        }

        @media (max-width: 600px) {
        .profile-actions-horizontal {
            flex-direction: row !important;
            gap: 8px !important;
            align-items: center !important;
            justify-content: center !important;
            margin-top: 10px !important;
        }
        .profile-btn-h {
            font-size: 0.92rem !important;
            padding: 7px 12px !important;
            border-radius: 7px !important;
            min-width: 0 !important;
            flex: 1 1 auto;
            max-width: 120px;
        }
        }
        @media (max-width: 600px) {
        .carousel-wrapper {
            margin-bottom: 14px !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .owl-carousel.profile-carousel .item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 2px !important;
        }
        .movie-card, .movie-poster-wrap {
            width: 120px !important;
            min-width: 100px !important;
            margin: 0 4px !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 180px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px #0008 !important;
        }
        .movie-title {
            font-size: 1rem !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            max-width: 100%;
            white-space: normal !important;
            font-weight: 700 !important;
            color: #fff !important;
        }
        .movie-meta {
            gap: 4px !important;
            font-size: 0.92rem !important;
            justify-content: center !important;
            margin-top: 0 !important;
            color: #ff2e7a !important;
        }
        .movie-year, .movie-rating {
            font-size: 0.92rem !important;
            color: #ff2e7a !important;
        }
        .carousel-nav {
            width: 36px !important;
            height: 36px !important;
            font-size: 1.3rem !important;
            top: 38% !important;
            background: #232027 !important;
            color: #fff !important;
            border-radius: 50% !important;
            border: none !important;
            box-shadow: 0 2px 8px #0005 !important;
            opacity: 0.85 !important;
            transition: background 0.2s, color 0.2s;
            z-index: 10;
        }
        .carousel-nav:active,
        .carousel-nav:focus {
            background: #e50914 !important;
            color: #fff !important;
            outline: none !important;
        }
        .section-title-row {
            margin-top: 12px !important;
            margin-bottom: 6px !important;
            text-align: center !important;
        }
        .section-title {
            font-size: 1.05rem !important;
            letter-spacing: 1px;
            color: #fff !important;
        }
        }

        @media (max-width: 600px) {
        /* Oculta las flechas de navegación de los carruseles */
        .carousel-nav {
            display: none !important;
        }

        @media (max-width: 600px) {
        .carousel-wrapper {
            margin-bottom: 10px !important;
            padding: 0 !important;
        }
        .owl-carousel.profile-carousel .item {
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .movie-card, .movie-poster-wrap {
            width: 46vw !important;
            min-width: 0 !important;
            max-width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 68vw !important;
            max-height: 220px !important;
            border-radius: 8px !important;
        }
        .movie-title {
            font-size: 0.98rem !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            max-width: 100%;
            white-space: normal !important;
            font-weight: 700 !important;
            color: #fff !important;
        }
        .movie-meta {
            gap: 6px !important;
            font-size: 0.92rem !important;
            justify-content: center !important;
            margin-top: 0 !important;
            color: #ff2e7a !important;
        }
        .carousel-nav {
            display: none !important;
        }
        
        }

        @media (max-width: 600px) {
        .movie-card, .movie-poster-wrap {
            width: 40vw !important;      /* Más delgado */
            min-width: 0 !important;
            max-width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 56vw !important;     /* Más alto */
            max-height: 320px !important;
            border-radius: 10px !important;
        }
        }
        @media (max-width: 600px) {
        .movie-title {
            font-size: 0.85rem !important;
            max-width: 100%;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            font-weight: 700 !important;
            color: #fff !important;
            line-height: 1.1 !important;
            min-height: unset !important;
        }
        }
        @media (max-width: 600px) {
        .movie-title {
            font-size: 0.85rem !important;
            max-width: 100%;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            font-weight: 700 !important;
            color: #fff !important;
            line-height: 1.1 !important;
            min-height: unset !important;
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
<body>
<!-- HEADER (idéntico a painel.php) -->
<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="index.php">
                            <img src="img/logo.png" alt="" height="48px">
                        </a>
                        <ul class="header__nav d-flex align-items-center mb-0">
                            <li class="header__nav-item">
                                <a href="./painel.php" class="header__nav-link">Inicio</a>
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

<!-- MODAL BUSCADOR (idéntico a painel.php) -->
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

<!-- CONTENIDO PRINCIPAL -->
<div class="container main-content">
    <div class="profile-section" style="justify-content:center;">
        <div class="profile-card-horizontal">
            <div class="profile-avatar-horizontal">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info-horizontal">
                <div class="profile-info-row">
                    <div class="profile-label-h">Usuario:</div>
                    <div class="profile-value-h"><?php echo htmlspecialchars($user); ?></div>
                    <div class="profile-label-h"><i class="fas fa-history"></i> Historial:</div>
                    <div class="profile-value-h" id="historial-count">0</div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-label-h">Clave:</div>
                    <div class="profile-value-h"><?php echo str_repeat('*', strlen($pwd)); ?></div>
                    <div class="profile-label-h"><i class="fas fa-heart"></i> Favoritos:</div>
                    <div class="profile-value-h" id="favoritos-count">0</div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-label-h">Vencimiento:</div>
                    <div class="profile-value-h"><?php echo isset($expira) ? htmlspecialchars($expira) : 'Desconocida'; ?></div>
                    <div class="profile-label-h"><i class="fas fa-calendar-day"></i> Fecha:</div>
                    <div class="profile-value-h" id="fecha-actual"></div>
                </div>
                <div class="profile-actions-horizontal">
                    <button class="profile-btn-h" onclick="location.href='painel.php'"><i class="fas fa-home"></i> Inicio</button>
                    <button class="profile-btn-h" onclick="location.href='painel.php?acao=sair'"><i class="fas fa-sign-out-alt"></i> Salir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- HISTORIAL -->
    <div class="section-title-row">
        <span class="section-title"><i class="fas fa-history"></i> HISTORIAL</span>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-nav historial-nav-prev" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="owl-carousel profile-carousel" id="historial-carousel"></div>
        <button class="carousel-nav historial-nav-next" type="button">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <!-- FAVORITOS -->
    <div class="section-title-row">
        <span class="section-title"><i class="fas fa-heart"></i> FAVORITOS</span>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-nav favoritos-nav-prev" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="owl-carousel profile-carousel" id="favoritos-carousel"></div>
        <button class="carousel-nav favoritos-nav-next" type="button">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <!-- RECOMENDADOS -->
    <div class="section-title-row">
        <span class="section-title"><i class="fas fa-magic"></i> RECOMENDADOS</span>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-nav recomendados-nav-prev" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="owl-carousel profile-carousel" id="recomendados-carousel">
            <?php foreach($recomendados as $item):
                $url = ($item['tipo'] === "serie")
                    ? "serie.php?stream={$item['id']}&streamtipo=serie"
                    : "filme.php?stream={$item['id']}&streamtipo=movie";
            ?>
            <div class="item">
                <div class="movie-card">
                    <div class="movie-poster-wrap">
                        <img class="movie-cover" src="<?php echo htmlspecialchars($item['img']); ?>" alt="">
                        <div class="movie-overlay">
                            <a href="<?php echo $url; ?>" class="movie-play"><i class="fas fa-play"></i></a>
                        </div>
                    </div>
                    <div class="movie-info">
                        <div class="movie-title">
                            <a href="<?php echo $url; ?>"><?php echo htmlspecialchars($item['nombre']); ?></a>
                        </div>
                        <div class="movie-meta">
                            <span class="movie-year"><?php echo htmlspecialchars($item['ano']); ?></span>
                            <span class="movie-rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($item['rate']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-nav recomendados-nav-next" type="button">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<script src="./js/jquery-3.5.1.min.js"></script>
<script src="./js/owl.carousel.min.js"></script>
<script>
// HISTORIAL
$(document).ready(function(){
    fetch('db/base.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_historial'
    })
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.success && data.historial.length > 0) {
            data.historial.forEach(function(item){
                let url = (item.tipo === "serie")
                    ? `serie.php?stream=${item.id}&streamtipo=serie`
                    : `filme.php?stream=${item.id}&streamtipo=movie`;
                html += `<div class="item">
                    <div class="movie-card">
                        <div class="movie-poster-wrap">
                            <img class="movie-cover" src="${item.img}" alt="">
                            <div class="movie-overlay">
                                <a href="${url}" class="movie-play"><i class="fas fa-play"></i></a>
                            </div>
                        </div>
                        <div class="movie-info">
                            <div class="movie-title">
                                <a href="${url}">${item.nombre}</a>
                            </div>
                            <div class="movie-meta">
                                <span class="movie-year">${item.ano ? item.ano.toString().substring(0,4) : ''}</span>
                                <span class="movie-rating"><i class="fas fa-star"></i> ${item.rate}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html = '<div style="color:#fff;padding:20px;">Sin historial</div>';
        }
        $('#historial-carousel').html(html);

        let $carousel = $('#historial-carousel');
        $carousel.owlCarousel({
            loop: true,
            margin: 20,
            nav: false,
            dots: false,
            autoplay: false,
            smartSpeed: 1800,
            responsive:{
            0:{ items:2, margin:30 },      // <-- margen 0 en móviles
            600:{ items:3, margin:20 },
            1000:{ items:4, margin:20 }
        }
    });
        $('.historial-nav-prev').off('click').on('click', function(){
            $carousel.trigger('prev.owl.carousel');
        });
        $('.historial-nav-next').off('click').on('click', function(){
            $carousel.trigger('next.owl.carousel');
        });
        setHistorialCount(data.success ? data.historial.length : 0);
    });

    // FAVORITOS
    fetch('db/base.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_favoritos'
    })
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.success && data.favoritos.length > 0) {
            data.favoritos.forEach(function(item){
                let url = (item.tipo === "serie")
                    ? `serie.php?stream=${item.id}&streamtipo=serie`
                    : `filme.php?stream=${item.id}&streamtipo=movie`;
                html += `<div class="item">
                    <div class="movie-card">
                        <div class="movie-poster-wrap">
                            <img class="movie-cover" src="${item.img}" alt="">
                            <div class="movie-overlay">
                                <a href="${url}" class="movie-play"><i class="fas fa-play"></i></a>
                            </div>
                        </div>
                        <div class="movie-info">
                            <div class="movie-title">
                                <a href="${url}">${item.nombre}</a>
                            </div>
                            <div class="movie-meta">
                                <span class="movie-year">${item.ano ? item.ano.toString().substring(0,4) : ''}</span>
                                <span class="movie-rating"><i class="fas fa-star"></i> ${item.rate}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html = '<div style="color:#fff;padding:20px;">Sin favoritos</div>';
        }
        $('#favoritos-carousel').html(html);

        let $carousel = $('#favoritos-carousel');
        $carousel.owlCarousel({
            loop: true,
            margin: 20,
            nav: false,
            dots: false,
            autoplay: false,
            smartSpeed: 1800,
            responsive:{
            0:{ items:2, margin:30 },      // <-- margen 0 en móviles
            600:{ items:3, margin:20 },
            1000:{ items:4, margin:20 }
        }
    });
        $('.favoritos-nav-prev').off('click').on('click', function(){
            $carousel.trigger('prev.owl.carousel');
        });
        $('.favoritos-nav-next').off('click').on('click', function(){
            $carousel.trigger('next.owl.carousel');
        });
        setFavoritosCount(data.success ? data.favoritos.length : 0);
    });

    // RECOMENDADOS (solo inicializa el carrusel, NO fetch)
    $('#recomendados-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: false,
        dots: false,
        autoplay: false,
        smartSpeed: 1800,
        responsive:{
        0:{ items:2, margin:30 },      // <-- margen 0 en móviles
        600:{ items:3, margin:20 },
        1000:{ items:4, margin:20 }
    }
});
    $('.recomendados-nav-prev').off('click').on('click', function(){
        $('#recomendados-carousel').trigger('prev.owl.carousel');
    });
    $('.recomendados-nav-next').off('click').on('click', function(){
        $('#recomendados-carousel').trigger('next.owl.carousel');
    });
});

// Fecha y hora en tiempo real
function actualizarFecha() {
    const fecha = new Date();
    const opciones = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute:'2-digit', second:'2-digit' };
    document.getElementById('fecha-actual').textContent = fecha.toLocaleString('es-ES', opciones);
}
setInterval(actualizarFecha, 1000);
actualizarFecha();

function setHistorialCount(n) {
    document.getElementById('historial-count').textContent = n;
}
function setFavoritosCount(n) {
    document.getElementById('favoritos-count').textContent = n;
}
</script>

<!-- MODAL BUSCADOR JS (igual que painel.php) -->
<script>
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
    document.getElementById('modalBuscadorResults').innerHTML = html;
}

// MODAL BUSCADOR JS
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