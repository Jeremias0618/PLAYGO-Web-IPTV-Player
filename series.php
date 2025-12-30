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

// --- FONDO ALEATORIO DE SERIE ---
$backdrop_fondo = '';
if ($output && is_array($output) && count($output) > 0) {
    $serie_aleatoria = $output[array_rand($output)];
    $series_id = $serie_aleatoria['series_id'];
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_series_info&series_id=$series_id";
    $res_info = apixtream($url_info);
    $data_info = json_decode($res_info, true);
    if (
        isset($data_info['info']['backdrop_path']) &&
        is_array($data_info['info']['backdrop_path']) &&
        count($data_info['info']['backdrop_path']) > 0
    ) {
        $backdrop_fondo = $data_info['info']['backdrop_path'][0];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./styles/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./styles/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./styles/owl.carousel.min.css">
    <link rel="stylesheet" href="./styles/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./styles/nouislider.min.css">
    <link rel="stylesheet" href="./styles/ionicons.min.css">
    <link rel="stylesheet" href="./styles/photoswipe.css">
    <link rel="stylesheet" href="./styles/glightbox.css">
    <link rel="stylesheet" href="./styles/default-skin.css">
    <link rel="stylesheet" href="./styles/jBox.all.min.css">
    <link rel="stylesheet" href="./styles/select2.min.css">
    <link rel="stylesheet" href="./styles/listings.css">
    <link rel="stylesheet" href="./styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - Series</title>
    <style>
    .seasons__cover,
    .details__bg,
    .home__bg {
        filter: blur(0px) !important;
        opacity: 10%;
    }
    /* PAGINADOR COMPACTO */
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
    /* POPULAR ESTE MES */
    .section-popular {
        margin: 50px 0 0 0;
        padding-bottom: 30px;
        position: relative;
    }
    .section-popular .home__title {
        color: #fff;
        font-size: 2.1rem;
        margin-bottom: 32px;
        text-align: left;
        letter-spacing: 1px;
    }
    .section-popular .home__title b {
        color: #e50914;
        font-weight: 700;
    }
    .section-popular .section__btn {
        display: inline-block;
        margin: 30px auto 0 auto;
        background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);
        color: #fff;
        border-radius: 8px;
        padding: 18px 48px;
        font-size: 1.25rem;
        font-weight: 700;
        text-transform: uppercase;
        text-decoration: none;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        text-align: center;
        box-shadow: 0 2px 8px #0002;
        letter-spacing: 1px;
        border: none;
        line-height: 1.2;
        white-space: nowrap;
        min-width: 180px;
    }
    .section-popular .section__btn:hover {
        background: #fff;
        color: #e50914;
        box-shadow: 0 4px 16px #0003;
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
        
    /* FILTROS */
      .filtros-bar {
         display: flex;
         justify-content: center;
         gap: 32px;
         margin: 37px 0 51px 0;
         flex-wrap: wrap;
         align-items: end;
      }
      .filtros-bar .filtro-aplicar-btn,
      .filtros-bar .filtro-limpiar-btn {
         margin: 0;
      }
      .filtros-bar .filtros-botones {
         display: flex;
         gap: 3px; /* Espacio entre los botones */
         align-items: center;
      }

    .filtro-opcion {
        color:rgb(255, 255, 255);
        font-size: 0.95rem;
        font-weight: 400;
        margin-bottom: 0;
        text-align: left;
    }
    .filtro-opcion label {
        display: block;
        color: #ffffff;
        font-size: 0.85rem;
        margin-bottom: 2px;
        letter-spacing: 1px;
    }
    .filtro-opcion select,
    .filtro-opcion input[type="number"] {
        background: transparent;
        border: none;
        color: #fff;
        font-weight: 700;
        font-size: 1.08rem;
        outline: none;
        border-bottom: 2px solid #444;
        padding: 2px 6px 2px 0;
        margin-right: 4px;
        min-width: 60px;
    }
    .filtro-opcion select {
        min-width: 120px;
    }
    .filtro-opcion input[type="number"]::-webkit-inner-spin-button,
    .filtro-opcion input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .filtro-opcion .filtro-sep {
        color: #888;
        margin: 0 6px;
        font-weight: 400;
    }
    .filtro-opcion .filtro-igual {
        color: #888;
        margin-left: 6px;
        font-weight: 400;
    }
    .filtro-aplicar-btn {
        background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 24px;
        font-size: 1rem;
        font-weight: 600;
        margin-left: 18px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    .filtro-aplicar-btn:hover {
        background: #fff;
        color: #e50914;
    }

    .filtro-opcion select {
        background: #181818 !important;
        color: #fff !important;
        border: none;
    }
    .filtro-opcion select option {
        background: #181818 !important;
        color: #fff !important;
    }
    /* Quitar azul de opción seleccionada y hover, poner #f50b60 */
    .filtro-opcion select option:hover,
    .filtro-opcion select option:focus,
    .filtro-opcion select option:checked,
    .filtro-opcion select:focus option,
    .filtro-opcion select:active option {
        background:rgb(43, 43, 43) !important;
        color: #fff !important;
    }
    .filtro-opcion select:focus,
    .filtro-opcion select:hover {
        border-bottom: 2px solid #f50b60;
    }
    /* Botón limpiar filtros */
    .filtro-limpiar-btn {
        background: linear-gradient(180deg, #e50914 0%, #c8008f 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 18px;
        font-size: 1rem;
        font-weight: 600;
        margin-left: 10px;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filtro-limpiar-btn:hover {
        background: #fff;
        color: #e50914;
    }
    .filtro-limpiar-btn i {
        font-size: 1.1em;
        margin-right: 2px;
    }
    .header__wrap,
    .header__content {
        background: #000 !important;
    }
    <?php if($backdrop_fondo): ?>
    .main-bg-fondo {
        min-height: 100vh;
        background: url('<?php echo $backdrop_fondo; ?>') no-repeat center center fixed;
        background-size: cover;
        position: relative;
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
    
    html, body {
    height: 100%;
    min-height: 100%;
}
    .card__cover img {
    width: 100%;
    height: 440px;      /* Puedes ajustar la altura según tu diseño */
    object-fit: cover;
    border-radius: 10px;
    background: #232027;
    display: block;
}
/* Solo para POPULAR ESTE MES en desktop */
.popular-img {
    height: 280px !important; /* O el alto que prefieras */
}
@media (max-width: 600px) {
  .card__cover img {
    height: 260px !important;
  }
}

@media only screen and (max-width: 600px) and (pointer: coarse) and (hover: none) {
    .filtro-hide-mobile {
        display: none !important;
    }
    .main-bg-fondo > div[style*="text-align:center"] {
        margin-top: 80px !important;
        margin-bottom: 18px !important;
    }
    /* Subir los filtros */
    .filtros-bar {
        margin-top: 0 !important;
        margin-bottom: 18px !important;
    }
    /* Subir el catálogo de series */
    .catalog.details {
        margin-top: 0 !important;
    }
}

body {
    margin: 0;
    padding: 0;
    background: none !important;
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
    <?php endif; ?>
    </style>
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
                                <a href="./painel.php" class="header__nav-link">Inicio</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="./canais.php" class="header__nav-link">TV en Vivo</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="filmes.php" class="header__nav-link">Películas</a>
                            </li>
                            <li class="header__nav-item">
                                <a href="series.php" class="header__nav-link header__nav-link--active">Series</a>
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

<!-- CONTENIDO PRINCIPAL CON FONDO OSCURO -->
<div class="main-bg-fondo">
<!-- Título centrado arriba de los filtros -->
<div style="width:100%;text-align:center;margin:130px 0 30px 0;">
    <h2 style="font-size:2.5rem;font-weight:800;letter-spacing:2px;color:#fff;display:inline-block;padding:10px 40px;border-radius:12px;">SERIES</h2>
</div>
<!-- FILTROS EN ESPAÑOL -->
<form id="filtrosForm" method="get" class="filtros-bar">
    <div class="filtro-opcion" style="position:relative;">
        <label for="genero">GÉNERO:</label>
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
        <span class="filtro-igual"></span>
    </div>
<!-- FILTRO CALIFICACIÓN -->
<div class="filtro-opcion filtro-hide-mobile" style="position:relative;">
    <label for="rating_min" id="label_rating" style="cursor:pointer;">CALIFICACIÓN:</label>
    <span id="rating_range" style="cursor:pointer;">
        <span id="rating_min_val"><?php echo isset($_GET['rating_min']) ? $_GET['rating_min'] : '0'; ?></span> - 
        <span id="rating_max_val"><?php echo isset($_GET['rating_max']) ? $_GET['rating_max'] : '10'; ?></span>
    </span>
    <input type="hidden" name="rating_min" id="rating_min" value="<?php echo isset($_GET['rating_min']) ? $_GET['rating_min'] : '0'; ?>">
    <input type="hidden" name="rating_max" id="rating_max" value="<?php echo isset($_GET['rating_max']) ? $_GET['rating_max'] : '10'; ?>">
    <span class="filtro-igual"></span>
    <div id="rating_slider_box" style="display:none;position:absolute;top:55px;left:0;z-index:10;width:220px;">
        <div id="rating_slider"></div>
    </div>
</div>
<div class="filtro-opcion filtro-hide-mobile" style="position:relative;">
    <label for="year_min" id="label_year" style="cursor:pointer;">AÑO:</label>
    <span id="year_range" style="cursor:pointer;">
        <span id="year_min_val"><?php echo isset($_GET['year_min']) ? $_GET['year_min'] : '1900'; ?></span> - 
        <span id="year_max_val"><?php echo isset($_GET['year_max']) ? $_GET['year_max'] : '2025'; ?></span>
    </span>
    <input type="hidden" name="year_min" id="year_min" value="<?php echo isset($_GET['year_min']) ? $_GET['year_min'] : '1900'; ?>">
    <input type="hidden" name="year_max" id="year_max" value="<?php echo isset($_GET['year_max']) ? $_GET['year_max'] : '2025'; ?>">
    <span class="filtro-igual"></span>
    <div id="year_slider_box" style="display:none;position:absolute;top:55px;left:0;z-index:10;width:220px;">
        <div id="year_slider"></div>
    </div>
</div>
    <div class="filtro-opcion">
        <label for="orden">ORDENAR:</label>
        <select name="orden" id="orden">
            <option value="">Por defecto</option>
            <option value="nombre" <?php if(isset($_GET['orden']) && $_GET['orden']=='nombre') echo 'selected'; ?>>Nombre</option>
            <option value="año" <?php if(isset($_GET['orden']) && $_GET['orden']=='año') echo 'selected'; ?>>Año</option>
            <option value="recientes" <?php if(isset($_GET['orden']) && $_GET['orden']=='recientes') echo 'selected'; ?>>Más recientes</option>
            <option value="antiguas" <?php if(isset($_GET['orden']) && $_GET['orden']=='antiguas') echo 'selected'; ?>>Más antiguas</option>
        </select>
        <span class="filtro-igual"></span>
    </div>
    <div class="filtros-botones">
        <button type="submit" class="filtro-aplicar-btn">Aplicar</button>
        <button type="button" class="filtro-limpiar-btn" id="limpiarFiltrosBtn" title="Limpiar filtros">
            <i class="fa-solid fa-xmark"></i>
        </button>
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

<script src="./scripts/jquery-3.5.1.min.js"></script>
<script src="./scripts/bootstrap.bundle.min.js"></script>
<script src="./scripts/owl.carousel.min.js"></script>
<script src="./scripts/jquery.mousewheel.min.js"></script>
<script src="./scripts/jquery.mcustomscrollbar.min.js"></script>
<script src="./scripts/wnumb.js"></script>
<script src="./scripts/nouislider.min.js"></script>
<script src="./scripts/jquery.morelines.min.js"></script>
<script src="./scripts/photoswipe.min.js"></script>
<script src="./scripts/photoswipe-ui-default.min.js"></script>
<script src="./scripts/glightbox.min.js"></script>
<script src="./scripts/jBox.all.min.js"></script>
<script src="./scripts/select2.min.js"></script>
<script src="./scripts/jwplayer.js"></script>
<script src="./scripts/jwplayer.core.controls.js"></script>
<script src="./scripts/provider.hlsjs.js"></script>
<script src="./scripts/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
<script>
// Barra de año
const yearSlider = document.getElementById('year_slider');
const yearSliderBox = document.getElementById('year_slider_box');
const yearMinInput = document.getElementById('year_min');
const yearMaxInput = document.getElementById('year_max');
const yearMinVal = document.getElementById('year_min_val');
const yearMaxVal = document.getElementById('year_max_val');
let yearMin = parseInt(yearMinInput.value) || 1900;
let yearMax = parseInt(yearMaxInput.value) || 2025;
noUiSlider.create(yearSlider, {
    start: [yearMin, yearMax],
    connect: true,
    step: 1,
    range: { min: 1900, max: 2025 },
    tooltips: [true, true],
    format: {
        to: v => parseInt(v),
        from: v => parseInt(v)
    }
});
yearSlider.noUiSlider.on('update', function(values) {
    yearMinVal.textContent = values[0];
    yearMaxVal.textContent = values[1];
    yearMinInput.value = values[0];
    yearMaxInput.value = values[1];
});
document.getElementById('label_year').onclick = document.getElementById('year_range').onclick = function(e) {
    yearSliderBox.style.display = yearSliderBox.style.display === 'block' ? 'none' : 'block';
    e.stopPropagation();
};
document.addEventListener('click', function() {
    yearSliderBox.style.display = 'none';
});
yearSliderBox.onclick = function(e){ e.stopPropagation(); }

// Barra de calificación
const ratingSlider = document.getElementById('rating_slider');
const ratingSliderBox = document.getElementById('rating_slider_box');
const ratingMinInput = document.getElementById('rating_min');
const ratingMaxInput = document.getElementById('rating_max');
const ratingMinVal = document.getElementById('rating_min_val');
const ratingMaxVal = document.getElementById('rating_max_val');
let ratingMin = parseFloat(ratingMinInput.value) || 0;
let ratingMax = parseFloat(ratingMaxInput.value) || 10;
noUiSlider.create(ratingSlider, {
    start: [ratingMin, ratingMax],
    connect: true,
    step: 0.1,
    range: { min: 0, max: 10 },
    tooltips: [true, true],
    format: {
        to: v => parseFloat(v).toFixed(1),
        from: v => parseFloat(v)
    }
});
ratingSlider.noUiSlider.on('update', function(values) {
    ratingMinVal.textContent = values[0];
    ratingMaxVal.textContent = values[1];
    ratingMinInput.value = values[0];
    ratingMaxInput.value = values[1];
});
document.getElementById('label_rating').onclick = document.getElementById('rating_range').onclick = function(e) {
    ratingSliderBox.style.display = ratingSliderBox.style.display === 'block' ? 'none' : 'block';
    e.stopPropagation();
};
document.addEventListener('click', function() {
    ratingSliderBox.style.display = 'none';
});
ratingSliderBox.onclick = function(e){ e.stopPropagation(); }

document.getElementById('limpiarFiltrosBtn').onclick = function() {
    // Borra todos los filtros y recarga la página sin parámetros GET
    window.location.href = window.location.pathname;
};

</script>

</body>
</html>