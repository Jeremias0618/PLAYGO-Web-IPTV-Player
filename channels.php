<?php
require_once("libs/lib.php");
require_once("libs/controllers/Channels.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$categoria = isset($_REQUEST['catg']) ? urldecode($_REQUEST['catg']) : 'TV en Vivo';
$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';

$pageData = getChannelsPageData($user, $pwd, $id ? $id : null);
$liveCategories = $pageData['categories'];
$streams = $pageData['streams'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - TV en Vivo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <link rel="stylesheet" href="./styles/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/nouislider.min.css">
    <link rel="stylesheet" href="./styles/ionicons.min.css">
    <link rel="stylesheet" href="./styles/photoswipe.css">
    <link rel="stylesheet" href="./styles/glightbox.css">
    <link rel="stylesheet" href="./styles/default-skin.css">
    <link rel="stylesheet" href="./styles/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/select2.min.css">
    <link rel="stylesheet" href="./styles/listings.css">
    <link rel="stylesheet" href="./styles/main.css">
    <link rel="stylesheet" href="./styles/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/channels/channels-layout.css">
    <link rel="stylesheet" href="./styles/channels/channels-cards.css">
    <link rel="stylesheet" href="./styles/channels/channels-sidebar.css">
    <link rel="stylesheet" href="./styles/channels/channels-mobile.css">
    <link rel="stylesheet" href="./styles/channels/channels-search-modal.css">
    <link rel="stylesheet" href="./styles/channels/channels-redesign.css">
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
                                <img src="assets/logo/logo.png" alt="">
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

    <section class="content channels-section details" style="margin-top:30px; position: relative;">
        <div class="details__bg" data-bg="./assets/image/wallpaper_02.webp"></div>
        <div class="container" style="position: relative; z-index: 1;">
            <div class="row">
                <div class="col-12">
                    <h1 class="channels-page-title">
                        TELEVISIÓN EN VIVO
                    </h1>
                    <div class="channels-categories-row">
                        <a href="channels.php?catg=TV%20en%20Vivo" class="category-btn <?php echo $id=='' ? 'active' : ''; ?>">
                            <i class="fa fa-th"></i> Todos
                        </a>
                        <?php foreach ($liveCategories as $cat): ?>
                            <?php
                                $idcatcanal = $cat['category_id'];
                                $catgcanal = $cat['category_name'];
                            ?>
                            <a href="channels.php?id=<?php echo $idcatcanal; ?>&catg=<?php echo urlencode($catgcanal); ?>" 
                               class="category-btn <?php echo $id==$idcatcanal ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($catgcanal); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mobile-categorias-filter" id="mobileCategoriasFilter">
                        <button id="mobileCategoriasBtn" style="background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);">
                            <i class="fa fa-list"></i> Categorías
                        </button>
                        <span id="mobileCategoriasClear" title="Limpiar selección">&times;</span>
                        <div class="mobile-categorias-dropdown" id="mobileCategoriasDropdown"></div>
                    </div>
                    <div class="canales-grid">
                        <?php
                        if (!empty($streams)) {
                            foreach($streams as $channel) {
                                $canal_nome = $channel['name'];
                                $canal_type = $channel['type'];
                                $canal_id = $channel['id'];
                                $canal_img = $channel['logo'];
                                $cat_id = $channel['category_id'];
                                $desc = $channel['description'];
                        ?>
                        <div class="canal-card" data-cat="<?php echo $cat_id; ?>">
                            <div class="canal-logo-container">
                                <img src="<?php echo htmlspecialchars($canal_img); ?>" alt="<?php echo htmlspecialchars($canal_nome); ?>" onerror="this.src='assets/logo/logo.png'">
                            </div>
                            <div class="canal-name"><?php echo htmlspecialchars($canal_nome); ?></div>
                            <a class="canal-watch-btn" href="channel.php?stream=<?php echo $canal_id; ?>">
                                <i class="fa fa-play"></i> Ver canal
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
            </div>
        </div>
    </section>

    <footer class="footer" style="margin-top:40px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="footer__copyright">
                        &copy; <?php echo date('Y'); ?> <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="assets/logo/logo.png"> PLAYGO
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="./scripts/jquery-3.5.1.min.js"></script>
    <script src="./scripts/bootstrap.bundle.min.js"></script>
    <script src="./scripts/owl.carousel.min.js"></script>
    <script src="./scripts/jquery.mousewheel.min.js"></script>
    <script src="./scripts/jquery.mcustomscrollbar.min.js"></script>
    <script src="./scripts/wnumb.js"></script>
    <script src="./scripts/nouislider.min.js"></script>
    <script src="./scripts/jquery.morelines.min.js"></script>
    <script src="./scripts/photoswipe.min.js"></script>
    <script src="./scripts/photoswipe-ui-default.min.js"></script>
    <script src="./scripts/glightbox.min.js"></script>
    <script src="./scripts/jBox.all.min.js"></script>
    <script src="./scripts/select2.min.js"></script>
    <script src="./scripts/jwplayer.js"></script>
    <script src="./scripts/jwplayer.core.controls.js"></script>
    <script src="./scripts/provider.hlsjs.js"></script>
    <script src="./scripts/main.js"></script>
    <script>
    const categoriasCanales = <?php echo json_encode(array_map(function($c){
        return [
            'id' => $c['category_id'],
            'nombre' => $c['category_name']
        ];
    }, $liveCategories), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="./scripts/channels/filter.js"></script>
    <script src="./scripts/channels/mobile.js"></script>
    <script src="./scripts/channels/navigation.js"></script>
    <script src="./scripts/channels/init.js"></script>
</body>
</html>

