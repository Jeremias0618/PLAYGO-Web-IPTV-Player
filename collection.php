<?php
require_once("libs/lib.php");
require_once("libs/controllers/Collection.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);
$saga_id = isset($_GET['saga']) ? $_GET['saga'] : '';

$page_start = microtime(true);
$collection_data = getCollectionData($saga_id, $user, $pwd);
$page_generation_time = (microtime(true) - $page_start) * 1000;

if (!$collection_data) {
    header("Location: sagas.php");
    exit;
}

$saga_actual = $collection_data['saga'];
$peliculas_pagina = $collection_data['peliculas'];
$backdrop_fondo = $collection_data['backdrop_fondo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/vendors/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/vendors/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/vendors/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/vendors/ionicons.min.css">
    <link rel="stylesheet" href="./styles/vendors/photoswipe.css">
    <link rel="stylesheet" href="./styles/vendors/glightbox.css">
    <link rel="stylesheet" href="./styles/vendors/default-skin.css">
    <link rel="stylesheet" href="./styles/vendors/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/vendors/select2.min.css">
    <link rel="stylesheet" href="./styles/core/main.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/collection/layout.css">
    <link rel="stylesheet" href="./styles/collection/items.css">
    <link rel="stylesheet" href="./styles/collection/search.css">
    <link rel="stylesheet" href="./styles/collection/pagination.css">
    <link rel="stylesheet" href="./styles/collection/mobile.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($saga_actual['nombre']); ?></title>
<?php if($backdrop_fondo): ?>
<style>
body {
    background: url('<?php echo htmlspecialchars($backdrop_fondo, ENT_QUOTES); ?>') no-repeat center center fixed !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}
body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    background: rgba(0,0,0,0.75);
    pointer-events: none;
}
</style>
<?php endif; ?>
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
                                <a href="./channels.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="movies.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link">Series</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="sagas.php" class="header__nav-link header__nav-link--active">Sagas</a>
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
<?php include_once __DIR__ . '/libs/views/search.php'; ?>
<div style="width:100%;text-align:center;margin:120px 0 15px 0;">
    <h2 style="font-size:2.5rem;font-weight:800;letter-spacing:2px;color:#fff;display:inline-block;padding:10px 40px;border-radius:12px;"><?php echo htmlspecialchars($saga_actual['nombre']); ?></h2>
</div>
<section class="section details">
    <div class="container" style="margin-top: 0;">
        <div class="row">
<?php
if ($peliculas_pagina && is_array($peliculas_pagina)) {
    foreach($peliculas_pagina as $index) {
        $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $index['name']);
        $filme_type = $index['stream_type'];
        $filme_id = $index['stream_id'];
        $filme_img = $index['stream_icon'];
        $filme_rat = isset($index['rating']) ? $index['rating'] : '';
        $filme_ano = isset($index['year']) ? $index['year'] : '';
        $filme_duration = isset($index['duration']) ? $index['duration'] : '';
        $filme_country = isset($index['country']) ? $index['country'] : '';
        $filme_plot = isset($index['plot']) ? $index['plot'] : '';
        $filme_genre = isset($index['genre']) ? $index['genre'] : '';
        $filme_cast = isset($index['cast']) ? $index['cast'] : '';
?>
            <div class="col-12">
                <div class="collection-list-item">
                    <div class="row">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <a href="<?php echo ($filme_type === 'series' || $filme_type === 'serie') ? 'serie.php' : 'movie.php'; ?>?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo ($filme_type === 'series' || $filme_type === 'serie') ? 'serie' : 'movie'; ?>">
                                <img loading="lazy" src="<?php echo htmlspecialchars($filme_img); ?>" alt="<?php echo htmlspecialchars($filme_nome); ?>" class="collection-poster">
                            </a>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <h2 class="collection-info-title">
                                <a href="<?php echo ($filme_type === 'series' || $filme_type === 'serie') ? 'serie.php' : 'movie.php'; ?>?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo ($filme_type === 'series' || $filme_type === 'serie') ? 'serie' : 'movie'; ?>" style="color: #fff; text-decoration: none;">
                                    <?php echo htmlspecialchars($filme_nome); ?>
                                </a>
                            </h2>
                            <?php if (!empty($filme_genre)): ?>
                            <ul class="collection-info-genres">
<?php
                                $genres = is_array($filme_genre) ? $filme_genre : explode(',', $filme_genre);
                                foreach($genres as $g): 
                                    $genre_trimmed = trim($g);
                                    if (!empty($genre_trimmed)):
                                ?>
                                    <li><?php echo htmlspecialchars($genre_trimmed); ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                            <?php endif; ?>
                            <div style="margin-bottom: 16px;">
                                <span style="color: #fff; font-size: 1.1rem;">
                                    <?php echo $filme_ano; ?>
                                    <?php if (!empty($filme_rat)): ?>
                                        &nbsp; <i class="fa-solid fa-star" style="background: -webkit-linear-gradient(0deg, #831f5e 0%, #f50b60 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i> <?php echo $filme_rat; ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <ul class="collection-info-meta">
                                <?php if (!empty($filme_duration)): 
                                    $duracao_formatted = $filme_duration;
                                    if (is_numeric($filme_duration)) {
                                        $hours = floor($filme_duration / 3600);
                                        $minutes = floor(($filme_duration % 3600) / 60);
                                        $seconds = $filme_duration % 60;
                                        if ($hours > 0) {
                                            $duracao_formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                        } else {
                                            $duracao_formatted = sprintf("%02d:%02d", $minutes, $seconds);
                                        }
                                    }
                                ?>
                                <li><strong style="background: -webkit-linear-gradient(0deg, #831f5e 0%, #f50b60 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Duración:</strong> <?php echo htmlspecialchars($duracao_formatted); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($filme_country)): ?>
                                <li><strong style="background: -webkit-linear-gradient(0deg, #831f5e 0%, #f50b60 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">País:</strong> <?php echo htmlspecialchars($filme_country); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($filme_cast)): ?>
                                <li><strong style="background: -webkit-linear-gradient(0deg, #831f5e 0%, #f50b60 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Reparto:</strong> <?php echo htmlspecialchars($filme_cast); ?></li>
                                <?php endif; ?>
                            </ul>
                            <?php if (!empty($filme_plot)): ?>
                            <div class="collection-info-plot">
                                <div class="plot-text-wrapper">
                                    <div class="plot-text" data-item-id="<?php echo $filme_id; ?>"><?php echo htmlspecialchars($filme_plot); ?></div>
                                    <button type="button" class="plot-toggle-btn" data-item-id="<?php echo $filme_id; ?>" style="display: none;">
                                        <span class="plot-toggle-text">Ver más</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
                    <?php
                    }
                    } else {
    echo '<div class="col-12" style="color:#fff;font-size:1.2rem;text-align:center;">No hay películas disponibles en esta saga.</div>';
                    }
                    ?>
        </div>
    </div>
