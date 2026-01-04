<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

require_once(__DIR__ . '/libs/controllers/Movies.php');
require_once(__DIR__ . '/libs/controllers/MoviesPagination.php');

if (!function_exists('limitar_texto')) {
    require_once(__DIR__ . '/libs/lib.php');
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$params = [
    'id' => isset($_REQUEST['id']) ? trim($_REQUEST['id']) : null,
    'genero' => isset($_GET['genero']) ? $_GET['genero'] : null,
    'rating' => isset($_GET['rating']) ? $_GET['rating'] : null,
    'rating_min' => isset($_GET['rating_min']) ? $_GET['rating_min'] : null,
    'rating_max' => isset($_GET['rating_max']) ? $_GET['rating_max'] : null,
    'year' => isset($_GET['year']) ? $_GET['year'] : null,
    'year_min' => isset($_GET['year_min']) ? $_GET['year_min'] : null,
    'year_max' => isset($_GET['year_max']) ? $_GET['year_max'] : null,
    'orden' => isset($_GET['orden']) ? $_GET['orden'] : null,
    'orden_dir' => isset($_GET['orden_dir']) ? $_GET['orden_dir'] : 'asc',
    'pagina' => isset($_GET['pagina']) ? $_GET['pagina'] : 1
];

$data = getMoviesPageData($user, $pwd, $params);
$peliculas_pagina = $data['movies'];
$total_paginas = $data['totalPages'];
$pagina_actual = $data['currentPage'];
$generos = $data['genres'];
$backdrop_fondo = $data['backdrop'];
$populares = $data['popular'];
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
    <link rel="stylesheet" href="./styles/movies/layout.css">
    <link rel="stylesheet" href="./styles/movies/pagination.css">
    <link rel="stylesheet" href="./styles/movies/title.css">
    <link rel="stylesheet" href="./styles/movies/filters.css">
    <link rel="stylesheet" href="./styles/movies/modals.css">
    <link rel="stylesheet" href="./styles/movies/cards.css">
    <link rel="stylesheet" href="./styles/movies/popular.css">
    <link rel="stylesheet" href="./styles/movies/mobile.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - Películas</title>
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
                                <a href="movies.php" class="header__nav-link header__nav-link--active">Películas</a>
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
    <div class="movies-page-title">
        <h2>PELÍCULAS</h2>
    </div>
    
    <button type="button" class="filtros-toggle-btn" id="filtrosToggleBtn">
        <i class="fa fa-filter"></i> <span id="filtrosToggleText">Ocultar Filtros</span>
    </button>
    
<form id="filtrosForm" method="get" class="filtros-bar">
    <div class="filtro-opcion" style="position:relative;">
        <label for="genero">GÉNERO</label>
        <select name="genero" id="genero">
            <option value="">Todos</option>
            <?php
            foreach ($generos as $g) {
                $sel = (isset($_GET['genero']) && $_GET['genero'] == $g) ? 'selected' : '';
                echo "<option value=\"".htmlspecialchars($g)."\" $sel>".htmlspecialchars($g)."</option>";
            }
            ?>
        </select>
    </div>
    <div class="filtro-opcion">
        <label for="rating_display">CALIFICACIÓN</label>
        <div class="movies-filter-display" id="rating_display">
            <?php
            if (isset($_GET['rating']) && $_GET['rating'] !== '') {
                echo intval($_GET['rating']);
            } else {
                $ratingMin = isset($_GET['rating_min']) ? intval($_GET['rating_min']) : null;
                $ratingMax = isset($_GET['rating_max']) ? intval($_GET['rating_max']) : null;
                if ($ratingMin !== null && $ratingMax !== null) {
                    echo $ratingMin . ' - ' . $ratingMax;
                } else {
                    echo 'Todos';
                }
            }
            ?>
        </div>
        <input type="hidden" name="rating" id="rating" value="<?php echo isset($_GET['rating']) ? $_GET['rating'] : ''; ?>">
        <input type="hidden" name="rating_min" id="rating_min" value="<?php echo isset($_GET['rating_min']) ? $_GET['rating_min'] : ''; ?>">
        <input type="hidden" name="rating_max" id="rating_max" value="<?php echo isset($_GET['rating_max']) ? $_GET['rating_max'] : ''; ?>">
    </div>
    <div class="filtro-opcion">
        <label for="year_display">AÑO</label>
        <div class="movies-filter-display" id="year_display">
            <?php
            if (isset($_GET['year']) && $_GET['year'] !== '') {
                echo intval($_GET['year']);
            } else {
                $yearMin = isset($_GET['year_min']) ? intval($_GET['year_min']) : null;
                $yearMax = isset($_GET['year_max']) ? intval($_GET['year_max']) : null;
                if ($yearMin !== null && $yearMax !== null) {
                    echo $yearMin . ' - ' . $yearMax;
                } else {
                    echo 'Todos';
                }
            }
            ?>
        </div>
        <input type="hidden" name="year" id="year" value="<?php echo isset($_GET['year']) ? $_GET['year'] : ''; ?>">
        <input type="hidden" name="year_min" id="year_min" value="<?php echo isset($_GET['year_min']) ? $_GET['year_min'] : ''; ?>">
        <input type="hidden" name="year_max" id="year_max" value="<?php echo isset($_GET['year_max']) ? $_GET['year_max'] : ''; ?>">
    </div>
    <div class="filtro-opcion">
        <label for="orden">ORDENAR</label>
        <div style="display: flex; align-items: center; gap: 8px;">
            <select name="orden" id="orden" style="flex: 1;">
            <option value="">Por defecto</option>
            <option value="nombre" <?php if(isset($_GET['orden']) && $_GET['orden']=='nombre') echo 'selected'; ?>>Nombre</option>
            <option value="año" <?php if(isset($_GET['orden']) && $_GET['orden']=='año') echo 'selected'; ?>>Año</option>
            <option value="rating" <?php if(isset($_GET['orden']) && $_GET['orden']=='rating') echo 'selected'; ?>>Rating</option>
            <option value="recientes" <?php if(isset($_GET['orden']) && $_GET['orden']=='recientes') echo 'selected'; ?>>Más recientes</option>
            <option value="antiguas" <?php if(isset($_GET['orden']) && $_GET['orden']=='antiguas') echo 'selected'; ?>>Más antiguas</option>
        </select>
            <button type="button" class="filtro-orden-btn" id="ordenDirectionBtn" title="Cambiar dirección de ordenamiento" style="display: <?php echo (isset($_GET['orden']) && $_GET['orden'] != '') ? 'block' : 'none'; ?>;">
                <i class="fa-solid fa-arrow-<?php echo (isset($_GET['orden_dir']) && $_GET['orden_dir'] == 'desc') ? 'down' : 'up'; ?>" id="ordenDirectionIcon"></i>
            </button>
        </div>
        <input type="hidden" name="orden_dir" id="orden_dir" value="<?php echo isset($_GET['orden_dir']) ? $_GET['orden_dir'] : 'asc'; ?>">
    </div>
    <div class="filtro-opcion">
        <label style="opacity: 0; pointer-events: none;">&nbsp;</label>
    <div class="filtros-botones">
        <button type="submit" class="filtro-aplicar-btn">Aplicar</button>
            <button type="button" class="filtro-limpiar-btn" id="limpiarFiltrosBtn" title="Limpiar filtros" disabled>
            <i class="fa-solid fa-xmark"></i>
        </button>
        </div>
    </div>
</form>

    <div class="catalog details">
        <div class="container">
            <div class="row">
<?php
if ($peliculas_pagina && is_array($peliculas_pagina)) {
    foreach($peliculas_pagina as $index) {
        $filme_nome = $index['name'];
        $filme_type = $index['stream_type'];
        $filme_id = $index['stream_id'];
        $filme_img = $index['stream_icon'];
        $filme_rat = $index['rating'];
        $filme_ano = isset($index['year']) ? $index['year'] : '';
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
        <div class="card">
            <div class="card__cover">
                <img loading="lazy" src="<?php echo $filme_img; ?>" alt="<?php echo htmlspecialchars($filme_nome); ?>">
                <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                    <i class="fa-solid fa-circle-play"></i>
                </a>
            </div>
            <div class="card__content">
                <h3 class="card__title">
                    <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                        <?php
                        $titulo_sin_anio = preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome);
                        echo limitar_texto($titulo_sin_anio, 40);
                        ?>
                    </a>
                </h3>
                <span class="card__rate"><?php echo $filme_ano; ?> &nbsp; <i class="fa-solid fa-star"></i><?php echo $filme_rat; ?></span>
            </div>
        </div>
    </div>
<?php
    }
} else {
    echo '<div style="color:#fff;font-size:1.2rem;">No hay películas en esta categoría.</div>';
}
?>
            </div>
            <div class="row">
                <div class="col-12 d-flex justify-content-center">
                    <?php echo renderMoviesPagination($pagina_actual, $total_paginas, $_GET); ?>
                </div>
            </div>
        </div>
    </div>
    <section class="section section-popular">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="home__title bottom-margin-sml">POPULAR <b>ESTE MES</b></h1>
                </div>
                <?php
