<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: index.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$categoria = isset($_REQUEST['catg']) ? urldecode($_REQUEST['catg']) : 'Movies';
$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
$adulto = isset($_REQUEST['adulto']) ? trim($_REQUEST['adulto']) : '';
$sessao = isset($_REQUEST['sessao']) ? $_REQUEST['sessao'] : gerar_hash(32);

$peliculas_por_pagina = 48;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina_actual - 1) * $peliculas_por_pagina;

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams".($id ? "&category_id=$id" : "");
$resposta = apixtream($url);
$output = json_decode($resposta,true);

$backdrop_fondo = '';
if ($output && is_array($output) && count($output) > 0) {
    $pelicula_aleatoria = $output[array_rand($output)];
    $vod_id = $pelicula_aleatoria['stream_id'];
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
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

// FILTROS
if (isset($_GET['genero']) && $_GET['genero'] != '') {
    $output = array_filter($output, function($p) {
        $g = isset($_GET['genero']) ? $_GET['genero'] : '';
        if (!isset($p['genre'])) return false;
        $gs = is_array($p['genre']) ? $p['genre'] : explode(',', $p['genre']);
        foreach ($gs as $gg) {
            if (trim($gg) == $g) return true;
        }
        return false;
    });
}
if (isset($_GET['rating_min']) && isset($_GET['rating_max'])) {
    $min = floatval($_GET['rating_min']);
    $max = floatval($_GET['rating_max']);
    $output = array_filter($output, function($p) use ($min, $max) {
        $r = isset($p['rating_5based']) ? floatval($p['rating_5based'])*2 : (isset($p['rating']) ? floatval($p['rating']) : 0);
        return $r >= $min && $r <= $max;
    });
}
if (isset($_GET['year_min']) && isset($_GET['year_max'])) {
    $min = intval($_GET['year_min']);
    $max = intval($_GET['year_max']);
    $output = array_filter($output, function($p) use ($min, $max) {
        $y = isset($p['year']) ? intval($p['year']) : 0;
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
            return intval($b['year']) <=> intval($a['year']);
        });
    } elseif ($_GET['orden'] == 'rating') {
        usort($output, function($a, $b) {
            $ra = isset($a['rating_5based']) ? floatval($a['rating_5based'])*2 : (isset($a['rating']) ? floatval($a['rating']) : 0);
            $rb = isset($b['rating_5based']) ? floatval($b['rating_5based'])*2 : (isset($b['rating']) ? floatval($b['rating']) : 0);
            return $rb <=> $ra;
        });
    } elseif ($_GET['orden'] == 'recientes') {
        usort($output, function($a, $b) {
            $timeA = isset($a['added']) ? intval($a['added']) : 0;
            $timeB = isset($b['added']) ? intval($b['added']) : 0;
            return $timeB <=> $timeA;
        });
    } elseif ($_GET['orden'] == 'antiguas') {
        usort($output, function($a, $b) {
            $timeA = isset($a['added']) ? intval($a['added']) : 0;
            $timeB = isset($b['added']) ? intval($b['added']) : 0;
            return $timeA <=> $timeB;
        });
    }
}

