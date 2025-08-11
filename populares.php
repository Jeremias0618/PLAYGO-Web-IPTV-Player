<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);

// --- Paginación ---
$peliculas_por_pagina = 48;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina_actual - 1) * $peliculas_por_pagina;

// Obtener películas
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
$resposta = apixtream($url);
$output = json_decode($resposta,true);

// --- FONDO ALEATORIO DE PELÍCULA ---
$backdrop_fondo = '';
if ($output && is_array($output) && count($output) > 0) {
    // Elegir una película aleatoria del listado
    $pelicula_aleatoria = $output[array_rand($output)];
    $vod_id = $pelicula_aleatoria['stream_id'];
    // Llamar a get_vod_info para obtener el backdrop_path (array de URLs)
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
    $res_info = apixtream($url_info);
    $data_info = json_decode($res_info, true);
    if (
        isset($data_info['info']['backdrop_path']) &&
        is_array($data_info['info']['backdrop_path']) &&
        count($data_info['info']['backdrop_path']) > 0
    ) {
        // Usar el primer backdrop disponible
        $backdrop_fondo = $data_info['info']['backdrop_path'][0];
    }
}

// Ordenar por rating descendente (más populares primero)
if ($output && is_array($output)) {
    usort($output, function($a, $b) {
        $ra = isset($a['rating_5based']) ? floatval($a['rating_5based'])*2 : (isset($a['rating']) ? floatval($a['rating']) : 0);
        $rb = isset($b['rating_5based']) ? floatval($b['rating_5based'])*2 : (isset($b['rating']) ? floatval($b['rating']) : 0);
        return $rb <=> $ra;
    });
}

