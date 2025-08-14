<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$categoria = isset($_REQUEST['catg']) ? urldecode($_REQUEST['catg']) : 'TV en Vivo';
$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
$adulto = isset($_REQUEST['adulto']) ? trim($_REQUEST['adulto']) : '';
$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - TV en Vivo</title>
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
        .header__content, .header__wrap { background: #000 !important; border-radius: 0px; }
        .header__content { padding: 12px 24px; }
        .navbar-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 80px; z-index: 1;
            pointer-events: none; background: linear-gradient(90deg, #0f2027 0%, #2c5364 100%);
            opacity: 0.85; transition: opacity 0.4s;
        }
        .bg-animate { animation: navbarBgMove 8s linear infinite alternate; background-size: 200% 100%; }
        @keyframes navbarBgMove { 0% { background-position: 0% 50%; } 100% { background-position: 100% 50%; } }
        .canales-grid { display: flex; flex-wrap: wrap; gap: 22px; margin-top: 24px; }
        .canal-card {
            background: #181818;
            border-radius: 14px;
            overflow: hidden;
            width: 180px;
            box-shadow: 0 2px 12px #0005;
            transition: transform 0.15s, box-shadow 0.15s, background 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }
        .canal-card:hover {
            background-image: linear-gradient(90deg, #831f5e 0%, #f50b60 100%);
            z-index: 1;
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 8px 32px #000a;
        }
        .canal-card img {
            width: 100%; height: 120px; object-fit: cover; background: #232027;
            border-bottom: 1px solid #222;
        }
        .canal-card .canal-title {
            color: #fff;
            font-size: 1.08rem;
            font-weight: 500;
            margin: 12px 0 16px 0;
            min-height: 38px;
            padding: 8px 8px;
            background: #e50914;
            border-radius: 8px;
            display: inline-block;
            width: 90%;
            transition: background 0.3s, color 0.3s;
        }
        .canal-card:hover .canal-title {
            background: #831f5e;
            color: #fff;
        }
        .canal-card .canal-play {
            /* Quita color y fondo transparentes */
            background: none !important;
            color: inherit !important;
            border: none !important;
            box-shadow: none !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            position: absolute;
            left: 0; top: 0;
            width: 100%; /* Hace todo el card clickeable */
            height: 100%;
            z-index: 2;
            display: block !important;
            margin: 0;
            font-size: 0;
        }
        .canal-card .canal-play i {
            display: none !important;
        }
        .sidebar-categorias {
            background: #181818; border-radius: 14px; padding: 18px 12px; margin-top: 152px;
        }
        .sidebar-categorias h2 {
            color: #e50914; font-size: 1.1rem; margin-bottom: 12px; font-weight: 700;
        }
        .sidebar-categorias ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-categorias li {
            margin-bottom: 8px; font-size: 1rem;
        }
        .sidebar-categorias li a {
            color: #fff; text-decoration: none; transition: color 0.2s;
        }
        .sidebar-categorias li a:hover { color: #e50914; }
        @media (max-width: 900px) {
            .canales-grid { gap: 12px; }
            .canal-card { width: 44vw; min-width: 120px; }
        }
        @media (max-width: 600px) {
            .canal-card { width: 98vw; min-width: 90px; }
            .sidebar-categorias { margin-top: 18px; }
        }
        .home__title {
            margin-top: 90px;
            text-align: center !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }
        @media (max-width: 600px) {
            .home__title {
                margin-top: 70px;
                font-size: 1.3rem;
                justify-content: center !important;
                text-align: center !important;
                width: 100vw;
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
    
        @media (max-width: 600px) {
            .canales-grid {
                gap: 10px !important;
                justify-content: center !important;
                margin-top: 16px;
            }
            .canal-card {
                width: 98vw !important;
                min-width: 90px !important;
                max-width: 99vw !important;
                margin: 0 auto 10px auto !important;
                border-radius: 14px !important;
                box-shadow: 0 2px 12px #0004 !important;
                display: flex;
                flex-direction: row;
                align-items: center;
                padding: 10px 0;
                position: relative;
                background: #181818 !important;
                transition: box-shadow 0.2s, background 0.2s;
            }
            .canal-card img {
                height: 70px !important;
                width: 70px !important;
                border-radius: 14px !important;
                margin-left: 8px;
                margin-right: 12px;
                object-fit: cover;
                background: #232027;
                flex-shrink: 0;
            }
            .canal-card-info {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                min-width: 0;
            }
            .canal-title {
                font-size: 1rem !important;
                font-weight: 700;
                padding: 6px 16px !important;
                border-radius: 12px !important;
                background: #e50914;
                color: #fff;
                display: inline-block;
                border: 2px solid #e50914;
                margin-bottom: 6px;
                max-width: 100%;
                word-break: break-word;
                box-shadow: 0 2px 8px #e5091440;
            }
            .canal-desc {
                font-size: 0.97rem;
                color: #fff;
                background: transparent;
                margin: 0;
                padding: 0 2px 0 2px;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .canal-play {
                margin-left: 10px;
                margin-right: 16px;
                width: 44px;
                height: 44px;
                font-size: 1.5rem;
                background: #e50914 !important;
                color: #fff !important;
                border-radius: 50%;
                display: flex !important;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px #e5091440;
                border: none !important;
                transition: background 0.2s, color 0.2s, transform 0.18s;
                animation: playPulse 1.4s infinite alternate;
            }
            .canal-play:active {
                background: #fff !important;
                color: #e50914 !important;
                transform: scale(1.18) rotate(-10deg);
            }
            /* Filtro móvil */
            .mobile-categorias-filter {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 12px;
                gap: 10px;
                position: relative;
            }
            #mobileCategoriasBtn {
                background: linear-gradient(180deg, #e50914 0%, #c8008f 100%) !important;
                color: #fff;
                border: none;
                border-radius: 8px;
                padding: 8px 18px;
                font-size: 1.08rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 2px 8px #c8008f40;
            }
            #mobileCategoriasClear {
                font-size: 1.5rem;
                color: #e50914;
                background: #fff;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: 2px solid #e50914;
                margin-left: 6px;
                transition: background 0.2s, color 0.2s;
            }
            #mobileCategoriasClear:hover {
                background: #e50914;
                color: #fff;
            }
            .mobile-categorias-dropdown {
                display: none;
                position: absolute;
                top: 44px;
                left: 0;
                right: 0;
                background: #181818;
                border-radius: 10px;
                box-shadow: 0 2px 12px #0007;
                z-index: 100;
                padding: 8px 0;
                max-height: 220px;
                overflow-y: auto;
            }
            .mobile-categorias-dropdown.active {
                display: block;
            }
            .mobile-categorias-dropdown .cat-option {
                padding: 10px 18px;
                color: #fff;
                cursor: pointer;
                font-size: 1.08rem;
                transition: background 0.2s, color 0.2s;
                border-bottom: 1px solid #232027;
            }
            .mobile-categorias-dropdown .cat-option:last-child {
                border-bottom: none;
            }
            .mobile-categorias-dropdown .cat-option.selected,
            .mobile-categorias-dropdown .cat-option:hover {
                background: #e50914;
                color: #fff;
            }
        }
        /* Oculta el filtro en desktop */
        @media (min-width: 601px) {
            .mobile-categorias-filter { display: none !important; }
        }
        @media (max-width: 600px) {
            .sidebar-categorias { display: none !important; }
        }
        @keyframes playPulse {
            0% { box-shadow: 0 2px 8px #e5091440, 0 0 0 0 #e5091422; }
            100% { box-shadow: 0 2px 16px #e5091440, 0 0 0 8px #e5091400; }
        }
        .canal-card:active {
            box-shadow: 0 6px 24px #e5091440 !important;
            background: #232027 !important;
        }
        .canal-title:active {
            background: #fff !important;
            color: #e50914 !important;
        }
        html {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
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
                                <img src="img/logo.png" alt="" height="48px">
                            </a>
                            <ul class="header__nav d-flex align-items-center mb-0">
                                <li class="header__nav-item">
                                    <a href="./painel.php" class="header__nav-link">Inicio</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="./canais.php?sessao=<?php echo $sessao; ?>" class="header__nav-link header__nav-link--active">TV en Vivo</a>
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

    <section class="content" style="margin-top:30px;">
        <div class="container">
            <div class="row">
                <!-- GRID CANALES -->
                <div class="col-lg-10 col-md-8">
                    <h1 class="home__title" style="margin-bottom:18px;">
                        <i class="fa fa-trophy" aria-hidden="true"></i> <?php echo htmlspecialchars($categoria); ?>
                    </h1>
                    <!-- Filtro móvil de categorías -->
                    <div class="mobile-categorias-filter" id="mobileCategoriasFilter">
                        <button id="mobileCategoriasBtn" style="background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);">
                            <i class="fa fa-list"></i> Categorías
                        </button>
                        <span id="mobileCategoriasClear" title="Limpiar selección">&times;</span>
                        <div class="mobile-categorias-dropdown" id="mobileCategoriasDropdown"></div>
                    </div>
                    <div class="canales-grid">
                        <?php
                        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams".($id ? "&category_id=$id" : "");
                        $resposta = apixtream($url);
                        $output = json_decode($resposta,true);
                        if ($output && is_array($output)) {
                            foreach($output as $index) {
                                $canal_nome = $index['name'];
                                $canal_type = $index['stream_type'];
                                $canal_id = $index['stream_id'];
                                $canal_img = $index['stream_icon'];
                                $cat_id = isset($index['category_id']) ? $index['category_id'] : '';
                        ?>
                        <div class="canal-card" style="position:relative;" data-cat="<?php echo $cat_id; ?>">
                            <img src="<?php echo $canal_img; ?>" alt="<?php echo htmlspecialchars($canal_nome); ?>">
                            <div class="canal-card-info">
                                <span class="canal-title"><?php echo limitar_texto($canal_nome, 32); ?></span>
                                <span class="canal-desc">
                                    <?php
                                    $desc = '';
                                    if (!empty($index['plot'])) {
                                        $desc = $index['plot'];
                                    } elseif (!empty($index['description'])) {
                                        $desc = $index['description'];
                                    } elseif (!empty($index['category_name'])) {
                                        $desc = 'Categoría: ' . $index['category_name'];
                                    }
                                    echo limitar_texto($desc, 60);
                                    ?>
                                </span>
                            </div>
                            <a class="canal-play" href="canal.php?stream=<?php echo $canal_id; ?>" title="Ver canal">
                                <i class="fa fa-play"></i>
                            </a>
                        </div>
                        <?php
                            }
                        } else {
                            echo '<div style="color:#fff;font-size:1.2rem;">No hay canales en esta categoría.</div>';
                        }
                        ?>
                    </div>
                </div>
                <!-- SIDEBAR CATEGORÍAS SOLO EN DESKTOP -->
                <div class="col-lg-2 col-md-4 sidebar-categorias-col">
                    <aside class="sidebar-categorias">
                        <h2><i class="fa fa-tv" aria-hidden="true"></i> Categorías</h2>
                        <ul>
                            <li>
                                <a href="canais.php?sessao=<?php echo $sessao; ?>&id=&catg=TV%20en%20Vivo"<?php if($id=='') echo ' style="color:#f50b60;"'; ?>>Todos</a>
                            </li>
                        <?php
                        $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_categories";
                        $resposta = apixtream($url);
                        $output = json_decode($resposta,true);
                        foreach($output as $res1) {
                            $idcatcanal = $res1['category_id'];
                            $catgcanal = $res1['category_name'];
                            echo '<li><a href="canais.php?id='.$idcatcanal.'&catg='.urlencode($catgcanal).'"';
                            if($id==$idcatcanal) echo ' style="color:#f50b60;"';
                            echo '>'.$catgcanal.'</a></li>';
                        }
                        ?>
                        </ul>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer" style="margin-top:40px;">
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
            'img'=>$p['stream_icon'],
            'tipo'=>$p['stream_type']
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
            'tipo'=>$c['stream_type'],
            'cat'=>isset($c['category_id']) ? $c['category_id'] : ''
        ];
    },$canales)).";\n";
    // Categorías para el filtro móvil
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_categories";
    $resposta = apixtream($url);
    $output = json_decode($resposta,true);
    echo "categoriasCanales = ".json_encode(array_map(function($c){
        return [
            'id'=>$c['category_id'],
            'nombre'=>$c['category_name']
        ];
    },$output)).";\n";
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

    // --- FILTRO DE CATEGORÍAS SOLO MÓVIL ---
    let categoriaSeleccionada = '';
    const mobileCategoriasBtn = document.getElementById('mobileCategoriasBtn');
    const mobileCategoriasDropdown = document.getElementById('mobileCategoriasDropdown');
    const mobileCategoriasClear = document.getElementById('mobileCategoriasClear');
    const canalesGrid = document.querySelector('.canales-grid');

    function renderMobileCategoriasDropdown() {
        let html = `<div class="cat-option" data-id="">Todos</div>`;
        categoriasCanales.forEach(cat => {
            html += `<div class="cat-option${cat.id===categoriaSeleccionada?' selected':''}" data-id="${cat.id}">${cat.nombre}</div>`;
        });
        mobileCategoriasDropdown.innerHTML = html;
    }

    function filtrarCanalesPorCategoria() {
        let cards = canalesGrid.querySelectorAll('.canal-card');
        cards.forEach(card => {
            let catId = card.getAttribute('data-cat');
            if (!categoriaSeleccionada || categoriaSeleccionada === '' || categoriaSeleccionada === catId) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
        // Cambiar la URL al filtrar
        let url = window.location.origin + window.location.pathname;
        if (categoriaSeleccionada && categoriaSeleccionada !== '') {
            let catObj = categoriasCanales.find(c => c.id === categoriaSeleccionada);
            let catg = catObj ? encodeURIComponent(catObj.nombre) : '';
            url += `?id=${categoriaSeleccionada}&catg=${catg}`;
        }
        history.replaceState(null, '', url);
    }

    function initMobileCategoriasFiltro() {
        if (window.innerWidth > 600) return;
        renderMobileCategoriasDropdown();

        mobileCategoriasBtn.onclick = function() {
            mobileCategoriasDropdown.classList.toggle('active');
        };
        mobileCategoriasDropdown.onclick = function(e) {
            if (e.target.classList.contains('cat-option')) {
                categoriaSeleccionada = e.target.getAttribute('data-id');
                renderMobileCategoriasDropdown();
                filtrarCanalesPorCategoria();
                mobileCategoriasDropdown.classList.remove('active');
            }
        };
        mobileCategoriasClear.onclick = function() {
            categoriaSeleccionada = '';
            renderMobileCategoriasDropdown();
            filtrarCanalesPorCategoria();
        };
        document.addEventListener('click', function(e){
            if (!mobileCategoriasDropdown.contains(e.target) && !mobileCategoriasBtn.contains(e.target)) {
                mobileCategoriasDropdown.classList.remove('active');
            }
        });
    }

function enableCanalTitleClick() {
    if (window.innerWidth > 600) return;
    canalesGrid.querySelectorAll('.canal-title').forEach(span => {
        let card = span.closest('.canal-card');
        if (!card) return;
        let link = card.querySelector('.canal-play');
        if (!link) return;
        span.style.cursor = 'pointer';
        span.onclick = function() {
            window.location = link.href;
        };
    });
    // Hace todo el card clickeable en móvil
    canalesGrid.querySelectorAll('.canal-card').forEach(card => {
        let link = card.querySelector('.canal-play');
        if (!link) return;
        card.onclick = function(e) {
            // Evita doble click si ya es el título
            if (e.target.classList.contains('canal-title')) return;
            window.location = link.href;
        };
    });
    // Cambia todos los enlaces de canal-play para que naveguen solo con ?stream=ID
    canalesGrid.querySelectorAll('.canal-play').forEach(link => {
        let href = link.getAttribute('href');
        let match = href.match(/stream=(\d+)/);
        if (match) {
            link.setAttribute('href', 'canal.php?stream=' + match[1]);
        }
    });
}

    window.addEventListener('DOMContentLoaded', function() {
        initMobileCategoriasFiltro();
        enableCanalTitleClick();
    });
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 600) {
            initMobileCategoriasFiltro();
            enableCanalTitleClick();
        }
    });
    </script>
</body>
</html>
</html>