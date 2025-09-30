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
            max-width: 95vw;
            width: 95vw;
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