foreach($populares as $pop) {
    $filme_nome = $pop['name'];
    $filme_img = $pop['stream_icon'];
    $filme_ano = isset($pop['year']) ? $pop['year'] : '';
    $filme_rat = $pop['rating'];
    $filme_id = $pop['stream_id'];
    $filme_type = $pop['stream_type'];
?>
<div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="card">
        <div class="card__cover">
        <img loading="lazy" class="popular-img" src="<?php echo $filme_img; ?>" alt="">
            <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                <i class="fa-solid fa-circle-play"></i>
            </a>
        </div>
        <div class="card__content">
            <a href="movie.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                <span class="card__title" style="display:block;color:#fff;font-weight:600;font-size:1.05rem;margin-bottom:2px;">
                    <?php
                    $titulo_sin_anio = preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome);
                    echo limitar_texto($titulo_sin_anio, 40);
                    ?>
                </span>
                <span class="card__rate"><?php echo $filme_ano; ?> &nbsp; <i class="fa-solid fa-star"></i><?php echo $filme_rat; ?></span>
            </a>
        </div>
    </div>
</div>
                <?php } ?>
                <div class="col-12 d-flex justify-content-center">
                    <a href="populares.php" class="section__btn">Ver más</a>
                </div>
            </div>
        </div>
