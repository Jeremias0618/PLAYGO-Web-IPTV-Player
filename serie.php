<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$id = trim($_REQUEST['stream']);

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
        $tmdb_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season_num?api_key=" . TMDB_API_KEY . "&language=es-ES";
        $tmdb_json = @file_get_contents($tmdb_url);
        $tmdb_data = json_decode($tmdb_json, true);
        if (!empty($tmdb_data['episodes'])) {
            foreach ($tmdb_data['episodes'] as $ep) {
                if (!empty($ep['still_path'])) {
                    $img_url = "https://image.tmdb.org/t/p/w500" . $ep['still_path'];
                    $img_local = $cache_dir . "{$tmdb_id}_{$season_num}_{$ep['episode_number']}.jpg";
                    $img_local_url = "tmdb_cache/{$tmdb_id}_{$season_num}_{$ep['episode_number']}.jpg";
                    if (!file_exists($img_local)) {
                        $img_data = @file_get_contents($img_url);
                        if ($img_data) file_put_contents($img_local, $img_data);
                    }
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
    <title>PLAYGO - <?php echo htmlspecialchars($serie_nome); ?></title>
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
        .mobile-menu {
        display: none;
        }
        #mobileMenuOverlay {
        display: none;
        }
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
            max-width: 95vw;
            width: 95vw;
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
            max-height: 420px;
            height: auto;
            object-fit: cover;
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
            color: #fff;
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
            .serie-hero .poster { margin-top: 52px; }
            .episode-card img { height: 120px; }
        }
            @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
                margin-right: auto !important;
            }
            .header__content {
                padding-left: 4px !important;
            }
        }
        @media (max-width: 600px) {
        .serie-hero .title,
        .serie-hero .info {
            text-align: center;
            margin-left: 0;
        }
        .serie-hero .info {
            align-items: center;
            display: flex;
            flex-direction: column;
        }
        }
    .modal-buscador-bg {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.85);
        align-items: center;
        justify-content: center;
    }
    .modal-buscador-bg.active {
        display: flex;
    }
    .modal-buscador {
        background: #181818;
        border-radius: 18px;
        padding: 32px 24px 24px 24px;
        max-width: 900px;
        width: 98vw;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 8px 32px #000a;
        position: relative;
    }
    .modal-buscador-close {
        position: absolute;
        top: 16px;
        right: 18px;
        font-size: 1.8rem;
        color: #fff;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 2;
    }
    .modal-buscador-inputbox {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
    }
    .modal-buscador-inputbox input {
        flex: 1;
        background: #232027;
        border: none;
        color: #fff;
        border-radius: 8px;
        padding: 12px 18px;
        font-size: 1.2rem;
        margin-right: 12px;
    }
    .modal-buscador-inputbox input::placeholder {
        color: #aaa;
    }
    .modal-buscador-inputbox button {
        background: #e50914;
        border: none;
        color: #fff;
        border-radius: 8px;
        padding: 10px 22px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .modal-buscador-inputbox button:hover {
        background: #fff;
        color: #e50914;
    }
    .modal-buscador-section {
        margin-bottom: 32px;
    }
    .modal-buscador-section h3 {
        color: #e50914;
        font-size: 1.25rem;
        margin-bottom: 16px;
        margin-top: 0;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .modal-buscador-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
    }
    .modal-buscador-card {
        width: 110px;
        text-align: center;
    }
    .modal-buscador-card img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        border-radius: 10px;
        background: #232027;
        margin-bottom: 7px;
        box-shadow: 0 2px 8px #0005;
    }
    .modal-buscador-card span {
        color: #fff;
        font-size: 0.98rem;
        display: block;
        margin-top: 2px;
        word-break: break-word;
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
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
    }
    #btnFavorito.favorito-active {
        background: linear-gradient(90deg,#ffd700 60%,#e50914 100%) !important;
        color: #232027 !important;
    }

.season-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 18px;
    justify-content: flex-start;
    margin-bottom: 24px;
    padding-left: 0;
}
.season-tabs .nav-item {
    margin-bottom: 0 !important;
}
.season-tabs .nav-link {
    min-width: 120px;
    text-align: center;
    margin: 0 !important;
    font-size: 1rem;
    padding: 10px 0;
    border-radius: 8px !important;
    transition: background 0.2s, color 0.2s;
    box-shadow: 0 2px 8px #0002;
}
@media (max-width: 900px) {
    .season-tabs .nav-link {
        min-width: 90px;
        font-size: 0.98rem;
        padding: 8px 0;
    }
}
@media (max-width: 600px) {
    .season-tabs {
        gap: 7px 7px;
    }
    .season-tabs .nav-link {
        min-width: 70px;
        font-size: 0.93rem;
        padding: 7px 0;
    }
}