</section>
<footer class="footer">
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
<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap.bundle.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js"></script>
<script src="./scripts/vendors/jquery.mousewheel.min.js"></script>
<script src="./scripts/vendors/jquery.mcustomscrollbar.min.js"></script>
<script src="./scripts/vendors/wnumb.js"></script>
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
<script src="./scripts/collection/init.js"></script>
<script>
(function() {
    function initPlotTruncation() {
        const plotWrappers = document.querySelectorAll('.collection-info-plot .plot-text-wrapper');
        
        plotWrappers.forEach(function(wrapper) {
            const plotText = wrapper.querySelector('.plot-text');
            const plotToggleBtn = wrapper.querySelector('.plot-toggle-btn');
            const posterImg = wrapper.closest('.collection-list-item').querySelector('.collection-poster');
            
            if (!plotText || !plotToggleBtn || !posterImg) {
                return;
            }
            
            function calculateMaxHeight(includeToggleBtn) {
                const posterHeight = posterImg.offsetHeight;
                const listItem = wrapper.closest('.collection-list-item');
                const infoSection = listItem.querySelector('.col-sm-8, .col-md-9, .col-lg-9');
                
                if (!infoSection || posterHeight === 0) {
                    return null;
                }
                
                let usedHeight = 0;
                
                const title = infoSection.querySelector('.collection-info-title');
                if (title) {
                    usedHeight += title.offsetHeight;
                    const titleMargin = window.getComputedStyle(title).marginBottom;
                    usedHeight += parseInt(titleMargin) || 0;
                }
                
                const genres = infoSection.querySelector('.collection-info-genres');
                if (genres) {
                    usedHeight += genres.offsetHeight;
                    const genresMargin = window.getComputedStyle(genres).marginBottom;
                    usedHeight += parseInt(genresMargin) || 0;
                }
                
                const yearRating = infoSection.querySelector('div[style*="margin-bottom: 16px"]');
                if (yearRating) {
                    usedHeight += yearRating.offsetHeight;
                    const yearMargin = window.getComputedStyle(yearRating).marginBottom;
                    usedHeight += parseInt(yearMargin) || 0;
                }
                
                const meta = infoSection.querySelector('.collection-info-meta');
                if (meta) {
                    usedHeight += meta.offsetHeight;
                    const metaMargin = window.getComputedStyle(meta).marginBottom;
                    usedHeight += parseInt(metaMargin) || 0;
                }
                
                const plotMargin = window.getComputedStyle(wrapper.closest('.collection-info-plot')).marginTop;
                usedHeight += parseInt(plotMargin) || 0;
                
                const toggleBtnHeight = includeToggleBtn ? 40 : 0;
                const padding = 10;
                
                const availableHeight = posterHeight - usedHeight - toggleBtnHeight - padding;
                
                return Math.max(50, availableHeight);
            }
            
            function checkTextHeight() {
                plotText.style.maxHeight = '';
                plotText.style.overflow = '';
                plotText.classList.remove('collapsed', 'expanded');
                
                const maxHeightWithoutBtn = calculateMaxHeight(false);
                
                if (maxHeightWithoutBtn === null || maxHeightWithoutBtn <= 0) {
                    plotText.classList.remove('collapsed');
                    plotText.style.maxHeight = '';
                    plotText.style.overflow = '';
                    plotToggleBtn.style.display = 'none';
                    return;
                }
                
                const originalHeight = plotText.scrollHeight;
                
                if (originalHeight <= maxHeightWithoutBtn) {
                    plotToggleBtn.style.display = 'none';
                    plotText.classList.remove('collapsed');
                    plotText.style.maxHeight = '';
                    plotText.style.overflow = '';
                } else {
                    const maxHeightWithBtn = calculateMaxHeight(true);
                    if (maxHeightWithBtn !== null && maxHeightWithBtn > 0) {
                        plotToggleBtn.style.display = 'flex';
                        plotText.style.maxHeight = maxHeightWithBtn + 'px';
                        plotText.style.overflow = 'hidden';
                        plotText.classList.add('collapsed');
                    } else {
                        plotToggleBtn.style.display = 'none';
                        plotText.classList.remove('collapsed');
                        plotText.style.maxHeight = '';
                        plotText.style.overflow = '';
                    }
                }
            }
            
            plotToggleBtn.addEventListener('click', function() {
                const toggleText = plotToggleBtn.querySelector('.plot-toggle-text');
                if (plotText.classList.contains('collapsed')) {
                    plotText.style.maxHeight = '';
                    plotText.style.overflow = '';
                    plotText.classList.remove('collapsed');
                    plotText.classList.add('expanded');
                    plotToggleBtn.classList.add('expanded');
                    if (toggleText) toggleText.textContent = 'Ver menos';
                } else {
                    const maxHeightWithBtn = calculateMaxHeight(true);
                    if (maxHeightWithBtn !== null && maxHeightWithBtn > 0) {
                        plotText.style.maxHeight = maxHeightWithBtn + 'px';
                        plotText.style.overflow = 'hidden';
                    }
                    plotText.classList.remove('expanded');
                    plotText.classList.add('collapsed');
                    plotToggleBtn.classList.remove('expanded');
                    if (toggleText) toggleText.textContent = 'Ver más';
                }
            });
            
            function initItem() {
                if (posterImg.complete && posterImg.offsetHeight > 0) {
                    checkTextHeight();
                } else {
                    posterImg.addEventListener('load', function() {
                        setTimeout(checkTextHeight, 50);
                    });
                    setTimeout(checkTextHeight, 100);
                }
            }
            
            initItem();
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPlotTruncation);
    } else {
        initPlotTruncation();
    }
    
    window.addEventListener('resize', function() {
        setTimeout(initPlotTruncation, 100);
    });
})();
</script>
</body>
</html>