</section>

</div>

<div class="movies-filter-modal" id="ratingModal">
    <div class="movies-filter-modal-content">
        <div class="movies-filter-modal-header">
            <h3>Filtrar por Calificación</h3>
            <button type="button" class="movies-filter-modal-close" data-modal="ratingModal">&times;</button>
        </div>
        <div class="movies-filter-modal-body">
            <div class="movies-filter-option-type">
                <label>Tipo de filtro</label>
                <div class="movies-filter-radio-group">
                    <div class="movies-filter-radio-item">
                        <input type="radio" name="rating_type" id="rating_type_single" value="single" checked>
                        <label for="rating_type_single">Calificación específica</label>
                    </div>
                    <div class="movies-filter-radio-item">
                        <input type="radio" name="rating_type" id="rating_type_range" value="range">
                        <label for="rating_type_range">Rango</label>
                    </div>
                </div>
            </div>
            <div id="rating_single_inputs">
                <div class="movies-filter-input-group">
                    <label>Calificación</label>
                    <select id="rating_single_value">
                        <option value="">Seleccionar</option>
                        <?php for ($r = 1; $r <= 10; $r++): ?>
                        <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div id="rating_range_inputs" style="display:none;">
                <div class="movies-filter-inputs">
                    <div class="movies-filter-input-group">
                        <label>Mínimo</label>
                        <select id="rating_range_min">
                            <option value="">Mín</option>
                            <?php for ($r = 1; $r <= 10; $r++): ?>
                            <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="movies-filter-input-group">
                        <label>Máximo</label>
                        <select id="rating_range_max">
                            <option value="">Máx</option>
                            <?php for ($r = 1; $r <= 10; $r++): ?>
                            <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="movies-filter-modal-footer">
            <button type="button" class="movies-filter-btn movies-filter-btn-secondary" data-modal="ratingModal">Cancelar</button>
            <button type="button" class="movies-filter-btn movies-filter-btn-primary" id="ratingModalApply">Aplicar</button>
        </div>
    </div>
</div>

