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
        top: 16px;
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

// Lógica del modal buscador
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
if(openSearchModal) openSearchModal.onclick = showModalBuscador;
if(closeSearchModal) closeSearchModal.onclick = hideModalBuscador;
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

</body>
</html>