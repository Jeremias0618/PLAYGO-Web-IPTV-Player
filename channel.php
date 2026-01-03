<?php
require_once("libs/lib.php");
require_once(__DIR__ . '/libs/controllers/Channel.php');

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

if (!function_exists('limitar_texto')) {
    require_once(__DIR__ . '/libs/lib.php');
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$id = isset($_GET['stream']) ? trim($_GET['stream']) : '';
$idcatg = isset($_GET['catg']) ? trim($_GET['catg']) : '';

$canal_data = getChannelData($user, $pwd, $id);

if(!$canal_data) {
    die('<div style="color:#fff;background:#e50914;padding:30px;text-align:center;font-size:1.3rem;">Canal no encontrado.</div>');
}

$canal = $canal_data['name'];
$defaultIcon = isset($canal_data['stream_icon']) ? $canal_data['stream_icon'] : '';
require_once(__DIR__ . '/libs/services/live.php');
$img = getChannelLogo($id, $defaultIcon);
$tipo = $canal_data['stream_type'];
$info = getChannelInfo($canal_data);
$epgListings = getChannelEPG($user, $pwd, $id);
$recommendedChannels = getRecommendedChannels($user, $pwd, $id, $idcatg, 6);
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
    <link rel="stylesheet" href="./styles/channel/layout.css">
    <link rel="stylesheet" href="./styles/channel/details.css">
    <link rel="stylesheet" href="./styles/channel/video.css">
    <link rel="stylesheet" href="./styles/channel/recommended.css">
    <link rel="stylesheet" href="./styles/channel/mobile.css">
    <style>
        .details__bg {
            background-image: url('<?php echo htmlspecialchars($img, ENT_QUOTES); ?>');
        }
    </style>
</head>
<body class="body">
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

<section class="section details" style="position:relative;">
    <div class="details__bg"></div>
    <div class="container" style="position:relative; z-index:1;">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-12">
                <div class="card card--details">
                    <div class="row">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3">
                            <div class="canal-logo-box">
                                <div class="canal-logo-box-content">
                                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($canal); ?>">
                                    <div class="canal-nombre"><?php echo htmlspecialchars($canal); ?></div>
                                    <div class="canal-logo-box-buttons">
                                        <div class="en-vivo-label"><i class="fa fa-circle"></i> EN VIVO</div>
                                        <div class="volver-canais-btn">
                                            <a href="channels.php"><i class="fa fa-arrow-left"></i> Volver a canales</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="canal-logo-box-mobile">
                                    <div class="canal-mobile-row canal-mobile-row-top">
                                        <img class="canal-mobile-logo" src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($canal); ?>">
                                        <div class="canal-nombre"><?php echo htmlspecialchars($canal); ?></div>
                                        <div class="en-vivo-label"><i class="fa fa-circle"></i> EN VIVO</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 d-flex flex-column align-items-center justify-content-center">
                            <div class="video-embed-box">
                                <div id="livevideo"></div>
                            </div>
                        </div>
                    </div>
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
                            <div class="epg-table-box">
                                <div class="epg-title"><i class="fa fa-calendar-alt"></i> Guía EPG</div>
                                <table class="epg-table">
                                    <tbody>
                                    <?php
                                if(!empty($epgListings)) {
                                    foreach($epgListings as $index) {
                                            $titulo = base64_decode($index['title']);
                                        $inicio = limpiarFechaEPG(ds($index['start']));
                                        $fin = limpiarFechaEPG(ds($index['end']));
                                            $descripcion = base64_decode($index['description']);
                                            echo '<tr>';
                                        echo '<td style="width:30%;"><b>' . htmlspecialchars($titulo) . '</b></td>';
                                        echo '<td style="width:18%;"><i class="fa fa-clock"></i> ' . htmlspecialchars($inicio) . ' - ' . htmlspecialchars($fin) . '</td>';
                                        echo '<td>' . htmlspecialchars($descripcion) . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="3">Sin EPG disponible.</td></tr>';
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <a href="channels.php" class="volver-canales-bottom-btn">
                                <i class="fa fa-arrow-left"></i> Volver a canales
                            </a>
                </div>
            </div>
        </div>
    </div>
</section>

    <section class="content">
        <div class="container" style="margin-top: 30px;">
            <div class="row">
                <div class="col-12">
                    <h2 class="section__title">Canales recomendados</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                <div class="recomendados-grid">
                <?php
                    foreach($recommendedChannels as $row) {
                    $canal_nome = $row['name'];
                    $canal_id = $row['stream_id'];
                    $defaultIcon = isset($row['stream_icon']) ? $row['stream_icon'] : '';
                    $canal_img = getChannelLogo($canal_id, $defaultIcon);
                ?>
                    <div class="canal-card">
                        <div class="canal-logo-container">
                            <img src="<?php echo htmlspecialchars($canal_img); ?>" alt="<?php echo htmlspecialchars($canal_nome); ?>" onerror="this.src='assets/logo/logo.png'">
                    </div>
                        <div class="canal-name"><?php echo htmlspecialchars($canal_nome); ?></div>
                        <a class="canal-watch-btn" href="channel.php?stream=<?php echo $canal_id; ?>&catg=<?php echo $idcatg; ?>">
                            <i class="fa fa-play"></i> Ver canal
                        </a>
                </div>
                <?php } ?>
                    </div>
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
        window.channelPlayerConfig = {
            streamUrl: "<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.m3u8",
            posterImage: "<?php echo $img; ?>"
        };
        
    <?php if(!$info): ?>
        window.channelWikipediaConfig = {
            canal: <?php echo json_encode($canal); ?>
        };
    <?php endif; ?>
    </script>
    <script src="./scripts/channel/player.js"></script>
    <?php if(!$info): ?>
    <script src="./scripts/channel/wikipedia.js"></script>
    <?php endif; ?>
</body>
</html>
