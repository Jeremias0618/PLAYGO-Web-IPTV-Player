<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

require_once(__DIR__ . '/libs/controllers/Profile.php');

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$pageData = getProfilePageData($user, $pwd);
$backdrop_fondo = $pageData['backdrop'];
$username = $pageData['username'];
$member_since = $pageData['member_since'];
$last_login = $pageData['last_login'];
$exp_date = $pageData['exp_date'];
$next_renewal = $pageData['next_renewal'];
$total_hours = $pageData['total_hours'];
$movies_watched = $pageData['movies_watched'];
$series_watched = $pageData['series_watched'];
$consecutive_days = $pageData['consecutive_days'];
$recent_history = $pageData['recent_history'];
$favorites = $pageData['favorites'];
$playlists = $pageData['playlists'];
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
    <link rel="stylesheet" href="./styles/vendors/nouislider.min.css">
    <link rel="stylesheet" href="./styles/vendors/ionicons.min.css">
    <link rel="stylesheet" href="./styles/vendors/photoswipe.css">
    <link rel="stylesheet" href="./styles/vendors/glightbox.css">
    <link rel="stylesheet" href="./styles/vendors/default-skin.css">
    <link rel="stylesheet" href="./styles/vendors/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/vendors/select2.min.css">
    <link rel="stylesheet" href="./styles/core/main.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="stylesheet" href="./styles/profile/layout.css">
    <link rel="stylesheet" href="./styles/profile/mobile.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - Perfil</title>