// Paginación
$total_peliculas = is_array($output) ? count($output) : 0;
$total_paginas = ceil($total_peliculas / $peliculas_por_pagina);
$peliculas_pagina = ($output && is_array($output)) ? array_slice(array_values($output), $inicio, $peliculas_por_pagina) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./css/owl.carousel.min.css">
    <link rel="stylesheet" href="./css/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./css/ionicons.min.css">
    <link rel="stylesheet" href="./css/photoswipe.css">
    <link rel="stylesheet" href="./css/glightbox.css">
    <link rel="stylesheet" href="./css/default-skin.css">
    <link rel="stylesheet" href="./css/jBox.all.min.css">
    <link rel="stylesheet" href="./css/select2.min.css">
    <link rel="stylesheet" href="./css/listings.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <title>PLAYGO - Populares</title>
    <style>
    html, body {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
}
    .custom-paginator {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 40px 0 30px 0;
        padding: 0;
        list-style: none;
        background: #292933;
        border-radius: 6px;
        min-height: 48px;
        box-shadow: 0 2px 8px #0002;
        overflow: hidden;
        width: fit-content;
        min-width: 340px;
    }
    .custom-paginator li {
        margin: 0;
        display: flex;
        align-items: center;
    }
    .custom-paginator a, .custom-paginator span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 48px;
        color: #bfc1c8;
        background: transparent;
        border: none;
        font-size: 1.15rem;
        font-weight: 400;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
        cursor: pointer;
        outline: none;
        border-radius: 0;
    }
    .custom-paginator .active a,
    .custom-paginator .active span {
        background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);
        color: #fff;
        font-weight: 600;
        border-radius: 0;
    }
    .custom-paginator li:not(.active):hover a {
        background: #23232b;
        color: #fff;
    }
    .custom-paginator .disabled span {
        color: #555;
        cursor: default;
        background: transparent;
    }
    .custom-paginator .arrow {
        font-size: 1.3rem;
        color: #bfc1c8;
        padding: 0 10px;
    }
    .custom-paginator .arrow:hover {
        color: #fff;
        background: #23232b;
    }
    .header__content {
        background: #000 !important;
        border-radius: 12px;
        padding: 12px 24px;
    }
    .header__wrap {
        background: #000 !important;
    }
    .navbar-overlay {
        display: none !important;
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
        z-index: 999999 !important;
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
    
    /* Asegurar que el modal esté por encima de todo */
    .modal-buscador-bg,
    .modal-buscador-bg * {
        z-index: 999999 !important;
    }
    
    /* Forzar que el modal esté por encima del header */
    .modal-buscador-bg {
        z-index: 999999 !important;
    }
    
    .modal-buscador {
        z-index: 999999 !important;
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
        display: block;
        margin-top: 2px;
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
    
    #bg-overlay {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.78);
        z-index: 0;
        pointer-events: none;
    }
    body > *:not(#bg-overlay) {
        position: relative;
        z-index: 1;
    }
    .header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: #000 !important;
        z-index: 2000 !important;
        box-shadow: none !important;
        border: none !important;
    }
    .header__wrap {
        background: #000 !important;
        border-radius: 0 !important;
    }
    .header__content {
        background: #000 !important;
        border-radius: 12px;
        padding: 12px 24px;
    }
    body {
        padding-top: 90px; /* Ajusta según la altura real de tu header */
        overflow-x: hidden !important;
    }
    html {
        overflow-x: hidden !important;
    }
        .card__cover img {
    width: 100%;
    height: 440px;      /* Puedes ajustar la altura según tu diseño */
    object-fit: cover;
    border-radius: 10px;
    background: #232027;
    display: block;
}
@media (max-width: 600px) {
  .card__cover img {
    height: 260px !important;
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

        @media (max-width: 600px) {
  .modal-buscador {
    max-width: 98vw !important;
    width: 98vw !important;
    min-width: 0 !important;
    padding: 18px 6px 16px 6px !important;
    border-radius: 14px !important;
    box-shadow: 0 4px 24px #000a !important;
    top: 10vw !important;
    left: 1vw !important;
    right: 1vw !important;
    position: relative !important;
  }
  .modal-buscador-inputbox {
    flex-direction: column !important;
    gap: 10px;
    margin-bottom: 18px !important;
  }
  .modal-buscador-inputbox input {
    font-size: 1.15rem !important;
    padding: 12px 10px !important;
    border-radius: 8px !important;
    width: 100% !important;
    margin-right: 0 !important;
  }
  .modal-buscador-inputbox button {
    width: 100% !important;
    font-size: 1.13rem !important;
    padding: 12px 0 !important;
    border-radius: 8px !important;
    margin-top: 4px;
  }
  .modal-buscador-close {
    top: 10px !important;
    right: 12px !important;
    font-size: 2.1rem !important;
    padding: 0 8px !important;
  }
  .modal-buscador-section h3 {
    font-size: 1.08rem !important;
    margin-bottom: 10px !important;
  }
  .modal-buscador-grid {
    gap: 10px !important;
  }
  .modal-buscador-card {
    width: 90px !important;
  }
  .modal-buscador-card img {
    height: 110px !important;
    border-radius: 8px !important;
  }
  .modal-buscador-card span {
    font-size: 0.93rem !important;
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
        
        /* FORZAR Z-INDEX MÁXIMO PARA EL MODAL */
        .modal-buscador-bg {
            z-index: 999999 !important;
            position: fixed !important;
        }
        
        .modal-buscador-bg * {
            z-index: 999999 !important;
        }
        
        .modal-buscador {
            z-index: 999999 !important;
            position: relative !important;
        }
    </style>
<?php if($backdrop_fondo): ?>
<style>
body {
    background: url('<?php echo $backdrop_fondo; ?>') no-repeat center center fixed !important;
    background-size: cover !important;
}
html:before {
    content: "";
    position: fixed;
    z-index: 0;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.88);
    pointer-events: none;
}
html > body > * {
    position: relative;
    z-index: 1;
}
</style>
<?php endif; ?>
</head>

<body class="body">

<!-- HEADER estilo filmes.php -->
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
<div id="bg-overlay"></div>

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

<!-- Título centrado -->
<div style="width:100%;text-align:center;margin:130px 0 30px 0;">
    <h2 style="font-size:2.5rem;font-weight:800;letter-spacing:2px;color:#fff;display:inline-block;padding:10px 40px;border-radius:12px;">PELÍCULAS POPULARES</h2>
</div>
<div class="catalog details">
    <div class="container">
        <div class="row">
<?php
if ($peliculas_pagina && is_array($peliculas_pagina)) {
    foreach($peliculas_pagina as $index) {
        // Eliminar año entre paréntesis del nombre
        $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $index['name']);
        $filme_type = $index['stream_type'];
        $filme_id = $index['stream_id'];
        $filme_img = $index['stream_icon'];
        $filme_rat = $index['rating'];
        $avs = $index['rating_5based'];
        $filme_ano = isset($index['year']) ? $index['year'] : '';
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
        <div class="card">
            <div class="card__cover">
                <img loading="lazy" src="<?php echo $filme_img; ?>" alt="<?php echo htmlspecialchars($filme_nome); ?>">
                <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                    <i class="fa-solid fa-circle-play"></i>
                </a>
            </div>
            <div class="card__content">
                <h3 class="card__title">
                    <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                        <?php echo limitar_texto($filme_nome, 40); ?>
                    </a>
                </h3>
                <span class="card__rate"><?php echo $filme_ano; ?> &nbsp; <i class="fa-solid fa-star"></i><?php echo $filme_rat; ?></span>
            </div>
        </div>
    </div>
<?php
    }
} else {
    echo '<div style="color:#fff;font-size:1.2rem;">No hay películas populares.</div>';
}
?>
        </div>
        <!-- PAGINADOR COMPACTO -->
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <ul class="custom-paginator">
                    <?php
                    $total_paginas = max(1, $total_paginas);
                    if($pagina_actual > 1) {
                        echo '<li><a class="arrow" href="?'.http_build_query(array_merge($_GET, ['pagina'=>($pagina_actual-1)])).'">&#60;</a></li>';
                    } else {
                        echo '<li class="disabled"><span class="arrow">&#60;</span></li>';
                    }
                    if($pagina_actual == 1) {
                        echo '<li class="active"><span>1</span></li>';
                    } else {
                        echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>1])).'">1</a></li>';
                    }
                    if($total_paginas >= 2) {
                        if($pagina_actual == 2) {
                            echo '<li class="active"><span>2</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>2])).'">2</a></li>';
                        }
                    }
                    if($total_paginas >= 3) {
                        if($pagina_actual == 3) {
                            echo '<li class="active"><span>3</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>3])).'">3</a></li>';
                        }
                    }
                    if($total_paginas > 4) {
                        if($pagina_actual > 3 && $pagina_actual < $total_paginas - 1) {
                            echo '<li class="disabled"><span>...</span></li>';
                            if($pagina_actual != $total_paginas && $pagina_actual != 1 && $pagina_actual != 2 && $pagina_actual != 3) {
                                echo '<li class="active"><span>'.$pagina_actual.'</span></li>';
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                        } else {
                            echo '<li class="disabled"><span>...</span></li>';
                        }
                        if($pagina_actual == $total_paginas) {
                            echo '<li class="active"><span>'.$total_paginas.'</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>$total_paginas])).'">'.$total_paginas.'</a></li>';
                        }
                    } elseif($total_paginas == 4) {
                        if($pagina_actual == 4) {
                            echo '<li class="active"><span>4</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>4])).'">4</a></li>';
                        }
                    }
                    if($pagina_actual < $total_paginas) {
                        echo '<li><a class="arrow" href="?'.http_build_query(array_merge($_GET, ['pagina'=>($pagina_actual+1)])).'">&#62;</a></li>';
                    } else {
                        echo '<li class="disabled"><span class="arrow">&#62;</span></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footer__copyright">
                    &copy; 2025 <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="img/logo.png"> PLAYGO
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
<?php
// Películas
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
$resposta = apixtream($url);
$peliculas = json_decode($resposta,true);
echo "var peliculas = ".json_encode(array_map(function($p){
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
echo "var series = ".json_encode(array_map(function($s){
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
echo "var canales = ".json_encode(array_map(function($c){
    return [
        'id'=>$c['stream_id'],
        'nombre'=>$c['name'],
        'img'=>$c['stream_icon'],
        'tipo'=>$c['stream_type']
    ];
},$canales)).";\n";
?>
// Función para renderizar resultados del buscador
function renderBuscadorResults(query) {
    query = query.trim().toLowerCase();
    let html = '';
    let totalResults = 0;
    
    // Aplicar filtro
    let showMovies = currentFilter === 'all' || currentFilter === 'movies';
    let showSeries = currentFilter === 'all' || currentFilter === 'series';
    let showChannels = currentFilter === 'all' || currentFilter === 'channels';
    
    // Películas
    if (showMovies) {
        let pelis = peliculas.filter(p => p.nombre.toLowerCase().includes(query));
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
        let sers = series.filter(s => s.nombre.toLowerCase().includes(query));
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
        let chans = canales.filter(c => c.nombre.toLowerCase().includes(query));
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

// Lógica del modal buscador
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
if(openSearchModal) openSearchModal.onclick = showModalBuscador;
if(closeSearchModal) closeSearchModal.onclick = hideModalBuscador;
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

</body>
</html>