.modal-buscador-bg {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.85);
    align-items: center;
    justify-content: center;
}
.modal-buscador-bg.active {
    display: flex;
}
.modal-buscador {
    background: #181818;
    border-radius: 18px;
    padding: 32px 24px 24px 24px;
    max-width: 900px;
    width: 98vw;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px #000a;
    position: relative;
}
.modal-buscador-close {
    position: absolute;
    top: 16px;
    right: 18px;
    font-size: 1.8rem;
    color: #fff;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 2;
}
.modal-buscador-inputbox {
    display: flex;
    align-items: center;
    margin-bottom: 24px;
}
.modal-buscador-inputbox input {
    flex: 1;
    background: #232027;
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 12px 18px;
    font-size: 1.2rem;
    margin-right: 12px;
}
.modal-buscador-inputbox input::placeholder {
    color: #aaa;
}
.modal-buscador-inputbox button {
    background: #e50914;
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.2s;
}
.modal-buscador-inputbox button:hover {
    background: #fff;
    color: #e50914;
}
.modal-buscador-section {
    margin-bottom: 32px;
}
.modal-buscador-section h3 {
    color: #e50914;
    font-size: 1.25rem;
    margin-bottom: 16px;
    margin-top: 0;
    font-weight: 700;
    letter-spacing: 1px;
}
.modal-buscador-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}
.modal-buscador-card {
    width: 110px;
    text-align: center;
}
.modal-buscador-card img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 10px;
    background: #232027;
    margin-bottom: 7px;
    box-shadow: 0 2px 8px #0005;
}
.modal-buscador-card span {
    color: #fff;
    font-size: 0.98rem;
    display: block;
    margin-top: 2px;
    word-break: break-word;
}

