<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$id = trim($_REQUEST['stream']);

// Obtener info de la serie
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$id";
$resposta = apixtream($url);
$output = json_decode($resposta,true);

$backdrop = '';
if (!empty($output['info']['backdrop_path']) && is_array($output['info']['backdrop_path'])) {
    $backdrop = $output['info']['backdrop_path'][0];
}
$poster_img = $output['info']['cover'];
$serie_nome = preg_replace('/\s*\(\d{4}\)$/', '', $output['info']['name']);
$sinopsis = $output['info']['plot'];
$genero = $output['info']['genre'];
$ano = $output['info']['releaseDate'];
$pais = $output['info']['country'];
$nota = $output['info']['rating'];
$cast = $output['info']['cast'];
$diretor = $output['info']['director'];
$duracao = $output['info']['duration'];
$trailer = $output['info']['youtube_trailer'] ?? '';
$youtube_id = '';
if (!empty($trailer)) {
    if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
        $youtube_id = $trailer;
    } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
        $youtube_id = $matches[1];
    }
}
$episodios = $output['episodes'] ?? [];

$tmdb_id = $output['info']['tmdb_id'] ?? null;
if (!$tmdb_id && !empty($serie_nome)) {
    $query = urlencode($serie_nome);
    $tmdb_search_url = "https://api.themoviedb.org/3/search/tv?api_key=" . TMDB_API_KEY . "&language=es-ES&query=$query";
    if (!empty($ano)) {
        $tmdb_search_url .= "&first_air_date_year=" . urlencode(substr($ano,0,4));
    }
    $tmdb_search_json = @file_get_contents($tmdb_search_url);
    $tmdb_search_data = json_decode($tmdb_search_json, true);
    if (!empty($tmdb_search_data['results'][0]['id'])) {
        $tmdb_id = $tmdb_search_data['results'][0]['id'];
    }
}
$tmdb_episodios_imgs = [];

if ($tmdb_id && is_array($episodios)) {
    $cache_dir = __DIR__ . '/tmdb_cache/';
    if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);

    foreach ($episodios as $season_num => $eps) {
        // Obtener episodios de la temporada desde TMDb
        $tmdb_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season_num?api_key=" . TMDB_API_KEY . "&language=es-ES";
        $tmdb_json = @file_get_contents($tmdb_url);
        $tmdb_data = json_decode($tmdb_json, true);
        if (!empty($tmdb_data['episodes'])) {
            foreach ($tmdb_data['episodes'] as $ep) {
                if (!empty($ep['still_path'])) {
                    $img_url = "https://image.tmdb.org/t/p/w500" . $ep['still_path'];
                    $img_local = $cache_dir . "{$tmdb_id}_{$season_num}_{$ep['episode_number']}.jpg";
                    $img_local_url = "tmdb_cache/{$tmdb_id}_{$season_num}_{$ep['episode_number']}.jpg";
                    // Descargar solo si no existe
                    if (!file_exists($img_local)) {
                        $img_data = @file_get_contents($img_url);
                        if ($img_data) file_put_contents($img_local, $img_data);
                    }
                    // Guardar la ruta local para el HTML
                    if (file_exists($img_local)) {
                        $tmdb_episodios_imgs[$season_num][$ep['episode_number']] = $img_local_url;
                    } else {
                        $tmdb_episodios_imgs[$season_num][$ep['episode_number']] = $img_url;
                    }
                }
            }
        }
    }
}

