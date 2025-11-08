<?php
require_once("libs/lib.php");

// Verifica sesión
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

// IDs de la saga Predator
$saga_predator_ids = [7716, 2693, 2690, 2689, 2692, 2691, 2450, 2449];

// Obtener info de cada película
$peliculas = [];
foreach ($saga_predator_ids as $id) {
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$id";
    $resposta = apixtream($url);
    $output = json_decode($resposta, true);
    if (!empty($output['movie_data'])) {
        $peliculas[] = [
            'id' => $id,
            'nombre' => $output['movie_data']['name'],
            'img' => $output['info']['movie_image'],
            'ano' => isset($output['info']['releasedate']) ? substr($output['info']['releasedate'], 0, 4) : '',
            'nota' => isset($output['info']['rating']) ? $output['info']['rating'] : ''
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SAGA PREDATOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <style>
        body {
            background: url('collection/img/predator.webp') no-repeat center center fixed;
            background-size: cover;
        }
        .predator-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(10,18,24,0.82);
            z-index: 0;
        }
        .header__wrap, .header__content {
            background: #000 !important;
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
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-right: auto !important;
            }
            .header__content {
                padding-left: 4px !important;
            }
        }
        .saga-title {
            color: #ffd700;
            text-shadow: 0 2px 16px #000a;
            font-size: 2.8rem;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 140px;
            margin-bottom: 32px;
            text-align: center;
        }
        .saga-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 32px 24px;
            justify-content: center;
            margin-bottom: 60px;
        }
        .saga-card {
            background: rgba(20,20,20,0.92);
            border-radius: 16px;
            box-shadow: 0 4px 24px #0007;
            overflow: hidden;
            width: 210px;
            transition: transform 0.18s;
            position: relative;
        }
        .saga-card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 32px #000b;
        }
        .saga-card img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            border-bottom: 2px solid #ffd70044;
            background: #232027;
        }
        .saga-card-content {
            padding: 18px 14px 14px 14px;
            text-align: center;
        }
        .saga-card-title {
            color: #fff;
            font-size: 1.12rem;
            font-weight: 600;
            margin-bottom: 8px;
            min-height: 42px;
        }
        .saga-card-meta {
            color: #ffd700;
            font-size: 1rem;
            font-weight: 500;
        }
        .saga-card-link {
            display: block;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .saga-card img { height: 220px; }
            .saga-card { width: 98vw; max-width: 320px; }
        }
        .audio-player {
            display: none;
        }
    </style>
</head>
<body>
<div class="predator-overlay"></div>
<!-- HEADER igual a filmes.php -->
<header class="header">
    <div class="navbar-overlay bg-animate"></div>
    <div class="header__wrap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header__content d-flex align-items-center justify-content-between">
                        <a class="header__logo" href="index.php">
                            <img src="img/logo.png" alt="">
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
<?php include_once __DIR__ . '/partials/search_modal.php'; ?>
<!-- FIN HEADER -->

<main>
    <div class="container">
        <h1 class="saga-title"><i class="fa fa-skull-crossbones"></i> SAGA PREDATOR</h1>
        <div class="saga-grid">
            <?php foreach ($peliculas as $p): ?>
                <a class="saga-card-link" href="filme.php?stream=<?php echo $p['id']; ?>&streamtipo=movie">
                    <div class="saga-card">
                        <img src="<?php echo htmlspecialchars($p['img']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>">
                        <div class="saga-card-content">
                            <div class="saga-card-title"><?php echo htmlspecialchars($p['nombre']); ?></div>
                            <div class="saga-card-meta">
                                <?php if ($p['ano']) echo $p['ano']; ?>
                                <?php if ($p['nota'] !== '') echo ' &nbsp;<i class="fa-solid fa-star"></i> ' . $p['nota']; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<audio id="predatorAudio" class="audio-player" src="collection/audio/predator.mp3" autoplay loop></audio>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        var audio = document.getElementById('predatorAudio');
        if (!audio) return;
        audio.volume = 0.5;
        audio.play().catch(function(){});
        document.body.addEventListener('click', function() {
            if (audio.paused) audio.play().catch(function(){});
        }, {once:true});
    });
</script>
</body>
</html>