<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);

$sagasFile = __DIR__ . '/storage/sagas.json';
$sagas = [];

if (file_exists($sagasFile)) {
    $content = file_get_contents($sagasFile);
    $sagasData = json_decode($content, true) ?: [];
    
    foreach ($sagasData as $saga) {
        $sagas[] = [
            'id' => $saga['id'] ?? '',
            'nombre' => $saga['title'] ?? '',
            'imagen' => $saga['image'] ?? '',
            'items_count' => isset($saga['items']) && is_array($saga['items']) ? count($saga['items']) : 0
        ];
    }
}

$backdrop_fondo = 'assets/image/wallpaper_03.webp';

function getRandomSagaWallpaper() {
    $wallpapers = [
        'assets/image/wallpaper_02.webp',
        'assets/image/wallpaper_03.webp',
        'assets/image/wallpaper_04.webp',
        'assets/image/wallpaper_05.webp',
        'assets/image/wallpaper_channels.webp'
    ];
    return $wallpapers[array_rand($wallpapers)];
}
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
    <link rel="stylesheet" href="./styles/movies/title.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - Sagas</title>
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
    
    .modal-buscador-bg,
    .modal-buscador-bg * {
        z-index: 999999 !important;
    }
    
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
        padding-top: 90px;
        overflow-x: hidden !important;
    }
    html {
        overflow-x: hidden !important;
    }
        .card__cover img {
    width: 100%;
    height: 440px;
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
    .saga-card {
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    .saga-card:hover {
        transform: translateY(-8px);
    }
    .saga-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .saga-card-cover {
        position: relative;
        width: 100%;
        height: 440px;
        border-radius: 10px;
        overflow: hidden;
        background: #232027;
        margin-bottom: 16px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
    }
    .saga-card:hover .saga-card-cover {
        box-shadow: 0 8px 24px rgba(229,9,20,0.4);
        border: 2px solid #e50914;
    }
    .saga-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .saga-card-title {
        color: #fff;
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        padding: 0 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .saga-card-count {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
        margin-top: 8px;
        padding: 0 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .saga-card-count i {
        font-size: 0.9rem;
        background: linear-gradient(90deg, #831f5e 0%, #f50b60 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    @media (max-width: 600px) {
        .saga-card-cover {
            height: 260px !important;
        }
        .saga-card-title {
            font-size: 1.1rem;
        }
        .saga-card-count {
            font-size: 0.85rem;
        }
    }

            @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-right: auto !important;
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

<div class="movies-page-title">
    <h2>SAGAS</h2>
</div>
<div class="catalog details">
    <div class="container">
        <div class="row">
<?php
if (!empty($sagas) && is_array($sagas)) {
    foreach($sagas as $saga) {
        $saga_id = $saga['id'];
        $saga_nombre = $saga['nombre'];
        $saga_imagen = $saga['imagen'] ?? '';
        $saga_items_count = $saga['items_count'] ?? 0;
        
        if (empty($saga_imagen) || !file_exists($saga_imagen)) {
            $imagen_path = getRandomSagaWallpaper();
        } else {
            $imagen_path = $saga_imagen;
        }
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
        <div class="saga-card">
            <a href="collection.php?saga=<?php echo urlencode($saga_id); ?>">
                <div class="saga-card-cover">
                    <img loading="lazy" src="<?php echo htmlspecialchars($imagen_path); ?>" alt="<?php echo htmlspecialchars($saga_nombre); ?>">
                </div>
                <h3 class="saga-card-title"><?php echo htmlspecialchars($saga_nombre); ?></h3>
                <div class="saga-card-count">
                    <i class="fas fa-film"></i> <?php echo $saga_items_count; ?> <?php echo $saga_items_count === 1 ? 'película' : 'películas'; ?>
                </div>
            </a>
        </div>
    </div>
<?php
    }
}
?>
        </div>
    </div>
</div>
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

</body>
</html>