@media (max-width: 600px) {
  .mobile-menu {
    display: none;
    position: fixed !important;
    top: 0;
    right: 0;
    width: 80vw;
    max-width: 320px;
    height: 100vh;
    background: #181818;
    z-index: 10000;
    padding: 32px 0 0 0;
    box-shadow: -2px 0 16px #000a;
    transition: transform 0.3s cubic-bezier(.4,2,.6,1), opacity 0.2s;
    transform: translateX(100%);
    opacity: 0;
  }
  .mobile-menu.active {
    display: block;
    transform: translateX(0);
    opacity: 1;
  }
  #mobileMenuOverlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.65);
    z-index: 9999;
    transition: opacity 0.2s;
  }
  #mobileMenuOverlay.active {
    display: block;
    opacity: 1;
  }
  .mobile-menu ul {
    list-style: none;
    padding: 0 0 0 0;
    margin: 0;
  }
  .mobile-menu li {
    border-bottom: 1px solid #232027;
  }
  .mobile-menu a {
    display: block;
    color: #fff;
    text-decoration: none;
    font-size: 1.13rem;
    padding: 18px 28px;
    font-weight: 600;
    letter-spacing: 1px;
    transition: background 0.2s, color 0.2s;
  }
  .mobile-menu a:hover,
  .mobile-menu a:focus {
    background: #232027;
    color: #e50914;
  }
  .mobile-menu .close-menu {
    position: absolute;
    top: 18px;
    right: 18px;
    font-size: 2rem;
    color: #fff;
    background: none;
    border: none;
    z-index: 10100;
  }
}

        @media (max-width: 600px) {
            .modal-buscador {
                padding: 18px 6px 12px 6px;
                max-width: 98vw;
            }
            .modal-buscador-inputbox input {
                font-size: 1rem;
                padding: 10px 8px;
            }
            .modal-buscador-inputbox button {
                font-size: 1rem;
                padding: 8px 14px;
            }
            .modal-buscador-card {
                width: 90px;
            }
            .modal-buscador-card img {
                height: 110px;
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

        #trailerModal .modal-dialog {
            max-width: 900px;
        }
        #trailerModal .modal-content {
            background: rgba(0, 0, 0, 0.95);
            border: none;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.75);
        }
        #trailerModal .modal-header {
            border: none;
            padding: 0;
            margin: 0;
            background: transparent;
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            width: auto;
            pointer-events: none;
        }
        #trailerModal .modal-body {
            padding: 0;
        }
        #trailerModal .ratio {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
        }
        #trailerModal .btn-close {
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 50%;
            opacity: 1;
            background-color: #e50914;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23fff' viewBox='0 0 16 16'%3e%3cpath d='M.293 1.707a1 1 0 0 1 1.414-1.414L8 6.586l6.293-6.293a1 1 0 0 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707Z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 16px 16px;
            box-shadow: 0 8px 20px rgba(229, 9, 20, 0.45);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            pointer-events: auto;
        }
        #trailerModal .btn-close:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 28px rgba(229, 9, 20, 0.6);
        }
        #trailerModal .btn-close:focus {
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.35);
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
              $parts = explode('-', $ep_name);
              $ep_name = trim(end($parts));
          }
          $ep_id = $ep['id'];
          $ep_num = $ep['episode_num'] ?? '';
          $ep_img = $poster_img;
          if (isset($tmdb_episodios_imgs[$num_temp][$ep_num])) {
              $ep_img = $tmdb_episodios_imgs[$num_temp][$ep_num];
          } elseif (!empty($ep['info']['movie_image'])) {
              $ep_img = $ep['info']['movie_image'];
          }
          $ep_plot = $ep['info']['plot'] ?? '';
          $ep_dur = $ep['info']['duration'] ?? '';
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
          <div class="episode-card w-100">
            <img src="<?php echo htmlspecialchars($ep_img); ?>" alt="">
            <div class="card-body d-flex flex-column">
              <div class="card-title">
                  <?php
                      $ep_num_str = $ep_num ? str_pad($ep_num, 2, '0', STR_PAD_LEFT) : '';
                      echo htmlspecialchars($ep_num_str ? "Episodio $ep_num_str - " : "") . limitar_texto($ep_name, 40);
                  ?>
              </div>
              <div class="card-text"><?php echo $ep_plot; ?></div>
              <?php if (!empty($ep_dur)):
                  $duracion_valida = !empty($ep_dur) && !in_array(trim($ep_dur), ['0', '00:00', '00:00:00']);
                  if ($duracion_valida): ?>
                  <div class="mb-2" style="color:#ffd700;"><?php echo $ep_dur; ?></div>
              <?php endif; endif; ?>
                <a href="serie_play.php?serie_id=<?php echo $id; ?>&ep_id=<?php echo $ep_id; ?>&ep_img=<?php echo urlencode($ep_img); ?>" class="btn-play mt-auto">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTrailer = document.getElementById('btnTrailer');
    const trailerModalElement = document.getElementById('trailerModal');
    const trailerIframe = document.getElementById('trailerIframe');
    const youtubeId = "<?php echo $youtube_id; ?>";

    if (!btnTrailer || !trailerModalElement || !trailerIframe || !youtubeId) {
        return;
    }

    const trailerModal = new bootstrap.Modal(trailerModalElement);

    btnTrailer.addEventListener('click', function() {
        trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
        trailerModal.show();
    });

    trailerModalElement.addEventListener('hidden.bs.modal', function () {
        trailerIframe.src = "";
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFav = document.getElementById('btnFavorito');
    const favText = document.getElementById('favText');
    let isFav = false;

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
    const dropdownItems = document.querySelectorAll('.season-dropdown-item');
    dropdownItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const season = this.getAttribute('data-season');
            document.querySelectorAll('.season-tabs .nav-link').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
            document.getElementById('season-dropdown-tab').classList.add('active');
            const pane = document.getElementById('season-' + season);
            if (pane) {
                pane.classList.add('show', 'active');
                document.getElementById('season-dropdown-tab').textContent = 'Temporada ' + season;
            }
        });
    });
    document.querySelectorAll('.season-tabs .nav-link:not(.dropdown-toggle)').forEach(function(tabBtn) {
        tabBtn.addEventListener('click', function() {
            const dropdownTab = document.getElementById('season-dropdown-tab');
            if (dropdownTab) dropdownTab.textContent = 'Temporada';
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const btnMenu = document.querySelector('.header__btn');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
  const closeMenuBtn = document.getElementById('closeMobileMenu');

  function openMenu() {
    mobileMenu.classList.add('active');
    mobileMenuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu() {
    mobileMenu.classList.remove('active');
    mobileMenuOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if(btnMenu && mobileMenu && mobileMenuOverlay) {
    btnMenu.addEventListener('click', openMenu);
    mobileMenuOverlay.addEventListener('click', closeMenu);
    if(closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
  }
});
</script>

</body>
</html>
