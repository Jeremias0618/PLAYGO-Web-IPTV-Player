<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$playlistName = isset($_GET['name']) ? urldecode($_GET['name']) : '';

if (empty($playlistName)) {
    header("Location: profile.php");
    exit;
}

$safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user);
$playlistsFile = __DIR__ . '/storage/users/' . $safeUser . '/playlists.json';

$playlistItems = [];
$playlistExists = false;

if (file_exists($playlistsFile)) {
    $playlistsContent = file_get_contents($playlistsFile);
    $playlistsData = json_decode($playlistsContent, true);
    if (is_array($playlistsData) && isset($playlistsData[$playlistName]) && is_array($playlistsData[$playlistName])) {
        $playlistItems = $playlistsData[$playlistName];
        $playlistExists = true;
    }
}

if (!$playlistExists) {
    header("Location: profile.php");
    exit;
}

require_once(__DIR__ . '/libs/services/movies.php');
$movies = getMoviesData($user, $pwd);
$backdrop_fondo = getMovieBackdrop($user, $pwd, $movies);
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
    <link rel="stylesheet" href="./styles/playlist/layout.css">
    <link rel="stylesheet" href="./styles/playlist/mobile.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($playlistName); ?></title>
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
    <div class="playlist-page-title">
        <h2><?php echo htmlspecialchars($playlistName); ?></h2>
    </div>
    
    <div class="catalog details">
        <div class="container">
            <div class="row">
<?php
if (!empty($playlistItems)) {
    foreach($playlistItems as $item) {
        $itemType = strtolower($item['type'] ?? '');
        $is_series = ($itemType === 'serie' || $itemType === 'series');
        $itemUrl = $is_series ? "serie.php?stream=" . htmlspecialchars($item['id']) . "&streamtipo=serie" : "movie.php?stream=" . htmlspecialchars($item['id']) . "&streamtipo=movie";
        $itemImg = $item['img'] ?? $item['backdrop'] ?? 'assets/logo/logo.png';
        $itemName = htmlspecialchars($item['name'] ?? 'Sin título');
        $itemYear = isset($item['year']) ? substr($item['year'], 0, 4) : '';
        $itemRating = isset($item['rating']) ? $item['rating'] : 'N/A';
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
        <div class="card">
            <div class="card__cover">
                <img loading="lazy" src="<?php echo htmlspecialchars($itemImg); ?>" alt="<?php echo $itemName; ?>" onerror="this.src='assets/logo/logo.png'">
                <a href="<?php echo $itemUrl; ?>" class="card__play">
                    <i class="fa-solid fa-circle-play"></i>
                </a>
            </div>
            <div class="card__content">
                <h3 class="card__title">
                    <a href="<?php echo $itemUrl; ?>">
                        <?php
                        $titulo_sin_anio = preg_replace('/\s*\(\d{4}\)$/', '', $itemName);
                        echo limitar_texto($titulo_sin_anio, 40);
                        ?>
                    </a>
                </h3>
                <span class="card__rate"><?php echo htmlspecialchars($itemYear); ?> &nbsp; <i class="fa-solid fa-star"></i><?php echo htmlspecialchars($itemRating); ?></span>
            </div>
        </div>
    </div>
<?php
    }
} else {
    echo '<div class="col-12" style="color:#fff;font-size:1.2rem;text-align:center;padding:40px;">No hay elementos en esta lista.</div>';
}
?>
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

</body>
</html>