<div class="movies-filter-modal" id="yearModal">
    <div class="movies-filter-modal-content">
        <div class="movies-filter-modal-header">
            <h3>Filtrar por Año</h3>
            <button type="button" class="movies-filter-modal-close" data-modal="yearModal">&times;</button>
        </div>
        <div class="movies-filter-modal-body">
            <div class="movies-filter-option-type">
                <label>Tipo de filtro</label>
                <div class="movies-filter-radio-group">
                    <div class="movies-filter-radio-item">
                        <input type="radio" name="year_type" id="year_type_single" value="single" checked>
                        <label for="year_type_single">Año específico</label>
                    </div>
                    <div class="movies-filter-radio-item">
                        <input type="radio" name="year_type" id="year_type_range" value="range">
                        <label for="year_type_range">Rango</label>
                    </div>
                </div>
            </div>
            <div id="year_single_inputs">
                <div class="movies-filter-input-group">
                    <label>Año</label>
                    <select id="year_single_value">
                        <option value="">Seleccionar</option>
                        <?php for ($y = 1970; $y <= 2025; $y++): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div id="year_range_inputs" style="display:none;">
                <div class="movies-filter-inputs">
                    <div class="movies-filter-input-group">
                        <label>Mínimo</label>
                        <select id="year_range_min">
                            <option value="">Mín</option>
                            <?php for ($y = 1970; $y <= 2025; $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="movies-filter-input-group">
                        <label>Máximo</label>
                        <select id="year_range_max">
                            <option value="">Máx</option>
                            <?php for ($y = 1970; $y <= 2025; $y++): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="movies-filter-modal-footer">
            <button type="button" class="movies-filter-btn movies-filter-btn-secondary" data-modal="yearModal">Cancelar</button>
            <button type="button" class="movies-filter-btn movies-filter-btn-primary" id="yearModalApply">Aplicar</button>
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
<script src="./scripts/movies/filters.js"></script>
<script src="./scripts/movies/modals.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const cleanedParams = new URLSearchParams();
    let hasChanges = false;
    
    const rating = urlParams.get('rating') || '';
    const year = urlParams.get('year') || '';
    
    for (const [key, value] of urlParams.entries()) {
        if (value === null || value === '' || value === undefined) {
            hasChanges = true;
            continue;
        }
        
        if (key === 'rating') {
            cleanedParams.append('rating', value);
            continue;
        }
        
        if (key === 'rating_min' || key === 'rating_max') {
            if (rating === '') {
                const min = urlParams.get('rating_min') || '';
                const max = urlParams.get('rating_max') || '';
                if (min !== '' && max !== '') {
                    if (key === 'rating_min') {
                        cleanedParams.append('rating_min', min);
                        cleanedParams.append('rating_max', max);
                    }
                } else {
                    hasChanges = true;
                }
            } else {
                hasChanges = true;
            }
            continue;
        }
        
        if (key === 'year') {
            cleanedParams.append('year', value);
            continue;
        }
        
        if (key === 'year_min' || key === 'year_max') {
            if (year === '') {
                const min = urlParams.get('year_min') || '';
                const max = urlParams.get('year_max') || '';
                if (min !== '' && max !== '') {
                    if (key === 'year_min') {
                        cleanedParams.append('year_min', min);
                        cleanedParams.append('year_max', max);
                    }
                } else {
                    hasChanges = true;
                }
            } else {
                hasChanges = true;
            }
            continue;
        }
        
        if (key === 'orden_dir') {
            const orden = urlParams.get('orden') || '';
            if (orden !== '') {
                cleanedParams.append(key, value);
            } else {
                hasChanges = true;
            }
            continue;
        }
        
        cleanedParams.append(key, value);
    }
    
    if (hasChanges) {
        const newUrl = cleanedParams.toString() 
            ? `${window.location.pathname}?${cleanedParams.toString()}`
            : window.location.pathname;
        window.history.replaceState({}, '', newUrl);
    }
    
    const toggleBtn = document.getElementById('filtrosToggleBtn');
    const filtrosForm = document.getElementById('filtrosForm');
    const toggleText = document.getElementById('filtrosToggleText');
    
    if (toggleBtn && filtrosForm && toggleText) {
        toggleBtn.addEventListener('click', function() {
            if (filtrosForm.classList.contains('filtros-hidden')) {
                filtrosForm.classList.remove('filtros-hidden');
                toggleText.textContent = 'Ocultar Filtros';
            } else {
                filtrosForm.classList.add('filtros-hidden');
                toggleText.textContent = 'Mostrar Filtros';
            }
        });
    }
});
</script>

</body>
</html>