// Cálculo de totales
$total_temporadas = is_array($episodios) ? count($episodios) : 0;
$total_episodios = 0;
if (is_array($episodios)) {
    foreach ($episodios as $eps) {
        $total_episodios += is_array($eps) ? count($eps) : 0;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MAXGO - <?php echo htmlspecialchars($serie_nome); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" />
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
    <style>
        body {
            background: linear-gradient(180deg,rgba(24,24,24,0.80) 0%,rgba(24,24,24,0.80) 100%), url('<?php echo $backdrop ?: $poster_img; ?>') center center/cover no-repeat;
            color: #fff;
            background-attachment: fixed;
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
        /* --- ESTILOS DE SERIE --- */
        .serie-hero {
            position: relative;
            min-height: 480px;
            padding-top: 150px;
            padding-bottom: 40px;
        }
        .serie-hero .poster {
            width: 220px;
            min-width: 180px;
            max-width: 90vw;
            max-height: 420px;      /* Limita la altura máxima */
            height: auto;           /* Permite que la altura se ajuste automáticamente */
            object-fit: cover;      /* Mantiene la proporción y recorta si es necesario */
            border-radius: 18px;
            box-shadow: 0 8px 32px #000a;
        }
        .serie-hero .info {
            margin-left: 32px;
        }
        .serie-hero .title {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .serie-hero .meta {
            font-size: 1.1rem;
            color: #white;
            margin-bottom: 10px;
        }
        .serie-hero .rate {
            color: #fff; /* Cambia el número a blanco */
            font-weight: 700;
            margin-right: 18px;
        }
        .serie-hero .rate i.fa-star {
            background-image: -webkit-linear-gradient(0deg, #831f5e 0%, #f50b60 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .serie-hero .genres span {
            background: #232027;
            color: #fff;
            border-radius: 6px;
            padding: 2px 10px;
            margin-right: 7px;
            font-size: 0.98rem;
            font-weight: 500;
        }
        .serie-hero .sinopsis {
            margin-top: 18px;
            font-size: 1.13rem;
            color: #eee;
        }
        .serie-hero .btn-trailer {
            margin-top: 18px;
            background: linear-gradient(90deg, #ff0000 60%, #c80000 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 4px 22px;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 2px 8px #0003;
            transition: background 0.2s;
        }
        .serie-hero .btn-trailer:hover {
            background: #fff;
            color: #c80000;
        }
        .season-tabs .nav-link {
            color: #fff;
            background: #232027;
            border-radius: 8px 8px 0 0;
            margin-right: 6px;
            font-weight: 600;
        }
        .season-tabs .nav-link.active {
            background: linear-gradient(90deg,#e50914 60%,#c8008f 100%);
            color: #fff;
        }
        .episode-card {
            background: #232027;
            border-radius: 12px;
            box-shadow: 0 2px 8px #0005;
            margin-bottom: 28px;
            transition: transform 0.15s;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .episode-card:hover {
            transform: translateY(-6px) scale(1.025);
            box-shadow: 0 8px 32px #000a;
        }
        .episode-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            background: #181818;
        }
        .episode-card .card-body {
            padding: 14px 14px 10px 14px;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }
        .episode-card .card-title {
            font-size: 1.08rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }
        .episode-card .card-text {
            font-size: 0.98rem;
            color: #ccc;
            margin-bottom: 8px;
        }
        .episode-card .btn-play {
            background: linear-gradient(90deg,#e50914 60%,#c8008f 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 7px 18px;
            font-size: 1rem;
            font-weight: 600;
            margin-top: auto;
            transition: background 0.2s;
        }
        .episode-card .btn-play:hover {
            background: #fff;
            color: #e50914;
        }
        @media (max-width: 900px) {
            .serie-hero .info { margin-left: 0; margin-top: 24px; }
            .serie-hero { flex-direction: column; align-items: center; }
        }
        @media (max-width: 600px) {
            .serie-hero { padding-top: 40px; min-height: 220px; }
            .serie-hero .poster { width: 120px; }
            .episode-card img { height: 120px; }
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
    }
    .icon-gradient {
        background-image: -webkit-linear-gradient(0deg, #831f5e 0%, #f50b60 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }
    .serie-hero .row.align-items-center {
        align-items: stretch !important;
        display: flex;
    }

    .episode-card .card-text {
        font-size: 0.98rem;
        color: #ccc;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 3; /* Número de líneas que quieres mostrar */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal; /* Asegura que no sea solo una línea */
    }
    #btnFavorito.favorito-active {
        background: linear-gradient(90deg,#ffd700 60%,#e50914 100%) !important;
        color: #232027 !important;
    }
    </style>
</head>
<body>
<!-- HEADER estilo painel.php -->
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
                                <a href="./channels.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="movies.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link header__nav-link--active">Series</a>
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

<!-- HERO SERIE -->
<section class="serie-hero d-flex align-items-center">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-md-auto d-flex justify-content-center">
        <img src="<?php echo $poster_img; ?>" class="poster" alt="">
      </div>
      <div class="col info">
        <div class="title"><?php echo htmlspecialchars($serie_nome); ?></div>
        <div class="meta">
          <?php echo ($ano ? substr($ano, 0, 4) : ''); ?>
          <?php if($nota !== ''): ?>
            <span class="rate"><i class="fa-solid fa-star"></i> <?php echo $nota; ?></span>
          <?php endif; ?>
          <span class="genres">
            <?php foreach (explode(',', $genero) as $g): ?>
              <span><?php echo htmlspecialchars(trim($g)); ?></span>
            <?php endforeach; ?>
          </span>
        </div>
        <div class="meta">
        <i class="fa-solid fa-layer-group icon-gradient"></i> Temporadas: <?php echo $total_temporadas; ?>
        &nbsp;&nbsp;
        <i class="fa-solid fa-clapperboard icon-gradient"></i> Capítulos: <?php echo $total_episodios; ?><br>
        <b>Showrunner:</b> <?php echo $diretor; ?><br>
        <b>Reparto:</b> <?php echo $cast; ?>
        </div>
        <div class="sinopsis"><?php echo $sinopsis; ?></div>
        <div style="display:flex;gap:12px;margin-top:18px;">
            <?php if (!empty($youtube_id)): ?>
<button class="btn d-flex align-items-center" id="btnTrailer"
    style="font-weight:600;font-size:1.1rem;height:44px;background:linear-gradient(90deg,#ff0000 60%,#c80000 100%);color:#fff;border:none;border-radius:8px;padding:4px 22px;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
    <i class="fab fa-youtube" style="font-size:1.4rem;margin-right:8px;"></i> Ver Tráiler
</button>
            <?php endif; ?>
            <button id="btnFavorito"
                class="btn d-flex align-items-center"
                style="background:linear-gradient(90deg,#232027 60%,#444 100%);color:#ffd700;border:none;border-radius:8px;padding:0 22px;font-size:1.1rem;font-weight:600;box-shadow:0 2px 8px #0003;transition:background 0.2s;height:44px;">
                <i class="fa fa-star" style="font-size:1.4rem;margin-right:8px;"></i>
                <span id="favText">Agregar a Favoritos</span>
            </button>
        </div>
    </div>
  </div>
</section>

<!-- TEMPORADAS Y EPISODIOS -->
<div class="container mt-5">
  <h2 class="mb-4" style="font-weight:700;letter-spacing:1px;">Capítulos</h2>
  <?php if ($episodios && is_array($episodios)): ?>
    <!-- Tabs de temporadas -->
    <ul class="nav season-tabs mb-3" id="seasonTab" role="tablist">
      <?php $i=0; foreach ($episodios as $num_temp => $eps): ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link<?php if($i==0) echo ' active'; ?>" data-season="<?php echo $num_temp; ?>" type="button" role="tab">
            Temporada <?php echo htmlspecialchars($num_temp); ?>
          </button>
        </li>
      <?php $i++; endforeach; ?>
    </ul>
    <div class="tab-content" id="seasonTabContent">
      <div class="row" id="episodiosTemporada"></div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">No hay episodios disponibles.</div>
  <?php endif; ?>
</div>

<!-- Modal Trailer -->
<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark">
      <div class="modal-header border-0">
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-0">
        <div class="ratio ratio-16x9">
          <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

// Modal trailer
document.addEventListener('DOMContentLoaded', function() {
    const btnTrailer = document.getElementById('btnTrailer');
    const trailerModal = new bootstrap.Modal(document.getElementById('trailerModal'));
    const trailerIframe = document.getElementById('trailerIframe');
    const youtubeId = "<?php echo $youtube_id; ?>";
    if(btnTrailer && youtubeId) {
        btnTrailer.onclick = function() {
            trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
            trailerModal.show();
        }
    }
    document.getElementById('trailerModal').addEventListener('hidden.bs.modal', function () {
        trailerIframe.src = "";
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFav = document.getElementById('btnFavorito');
    const favText = document.getElementById('favText');
    let isFav = false;

    // Consultar estado inicial
    fetch('db/base.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=fav_check&id=<?php echo $id; ?>&tipo=serie`
    })
    .then(res => res.json())
    .then(data => {
        if (data.is_fav) {
            isFav = true;
            favText.textContent = 'Favorito';
            btnFav.classList.add('favorito-active');
        }
    });

    btnFav.addEventListener('click', function() {
        const action = isFav ? 'fav_remove' : 'fav_add';
        fetch('db/base.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=${action}&id=<?php echo $id; ?>&nombre=<?php echo urlencode($serie_nome); ?>&img=<?php echo urlencode($poster_img); ?>&ano=<?php echo urlencode($ano); ?>&rate=<?php echo urlencode($nota); ?>&tipo=serie`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                isFav = !isFav;
                favText.textContent = isFav ? 'Favorito' : 'Agregar a Favoritos';
                btnFav.classList.toggle('favorito-active', isFav);
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function cargarEpisodiosTemporada(season) {
        const contenedor = document.getElementById('episodiosTemporada');
        contenedor.innerHTML = '<div style="color:#fff;padding:40px;text-align:center;">Cargando episodios...</div>';
        fetch('serie_episodios_ajax.php?serie_id=<?php echo $id; ?>&season=' + season)
            .then(res => res.text())
            .then(html => {
                contenedor.innerHTML = html;
            });
    }

    // Cargar la primera temporada por defecto
    let primerTemp = document.querySelector('.season-tabs .nav-link.active');
    let temporadaActual = primerTemp ? primerTemp.getAttribute('data-season') : 1;
    cargarEpisodiosTemporada(temporadaActual);

    // Cambiar de temporada al hacer clic
    document.querySelectorAll('.season-tabs .nav-link').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.season-tabs .nav-link').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            let season = this.getAttribute('data-season');
            cargarEpisodiosTemporada(season);
        });
    });
});
</script>

</body>
</html>