<?php if($backdrop_fondo): ?>
<style>
.main-bg-fondo {
    background: url('<?php echo $backdrop_fondo; ?>') no-repeat center center fixed;
    background-size: cover;
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.main-bg-fondo:before {
    content: "";
    position: fixed;
    z-index: 0;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.78);
    pointer-events: none;
}
.main-bg-fondo > * {
    position: relative;
    z-index: 1;
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

<div class="main-bg-fondo">
    <div class="profile-page-title">
        <h2>PERFIL</h2>
    </div>
    
    <div class="profile-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <section class="profile-header">
                        <div class="avatar">
                            <img src="assets/image/profile.webp" alt="Avatar del usuario">
                        </div>
                        <div class="profile-info">
                            <h1>¡Bienvenido, <?php echo htmlspecialchars($username); ?>!</h1>
                            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>@playgo.pe</p>
                            <p><i class="fas fa-calendar-alt"></i> Miembro Desde: <?php echo htmlspecialchars($member_since); ?></p>
                            <span class="account-status"><i class="fas fa-check-circle"></i> Cuenta Activa</span>
                        </div>
                    </section>
                    
                    <h2 class="section-title"><i class="fas fa-user-circle"></i> Información de Cuenta</h2>
                    <div class="section-grid">
                        <div class="info-card">
                            <h3><i class="fas fa-key"></i> Datos de Acceso</h3>
                            <div class="info-item">
                                <span class="info-label">Usuario:</span>
                                <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Clave:</span>
                                <span class="info-value">******</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Último inicio de sesión:</span>
                                <span class="info-value"><?php echo htmlspecialchars($last_login); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Autenticación 2FA:</span>
                                <span class="info-value">Deshabilitado</span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <h3><i class="fas fa-credit-card"></i> Suscripción</h3>
                            <div class="info-item">
                                <span class="info-label">Plan:</span>
                                <span class="info-value">Estándar</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Pago mensual:</span>
                                <span class="info-value">Ninguno</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de expiración:</span>
                                <span class="info-value"><?php echo htmlspecialchars($exp_date); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Próxima renovación:</span>
                                <span class="info-value"><?php echo htmlspecialchars($next_renewal); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <h3><i class="fas fa-chart-line"></i> Estadísticas</h3>
                            <div class="info-item">
                                <span class="info-label">Total visualizado:</span>
                                <span class="info-value"><?php echo $total_hours; ?> horas</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Películas vistas:</span>
                                <span class="info-value"><?php echo $movies_watched; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Series vistas:</span>
                                <span class="info-value"><?php echo $series_watched; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Días consecutivos:</span>
                                <span class="info-value"><?php echo $consecutive_days; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="history-section-header">
                        <h2 class="section-title"><i class="fas fa-history"></i> Historial Reciente</h2>
                        <div class="history-nav-buttons">
                            <button class="history-nav-btn history-nav-prev" type="button">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <button class="history-nav-btn history-nav-next" type="button">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <?php if (!empty($recent_history)): ?>
                        <div class="history-carousel-wrapper">
                            <div class="owl-carousel history-carousel">
                                <?php foreach ($recent_history as $item): ?>
                                    <?php
                                    $itemType = strtolower($item['type'] ?? '');
                                    $is_series = ($itemType === 'serie' || $itemType === 'series');
                                    $itemUrl = $is_series ? "serie.php?stream=" . htmlspecialchars($item['id']) . "&streamtipo=serie" : "movie.php?stream=" . htmlspecialchars($item['id']) . "&streamtipo=movie";
                                    $itemImg = $item['img'] ?? 'assets/logo/logo.png';
                                    $itemName = htmlspecialchars($item['name'] ?? 'Sin título');
                                    $itemDate = $item['date_formatted'] ?? '';
                                    $itemYear = isset($item['year']) ? substr($item['year'], 0, 4) : '';
                                    $itemRating = isset($item['rating']) ? $item['rating'] : 'N/A';
                                    ?>
                                    <div class="item">
                                        <div class="card card--big">
                                            <div class="card__cover">
                                                <img loading="lazy" src="<?php echo htmlspecialchars($itemImg); ?>" alt="<?php echo $itemName; ?>" onerror="this.src='assets/logo/logo.png'">
                                                <a href="<?php echo $itemUrl; ?>" class="card__play">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            </div>
                                            <div class="card__content">
                                                <h3 class="card__title" style="margin-top:0;">
                                                    <a href="<?php echo $itemUrl; ?>">
                                                        <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $itemName), 30); ?>
                                                    </a>
                                                </h3>
                                                <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                                                    <?php echo htmlspecialchars($itemYear); ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo htmlspecialchars($itemRating); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-history">
                            <p>No hay historial reciente</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="history-section-header">
                        <h2 class="section-title"><i class="fas fa-heart"></i> Mis Favoritos</h2>
                        <div class="history-nav-buttons">
                            <button class="history-nav-btn favorites-nav-prev" type="button">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <button class="history-nav-btn favorites-nav-next" type="button">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                    <?php if (!empty($favorites)): ?>
                        <div class="history-carousel-wrapper">
                            <div class="owl-carousel favorites-carousel">
                                <?php foreach ($favorites as $item): ?>
                                    <?php
                                    $itemType = strtolower($item['type'] ?? '');
                                    $is_series = ($itemType === 'serie' || $itemType === 'series');
                                    $itemUrl = $is_series ? "serie.php?stream=" . htmlspecialchars($item['id']) . "&streamtipo=serie" : "movie.php?stream=" . htmlspecialchars($item['id']) . "&streamtipo=movie";
                                    $itemImg = $item['img'] ?? 'assets/logo/logo.png';
                                    $itemName = htmlspecialchars($item['name'] ?? 'Sin título');
                                    $itemYear = isset($item['year']) ? substr($item['year'], 0, 4) : '';
                                    $itemRating = isset($item['rating']) ? $item['rating'] : 'N/A';
                                    ?>
                                    <div class="item">
                                        <div class="card card--big">
                                            <div class="card__cover">
                                                <img loading="lazy" src="<?php echo htmlspecialchars($itemImg); ?>" alt="<?php echo $itemName; ?>" onerror="this.src='assets/logo/logo.png'">
                                                <a href="<?php echo $itemUrl; ?>" class="card__play">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            </div>
                                            <div class="card__content">
                                                <h3 class="card__title" style="margin-top:0;">
                                                    <a href="<?php echo $itemUrl; ?>">
                                                        <?php echo limitar_texto(preg_replace('/\s*\(\d{4}\)$/', '', $itemName), 30); ?>
                                                    </a>
                                                </h3>
                                                <span class="card__rate" style="display:block;margin-top:4px;margin-bottom:0;font-size:1.05rem;">
                                                    <?php echo htmlspecialchars($itemYear); ?> &nbsp; <i class="fas fa-star" style="color:#FFD700"></i> <?php echo htmlspecialchars($itemRating); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-history">
                            <p>No hay favoritos</p>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="section-title"><i class="fas fa-list"></i> Mis Listas</h2>
                    <?php if (!empty($playlists)): ?>
                        <div class="playlists-grid">
                            <?php foreach ($playlists as $playlistName => $playlistItems): ?>
                                <?php if (is_array($playlistItems) && !empty($playlistItems)): ?>
                                    <?php
                                    $firstItem = $playlistItems[0];
                                    $playlistCover = $firstItem['backdrop'] ?? $firstItem['img'] ?? 'assets/logo/logo.png';
                                    $playlistCount = count($playlistItems);
                                    ?>
                                    <div class="playlist-card">
                                        <a href="playlist.php?name=<?php echo urlencode($playlistName); ?>" class="playlist-link">
                                            <div class="playlist-cover">
                                                <img src="<?php echo htmlspecialchars($playlistCover); ?>" alt="<?php echo htmlspecialchars($playlistName); ?>" onerror="this.src='assets/logo/logo.png'">
                                                <div class="playlist-count-badge">
                                                    <i class="fas fa-list"></i> <?php echo $playlistCount; ?>
                                                </div>
                                            </div>
                                            <div class="playlist-info">
                                                <h3 class="playlist-title"><?php echo htmlspecialchars($playlistName); ?></h3>
                                                <p class="playlist-meta"><?php echo $playlistCount; ?> <?php echo $playlistCount == 1 ? 'elemento' : 'elementos'; ?></p>
                                            </div>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-history">
                            <p>No hay listas creadas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
<script src="./scripts/core/main.js"></script>
<script src="./scripts/profile/init.js"></script>

</body>
</html>

