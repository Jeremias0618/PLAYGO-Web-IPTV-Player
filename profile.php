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
$sagas = $pageData['sagas'];
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
            <button class="profile-logout-btn" id="profileLogoutBtn">
                <span>Cerrar sesión</span>
                <i class="fas fa-sign-out-alt"></i>
            </button>
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
                        <?php
                        $playlistsArray = [];
                        foreach ($playlists as $playlistName => $playlistItems) {
                            if (is_array($playlistItems) && !empty($playlistItems)) {
                                $playlistsArray[] = [
                                    'name' => $playlistName,
                                    'items' => $playlistItems
                                ];
                            }
                        }
                        $totalPlaylists = count($playlistsArray);
                        $initialPlaylists = array_slice($playlistsArray, 0, 6);
                        $remainingPlaylists = array_slice($playlistsArray, 6);
                        ?>
                        <div class="playlists-grid" id="playlistsGrid">
                            <?php foreach ($initialPlaylists as $playlist): ?>
                                <?php
                                $playlistName = $playlist['name'];
                                $playlistItems = $playlist['items'];
                                $firstItem = $playlistItems[0];
                                $playlistCover = $firstItem['backdrop'] ?? $firstItem['img'] ?? 'assets/logo/logo.png';
                                $playlistCount = count($playlistItems);
                                ?>
                                <div class="playlist-card" data-playlist-name="<?php echo htmlspecialchars($playlistName); ?>">
                                    <button class="playlist-delete-btn" data-playlist-name="<?php echo htmlspecialchars($playlistName); ?>" title="Eliminar lista">
                                        <i class="fas fa-times"></i>
                                    </button>
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
                            <?php endforeach; ?>
                        </div>
                        <?php if ($totalPlaylists > 6): ?>
                            <div class="ver-mas-button-wrapper">
                                <button class="ver-mas-button" id="playlistVerMasBtn">
                                    <span>VER MÁS</span>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php if ($totalPlaylists > 6): ?>
                        <script>
                        (function() {
                            const allPlaylists = <?php echo json_encode($remainingPlaylists); ?>;
                            const playlistsGrid = document.getElementById('playlistsGrid');
                            let currentIndex = 0;
                            
                            function getVerMasButton() {
                                return document.getElementById('playlistVerMasBtn');
                            }
                            
                            function getVerMasButtonWrapper() {
                                const btn = getVerMasButton();
                                return btn ? btn.closest('.ver-mas-button-wrapper') : null;
                            }
                            
                            if (allPlaylists && allPlaylists.length > 0 && playlistsGrid) {
                                const verMasBtn = getVerMasButton();
                                if (verMasBtn) {
                                    verMasBtn.addEventListener('click', function() {
                                        const nextBatch = allPlaylists.slice(currentIndex, currentIndex + 6);
                                        
                                        if (nextBatch.length === 0) {
                                            const wrapper = getVerMasButtonWrapper();
                                            if (wrapper) {
                                                wrapper.style.display = 'none';
                                            }
                                            return;
                                        }
                                        
                                        nextBatch.forEach(function(playlist) {
                                            const playlistName = playlist.name;
                                            const playlistItems = playlist.items;
                                            const firstItem = playlistItems[0];
                                            const playlistCover = firstItem['backdrop'] || firstItem['img'] || 'assets/logo/logo.png';
                                            const playlistCount = playlistItems.length;
                                            
                                            const card = document.createElement('div');
                                            card.className = 'playlist-card';
                                            card.setAttribute('data-playlist-name', playlistName);
                                            card.innerHTML = '<button class="playlist-delete-btn" data-playlist-name="' + playlistName.replace(/"/g, '&quot;') + '" title="Eliminar lista">' +
                                                '<i class="fas fa-times"></i>' +
                                            '</button>' +
                                            '<a href="playlist.php?name=' + encodeURIComponent(playlistName) + '" class="playlist-link">' +
                                                '<div class="playlist-cover">' +
                                                    '<img src="' + playlistCover.replace(/"/g, '&quot;') + '" alt="' + playlistName.replace(/"/g, '&quot;') + '" onerror="this.src=\'assets/logo/logo.png\'">' +
                                                    '<div class="playlist-count-badge">' +
                                                        '<i class="fas fa-list"></i> ' + playlistCount +
                                                    '</div>' +
                                                '</div>' +
                                                '<div class="playlist-info">' +
                                                    '<h3 class="playlist-title">' + playlistName.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</h3>' +
                                                    '<p class="playlist-meta">' + playlistCount + ' ' + (playlistCount == 1 ? 'elemento' : 'elementos') + '</p>' +
                                                '</div>' +
                                            '</a>';
                                            
                                            playlistsGrid.appendChild(card);
                                            
                                            const newDeleteBtn = card.querySelector('.playlist-delete-btn');
                                            if (newDeleteBtn) {
                                                newDeleteBtn.addEventListener('click', function(e) {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deletePlaylistModal')) || new bootstrap.Modal(document.getElementById('deletePlaylistModal'));
                                                    const deletePlaylistNameEl = document.getElementById('deletePlaylistName');
                                                    const confirmDeleteBtn = document.getElementById('confirmDeletePlaylistBtn');
                                                    const playlistNameToDelete = newDeleteBtn.getAttribute('data-playlist-name');
                                                    
                                                    deletePlaylistNameEl.textContent = playlistNameToDelete;
                                                    deleteModal.show();
                                                    
                                                    const handleConfirm = function() {
                                                        confirmDeleteBtn.disabled = true;
                                                        confirmDeleteBtn.textContent = 'Eliminando...';
                                                        
                                                        fetch('libs/endpoints/UserData.php', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/x-www-form-urlencoded',
                                                            },
                                                            body: 'action=playlist_delete&playlist_name=' + encodeURIComponent(playlistNameToDelete)
                                                        })
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            if (data.success) {
                                                                deleteModal.hide();
                                                                location.reload();
                                                            } else {
                                                                alert('Error al eliminar la lista: ' + (data.error || 'Error desconocido'));
                                                                confirmDeleteBtn.disabled = false;
                                                                confirmDeleteBtn.textContent = 'Eliminar';
                                                            }
                                                        })
                                                        .catch(error => {
                                                            console.error('Error:', error);
                                                            alert('Error al eliminar la lista');
                                                            confirmDeleteBtn.disabled = false;
                                                            confirmDeleteBtn.textContent = 'Eliminar';
                                                        });
                                                    };
                                                    
                                                    confirmDeleteBtn.onclick = handleConfirm;
                                                });
                                            }
                                        });
                                        
                                        currentIndex += nextBatch.length;
                                        
                                        if (currentIndex >= allPlaylists.length) {
                                            const wrapper = getVerMasButtonWrapper();
                                            if (wrapper) {
                                                wrapper.style.display = 'none';
                                            }
                                        }
                                    });
                                }
                            }
                        })();
                        </script>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-history">
                            <p>No hay listas creadas</p>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="section-title"><i class="fas fa-layer-group"></i> Sagas</h2>
                    <?php if (!empty($sagas)): ?>
                        <?php
                        $totalSagas = count($sagas);
                        $initialSagas = array_slice($sagas, 0, 6);
                        ?>
                        <div class="playlists-grid">
                            <?php foreach ($initialSagas as $saga): ?>
                                <?php
                                $sagaItems = $saga['items'] ?? [];
                                $sagaCount = count($sagaItems);
                                $sagaCover = $saga['image'] ?? '';
                                
                                if (empty($sagaCover) && !empty($sagaItems) && isset($sagaItems[0])) {
                                    $firstItem = $sagaItems[0];
                                    $sagaCover = $firstItem['img'] ?? $firstItem['backdrop'] ?? $firstItem['poster'] ?? 'assets/logo/logo.png';
                                }
                                
                                if (empty($sagaCover)) {
                                    $sagaCover = 'assets/logo/logo.png';
                                }
                                
                                $sagaName = htmlspecialchars($saga['title'] ?? 'Sin título');
                                $sagaId = $saga['id'] ?? '';
                                ?>
                                <div class="playlist-card">
                                    <a href="collection.php?saga=<?php echo htmlspecialchars($sagaId); ?>" class="playlist-link">
                                        <div class="playlist-cover">
                                            <img src="<?php echo htmlspecialchars($sagaCover); ?>" alt="<?php echo $sagaName; ?>" onerror="this.src='assets/logo/logo.png'">
                                            <div class="playlist-count-badge">
                                                <i class="fas fa-list"></i> <?php echo $sagaCount; ?>
                                            </div>
                                        </div>
                                        <div class="playlist-info">
                                            <h3 class="playlist-title"><?php echo $sagaName; ?></h3>
                                            <p class="playlist-meta"><?php echo $sagaCount; ?> <?php echo $sagaCount == 1 ? 'elemento' : 'elementos'; ?></p>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($totalSagas > 6): ?>
                            <div class="ver-mas-button-wrapper">
                                <a href="sagas.php" class="ver-mas-button">
                                    <span>VER MÁS</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-history">
                            <p>No hay sagas disponibles</p>
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

