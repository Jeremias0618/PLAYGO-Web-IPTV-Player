<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

session_write_close();

require_once(__DIR__ . '/libs/controllers/SeriePageController.php');

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$id = trim($_REQUEST['stream']);

$pageData = getSeriePageData($user, $pwd, $id);

if (!$pageData) {
    header("Location: series.php");
    exit;
}

extract($pageData, EXTR_SKIP);
$generos_array = !empty($genero) ? array_map('trim', explode(',', $genero)) : [];
$ano_short = $ano ? substr($ano, 0, 4) : '';
$poster_final = $poster_tmdb ?: $poster_img;
$backdrop_final = $wallpaper_tmdb ?: ($backdrop ?: $poster_final);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PLAYGO - <?php echo htmlspecialchars($serie_nome); ?></title>
    <script>
window.serieId = <?php echo json_encode($id); ?>;
window.serieYoutubeId = <?php echo json_encode($youtube_id); ?>;
window.serieName = <?php echo json_encode($serie_nome); ?>;
window.serieImg = <?php echo json_encode($poster_final); ?>;
window.serieBackdrop = <?php echo json_encode($backdrop_final); ?>;
window.serieYear = <?php echo json_encode($ano); ?>;
window.serieRating = <?php echo json_encode($nota); ?>;
    </script>
    <link rel="stylesheet" href="./styles/vendors/bootstrap-5.3.3.min.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
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
    <link rel="stylesheet" href="./styles/serie/layout.css">
    <link rel="stylesheet" href="./styles/serie/hero.css">
    <link rel="stylesheet" href="./styles/serie/episodes.css">
    <link rel="stylesheet" href="./styles/serie/modal.css">
    <link rel="stylesheet" href="./styles/serie/mobile.css">
    <style>
body {
    background: linear-gradient(180deg,rgba(24,24,24,0.80) 0%,rgba(24,24,24,0.80) 100%), url('<?php echo $backdrop_final; ?>') center center/cover no-repeat;
    color: #fff;
    background-attachment: fixed;
}
    </style>
</head>
<body>
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

<nav class="mobile-menu" id="mobileMenu">
  <button class="close-menu" id="closeMobileMenu" aria-label="Cerrar menú">&times;</button>
  <ul>
    <li><a href="./home.php">INICIO</a></li>
    <li><a href="./channels.php">TV EN VIVO</a></li>
    <li><a href="movies.php">PELÍCULAS</a></li>
    <li><a href="series.php">SERIES</a></li>
    <li><a href="profile.php">PERFIL</a></li>
  </ul>
</nav>
<div id="mobileMenuOverlay"></div>


<?php include_once __DIR__ . '/libs/views/search.php'; ?>
<section class="serie-hero d-flex align-items-center">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-12 col-md-auto d-flex justify-content-center">
        <img src="<?php echo $poster_final; ?>" class="poster" alt="">
      </div>
      <div class="col info">
        <div class="title"><?php echo htmlspecialchars($serie_nome); ?></div>
        <div class="meta">
          <?php echo $ano_short; ?>
          <?php if($nota !== ''): ?>
            <span class="rate"><i class="fa-solid fa-star"></i> <?php echo $nota; ?></span>
          <?php endif; ?>
          <span class="genres">
            <?php foreach ($generos_array as $g): ?>
              <span><?php echo htmlspecialchars($g); ?></span>
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
                style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#232027 60%,#444 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                <i class="fa fa-star" style="font-size:1.4rem;color:#fff;"></i>
                <span id="favText" style="color:#fff;">Agregar a Favoritos</span>
            </button>
            <div style="position: relative; display: inline-block;">
                <button id="btnPlaylist"
                    style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#232027 60%,#444 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                    <i class="fa fa-bookmark" style="font-size:1.4rem;color:#fff;"></i>
                    <span style="color:#fff;">Guardar</span>
                </button>
                <div id="playlistTooltip" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; z-index: 10000;"></div>
            </div>
        </div>
    </div>
  </div>
</section>

<div class="container mt-5">
  <h2 class="mb-4" style="font-weight:700;letter-spacing:1px;">Capítulos</h2>
  <?php if ($episodios && is_array($episodios)): ?>
