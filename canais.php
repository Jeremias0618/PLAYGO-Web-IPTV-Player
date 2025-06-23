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
            text-align: center;
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
            gap: 10px;
            justify-content: center; /* Esto centra los cards */
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
    // MODAL BUSCADOR (igual painel.php)
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