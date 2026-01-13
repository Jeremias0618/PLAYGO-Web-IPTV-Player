<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

if (!defined('SAGAS_ADMIN_ENABLED') || SAGAS_ADMIN_ENABLED !== true) {
    header("Location: home.php");
    exit;
}

require_once(__DIR__ . '/libs/controllers/SagasAdminController.php');

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$data = getSagasAdminData();
$backdrop_fondo = $data['backdrop'];
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
    <link rel="stylesheet" href="./styles/movies/title.css">
    <link rel="stylesheet" href="./styles/movie/layout.css">
    <link rel="stylesheet" href="./styles/movie/mobile.css">
    <link rel="stylesheet" href="./styles/sagas-admin/layout.css">
    <link rel="stylesheet" href="./styles/sagas-admin/content.css">
    <link rel="stylesheet" href="./styles/sagas-admin/table.css">
    <link rel="stylesheet" href="./styles/sagas-admin/modal.css">
    <style>
    #sagaModal {
        z-index: 10000 !important;
    }
    #sagaModal.show {
        z-index: 10000 !important;
    }
    #sagaModal .modal-dialog {
        z-index: 10002 !important;
        position: relative !important;
    }
    #sagaModal .saga-modal-content {
        z-index: 10003 !important;
        position: relative !important;
    }
    .modal-backdrop {
        z-index: 9999 !important;
    }
    </style>
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - Administración de Sagas</title>
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
<?php include_once __DIR__ . '/libs/views/search.php'; ?>

<div class="main-bg-fondo">
    <div class="movies-page-title">
        <h2>ADMINISTRACIÓN DE SAGAS</h2>
    </div>
    
    <div class="catalog details">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sagas-admin-actions">
                        <button id="collectMoviesBtn" class="sagas-admin-btn">
                            <i class="fas fa-download"></i> Recolectar Películas
                        </button>
                    </div>
                    
                    <div id="groupedMoviesContainer" class="sagas-admin-container">
                        <div class="sagas-admin-message">
                            Haz clic en "Recolectar Películas" para comenzar
                        </div>
                    </div>
                    
                    <div id="savedSagasContainer" style="margin-top: 50px;">
                        <h3 style="color: #fff; margin-bottom: 20px; font-size: 1.5rem;">Sagas Guardadas</h3>
                        <div class="sagas-admin-message">Cargando sagas...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="sagaModal" tabindex="-1" aria-hidden="true" aria-modal="false" role="dialog" style="z-index: 10000; display: none;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px; width: 90%; margin: 40px auto; z-index: 10002;">
            <div class="modal-content saga-modal-content">
                <div class="modal-header saga-modal-header">
                    <h2 class="modal-title">Crear Nueva Saga</h2>
                    <button type="button" class="btn-close btn-close-white saga-modal-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body saga-modal-body">
                    <div class="saga-modal-section">
                        <label for="sagaTitle" class="saga-field-label">Título de la Saga</label>
                        <input type="text" id="sagaTitle" placeholder="Ej: SAGA IRON MAN" required>
                    </div>
                    
                    <div class="saga-modal-section">
                        <label for="sagaImageFile" class="saga-field-label">Imagen de la Saga (JPG, PNG, WEBP)</label>
                        <div class="saga-dropzone" id="sagaDropzone">
                            <input type="file" id="sagaImageFile" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;">
                            <div class="saga-dropzone-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p class="saga-dropzone-text">Arrastra una imagen aquí o haz clic para seleccionar</p>
                                <p class="saga-dropzone-hint">JPG, PNG o WEBP (máx. 5MB)</p>
                            </div>
                            <img id="sagaImagePreview" class="saga-image-preview" alt="Preview" style="display: none;">
                        </div>
                    </div>
                    
                    <div class="saga-modal-section">
                        <label>Contenido de esta Saga</label>
                        <div id="sagaMoviesList" class="saga-modal-movies-grid sortable-list"></div>
                    </div>
                    
                    <div class="saga-modal-section">
                        <div class="saga-search-wrapper">
                            <div class="saga-search-input-wrapper">
                                <i class="fas fa-search saga-search-icon"></i>
                                <input type="text" id="sagaSearchMovies" placeholder="Buscar películas por nombre..." class="saga-search-input">
                                <input type="text" id="sagaSearchSeries" placeholder="Buscar series por nombre..." class="saga-search-input" style="display: none;">
                            </div>
                            <div class="saga-segmented-control">
                                <button class="saga-segmented-btn active" data-tab="movies" onclick="switchSearchTab('movies')">
                                    <i class="fas fa-film"></i> Películas
                                </button>
                                <button class="saga-segmented-btn" data-tab="series" onclick="switchSearchTab('series')">
                                    <i class="fas fa-tv"></i> Series
                                </button>
                            </div>
                        </div>
                        <div id="searchMoviesTab" class="saga-search-tab-content active">
                            <div id="sagaSearchResults" class="saga-search-results"></div>
                        </div>
                        <div id="searchSeriesTab" class="saga-search-tab-content">
                            <div id="sagaSearchSeriesResults" class="saga-search-results"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer saga-modal-footer">
                    <button type="button" class="saga-modal-btn saga-modal-btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" id="saveSagaBtn" class="saga-modal-btn saga-modal-btn-primary" onclick="saveSaga()">
                        <i class="fas fa-save"></i> Guardar Saga
                    </button>
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
<script src="./scripts/vendors/jwplayer.js"></script>
<script src="./scripts/vendors/jwplayer.core.controls.js"></script>
<script src="./scripts/vendors/provider.hlsjs.js"></script>
<script src="./scripts/core/main.js"></script>
<script src="./scripts/search/modal.js"></script>
<script src="./scripts/sagas-admin/init.js"></script>
<script src="./scripts/sagas-admin/collect.js"></script>
<script src="./scripts/sagas-admin/save.js"></script>

</body>
</html>

