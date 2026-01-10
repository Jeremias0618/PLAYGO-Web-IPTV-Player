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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./styles/vendors/bootstrap.min.css">
    <link rel="stylesheet" href="./styles/vendors/font-awesome-6.5.0.min.css">
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
    <link rel="stylesheet" href="./styles/serie/layout.css">
    <link rel="stylesheet" href="./styles/serie/hero.css">
    <link rel="stylesheet" href="./styles/serie/episodes.css">
    <link rel="stylesheet" href="./styles/serie/modal.css">
    <link rel="stylesheet" href="./styles/serie/mobile.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
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
    <style>
body {
    background: linear-gradient(180deg,rgba(24,24,24,0.80) 0%,rgba(24,24,24,0.80) 100%), url('<?php echo $backdrop_final; ?>') center center/cover no-repeat;
    color: #fff;
    background-attachment: fixed;
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
  <?php if ($episodios && is_array($episodios)): ?>
  <div class="d-flex justify-content-between align-items-center mb-3" style="gap: 16px; flex-wrap: wrap;">
    <div class="season-select-wrapper">
      <select class="form-select" id="seasonSelect">
        <?php
          $i = 0;
          foreach ($episodios as $num_temp => $eps):
        ?>
          <option value="<?php echo $num_temp; ?>"<?php if($i==0) echo ' selected'; ?>>
            Temporada <?php echo htmlspecialchars($num_temp); ?>
          </option>
        <?php
            $i++;
          endforeach;
        ?>
      </select>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <button id="viewToggleBtn" class="btn view-toggle-btn" data-view="grid" title="Cambiar vista">
        <i class="fa-solid fa-th view-icon-grid"></i>
        <i class="fa-solid fa-list view-icon-list" style="display: none;"></i>
        <span class="view-toggle-text">Cuadrícula</span>
      </button>
    </div>
  </div>
<div class="tab-content" id="seasonTabContent">
  <?php
    $i = 0;
    foreach ($episodios as $num_temp => $eps):
  ?>
    <div class="tab-pane fade<?php if($i==0) echo ' show active'; ?>" id="season-<?php echo $num_temp; ?>" role="tabpanel">
      <div class="row episodes-container" data-view="grid">
        <?php foreach ($eps as $ep):
          $ep_name = limpiar_titulo_episodio($ep['title'] ?? 'Episodio');
          $ep_id = $ep['id'];
          $ep_num = $ep['episode_num'] ?? '';
          $ep_img = isset($tmdb_episodios_imgs[$num_temp][$ep_num]) 
              ? $tmdb_episodios_imgs[$num_temp][$ep_num] 
              : (!empty($ep['info']['movie_image']) ? $ep['info']['movie_image'] : $poster_final);
          $ep_plot = $ep['info']['plot'] ?? '';
          $ep_dur = $ep['info']['duration'] ?? '';
          $ep_num_str = $ep_num ? str_pad($ep_num, 2, '0', STR_PAD_LEFT) : '';
          $duracion_valida = !empty($ep_dur) && !in_array(trim($ep_dur), ['0', '00:00', '00:00:00']);
          $ep_date = $ep['info']['release_date'] ?? '';
          $ep_release_date = '';
          if ($ep_date) {
              $timestamp = strtotime($ep_date);
              if ($timestamp) {
                  $ep_release_date = date('Y-m-d', $timestamp);
              } else {
                  $ep_release_date = $ep_date;
              }
          }
          $ep_rating = $ep['info']['rating'] ?? '';
        ?>
        <?php
          $ep_progress = isset($episodes_progress[$ep_id]) ? $episodes_progress[$ep_id] : null;
          $ep_percentage = $ep_progress ? $ep_progress['percentage'] : 0;
          $ep_watched = $ep_progress && $ep_progress['watched'];
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
          <div class="episode-card w-100">
            <div class="episode-image-wrapper">
              <img src="<?php echo htmlspecialchars($ep_img); ?>" alt="">
              <?php if ($ep_watched): ?>
                <div class="episode-watched-badge">Visto</div>
              <?php endif; ?>
              <?php if ($ep_progress && $ep_percentage > 0 && !$ep_watched): ?>
                <div class="episode-progress-bar">
                  <div class="episode-progress-fill" style="width: <?php echo min(100, max(0, $ep_percentage)); ?>%"></div>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column">
              <div class="card-title">
                  <?php echo htmlspecialchars($ep_num_str ? "Episodio $ep_num_str - " : "") . limitar_texto($ep_name, 40); ?>
              </div>
              <div class="card-text"><?php echo $ep_plot; ?></div>
              <div class="episode-meta">
                <?php if ($ep_release_date): ?>
                  <span class="meta-date"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($ep_release_date); ?></span>
                <?php endif; ?>
                <?php if ($duracion_valida): ?>
                  <span class="meta-duration"><i class="fa-regular fa-clock"></i> <?php echo $ep_dur; ?></span>
                <?php endif; ?>
                <?php if ($ep_rating): ?>
                  <span class="meta-rating"><i class="fa-solid fa-star"></i> <?php echo htmlspecialchars($ep_rating); ?></span>
                <?php endif; ?>
              </div>
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

<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/bootstrap-5.3.3.bundle.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js" defer></script>
<script src="./scripts/vendors/jquery.mousewheel.min.js" defer></script>
<script src="./scripts/vendors/jquery.mcustomscrollbar.min.js" defer></script>
<script src="./scripts/vendors/wnumb.js" defer></script>
<script src="./scripts/vendors/nouislider.min.js" defer></script>
<script src="./scripts/vendors/jquery.morelines.min.js" defer></script>
<script src="./scripts/vendors/photoswipe.min.js" defer></script>
<script src="./scripts/vendors/photoswipe-ui-default.min.js" defer></script>
<script src="./scripts/vendors/glightbox.min.js" defer></script>
<script src="./scripts/vendors/jBox.all.min.js" defer></script>
<script src="./scripts/vendors/select2.min.js" defer></script>
<script src="./scripts/core/main.js" defer></script>
<script src="./scripts/search/modal.js"></script>
<script src="./scripts/serie/trailer.js"></script>
<script src="./scripts/serie/favorites.js"></script>
<script src="./scripts/serie/playlist.js"></script>
<script src="./scripts/serie/seasons.js"></script>
<script src="./scripts/serie/mobile.js"></script>

</body>
</html>