<div class="delete-modal-overlay" id="deletePlaylistModal">
    <div class="delete-modal-backdrop"></div>
    <div class="delete-modal-container">
        <div class="delete-modal-content">
            <div class="delete-modal-header">
                <h3>Confirmar eliminación</h3>
            </div>
            <div class="delete-modal-body">
                <div class="delete-modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>¿Estás seguro de que deseas eliminar la lista de reproducción "<strong id="deletePlaylistName"></strong>"?</p>
                <span class="delete-modal-warning">Esta acción no se puede deshacer.</span>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="delete-modal-btn delete-modal-btn-cancel" id="cancelDeletePlaylistBtn">Cancelar</button>
                <button type="button" class="delete-modal-btn delete-modal-btn-confirm" id="confirmDeletePlaylistBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const deleteButtons = document.querySelectorAll('.playlist-delete-btn');
    const deleteModal = document.getElementById('deletePlaylistModal');
    const deleteModalBackdrop = deleteModal ? deleteModal.querySelector('.delete-modal-backdrop') : null;
    const deletePlaylistNameEl = document.getElementById('deletePlaylistName');
    const confirmDeleteBtn = document.getElementById('confirmDeletePlaylistBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeletePlaylistBtn');
    let playlistToDelete = null;
    
    function showModal() {
        if (deleteModal) {
            deleteModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function hideModal() {
        if (deleteModal) {
            deleteModal.classList.remove('show');
            document.body.style.overflow = '';
            playlistToDelete = null;
            if (confirmDeleteBtn) {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.textContent = 'Eliminar';
            }
        }
    }
    
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            playlistToDelete = btn.getAttribute('data-playlist-name');
            
            if (deletePlaylistNameEl) {
                deletePlaylistNameEl.textContent = playlistToDelete;
            }
            
            showModal();
        });
    });
    
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            hideModal();
        });
    }
    
    if (deleteModalBackdrop) {
        deleteModalBackdrop.addEventListener('click', function() {
            hideModal();
        });
    }
    
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                hideModal();
            }
        });
    }
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (!playlistToDelete) return;
            
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.textContent = 'Eliminando...';
            
            fetch('libs/endpoints/UserData.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=playlist_delete&playlist_name=' + encodeURIComponent(playlistToDelete)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideModal();
                    location.reload();
                } else {
                    alert('Error al eliminar la lista: ' + (data.error || 'Error desconocido'));
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.textContent = 'Eliminar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar la lista');
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.textContent = 'Eliminar';
            });
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && deleteModal && deleteModal.classList.contains('show')) {
            hideModal();
        }
    });
})();

(function() {
    const logoutBtn = document.getElementById('profileLogoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                document.cookie = 'xuserm=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                document.cookie = 'xpwdm=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                window.location.href = 'login.php';
            }
        });
    }
})();
</script>

</body>
</html>