<ul class="nav season-tabs mb-3" id="seasonTab" role="tablist">
  <?php
    $i = 0;
    $total = count($episodios);
    foreach ($episodios as $num_temp => $eps):
      if ($i < 7):
  ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link<?php if($i==0) echo ' active'; ?>" id="season-<?php echo $num_temp; ?>-tab" data-bs-toggle="tab" data-bs-target="#season-<?php echo $num_temp; ?>" type="button" role="tab">
        Temporada <?php echo htmlspecialchars($num_temp); ?>
      </button>
    </li>
  <?php
      elseif ($i == 7):
        $restantes = array_slice(array_keys($episodios), 7);
  ?>
    <li class="nav-item dropdown" role="presentation">
      <button class="nav-link dropdown-toggle" id="season-dropdown-tab" data-bs-toggle="dropdown" type="button" role="tab" aria-expanded="false">
        Temporadas
      </button>
      <ul class="dropdown-menu" id="seasonDropdownMenu">
        <?php foreach ($restantes as $rest_num): ?>
          <li>
            <a class="dropdown-item season-dropdown-item" href="#" data-season="<?php echo $rest_num; ?>">
              Temporada <?php echo htmlspecialchars($rest_num); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </li>
  <?php
        break;
      endif;
      $i++;
    endforeach;
  ?>
</ul>
<div class="tab-content" id="seasonTabContent">
  <?php
    $i = 0;
    foreach ($episodios as $num_temp => $eps):
  ?>
    <div class="tab-pane fade<?php if($i==0) echo ' show active'; ?>" id="season-<?php echo $num_temp; ?>" role="tabpanel">
      <div class="row">
        <?php foreach ($eps as $ep):
          $ep_name = $ep['title'] ?? 'Episodio';
          if (strpos($ep_name, '-') !== false) {
              $ep_name = trim(end(explode('-', $ep_name)));
          }
          $ep_id = $ep['id'];
          $ep_num = $ep['episode_num'] ?? '';
          $ep_img = isset($tmdb_episodios_imgs[$num_temp][$ep_num]) 
              ? $tmdb_episodios_imgs[$num_temp][$ep_num] 
              : (!empty($ep['info']['movie_image']) ? $ep['info']['movie_image'] : $poster_final);
          $ep_plot = $ep['info']['plot'] ?? '';
          $ep_dur = $ep['info']['duration'] ?? '';
          $ep_num_str = $ep_num ? str_pad($ep_num, 2, '0', STR_PAD_LEFT) : '';
          $duracion_valida = !empty($ep_dur) && !in_array(trim($ep_dur), ['0', '00:00', '00:00:00']);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
          <div class="episode-card w-100">
            <img src="<?php echo htmlspecialchars($ep_img); ?>" alt="">
            <div class="card-body d-flex flex-column">
              <div class="card-title">
                  <?php echo htmlspecialchars($ep_num_str ? "Episodio $ep_num_str - " : "") . limitar_texto($ep_name, 40); ?>
              </div>
              <div class="card-text"><?php echo $ep_plot; ?></div>
              <?php if ($duracion_valida): ?>
                  <div class="mb-2" style="color:#ffd700;"><?php echo $ep_dur; ?></div>
              <?php endif; ?>
                <a href="episode.php?serie_id=<?php echo $id; ?>&episode_id=<?php echo $ep_id; ?>" class="btn-play mt-auto">
                    <i class="fa-solid fa-circle-play"></i> Ver episodio
                </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php $i++; endforeach; ?>
</div>

  <?php else: ?>
    <div class="alert alert-warning">No hay episodios disponibles.</div>
  <?php endif; ?>
</div>

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

<script src="./scripts/vendors/bootstrap-5.3.3.bundle.min.js"></script>
<script src="./scripts/serie/trailer.js"></script>
<script src="./scripts/serie/favorites.js"></script>
<script src="./scripts/serie/playlist.js"></script>
<script src="./scripts/serie/seasons.js"></script>
<script src="./scripts/serie/mobile.js"></script>

</body>
</html>
