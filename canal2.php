<?php
require_once("libs/lib.php");

// Asegurarnos de que IP use HTTPS en vez de HTTP
if (defined('IP') && strpos(IP, 'http:') === 0) {
    // Redefinir IP con HTTPS
    define('IP_HTTPS', str_replace('http:', 'https:', IP));
} else {
    define('IP_HTTPS', IP);
}

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

// Solo recibimos el id del canal y la categoría (opcional)
$id = isset($_GET['stream']) ? trim($_GET['stream']) : '';
$idcatg = isset($_GET['catg']) ? trim($_GET['catg']) : '';

// Obtenemos los datos del canal por su id
$url = IP_HTTPS."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
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
$img = $canal_data['stream_icon'];
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
            width: 100% !important;
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

        @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            .header__content {
                padding-left: 4px !important;
            }
        }

        
    </style>
    <script src="https://content.jwplatform.com/libraries/KB5zFt7A.js"></script>
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
                            <a class="header__logo" href="index.php">
                                <img src="img/logo.png" alt="" height="48px">
                            </a>
                            <ul class="header__nav d-flex align-items-center mb-0">
                                <li class="header__nav-item">
                                    <a href="./painel.php" class="header__nav-link">Inicio</a>
                                </li>
                                <li class="header__nav-item">
                                    <a href="./canais.php" class="header__nav-link header__nav-link--active">TV en Vivo</a>
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

    <!-- MODAL BUSCADOR igual painel.php -->
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
                                    <a href="canais.php"><i class="fa fa-arrow-left"></i> Volver a canales</a>
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
                                    $url = IP_HTTPS."/player_api.php?username=$user&password=$pwd&action=get_short_epg&stream_id=$id";
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
                $recom_url = IP_HTTPS."/player_api.php?username=$user&password=$pwd&action=get_live_streams".($idcatg ? "&category_id=$idcatg" : "");
                $resposta = apixtream($recom_url);
                $output = json_decode($resposta,true);
                // Filtrar el canal actual
                $output = array_filter($output, function($c) use ($id) { return $c['stream_id'] != $id; });
                shuffle($output);
                foreach(array_slice($output,0,5) as $row) { // SOLO 5 CANALES
                    $canal_nome = $row['name'];
                    $canal_id = $row['stream_id'];
                    $canal_img = $row['stream_icon'];
                ?>
                <div class="recomendado-card">
                    <div class="card__cover" style="position:relative;">
                        <img loading="lazy" src="<?php echo $canal_img; ?>" alt="<?php echo htmlspecialchars($canal_nome); ?>">
                        <a href="canal.php?stream=<?php echo $canal_id; ?>&catg=<?php echo $idcatg; ?>" class="card__play">
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                    <div class="card__content">
                        <h3 class="card__title">
                            <a href="canal.php?stream=<?php echo $canal_id; ?>&catg=<?php echo $idcatg; ?>">
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
                        &copy; 2025 <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="img/logo.png"> MAXGO
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
    <script src="./js/main.js"></script>
    <script src="https://content.jwplatform.com/libraries/KB5zFt7A.js"></script>
    <script>
    // MODAL BUSCADOR igual painel.php
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
    if(modalBuscador) modalBuscador.addEventListener('click', function(e) {
        if (e.target === modalBuscador) hideModalBuscador();
    });

    // Cargar datos para búsqueda (películas, series, canales)
    let peliculas = [];
    let series = [];
    let canales = [];
    <?php
    // Películas
    $url = IP_HTTPS."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
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
    $url = IP_HTTPS."/player_api.php?username=$user&password=$pwd&action=get_series";
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
    $url = IP_HTTPS."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
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
    // JWPlayer 8.7.4 para el canal en vivo
    document.addEventListener("DOMContentLoaded", function() {
        jwplayer.key = "W7zSm81+mmIsg7F+fyHRKhF3ggLkTqtGMhvI92kbqf/ysE99";
        
        // Usar directamente la versión HTTPS de la URL
        let streamUrl = "<?php echo IP_HTTPS; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.m3u8";
        
        jwplayer("livevideo").setup({
            file: streamUrl,
            image: "<?php echo $img; ?>",
            width: "100%",
            aspectratio: "16:9",
            autostart: true,
            mute: false,
            primary: "html5",
            hlshtml: true,
            preload: "auto",
            stretching: "uniform",
            playbackRateControls: [0.5, 1, 1.25, 1.5, 2],
            cast: {},
            skin: {
                name: "netflix"
            }
        }).on('error', function(error) {
            console.error('Error de reproducción:', error);
        }).on('setupError', function(error) {
            console.error('Error de configuración:', error);
        });
    });

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