// Recalcular paginación después de filtrar
$total_peliculas = is_array($output) ? count($output) : 0;
$total_paginas = ceil($total_peliculas / $peliculas_por_pagina);
$peliculas_pagina = ($output && is_array($output)) ? array_slice(array_values($output), $inicio, $peliculas_por_pagina) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="./css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="./css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="./css/owl.carousel.min.css">
    <link rel="stylesheet" href="./css/jquery.mcustomscrollbar.min.css">
    <link rel="stylesheet" href="./css/nouislider.min.css">
    <link rel="stylesheet" href="./css/ionicons.min.css">
    <link rel="stylesheet" href="./css/photoswipe.css">
    <link rel="stylesheet" href="./css/glightbox.css">
    <link rel="stylesheet" href="./css/default-skin.css">
    <link rel="stylesheet" href="./css/jBox.all.min.css">
    <link rel="stylesheet" href="./css/select2.min.css">
    <link rel="stylesheet" href="./css/listings.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="shortcut icon" href="img/favicon.ico">
    <title>PLAYGO - Películas</title>
    <style>
        body {
            background: transparent !important;
        }
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
    /* HEADER estilo painel.php */
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
    .card__cover img {
    width: 100%;
    height: 440px;      /* Puedes ajustar la altura según tu diseño */
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
/* Solo para POPULAR ESTE MES en desktop */
.popular-img {
    height: 280px !important; /* O el alto que prefieras */
}

@media (max-width: 600px) {
  .card__cover img,
  .popular-img {
    height: 260px !important;
  }
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
        .footer {
            background: transparent !important;
            box-shadow: none !important;
        }
        html, body {
            height: 100vh;
            overflow: hidden;
        }
        .main-bg-fondo {
            height: 100vh;
            min-height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            /* El resto de tus estilos */
        }
        @media (max-width: 600px) {
  /* Oculta filtro de calificación y año en móviles */
  .filtro-opcion label[for="rating_min"],
  .filtro-opcion label[for="year_min"],
  .filtro-opcion #rating_range,
  .filtro-opcion #year_range,
  .filtro-opcion #rating_min,
  .filtro-opcion #rating_max,
  .filtro-opcion #year_min,
  .filtro-opcion #year_max,
  .filtro-opcion #rating_slider_box,
  .filtro-opcion #year_slider_box {
    display: none !important;
  }
  /* Opcional: si tus filtros están en divs separados, puedes ocultar todo el div */
  .filtro-opcion:nth-child(2), /* Calificación */
  .filtro-opcion:nth-child(3)  /* Año */ {
    display: none !important;
  }
}
@media (max-width: 600px) {
  /* Sube el título */
  .main-bg-fondo > div[style*="PELÍCULAS"] {
    margin-top: 70px !important; /* antes 130px */
    margin-bottom: 18px !important;
  }
  /* Sube los filtros */
  .filtros-bar {
    margin-top: 0 !important;
    margin-bottom: 18px !important;
  }
  /* Sube las películas (catálogo) */
  .catalog.details {
    margin-top: 0 !important;
    padding-top: 0 !important;
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
    </style>
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
                                <a href="filmes.php" class="header__nav-link header__nav-link--active">Películas</a>
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

<!-- CONTENIDO PRINCIPAL CON FONDO -->
<div class="main-bg-fondo">
    <!-- Título centrado arriba de los filtros -->
    <div style="width:100%;text-align:center;margin:130px 0 30px 0;">
        <h2 style="font-size:2.5rem;font-weight:800;letter-spacing:2px;color:#fff;display:inline-block;padding:10px 40px;border-radius:12px;">PELÍCULAS</h2>
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
                foreach ($output as $pelicula) {
                    if (!empty($pelicula['genre'])) {
                        $gs = is_array($pelicula['genre']) ? $pelicula['genre'] : explode(',', $pelicula['genre']);
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
    <div class="filtro-opcion" style="position:relative;">
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
    <div class="filtro-opcion" style="position:relative;">
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
            <option value="rating" <?php if(isset($_GET['orden']) && $_GET['orden']=='rating') echo 'selected'; ?>>Rating</option>
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
if ($peliculas_pagina && is_array($peliculas_pagina)) {
    foreach($peliculas_pagina as $index) {
        $filme_nome = $index['name'];
        $filme_type = $index['stream_type'];
        $filme_id = $index['stream_id'];
        $filme_img = $index['stream_icon'];
        $filme_rat = $index['rating'];
        $avs = $index['rating_5based'];
        $filme_ano = isset($index['year']) ? $index['year'] : '';
?>
    <div class="col-6 col-sm-4 col-lg-3 col-xl-3">
        <div class="card">
            <div class="card__cover">
                <img loading="lazy" src="<?php echo $filme_img; ?>" alt="<?php echo htmlspecialchars($filme_nome); ?>">
                <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">                    <i class="fa-solid fa-circle-play"></i>
                </a>
            </div>
            <div class="card__content">
                <h3 class="card__title">
                        <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                        <?php
                        // Elimina el año entre paréntesis al final del nombre
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
            <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                <i class="fa-solid fa-circle-play"></i>
            </a>
        </div>
        <div class="card__content">
            <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                <span class="card__title" style="display:block;color:#fff;font-weight:600;font-size:1.05rem;margin-bottom:2px;">
                    <?php
                    // Elimina el año entre paréntesis al final del nombre
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

<!-- FIN main-bg-fondo -->


<script src="./js/jquery-3.5.1.min.js"></script>
<script src="./js/bootstrap.bundle.min.js"></script>
<script src="./js/owl.carousel.min.js"></script>
<script src="./js/jquery.mousewheel.min.js"></script>
<script src="./js/jquery.mcustomscrollbar.min.js"></script>
<script src="./js/wnumb.js"></script>
<script src="./js/nouislider.min.js"></script>
<script src="./js/jquery.morelines.min.js"></script>
<script src="./js/photoswipe.min.js"></script>
<script src="./js/photoswipe-ui-default.min.js"></script>
<script src="./js/glightbox.min.js"></script>
<script src="./js/jBox.all.min.js"></script>
<script src="./js/select2.min.js"></script>
<script src="./js/jwplayer.js"></script>
<script src="./js/jwplayer.core.controls.js"></script>
<script src="./js/provider.hlsjs.js"></script>
<script src="./js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
<script>
// Barra de calificación
const ratingSlider = document.getElementById('rating_slider');
const ratingSliderBox = document.getElementById('rating_slider_box');
const ratingMinInput = document.getElementById('rating_min');
const ratingMaxInput = document.getElementById('rating_max');
const ratingMinVal = document.getElementById('rating_min_val');
const ratingMaxVal = document.getElementById('rating_max_val');
let ratingMin = parseFloat(ratingMinInput.value) || 0.0;
let ratingMax = parseFloat(ratingMaxInput.value) || 10.0;
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
    yearSliderBox.style.display = 'none';
    e.stopPropagation();
};
document.addEventListener('click', function() {
    ratingSliderBox.style.display = 'none';
});

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
    ratingSliderBox.style.display = 'none';
    e.stopPropagation();
};
document.addEventListener('click', function() {
    yearSliderBox.style.display = 'none';
});
ratingSliderBox.onclick = function(e){ e.stopPropagation(); }
yearSliderBox.onclick = function(e){ e.stopPropagation(); }

document.getElementById('limpiarFiltrosBtn').onclick = function() {
    // Borra todos los filtros y recarga la página sin parámetros GET
    window.location.href = window.location.pathname;
};

</script>

</body>
</html>