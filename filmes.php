<?php
require_once("libs/lib.php");

// Redirigir si no hay sesión iniciada
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

// --- Paginación ---
$peliculas_por_pagina = 48;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina_actual - 1) * $peliculas_por_pagina;

// Obtener películas
$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams".($id ? "&category_id=$id" : "");
$resposta = apixtream($url);
$output = json_decode($resposta,true);

// --- FONDO ALEATORIO ---
$backdrop_fondo = '';
if ($output && is_array($output) && count($output) > 0) {
    // Elegir una película aleatoria del listado
    $pelicula_aleatoria = $output[array_rand($output)];
    $vod_id = $pelicula_aleatoria['stream_id'];
    // Llamar a get_vod_info para obtener el backdrop_path (array de URLs)
    $url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$vod_id";
    $res_info = apixtream($url_info);
    $data_info = json_decode($res_info, true);
    if (
        isset($data_info['info']['backdrop_path']) &&
        is_array($data_info['info']['backdrop_path']) &&
        count($data_info['info']['backdrop_path']) > 0
    ) {
        // Usar el primer backdrop disponible
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
    /* MODAL BUSCADOR MEJORADO */
    .modal-buscador-bg {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100vw; height: 100vh;
        background: linear-gradient(135deg, rgba(15,32,39,0.95) 0%, rgba(44,83,100,0.95) 100%);
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal-buscador-bg.active {
        display: flex;
        opacity: 1;
    }
    .modal-buscador {
        background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
        border-radius: 20px;
        padding: 0;
        max-width: 800px;
        width: 90vw;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.8);
        position: relative;
        transform: scale(0.9);
        transition: transform 0.3s ease;
        border: 1px solid rgba(229,9,20,0.3);
    }
    .modal-buscador-bg.active .modal-buscador {
        transform: scale(1);
    }
    .modal-buscador-header {
        background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
        padding: 20px 24px;
        border-radius: 20px 20px 0 0;
        position: relative;
        overflow: hidden;
    }
    .modal-buscador-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
    .modal-buscador-title {
        color: #fff;
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .modal-buscador-close {
        position: absolute;
        top: 20px;
        right: 24px;
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.2);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 2;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-buscador-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }
    .modal-buscador-body {
        padding: 24px;
        max-height: calc(85vh - 100px);
        overflow-y: auto;
    }
    .modal-buscador-inputbox {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
        background: rgba(255,255,255,0.05);
        border-radius: 16px;
        padding: 4px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .modal-buscador-inputbox input {
        flex: 1;
        background: transparent;
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: 16px 20px;
        font-size: 1.1rem;
        margin: 0;
        outline: none;
    }
    .modal-buscador-inputbox input::placeholder {
        color: rgba(255,255,255,0.6);
    }
    .modal-buscador-inputbox button {
        background: linear-gradient(90deg, #e50914 0%, #c8008f 100%);
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: 16px 24px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-left: 4px;
        white-space: nowrap;
    }
    .modal-buscador-inputbox button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(229,9,20,0.4);
    }
    .modal-buscador-inputbox button:active {
        transform: translateY(0);
    }
    .modal-buscador-section {
        margin-bottom: 32px;
    }
    .modal-buscador-section h3 {
        color: #e50914;
        font-size: 1.2rem;
        margin-bottom: 20px;
        margin-top: 0;
        font-weight: 700;
        text-align: left;
        padding-left: 8px;
        border-left: 4px solid #e50914;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modal-buscador-section h3::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #e50914;
        border-radius: 50%;
    }
    .modal-buscador-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 16px;
        justify-content: start;
    }
    .modal-buscador-card {
        text-align: center;
        transition: transform 0.2s ease;
    }
    .modal-buscador-card:hover {
        transform: translateY(-4px);
    }
    .modal-buscador-card a {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .modal-buscador-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 12px;
        background: #232027;
        margin-bottom: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    .modal-buscador-card:hover img {
        border-color: #e50914;
        box-shadow: 0 8px 24px rgba(229,9,20,0.3);
    }
    .modal-buscador-card span {
        color: #fff;
        font-size: 0.9rem;
        display: block;
        font-weight: 500;
        line-height: 1.3;
        padding: 0 4px;
        word-break: break-word;
    }
    .modal-buscador-empty {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255,255,255,0.7);
    }
    .modal-buscador-empty i {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.5;
    }
    .modal-buscador-empty p {
        font-size: 1.1rem;
        margin: 0;
    }
    .modal-buscador-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filter-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filter-btn.active {
        background: #e50914;
        border-color: #e50914;
    }
    .filter-btn:hover {
        background: rgba(255,255,255,0.2);
    }
    .filter-btn.active:hover {
        background: #c8008f;
    }
    
    /* Estilos específicos para desktop del buscador */
    @media (min-width: 1200px) {
        .modal-buscador {
            max-width: 700px !important;
            width: 700px !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 1199px) {
        .modal-buscador {
            max-width: 750px !important;
            width: 85vw !important;
        }
    }
    
    /* Estilos específicos para móviles del buscador */
    @media (max-width: 600px) {
        .modal-buscador {
            width: 98vw !important;
            max-width: 98vw !important;
            border-radius: 16px !important;
            margin: 10px;
        }
        .modal-buscador-header {
            padding: 16px 20px;
            border-radius: 16px 16px 0 0;
        }
        .modal-buscador-title {
            font-size: 1.2rem;
        }
        .modal-buscador-body {
            padding: 20px 16px;
        }
        .modal-buscador-inputbox {
            flex-direction: column;
            gap: 12px;
            padding: 8px;
        }
        .modal-buscador-inputbox input {
            width: 100%;
            font-size: 1rem;
            padding: 14px 16px;
        }
        .modal-buscador-inputbox button {
            width: 100%;
            padding: 14px 20px;
            font-size: 1rem;
        }
        .modal-buscador-filters {
            justify-content: center;
            gap: 8px;
        }
        .filter-btn {
            padding: 10px 14px;
            font-size: 0.9rem;
            min-width: 80px;
        }
        .modal-buscador-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
        }
        .modal-buscador-card img {
            height: 140px;
        }
        .modal-buscador-card span {
            font-size: 0.85rem;
            line-height: 1.2;
        }
        .modal-buscador-close {
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }
    }
    
    /* Animaciones adicionales para el buscador */
    .modal-buscador-card {
        animation: fadeInUp 0.4s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    .modal-buscador-card:nth-child(1) { animation-delay: 0.1s; }
    .modal-buscador-card:nth-child(2) { animation-delay: 0.15s; }
    .modal-buscador-card:nth-child(3) { animation-delay: 0.2s; }
    .modal-buscador-card:nth-child(4) { animation-delay: 0.25s; }
    .modal-buscador-card:nth-child(5) { animation-delay: 0.3s; }
    .modal-buscador-card:nth-child(6) { animation-delay: 0.35s; }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Mejoras para el estado de carga */
    .modal-buscador-loading {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255,255,255,0.7);
    }
    
    .modal-buscador-loading i {
        font-size: 2rem;
        margin-bottom: 16px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .modal-buscador-card span {
        color: #fff;
        font-size: 0.98rem;
        display: block;
        margin-top: 2px;
        word-break: break-word;
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
                            <img src="img/logo.png" alt="" height="48px">
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
<!-- MODAL BUSCADOR MEJORADO -->
<div class="modal-buscador-bg" id="modalBuscador">
    <div class="modal-buscador">
        <div class="modal-buscador-header">
            <h2 class="modal-buscador-title">
                <i class="fas fa-search"></i> Buscador PLAYGO
            </h2>
            <button class="modal-buscador-close" id="closeSearchModal" title="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-buscador-body">
            <form id="modalBuscadorForm" autocomplete="off" onsubmit="return false;">
                <div class="modal-buscador-inputbox">
                    <input type="text" id="modalBuscadorInput" placeholder="Buscar películas, series o canales..." autofocus>
                    <button type="button" id="modalBuscadorBtn">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>
            
            <!-- Filtros de búsqueda -->
            <div class="modal-buscador-filters" id="searchFilters">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-th-large"></i> Todo
                </button>
                <button class="filter-btn" data-filter="movies">
                    <i class="fas fa-film"></i> Películas
                </button>
                <button class="filter-btn" data-filter="series">
                    <i class="fas fa-tv"></i> Series
                </button>
                <button class="filter-btn" data-filter="channels">
                    <i class="fas fa-broadcast-tower"></i> TV
                </button>
            </div>
            
            <div id="modalBuscadorResults"></div>
        </div>
    </div>
</div>

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
<script>
    // MODAL BUSCADOR MEJORADO
    const openSearchModal = document.getElementById('openSearchModal');
    const closeSearchModal = document.getElementById('closeSearchModal');
    const modalBuscador = document.getElementById('modalBuscador');
    const modalBuscadorInput = document.getElementById('modalBuscadorInput');
    const modalBuscadorBtn = document.getElementById('modalBuscadorBtn');
    const modalBuscadorResults = document.getElementById('modalBuscadorResults');
    const searchFilters = document.getElementById('searchFilters');

    let currentFilter = 'all';
    let searchTimeout;

    function showModalBuscador() {
        modalBuscador.classList.add('active');
        setTimeout(() => { 
            modalBuscadorInput.focus();
            modalBuscadorInput.select();
        }, 300);
    }
    
    function hideModalBuscador() {
        modalBuscador.classList.remove('active');
        setTimeout(() => {
            modalBuscadorInput.value = '';
            modalBuscadorResults.innerHTML = '';
            resetFilters();
        }, 300);
    }
    
    function resetFilters() {
        currentFilter = 'all';
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector('[data-filter="all"]').classList.add('active');
    }

    openSearchModal.onclick = showModalBuscador;
    closeSearchModal.onclick = hideModalBuscador;
    
    window.addEventListener('keydown', function(e) {
        if (e.key === "Escape") hideModalBuscador();
    });
    
    modalBuscador.addEventListener('click', function(e) {
        if (e.target === modalBuscador) hideModalBuscador();
    });

    // Cargar datos para búsqueda (películas, series, canales)
    let peliculas = [];
    let series = [];
    let canales = [];
    <?php
    // Películas
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
    $resposta = apixtream($url);
    $peliculas = json_decode($resposta,true);
    echo "peliculas = ".json_encode(array_map(function($p){
        return [
            'id'=>$p['stream_id'],
            'nombre'=>$p['name'],
            'img'=>$p['stream_icon']
        ];
    },$peliculas)).";\n";
    // Series
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
    $resposta = apixtream($url);
    $series = json_decode($resposta,true);
    echo "series = ".json_encode(array_map(function($s){
        return [
            'id'=>$s['series_id'],
            'nombre'=>$s['name'],
            'img'=>$s['cover']
        ];
    },$series)).";\n";
    // Canales
    $url = IP."/player_api.php?username=$user&password=$pwd&action=get_live_streams";
    $resposta = apixtream($url);
    $canales = json_decode($resposta,true);
    echo "canales = ".json_encode(array_map(function($c){
        return [
            'id'=>$c['stream_id'],
            'nombre'=>$c['name'],
            'img'=>$c['stream_icon'],
            'tipo'=>$c['stream_type']
        ];
    },$canales)).";\n";
    ?>

    // Función para normalizar texto (remover tildes y caracteres especiales)
    function normalizarTexto(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remover diacríticos (tildes, diéresis, etc.)
            .replace(/[^a-z0-9\s]/g, ' ') // Remover caracteres especiales, mantener solo letras, números y espacios
            .replace(/\s+/g, ' ') // Normalizar espacios múltiples
            .trim();
    }

    function renderBuscadorResults(query) {
        query = query.trim();
        let queryNormalizado = normalizarTexto(query);
        let html = '';
        let totalResults = 0;
        
        // Aplicar filtro
        let showMovies = currentFilter === 'all' || currentFilter === 'movies';
        let showSeries = currentFilter === 'all' || currentFilter === 'series';
        let showChannels = currentFilter === 'all' || currentFilter === 'channels';
        
        // Películas
        if (showMovies) {
            let pelis = peliculas.filter(p => {
                let nombreNormalizado = normalizarTexto(p.nombre);
                return nombreNormalizado.includes(queryNormalizado) || 
                       p.nombre.toLowerCase().includes(query.toLowerCase());
            });
            if (pelis.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-film"></i> PELÍCULAS (${pelis.length})</h3>
                    <div class="modal-buscador-grid">`;
                pelis.slice(0,12).forEach(p => {
                    html += `<div class="modal-buscador-card">
                        <a href="filme.php?stream=${p.id}&streamtipo=movie">
                            <img src="${p.img}" alt="${p.nombre}" onerror="this.src='img/logo.png'">
                            <span>${p.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += pelis.length;
            }
        }
        
        // Series
        if (showSeries) {
            let sers = series.filter(s => {
                let nombreNormalizado = normalizarTexto(s.nombre);
                return nombreNormalizado.includes(queryNormalizado) || 
                       s.nombre.toLowerCase().includes(query.toLowerCase());
            });
            if (sers.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-tv"></i> SERIES (${sers.length})</h3>
                    <div class="modal-buscador-grid">`;
                sers.slice(0,12).forEach(s => {
                    html += `<div class="modal-buscador-card">
                        <a href="serie.php?stream=${s.id}&streamtipo=serie">
                            <img src="${s.img}" alt="${s.nombre}" onerror="this.src='img/logo.png'">
                            <span>${s.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += sers.length;
            }
        }
        
        // Canales
        if (showChannels) {
            let chans = canales.filter(c => {
                let nombreNormalizado = normalizarTexto(c.nombre);
                return nombreNormalizado.includes(queryNormalizado) || 
                       c.nombre.toLowerCase().includes(query.toLowerCase());
            });
            if (chans.length > 0) {
                html += `<div class="modal-buscador-section">
                    <h3><i class="fas fa-broadcast-tower"></i> TV EN VIVO (${chans.length})</h3>
                    <div class="modal-buscador-grid">`;
                chans.slice(0,12).forEach(c => {
                    html += `<div class="modal-buscador-card">
                        <a href="canal.php?stream=${c.id}">
                            <img src="${c.img}" alt="${c.nombre}" onerror="this.src='img/logo.png'">
                            <span>${c.nombre}</span>
                        </a>
                    </div>`;
                });
                html += `</div></div>`;
                totalResults += chans.length;
            }
        }
        
        if (!html && query.length > 0) {
            html = `<div class="modal-buscador-empty">
                <i class="fas fa-search"></i>
                <p>No se encontraron resultados para "${query}"</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Intenta con otros términos o cambia el filtro</p>
            </div>`;
        } else if (query.length > 0) {
            html = `<div style="text-align: center; margin-bottom: 20px; color: rgba(255,255,255,0.7);">
                <i class="fas fa-info-circle"></i> Se encontraron ${totalResults} resultados
            </div>` + html;
        }
        
        modalBuscadorResults.innerHTML = html;
    }

    // Filtros de búsqueda
    searchFilters.addEventListener('click', function(e) {
        if (e.target.classList.contains('filter-btn')) {
            // Remover clase active de todos los botones
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            // Agregar clase active al botón clickeado
            e.target.classList.add('active');
            currentFilter = e.target.getAttribute('data-filter');
            
            // Re-renderizar resultados si hay una búsqueda activa
            let query = modalBuscadorInput.value.trim();
            if (query.length > 1) {
                renderBuscadorResults(query);
            }
        }
    });

    // Buscar con debounce para mejor performance
    modalBuscadorInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        let q = this.value;
        
        if (q.length > 1) {
            searchTimeout = setTimeout(() => {
                renderBuscadorResults(q);
            }, 300);
        } else {
            modalBuscadorResults.innerHTML = '';
        }
    });
    
    // Buscar al hacer clic en botón
    modalBuscadorBtn.addEventListener('click', function() {
        let q = modalBuscadorInput.value;
        if (q.length > 1) renderBuscadorResults(q);
    });
    
    // Enter en input
    modalBuscadorInput.addEventListener('keydown', function(e){
        if(e.key === "Enter") {
            e.preventDefault();
            let q = modalBuscadorInput.value;
            if (q.length > 1) renderBuscadorResults(q);
        }
    });
</script>

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