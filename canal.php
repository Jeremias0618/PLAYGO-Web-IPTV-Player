<?php
require_once("libs/lib.php");
require_once("libs/services/live.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$id = isset($_GET['stream']) ? trim($_GET['stream']) : '';
$idcatg = isset($_GET['catg']) ? trim($_GET['catg']) : '';

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
$resposta = apixtream($url);
$canales = json_decode($resposta, true);
$canal_data = null;
foreach($canales as $c) {
    if($c['stream_id'] == $id) {
        $canal_data = $c;
        break;
    }
}
if(!$canal_data) {
    die('<div style="color:#fff;background:#e50914;padding:30px;text-align:center;font-size:1.3rem;">Canal no encontrado.</div>');
}
$canal = $canal_data['name'];
$defaultIcon = isset($canal_data['stream_icon']) ? $canal_data['stream_icon'] : '';
$img = getChannelLogo($id, $defaultIcon);
$tipo = $canal_data['stream_type'];
$info = '';
if (!empty($canal_data['plot'])) {
    $info = $canal_data['plot'];
} elseif (!empty($canal_data['description'])) {
    $info = $canal_data['description'];
} elseif (!empty($canal_data['category_name'])) {
    $info = 'Categoría: ' . $canal_data['category_name'];
} else {
    $info = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - <?php echo htmlspecialchars($canal); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/vendors/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/vendors/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/vendors/nouislider.min.css">
    <link rel="stylesheet" href="./styles/vendors/ionicons.min.css">
    <link rel="stylesheet" href="./styles/vendors/photoswipe.css">
    <link rel="stylesheet" href="./styles/vendors/glightbox.css">
    <link rel="stylesheet" href="./styles/vendors/default-skin.css">
    <link rel="stylesheet" href="./styles/vendors/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/vendors/select2.min.css">
    <link rel="stylesheet" href="./styles/core/main.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <style>
        body {
            background: #111 !important;
            color: #fff;
        }
        .home__bg { filter: blur(0px) !important; opacity: 10%; }
        .header__content {
            background: #000 !important;
            border-radius: 12px;
            padding: 12px 24px;
        }
        .header__wrap {
            background: #000 !important;
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
        
        .details__bg {
            background: url('<?php echo $img; ?>') center center/cover no-repeat;
            filter: blur(0px) !important;
            opacity: 0.12;
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0;
        }
        .section.details {
            position: relative;
            min-height: 480px;
            padding-top: 110px;
            padding-bottom: 40px;
            overflow: hidden;
        }
        .card--details {
            background: #181818;
            border-radius: 18px;
            box-shadow: 0 4px 24px #0005;
            padding: 32px 24px;
            position: relative;
            z-index: 1;
            max-width: 1150px;
            margin-left: auto;
            margin-right: auto;
        }
        @media (max-width: 1200px) {
            .card--details {
                max-width: 98vw;
            }
        }
        .canal-logo-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 18px;
        }
        .canal-logo-box img {
            border-radius: 12px;
            width: 100%;
            max-width: 220px;
            max-height: 160px;
            object-fit: contain;
            background: #222;
            box-shadow: 0 2px 16px #0008;
        }
        .canal-nombre {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            margin-top: 12px;
            text-align: center;
            word-break: break-word;
        }
        .en-vivo-label {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            font-weight: 600;
            color: #fff;
            background: #e50914;
            border-radius: 8px;
            padding: 7px 18px;
            margin-bottom: 10px;
            margin-top: 8px;
            width: fit-content;
            box-shadow: 0 2px 8px #e5091444;
            letter-spacing: 1px;
        }
        .en-vivo-label i {
            margin-right: 8px;
            font-size: 1.2rem;
            animation: blink 1.2s infinite alternate;
        }
        .volver-canais-btn {
            margin-bottom: 10px;
            margin-top: 0;
            display: flex;
            justify-content: center;
        }
        .volver-canais-btn a {
            background: #e50914;
            color: #fff;
            border-radius: 8px;
            padding: 8px 22px;
            font-size: 1.08rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            box-shadow: 0 2px 8px #e5091444;
            display: inline-block;
        }
        .volver-canais-btn a:hover {
            background: #fff;
            color: #e50914;
        }
        .card__meta, .card__description {
            color: #ccc;
        }
        .epg-table-box {
            background: #232027;
            color: #fff;
            border-radius: 14px;
            margin-top: 28px;
            margin-bottom: 24px;
            padding: 18px 18px 8px 18px;
            box-shadow: 0 2px 12px #0003;
        }
        .epg-table {
            width: 100%;
            margin-bottom: 0;
        }
        .epg-table th, .epg-table td {
            color: #fff;
            border: none;
            padding: 8px 12px;
            vertical-align: top;
        }
        .epg-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #e50914;
            margin-bottom: 12px;
        }
        .info-canal-box {
            background: #232027;
            color: #fff;
            border-radius: 14px;
            margin-bottom: 18px;
            padding: 16px 18px;
            box-shadow: 0 2px 12px #0003;
            font-size: 1.08rem;
        }
        .info-canal-title {
            color: #e50914;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 7px;
            letter-spacing: 1px;
        }
        .section__title {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .recomendados-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            justify-content: flex-start;
        }
        .recomendado-card {
            background: #181818;
            border-radius: 14px;
            box-shadow: 0 2px 12px #0003;
            width: 200px;
            min-width: 200px;
            max-width: 200px;
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            position: relative;
            transition: box-shadow 0.2s;
        }
        .recomendado-card .card__cover {
            position: relative;
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #232027;
        }

            .recomendado-card .card__cover img {
                position: absolute;
                left: 0; top: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: 1;
            }

        .recomendado-card .card__play {
            background: transparent !important;
            color: transparent !important;
            box-shadow: none !important;
            border: none !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            transform: none;
            display: block;
            position: absolute;
            z-index: 2;
        }

        .recomendado-card .card__play i {
            display: none !important;
        }

        .recomendado-card:hover .card__cover img {
            filter: brightness(0.7);
        }
        .recomendado-card:hover .card__play {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        .recomendado-card .card__content {
            padding: 10px 6px 14px 6px;
            width: 100%;
            text-align: center;
        }
        .recomendado-card .card__title {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            margin: 0;
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 900px) {
            .recomendado-card {
                width: 46vw;
                min-width: 46vw;
                max-width: 46vw;
            }
        }
        @media (max-width: 600px) {
            .recomendado-card {
                width: 98vw;
                min-width: 98vw;
                max-width: 98vw;
            }
        }
        @keyframes blink {
            0% { opacity: 1; }
            100% { opacity: 0.5; }
        }
        .video-embed-box {
            width: 100%;
            max-width: 100%;
            margin: 0 auto 18px auto;
            border-radius: 14px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 2px 16px #0008;
        }
        #livevideo {
            width: 100vw !important;
            max-width: 100% !important;
            aspect-ratio: 16/9;
            min-height: 220px;
            background: #000;
            border-radius: 0 0 14px 14px;
            margin: 0 auto;
            display: block;
        }
        @media (max-width: 900px) {
            .video-embed-box { max-width: 100vw; }
            #livevideo { min-height: 200px !important; height: auto !important; }
        }
        @media (max-width: 600px) {
            #livevideo { min-height: 160px !important; height: auto !important; }
        }

        /* SOLO MOBILE ANDROID: Mejoras de responsive */
        @media only screen and (max-width: 600px) and (pointer: coarse) and (hover: none) {
            .section.details {
                padding-top: 60px;
                min-height: unset;
            }
            .card--details {
                padding: 14px 4px;
                border-radius: 10px;
            }
            .canal-logo-box img {
                max-width: 120px;
                max-height: 80px;
            }
            .canal-nombre {
                font-size: 1.1rem;
                margin-top: 7px;
            }
            .en-vivo-label {
                font-size: 0.95rem;
                padding: 5px 10px;
                border-radius: 6px;
            }
            .volver-canais-btn a {
                font-size: 0.98rem;
                padding: 6px 12px;
                border-radius: 6px;
            }
            .video-embed-box {
                border-radius: 8px;
                margin-bottom: 10px;
            }
            #livevideo {
                min-height: 140px !important;
                height: auto !important;
                border-radius: 0 0 8px 8px;
            }
            .info-canal-box {
                font-size: 0.98rem;
                padding: 10px 8px;
                border-radius: 8px;
            }
            .epg-table-box {
                padding: 10px 4px 4px 4px;
                border-radius: 8px;
                font-size: 0.95rem;
            }
            .epg-title {
                font-size: 1.05rem;
                margin-bottom: 7px;
            }
            .epg-table th, .epg-table td {
                padding: 5px 4px;
                font-size: 0.93rem;
            }
            .recomendados-grid {
                gap: 10px;
                justify-content: center;
            }
            .recomendado-card {
                width: 98vw;
                min-width: 98vw;
                max-width: 98vw;
                border-radius: 8px;
            }
            .recomendado-card .card__cover {
                height: 90px;
            }
            .recomendado-card .card__title {
                font-size: 0.98rem;
                min-height: 28px;
            }
            .modal-buscador {
                padding: 18px 4px 10px 4px;
                border-radius: 10px;
                max-width: 99vw;
            }
            .modal-buscador-card {
                width: 80px;
            }
            .modal-buscador-card img {
                height: 90px;
                border-radius: 6px;
            }
            .footer__copyright {
                font-size: 0.95rem;
                padding-bottom: 10px;
            }
        }

        /* Estilos específicos para iOS */
        @supports (-webkit-touch-callout: none) {
            #livevideo video {
                -webkit-appearance: none;
                border-radius: 0;
                background: #000;
            }
            
            #livevideo video::-webkit-media-controls {
                display: none !important;
            }
            
            #livevideo video::-webkit-media-controls-panel {
                display: none !important;
            }
            
            #livevideo video::-webkit-media-controls-play-button {
                display: none !important;
            }
            
            #livevideo video::-webkit-media-controls-start-playback-button {
                display: none !important;
            }
        }

        /* Mejoras para video nativo en iOS */
        #nativeVideo, #fallbackVideo {
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
            outline: none;
            border: none;
            background: #000;
        }

        /* Estilos para controles personalizados en iOS si es necesario */
        .ios-video-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .ios-play-button {
            background: rgba(229,9,20,0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .ios-play-button:hover {
            background: rgba(229,9,20,1);
            transform: scale(1.1);
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
    <script src="https://cdn.jsdelivr.net/npm/jwplayer@8.32.0/jwplayer.js"></script>
</head>
<body class="body">
    <!-- HEADER igual painel.php -->
    <header class="header">
        <div class="navbar-overlay bg-animate"></div>
        <div class="header__wrap">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="header__content d-flex align-items-center justify-content-between">
                            <a class="header__logo" href="login.php">
                                <img src="assets/logo/logo.png" alt="" height="48px">
                            </a>
                            <ul class="header__nav d-flex align-items-center mb-0">
                                <li class="header__nav-item">
                                    <a href="./home.php" class="header__nav-link">Inicio</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="./channels.php" class="header__nav-link header__nav-link--active">TV en Vivo</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="movies.php" class="header__nav-link">Películas</a>
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

    <?php include_once __DIR__ . '/libs/views/search.php'; ?>

    <!-- DETALLES DEL CANAL -->
<section class="section details" style="position:relative;">
    <div class="details__bg"></div>
    <div class="container" style="position:relative; z-index:1;">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-12">
                <div class="card card--details">
                    <div class="row">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 d-flex flex-column align-items-center justify-content-center">
                            <div class="canal-logo-box">
                                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($canal); ?>">
                                <div class="canal-nombre"><?php echo htmlspecialchars($canal); ?></div>
                                <div class="en-vivo-label"><i class="fa fa-circle"></i> EN VIVO</div>
                                <div class="volver-canais-btn">
                                    <a href="channels.php"><i class="fa fa-arrow-left"></i> Volver a canales</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 d-flex flex-column align-items-center justify-content-center">
                            <div class="video-embed-box">
                                <div id="livevideo"></div>
                            </div>
                        </div>
                    </div>
                    <!-- INFORMACION DEL CANAL -->
                    <div class="info-canal-box" id="infoCanalBox">
                        <div class="info-canal-title"><i class="fa fa-info-circle"></i> INFORMACIÓN:</div>
                        <div id="infoCanalContent">
                            <?php if($info): ?>
                                <?php echo nl2br(htmlspecialchars($info)); ?>
                            <?php else: ?>
                                <span id="infoCanalLoading">Cargando información...</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- EPG -->
                            <div class="epg-table-box">
                                <div class="epg-title"><i class="fa fa-calendar-alt"></i> Guía EPG</div>
                                <table class="epg-table">
                                    <tbody>
                                    <?php
                                    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_short_epg&stream_id=$id";
                                    $resposta = apixtream($url);
                                    $output = json_decode($resposta,true);

                                    // Función para limpiar la fecha y quitar el texto no deseado
                                    function limpiar_fecha_epg($texto) {
                                        // Quita todo lo que esté después de un guion (incluido el guion)
                                        $partes = explode('-', $texto, 2);
                                        return trim($partes[0]);
                                    }

                                    if(isset($output['epg_listings'])) {
                                        foreach($output['epg_listings'] as $index) {
                                            $titulo = base64_decode($index['title']);
                                            $inicio = limpiar_fecha_epg(ds($index['start']));
                                            $fin = limpiar_fecha_epg(ds($index['end']));
                                            $descripcion = base64_decode($index['description']);
                                            echo '<tr>';
                                            echo '<td style="width:30%;"><b>' . $titulo . '</b></td>';
                                            echo '<td style="width:18%;"><i class="fa fa-clock"></i> ' . $inicio . ' - ' . $fin . '</td>';
                                            echo '<td>' . $descripcion . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="3">Sin EPG disponible.</td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                    <!-- FIN EPG -->
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- CANALES RECOMENDADOS -->
    <section class="content">
        <div class="container" style="margin-top: 30px;">
            <div class="row">
                <div class="col-12">
                    <h2 class="section__title">Canales recomendados</h2>
                </div>
                <div class="recomendados-grid">
                <?php
                // Obtener canales aleatorios (excepto el actual)
                $recom_url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams".($idcatg ? "&category_id=$idcatg" : "");
                $resposta = apixtream($recom_url);
                $output = json_decode($resposta,true);
                // Filtrar el canal actual
                $output = array_filter($output, function($c) use ($id) { return $c['stream_id'] != $id; });
                shuffle($output);
                foreach(array_slice($output,0,5) as $row) {
                    $canal_nome = $row['name'];
                    $canal_id = $row['stream_id'];
                    $defaultIcon = isset($row['stream_icon']) ? $row['stream_icon'] : '';
                    $canal_img = getChannelLogo($canal_id, $defaultIcon);
                ?>
                <div class="recomendado-card">
                    <div class="card__cover" style="position:relative;">
                        <img loading="lazy" src="<?php echo $canal_img; ?>" alt="<?php echo htmlspecialchars($canal_nome); ?>">
                        <a href="channel.php?stream=<?php echo $canal_id; ?>&catg=<?php echo $idcatg; ?>" class="card__play">
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                    <div class="card__content">
                        <h3 class="card__title">
                            <a href="channel.php?stream=<?php echo $canal_id; ?>&catg=<?php echo $idcatg; ?>">
                                <?php echo limitar_texto($canal_nome,30); ?>
                            </a>
                        </h3>
                    </div>
                </div>
                <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="footer__copyright">
                        &copy; 2025 <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="assets/logo/logo.png"> MAXGO
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
    <script src="./scripts/vendors/bootstrap.bundle.min.js"></script>
    <script src="./scripts/vendors/owl.carousel.min.js"></script>
    <script src="./scripts/vendors/jquery.mousewheel.min.js"></script>
    <script src="./scripts/vendors/jquery.mcustomscrollbar.min.js"></script>
    <script src="./scripts/vendors/wnumb.js"></script>
    <script src="./scripts/vendors/nouislider.min.js"></script>
    <script src="./scripts/vendors/jquery.morelines.min.js"></script>
    <script src="./scripts/vendors/photoswipe.min.js"></script>
    <script src="./scripts/vendors/photoswipe-ui-default.min.js"></script>
    <script src="./scripts/vendors/glightbox.min.js"></script>
    <script src="./scripts/vendors/jBox.all.min.js"></script>
    <script src="./scripts/vendors/select2.min.js"></script>
    <script src="./scripts/vendors/jwplayer.js"></script>
    <script src="./scripts/vendors/jwplayer.core.controls.js"></script>
    <script src="./scripts/vendors/provider.hlsjs.js"></script>
    <script src="./scripts/core/main.js"></script>
<script>
    // Detectar si es dispositivo iOS
    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) || 
               (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    // Detectar si es Safari en iOS
    function isSafariIOS() {
        return isIOS() && /Safari/.test(navigator.userAgent) && !/CriOS|FxiOS|OPiOS|mercury/.test(navigator.userAgent);
    }

    // Detectar versión de iOS
    function getIOSVersion() {
        var match = navigator.userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/);
        if (match) {
            return parseInt(match[1]);
        }
        return 0;
    }

    // URL del stream
    var streamUrl = "<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.m3u8";
    var posterImage = "<?php echo $img; ?>";
    var iosVersion = getIOSVersion();

    // Configuración específica para iOS
    if (isIOS()) {
        console.log('Dispositivo iOS detectado, versión:', iosVersion);
        
        // Para iOS, usar video nativo HTML5 con HLS.js como fallback
        var videoElement = document.getElementById('livevideo');
        videoElement.innerHTML = `
            <video 
                id="nativeVideo" 
                controls 
                autoplay 
                muted 
                playsinline 
                webkit-playsinline
                x-webkit-airplay="allow"
                style="width: 100%; height: 100%; background: #000;"
                poster="${posterImage}"
                preload="metadata"
            >
                <source src="${streamUrl}" type="application/x-mpegURL">
                Tu navegador no soporta el elemento de video.
            </video>
        `;

        var video = document.getElementById('nativeVideo');
        
        // Configuraciones específicas para diferentes versiones de iOS
        if (iosVersion >= 10) {
            video.setAttribute('webkit-playsinline', 'true');
            video.setAttribute('playsinline', 'true');
        }
        
        // Intentar reproducir automáticamente
        video.addEventListener('loadedmetadata', function() {
            console.log('Metadata cargado, intentando autoplay...');
            video.play().catch(function(error) {
                console.log('Autoplay falló:', error);
                // Para iOS, intentar con muted
                video.muted = true;
                video.play().catch(function(e) {
                    console.log('Muted autoplay también falló:', e);
                    // Mostrar botón de play manual
                    showManualPlayButton();
                });
            });
        });

        // Manejar errores de carga
        video.addEventListener('error', function(e) {
            console.error('Error en video nativo:', e);
            var errorCode = video.error ? video.error.code : 'unknown';
            console.log('Código de error:', errorCode);
            
            // Intentar diferentes estrategias según el error
            if (errorCode === 4 || errorCode === 'MEDIA_ELEMENT_ERROR') {
                // Error de formato no soportado, intentar con HLS.js
                console.log('Formato no soportado, intentando con HLS.js...');
                tryHLSJS();
            } else {
                // Otros errores, fallback a JWPlayer
                fallbackToJWPlayer();
            }
        });

        // Si es Safari en iOS, intentar usar HLS.js como fallback
        if (isSafariIOS()) {
            console.log('Safari iOS detectado, cargando HLS.js...');
            tryHLSJS();
        }

    } else {
        // Para Android y otros dispositivos, usar JWPlayer
        console.log('Dispositivo no-iOS detectado, usando JWPlayer');
        setupJWPlayer();
    }

    // Función para intentar con HLS.js
    function tryHLSJS() {
        // Cargar HLS.js dinámicamente
        var hlsScript = document.createElement('script');
        hlsScript.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
        hlsScript.onload = function() {
            if (Hls.isSupported()) {
                console.log('HLS.js soportado, configurando...');
                var video = document.getElementById('nativeVideo');
                var hls = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 90,
                    maxBufferLength: 30,
                    maxMaxBufferLength: 600,
                    maxBufferSize: 60 * 1000 * 1000,
                    maxBufferHole: 0.5,
                    highBufferWatchdogPeriod: 2,
                    nudgeOffset: 0.2,
                    nudgeMaxRetry: 5,
                    maxFragLookUpTolerance: 0.25,
                    liveSyncDurationCount: 3,
                    liveMaxLatencyDurationCount: 10
                });
                
                hls.loadSource(streamUrl);
                hls.attachMedia(video);
                
                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                    console.log('HLS.js manifest parseado, intentando reproducir...');
                    video.play().catch(function(e) {
                        console.log('HLS.js autoplay falló:', e);
                        showManualPlayButton();
                    });
                });
                
                hls.on(Hls.Events.ERROR, function(event, data) {
                    console.error('HLS.js error:', data);
                    if (data.fatal) {
                        console.log('Error fatal en HLS.js, cambiando a JWPlayer...');
                        fallbackToJWPlayer();
                    }
                });
            } else {
                console.log('HLS.js no soportado, cambiando a JWPlayer...');
                fallbackToJWPlayer();
            }
        };
        
        hlsScript.onerror = function() {
            console.log('Error cargando HLS.js, cambiando a JWPlayer...');
            fallbackToJWPlayer();
        };
        
        document.head.appendChild(hlsScript);
    }

    // Función para mostrar botón de play manual
    function showManualPlayButton() {
        var videoElement = document.getElementById('livevideo');
        if (videoElement.querySelector('.ios-video-controls')) return; // Ya existe
        
        var controls = document.createElement('div');
        controls.className = 'ios-video-controls';
        controls.innerHTML = `
            <button class="ios-play-button" onclick="playVideo()">
                <i class="fas fa-play"></i>
            </button>
        `;
        videoElement.appendChild(controls);
    }

    // Función global para reproducir video
    function playVideo() {
        var video = document.getElementById('nativeVideo') || document.getElementById('fallbackVideo');
        if (video) {
            video.play().catch(function(e) {
                console.log('Play manual falló:', e);
            });
        }
        // Remover controles manuales
        var controls = document.querySelector('.ios-video-controls');
        if (controls) controls.remove();
    }

    // Función de fallback a JWPlayer
    function fallbackToJWPlayer() {
        console.log('Cambiando a JWPlayer...');
        var videoElement = document.getElementById('livevideo');
        videoElement.innerHTML = '<div id="jwplayerContainer"></div>';
        
        // Configurar JWPlayer
        jwplayer.key = "";
        jwplayer("jwplayerContainer").setup({
            file: streamUrl,
            image: posterImage,
            width: "100%",
            aspectratio: "16:9",
            autostart: true,
            mute: false,
            stretching: "fill",
            hlshtml: true,
            primary: "html5",
            fallback: true,
            // Configuraciones específicas para iOS
            preload: "metadata",
            ga: {},
            // Configuraciones específicas para HLS
            hls: {
                lowLatencyMode: true,
                backBufferLength: 90
            }
        });
    }

    // Configuración estándar de JWPlayer para dispositivos no-iOS
    function setupJWPlayer() {
        jwplayer.key = "";
        jwplayer("livevideo").setup({
            file: streamUrl,
            image: posterImage,
            width: "100%",
            aspectratio: "16:9",
            autostart: true,
            mute: false,
            stretching: "fill",
            hlshtml: true,
            primary: "html5",
            fallback: true,
            // Configuraciones adicionales para mejor compatibilidad
            preload: "metadata",
            ga: {},
            // Configuraciones específicas para HLS
            hls: {
                lowLatencyMode: true,
                backBufferLength: 90
            }
        });

        // Manejar errores de JWPlayer
        jwplayer("livevideo").on('error', function(e) {
            console.error('JWPlayer error:', e);
            // Si hay error en JWPlayer, intentar con video nativo
            if (e.code === 101104 || e.code === 101104) {
                console.log('Error 101104 detectado, intentando con video nativo...');
                var videoElement = document.getElementById('livevideo');
                videoElement.innerHTML = `
                    <video 
                        id="fallbackVideo" 
                        controls 
                        autoplay 
                        muted 
                        playsinline 
                        webkit-playsinline
                        style="width: 100%; height: 100%; background: #000;"
                        poster="${posterImage}"
                    >
                        <source src="${streamUrl}" type="application/x-mpegURL">
                        Tu navegador no soporta el elemento de video.
                    </video>
                `;
                
                var fallbackVideo = document.getElementById('fallbackVideo');
                fallbackVideo.play().catch(function(error) {
                    console.log('Fallback video autoplay falló:', error);
                });
            }
        });
    }

    // Si no hay info, buscar en Wikipedia y mostrar solo el primer párrafo
    <?php if(!$info): ?>
    document.addEventListener("DOMContentLoaded", function() {
        var canal = <?php echo json_encode($canal); ?>;
        var loading = document.getElementById("infoCanalLoading");
        // Buscar el título exacto primero
        fetch("https://es.wikipedia.org/w/api.php?action=query&prop=extracts&explaintext=1&format=json&titles=" + encodeURIComponent(canal) + "&origin=*")
            .then(resp => resp.json())
            .then(data => {
                let pages = data.query.pages;
                let found = false;
                for (let key in pages) {
                    if (pages[key].extract && pages[key].extract.trim().length > 0) {
                        // Solo el primer párrafo
                        let parrafo = pages[key].extract.split('\n').find(p => p.trim().length > 0);
                        loading.innerText = parrafo ? parrafo : pages[key].extract;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    // Si no hay extracto exacto, buscar por término
                    fetch("https://es.wikipedia.org/w/api.php?action=query&list=search&srsearch=" + encodeURIComponent(canal + " canal tv") + "&utf8=&format=json&origin=*")
                        .then(resp => resp.json())
                        .then(data2 => {
                            if(data2.query && data2.query.search && data2.query.search.length > 0) {
                                var pageTitle = data2.query.search[0].title;
                                // Buscar extracto completo del primer resultado
                                fetch("https://es.wikipedia.org/w/api.php?action=query&prop=extracts&explaintext=1&format=json&titles=" + encodeURIComponent(pageTitle) + "&origin=*")
                                    .then(resp => resp.json())
                                    .then(data3 => {
                                        let pages2 = data3.query.pages;
                                        let found2 = false;
                                        for (let key2 in pages2) {
                                            if (pages2[key2].extract && pages2[key2].extract.trim().length > 0) {
                                                let parrafo2 = pages2[key2].extract.split('\n').find(p => p.trim().length > 0);
                                                loading.innerText = parrafo2 ? parrafo2 : pages2[key2].extract;
                                                found2 = true;
                                                break;
                                            }
                                        }
                                        if (!found2) {
                                            loading.innerText = "No se encontró información en internet.";
                                        }
                                    })
                                    .catch(() => {
                                        loading.innerText = "No se encontró información en internet.";
                                    });
                            } else {
                                loading.innerText = "No se encontró información en internet.";
                            }
                        })
                        .catch(() => {
                            loading.innerText = "No se encontró información en internet.";
                        });
                }
            })
            .catch(() => {
                loading.innerText = "No se encontró información en internet.";
            });
    });
    <?php endif; ?>
    </script>
</body>
</html>
