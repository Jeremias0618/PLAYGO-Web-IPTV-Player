<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$categoria = isset($_REQUEST['catg']) ? urldecode($_REQUEST['catg']) : 'Series';
$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
$adulto = isset($_REQUEST['adulto']) ? trim($_REQUEST['adulto']) : '';
$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);

// --- Paginación ---
$series_por_pagina = 48;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina_actual - 1) * $series_por_pagina;

// Obtener series
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_series".($id ? "&category_id=$id" : "");
$resposta = apixtream($url);
$output = json_decode($resposta,true);

// FILTROS
if (isset($_GET['genero']) && $_GET['genero'] != '') {
    $output = array_filter($output, function($s) {
        $g = isset($_GET['genero']) ? $_GET['genero'] : '';
        if (!isset($s['genre'])) return false;
        $gs = is_array($s['genre']) ? $s['genre'] : explode(',', $s['genre']);
        foreach ($gs as $gg) {
            if (trim($gg) == $g) return true;
        }
        return false;
    });
}
if (isset($_GET['rating_min']) && isset($_GET['rating_max'])) {
    $min = floatval($_GET['rating_min']);
    $max = floatval($_GET['rating_max']);
    $output = array_filter($output, function($s) use ($min, $max) {
        $r = isset($s['rating']) ? floatval($s['rating']) : 0;
        return $r >= $min && $r <= $max;
    });
}
if (isset($_GET['year_min']) && isset($_GET['year_max'])) {
    $min = intval($_GET['year_min']);
    $max = intval($_GET['year_max']);
    $output = array_filter($output, function($s) use ($min, $max) {
        $y = isset($s['releaseDate']) ? intval(substr($s['releaseDate'],0,4)) : (isset($s['year']) ? intval($s['year']) : 0);
        return $y >= $min && $y <= $max;
    });
}
if (isset($_GET['orden']) && $_GET['orden'] != '') {
    if ($_GET['orden'] == 'nombre') {
        usort($output, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
    } elseif ($_GET['orden'] == 'año') {
        usort($output, function($a, $b) {
            $ya = isset($a['releaseDate']) ? intval(substr($a['releaseDate'],0,4)) : (isset($a['year']) ? intval($a['year']) : 0);
            $yb = isset($b['releaseDate']) ? intval(substr($b['releaseDate'],0,4)) : (isset($b['year']) ? intval($b['year']) : 0);
            return $yb <=> $ya;
        });
    } elseif ($_GET['orden'] == 'rating') {
        usort($output, function($a, $b) {
            $ra = isset($a['rating']) ? floatval($a['rating']) : 0;
            $rb = isset($b['rating']) ? floatval($b['rating']) : 0;
            return $rb <=> $ra;
        });
    } elseif ($_GET['orden'] == 'recientes') {
        usort($output, function($a, $b) {
            $timeA = isset($a['last_modified']) ? intval($a['last_modified']) : (isset($a['added']) ? intval($a['added']) : 0);
            $timeB = isset($b['last_modified']) ? intval($b['last_modified']) : (isset($b['added']) ? intval($b['added']) : 0);
            return $timeB <=> $timeA;
        });
    } elseif ($_GET['orden'] == 'antiguas') {
        usort($output, function($a, $b) {
            $timeA = isset($a['last_modified']) ? intval($a['last_modified']) : (isset($a['added']) ? intval($a['added']) : 0);
            $timeB = isset($b['last_modified']) ? intval($b['last_modified']) : (isset($b['added']) ? intval($b['added']) : 0);
            return $timeA <=> $timeB;
        });
    }
}

// Recalcular paginación después de filtrar
$total_series = is_array($output) ? count($output) : 0;
$total_paginas = ceil($total_series / $series_por_pagina);
$series_pagina = ($output && is_array($output)) ? array_slice(array_values($output), $inicio, $series_por_pagina) : [];

$backdrop_fondo = '';
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
    <title>PLAYGO - Series</title>
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

<!-- CONTENIDO PRINCIPAL CON FONDO OSCURO -->
<div class="main-bg-fondo">
    <div class="movies-page-title">
        <h2>SERIES</h2>
    </div>
    
    <button type="button" class="filtros-toggle-btn" id="filtrosToggleBtn">
        <i class="fa fa-filter"></i> <span id="filtrosToggleText">Mostrar Filtros</span>
    </button>

<form id="filtrosForm" method="get" class="filtros-bar filtros-hidden">
    <div class="filtro-opcion" style="position:relative;">
        <label for="genero">GÉNERO</label>
        <select name="genero" id="genero">
            <option value="">Todos</option>
            <?php
            $generos = [];
            if ($output && is_array($output)) {
                foreach ($output as $serie) {
                    if (!empty($serie['genre'])) {
                        $gs = is_array($serie['genre']) ? $serie['genre'] : explode(',', $serie['genre']);
                        foreach ($gs as $g) {
                            $g = trim($g);
                            if ($g && !in_array($g, $generos)) $generos[] = $g;
                        }
                    }
                }
            }
            sort($generos);
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
            $ratingMin = isset($_GET['rating_min']) ? floatval($_GET['rating_min']) : null;
            $ratingMax = isset($_GET['rating_max']) ? floatval($_GET['rating_max']) : null;
            if ($ratingMin !== null && $ratingMax !== null) {
                if ($ratingMin == $ratingMax) {
                    echo number_format($ratingMin, 1);
                } else {
                    echo number_format($ratingMin, 1) . ' - ' . number_format($ratingMax, 1);
                }
            } else {
                echo 'Todos';
            }
            ?>
        </div>
        <input type="hidden" name="rating_min" id="rating_min" value="<?php echo isset($_GET['rating_min']) ? $_GET['rating_min'] : ''; ?>">
        <input type="hidden" name="rating_max" id="rating_max" value="<?php echo isset($_GET['rating_max']) ? $_GET['rating_max'] : ''; ?>">
    </div>
    <div class="filtro-opcion">
        <label for="year_display">AÑO</label>
        <div class="movies-filter-display" id="year_display">
            <?php
            $yearMin = isset($_GET['year_min']) ? intval($_GET['year_min']) : null;
            $yearMax = isset($_GET['year_max']) ? intval($_GET['year_max']) : null;
            if ($yearMin !== null && $yearMax !== null) {
                if ($yearMin == $yearMax) {
                    echo $yearMin;
                } else {
                    echo $yearMin . ' - ' . $yearMax;
                }
            } else {
                echo 'Todos';
            }
            ?>
        </div>
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
if ($series_pagina && is_array($series_pagina)) {
    foreach($series_pagina as $index) {
        // Elimina el año entre paréntesis del nombre
        $serie_nome = preg_replace('/\s*\(\d{4}\)$/', '', $index['name']);
        $serie_id = $index['series_id'];
        $serie_img = $index['cover'];
        $serie_ano = isset($index['releaseDate']) ? substr($index['releaseDate'],0,4) : (isset($index['year']) ? $index['year'] : '');
        $serie_rate = isset($index['rating']) ? $index['rating'] : '';
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
        <div class="card">
            <div class="card__cover">
                <img loading="lazy" src="<?php echo $serie_img; ?>" alt="<?php echo htmlspecialchars($serie_nome); ?>">
                <a href="serie.php?stream=<?php echo $serie_id; ?>&streamtipo=serie" class="card__play">
                    <i class="fa-solid fa-circle-play"></i>
                </a>
            </div>
            <div class="card__content">
                <h3 class="card__title">
                    <a href="serie.php?stream=<?php echo $serie_id; ?>&streamtipo=serie">
                        <?php echo limitar_texto($serie_nome, 40); ?>
                    </a>
                </h3>
                <span class="card__rate">
                    <?php echo $serie_ano; ?>
                    <?php if($serie_rate !== ''): ?>
                        &nbsp; <i class="fa-solid fa-star"></i><?php echo $serie_rate; ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
<?php
    }
} else {
    echo '<div style="color:#fff;font-size:1.2rem;">No hay series en esta categoría.</div>';
}
?>
        </div>
        <!-- PAGINADOR COMPACTO -->
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <ul class="custom-paginator">
                    <?php
                    $total_paginas = max(1, $total_paginas);
                    if($pagina_actual > 1) {
                        echo '<li><a class="arrow" href="?'.http_build_query(array_merge($_GET, ['pagina'=>($pagina_actual-1)])).'">&#60;</a></li>';
                    } else {
                        echo '<li class="disabled"><span class="arrow">&#60;</span></li>';
                    }
                    if($pagina_actual == 1) {
                        echo '<li class="active"><span>1</span></li>';
                    } else {
                        echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>1])).'">1</a></li>';
                    }
                    if($total_paginas >= 2) {
                        if($pagina_actual == 2) {
                            echo '<li class="active"><span>2</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>2])).'">2</a></li>';
                        }
                    }
                    if($total_paginas >= 3) {
                        if($pagina_actual == 3) {
                            echo '<li class="active"><span>3</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>3])).'">3</a></li>';
                        }
                    }
                    if($total_paginas > 4) {
                        if($pagina_actual > 3 && $pagina_actual < $total_paginas - 1) {
                            echo '<li class="disabled"><span>...</span></li>';
                            if($pagina_actual != $total_paginas && $pagina_actual != 1 && $pagina_actual != 2 && $pagina_actual != 3) {
                                echo '<li class="active"><span>'.$pagina_actual.'</span></li>';
                                echo '<li class="disabled"><span>...</span></li>';
                            }
                        } else {
                            echo '<li class="disabled"><span>...</span></li>';
                        }
                        if($pagina_actual == $total_paginas) {
                            echo '<li class="active"><span>'.$total_paginas.'</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>$total_paginas])).'">'.$total_paginas.'</a></li>';
                        }
                    } elseif($total_paginas == 4) {
                        if($pagina_actual == 4) {
                            echo '<li class="active"><span>4</span></li>';
                        } else {
                            echo '<li><a href="?'.http_build_query(array_merge($_GET, ['pagina'=>4])).'">4</a></li>';
                        }
                    }
                    if($pagina_actual < $total_paginas) {
                        echo '<li><a class="arrow" href="?'.http_build_query(array_merge($_GET, ['pagina'=>($pagina_actual+1)])).'">&#62;</a></li>';
                    } else {
                        echo '<li class="disabled"><span class="arrow">&#62;</span></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- POPULAR ESTE MES ESTILO -->
<section class="section section-popular">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="home__title bottom-margin-sml">POPULAR <b>ESTE MES</b></h1>
            </div>
            <?php
            // Selecciona 6 películas populares (por rating descendente)
            $populares = [];
            if ($output && is_array($output)) {
                $populares = $output;
                usort($populares, function($a, $b) {
                    return floatval($b['rating']) <=> floatval($a['rating']);
                });
                $populares = array_slice($populares, 0, 6);
            }
foreach($populares as $pop) {
    // Elimina el año entre paréntesis del nombre
    $serie_nome = preg_replace('/\s*\(\d{4}\)$/', '', $pop['name']);
    $serie_img = $pop['cover'];
    $serie_ano = isset($pop['releaseDate']) ? substr($pop['releaseDate'],0,4) : (isset($pop['year']) ? $pop['year'] : '');
    $serie_id = $pop['series_id'];
    $serie_rate = isset($pop['rating']) ? $pop['rating'] : '';
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
        <div class="card">
            <div class="card__cover">
            <img loading="lazy" class="popular-img" src="<?php echo $serie_img; ?>" alt="">
            <a href="serie.php?stream=<?php echo $serie_id; ?>&streamtipo=serie" class="card__play">
                    <i class="fa-solid fa-circle-play"></i>
                </a>
            </div>
            <div class="card__content">
                <h3 class="card__title">
                    <a href="serie.php?stream=<?php echo $serie_id; ?>&streamtipo=serie">
                        <?php echo limitar_texto($serie_nome, 40); ?>
                    </a>
                </h3>
                <span class="card__rate">
                    <?php echo $serie_ano; ?>
                    <?php if($serie_rate !== ''): ?>
                        &nbsp; <i class="fa-solid fa-star"></i><?php echo $serie_rate; ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
<?php } ?>
            <div class="col-12 d-flex justify-content-center">
                <a href="series_popular.php" class="section__btn">Ver más</a>
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
                        <?php for ($r = 0.0; $r <= 10.0; $r += 0.1): $r = round($r, 1); ?>
                        <option value="<?php echo $r; ?>"><?php echo number_format($r, 1); ?></option>
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
                            <?php for ($r = 0.0; $r <= 10.0; $r += 0.1): $r = round($r, 1); ?>
                            <option value="<?php echo $r; ?>"><?php echo number_format($r, 1); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="movies-filter-input-group">
                        <label>Máximo</label>
                        <select id="rating_range_max">
                            <option value="">Máx</option>
                            <?php for ($r = 0.0; $r <= 10.0; $r += 0.1): $r = round($r, 1); ?>
                            <option value="<?php echo $r; ?>"><?php echo number_format($r, 1); ?></option>
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
<script src="./scripts/series/filters.js"></script>
<script src="./scripts/series/modals.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
