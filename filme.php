<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];
$id = trim($_REQUEST['stream']);
$tipo = trim($_REQUEST['streamtipo']);

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_info&vod_id=$id";
$resposta = apixtream($url);
$output = json_decode($resposta,true);

$backdrop = '';
if (!empty($output['info']['backdrop_path']) && is_array($output['info']['backdrop_path'])) {
    $backdrop = $output['info']['backdrop_path'][0];
}
$poster_img = $output['info']['movie_image'];
$repro_img = $backdrop ?: $poster_img;
$filme = $output['movie_data']['name'];
$filme = preg_replace('/\s*\(\d{4}\)$/', '', $filme);

$tmdb_id = $output['info']['tmdb_id'] ?? null;
$ano = $output['info']['releasedate'] ?? '';

if (!$tmdb_id && !empty($filme)) {
    $query = urlencode($filme);
    $tmdb_search_url = "https://api.themoviedb.org/3/search/movie?api_key=" . TMDB_API_KEY . "&language=es-ES&query=$query";
    if (!empty($ano)) {
        $tmdb_search_url .= "&year=" . urlencode(substr($ano,0,4));
    }
    $tmdb_search_json = @file_get_contents($tmdb_search_url);
    $tmdb_search_data = json_decode($tmdb_search_json, true);
    if (!empty($tmdb_search_data['results'][0]['id'])) {
        $tmdb_id = $tmdb_search_data['results'][0]['id'];
    }
}

$tmdb_backdrops = [];
$tmdb_posters = [];
$wallpaper_tmdb = '';
$poster_tmdb = '';

if ($tmdb_id) {
    $tmdb_images_url = "https://api.themoviedb.org/3/movie/$tmdb_id/images?api_key=" . TMDB_API_KEY;
    $tmdb_images_json = @file_get_contents($tmdb_images_url);
    $tmdb_images_data = json_decode($tmdb_images_json, true);

    if (!empty($tmdb_images_data['backdrops'])) {
        foreach ($tmdb_images_data['backdrops'] as $img) {
            if (!empty($img['file_path']) && $img['iso_639_1'] === 'es') {
                $tmdb_backdrops[] = "https://image.tmdb.org/t/p/original" . $img['file_path'];
            }
        }
        if (empty($tmdb_backdrops)) {
            foreach ($tmdb_images_data['backdrops'] as $img) {
                if (!empty($img['file_path'])) {
                    $tmdb_backdrops[] = "https://image.tmdb.org/t/p/original" . $img['file_path'];
                }
            }
        }
    }
    if (!empty($tmdb_backdrops)) {
        $wallpaper_tmdb = $tmdb_backdrops[array_rand($tmdb_backdrops)];
    }

    if (!empty($tmdb_images_data['posters'])) {
        foreach ($tmdb_images_data['posters'] as $img) {
            if (!empty($img['file_path']) && $img['iso_639_1'] === 'es') {
                $tmdb_posters[] = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
            }
        }
        if (empty($tmdb_posters)) {
            foreach ($tmdb_images_data['posters'] as $img) {
                if (!empty($img['file_path'])) {
                    $tmdb_posters[] = "https://image.tmdb.org/t/p/w500" . $img['file_path'];
                }
            }
        }
    }
    if (!empty($tmdb_posters)) {
        $poster_tmdb = $tmdb_posters[array_rand($tmdb_posters)];
    }
}

$idcategoria = $output['movie_data']['category_id'];
$exts = $output['movie_data']['container_extension'];
$trailer = $output['info']['youtube_trailer'];
$youtube_id = '';
if (!empty($trailer)) {
    if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $trailer)) {
        $youtube_id = $trailer;
    } else if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([A-Za-z0-9_\-]+)/', $trailer, $matches)) {
        $youtube_id = $matches[1];
    }
}
$diretor = $output['info']['director'];
$cast = $output['info']['cast'];
$plot = $output['info']['plot'];
$genero = $output['info']['genre'];
$duracao = $output['info']['duration'];
$pais = $output['info']['country'];
$nota = $output['info']['rating'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styles/font-awesome-6.5.0.min.css">
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
    <link rel="stylesheet" href="./styles/font-awesome-6.5.0.min.css">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
    <title>PLAYGO - <?php echo htmlspecialchars($filme); ?></title>
    <style>
    .seasons__cover, .details__bg, .home__bg {
        filter: blur(0px) !important;
        opacity: 10%;
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
    }
    #trailerModal .btn-close:hover {
        transform: scale(1.08);
        box-shadow: 0 12px 28px rgba(229, 9, 20, 0.6);
    }
    #trailerModal .btn-close:focus {
        box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.35);
    }

    .card.card--details {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .card.card--details .card__content,
    .card.card--details .card__cover {
        background: transparent !important;
    }
    .content .card {
        background: #181818 !important;
        border: none !important;
        border-radius: 14px !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.45) !important;
        overflow: hidden;
    }
    .content .card .card__content {
        background: transparent !important;
    }
    .content .card .card__cover {
        background: transparent !important;
    }

    @supports (-webkit-touch-callout: none) {
        video::-webkit-media-controls-fullscreen-button {
            display: block !important;
        }
        
        video:fullscreen ~ .header,
        video:fullscreen ~ .navbar-overlay,
        video:-webkit-full-screen ~ .header,
        video:-webkit-full-screen ~ .navbar-overlay,
        video:-moz-full-screen ~ .header,
        video:-moz-full-screen ~ .navbar-overlay {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
        
        :fullscreen .header,
        :fullscreen .navbar-overlay,
        :-webkit-full-screen .header,
        :-webkit-full-screen .navbar-overlay,
        :-moz-full-screen .header,
        :-moz-full-screen .navbar-overlay {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
    }
    
@media (max-width: 600px) {
    .modal-buscador {
        max-width: 98vw;
        width: 98vw;
        min-width: unset;
        padding: 18px 6px 12px 6px;
        border-radius: 12px;
        box-shadow: 0 4px 16px #000a;
    }
    .modal-buscador-inputbox input {
        font-size: 1rem;
        padding: 10px 10px;
    }
    .modal-buscador-inputbox button {
        font-size: 1rem;
        padding: 8px 12px;
    }
    .modal-buscador-section h3 {
        font-size: 1.05rem;
        margin-bottom: 10px;
    }
    .modal-buscador-grid {
        gap: 10px;
    }
    .modal-buscador-card {
        width: 90px;
    }
    .modal-buscador-card img {
        height: 100px;
    }
    .modal-buscador-card span {
        font-size: 0.85rem;
    }
}

@media (max-width: 600px) {
    .card__content > div[style*="display: flex"] {
        flex-direction: column !important;
        gap: 10px !important;
        margin-top: 18px !important;
    }
    #btnTrailer,
    #btnFavorito,
    a[href="predator.php"] {
        width: 100% !important;
        min-width: 0 !important;
        justify-content: center !important;
        align-items: center !important;
        font-size: 1.08rem !important;
        padding: 13px 0 !important;
        border-radius: 12px !important;
        margin-bottom: 0 !important;
        box-shadow: 0 2px 12px #0002;
        transition: background 0.18s, color 0.18s;
        gap: 10px !important;
    }
    #btnTrailer i,
    #btnFavorito i,
    a[href="predator.php"] i {
        font-size: 1.35rem !important;
        margin-right: 8px !important;
    }
    #btnTrailer span,
    #btnFavorito span,
    a[href="predator.php"] span {
        font-size: 1.08rem !important;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    #btnTrailer {
        background: linear-gradient(90deg,#ff0000 60%,#c80000 100%) !important;
        color: #fff !important;
    }
    #btnFavorito {
        background: linear-gradient(90deg,#232027 60%,#444 100%) !important;
        color: #ffd700 !important;
    }
    a[href="predator.php"] {
        background: linear-gradient(90deg,#0f2027 60%,#2c5364 100%) !important;
        color: #fff !important;
        text-decoration: none !important;
    }
    #btnTrailer:active,
    #btnFavorito:active,
    a[href="predator.php"]:active {
        opacity: 0.85;
    }
}

    @media (max-width: 600px) {
        .details__title {
            text-align: center !important;
            width: 100%;
            display: block;
        }
        .card__cover {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 18px auto;
        }
        .card__cover img {
            margin: 0 auto;
            display: block;
            max-width: 90vw;
            height: auto;
        }
        .card__content {
            text-align: center !important;
            align-items: center !important;
            justify-content: center !important;
            display: flex;
            flex-direction: column;
        }
        .card__meta {
            justify-content: center !important;
            text-align: center !important;
            margin: 0 auto 10px auto;
            padding: 0;
        }
        .card__description--details {
            text-align: center !important;
        }
    }

    @media (max-width: 600px) {
        .card__meta li:nth-child(2),
        .card__meta li:nth-child(3) {
            display: none !important;
        }
    }
    @media (max-width: 600px) {
        .details__title {
            text-align: center !important;
            width: 100%;
            display: block;
            font-weight: 700 !important;
        }
    @media (max-width: 600px) {
        .card__wrap {
            margin-top: 4px !important;
            margin-bottom: 0 !important;
        }
    }
    
    @media (max-width: 600px) {
        .content .card__title {
            max-width: 110px;
            margin-left: auto;
            margin-right: auto;
            overflow: hidden;
        }
        .content .card__title a {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 2.3em;
            font-size: 0.95rem;
            line-height: 1.2;
            color: #fff;
            text-decoration: none;
            word-break: break-word;
            width: 100%;
            max-width: 110px;
        }
        .content .card {
            max-width: 110px;
            margin-left: auto;
            margin-right: auto;
        }
    }    
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
                                <a href="filmes.php" class="header__nav-link">Películas</a>
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
<section class="section details">
<div class="details__bg" data-bg="<?php echo $wallpaper_tmdb ?: ($backdrop ?: $poster_img); ?>"></div>
    <div class="container top-margin">
        <div class="row">
            <div class="col-12">
                <!-- Solo el título, sin año -->
                <h1 class="details__title"><?php echo htmlspecialchars($filme); ?><br/>
                    <ul class="card__list">
                        <?php foreach (explode(',', $genero) as $g): ?>
                            <li><?php echo htmlspecialchars(trim($g)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </h1>
            </div>
            <div class="col-12 col-xl-12">
                <div class="card card--details">
                    <div class="row">
                        <div class="col-12 col-sm-3 col-md-3 col-lg-3 col-xl-3">
                            <div class="card__cover">
<img src="<?php echo $poster_tmdb ?: $poster_img; ?>" alt="">
                            </div>
                        </div>
                        <div class="col-12 col-sm-9 col-md-9 col-lg-9 col-xl-9">
                            <div class="card__content">
                           <div class="card__wrap">
                              <span class="card__rate">
                                 <?php
                                 echo ($ano ? substr($ano, 0, 4) : '');
                                 if ($nota !== '') {
                                       echo ' &nbsp; <i class="fa-solid fa-star"></i> ' . $nota;
                                 }
                                 ?>
                              </span>
                           </div>
                                <ul class="card__meta">
                                    <li><span><strong>Duración:</strong></span> <?php echo $duracao; ?></li>
                                    <li><span><strong>País:</strong></span> <a href="#"><?php echo $pais; ?></a></li>
                                    <li><span><strong>Reparto:</strong></span> <?php echo $cast; ?></li>
                                </ul>
                    <div class="card__description card__description--details">
                        <?php echo $plot; ?>
                    </div>
                    <!-- Botón Tráiler YouTube -->
                    <div style="display: flex; gap: 16px; margin-top: 20px;">
                        <?php if (!empty($youtube_id)): ?>
                            <button id="btnTrailer" style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#ff0000 60%,#c80000 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                                <i class="fab fa-youtube" style="font-size:1.5rem;"></i>
                                <span>Tráiler</span>
                                            </button>
                                        <?php endif; ?>

                    <!-- Botón Favoritos -->
                    <button id="btnFavorito"
                        style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#232027 60%,#444 100%);color:#ffd700;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                        <i class="fa fa-star" style="font-size:1.4rem;"></i>
                        <span id="favText">Agregar a Favoritos</span>
                    </button>
                    <?php
                    $saga_predator_ids = [7716, 2693, 2690, 2689, 2692, 2691, 2450, 2449];
                    if (in_array((int)$id, $saga_predator_ids)): ?>
                        <a href="predator.php" style="display:flex;align-items:center;gap:10px;background:linear-gradient(90deg,#0f2027 60%,#2c5364 100%);color:#fff;border:none;border-radius:8px;padding:8px 22px;font-size:1.1rem;cursor:pointer;text-decoration:none;box-shadow:0 2px 8px #0003;transition:background 0.2s;">
                            <i class="fa fa-film" style="font-size:1.4rem;"></i>
                            <span>Saga Predator</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
                    <div class="row top-margin-sml">
                        <div class="col-12">
                            <div class="alert alert-danger" id="player__error" style="display: none;"></div>
                            <div id="player_row">
                                <div id="now__playing__player"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Reproductor Plyr con botones avanzar/retroceder -->
                    <div class="row">
                        <div class="col-12">
                            <?php
                            $ext = strtolower($exts);
                            if ($ext == 'mp4'): ?>
                                <!-- Plyr CSS -->
                                <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
                                <video
                                    id="plyr-video"
                                    playsinline
                                    webkit-playsinline
                                    controls
                                    width="100%"
                                    height="450"
                                    poster="<?php echo $wallpaper_tmdb ?: $repro_img; ?>"
                                    x-webkit-airplay="allow"
                            >
                                    <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.mp4" type="video/mp4" />
                                </video>
                                <!-- Plyr JS -->
                                <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
                                <script>
                                const player = new Plyr('#plyr-video', {
                                    controls: [
                                        'play-large', 'play', 'rewind', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'
                                    ],
                                    seekTime: 10,
                                });
                                window.player = player; // <-- AGREGA ESTA LÍNEA
                                </script>
                            <?php else: ?>
                                <!-- Reproductor HTML5 nativo ajustando tamaño automáticamente y más grande -->
                                <div style="width:100%;max-width:1100px;margin:auto;">
                                    <video
                                        controls
                                        playsinline
                                        webkit-playsinline
                                        poster="<?php echo $wallpaper_tmdb ?: $repro_img; ?>"
                                        style="background:#000;display:block;margin:auto;width:100%;max-width:1100px;height:600px;object-fit:contain;"
                                        x-webkit-airplay="allow"
                                    >
                                        <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.<?php echo $exts; ?>" type="video/<?php echo htmlspecialchars($exts); ?>" />
                                        <source src="<?php echo IP; ?>/<?php echo $tipo; ?>/<?php echo $user; ?>/<?php echo $pwd; ?>/<?php echo $id; ?>.<?php echo $exts; ?>" />
                                        Tu navegador no soporta este formato de video.
                                    </video>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Fin reproductor -->
                </div>
            </div>
        </div>
    </div>
</section>
<?php if (!empty($youtube_id)): ?>
<div class="modal fade" id="trailerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<section class="content">
    <div class="container" style="margin-top: 30px;">
        <div class="row">
            <div class="col-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-12">
                        <h2 class="section__title section__title--sidebar">Usuarios también vieron</h2>
                    </div>
                    <?php
                $url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams&category_id=$idcategoria";
                $resposta = apixtream($url);
                $output = json_decode($resposta,true);
                shuffle($output);
                $i = 1;
                foreach(array_rand($output,6) as $index) {
                    $row = $output[$index];
                    $filme_nome = $row['name'];
                    $filme_nome = preg_replace('/\s*\(\d{4}\)$/', '', $filme_nome);
                    $filme_type = $row['stream_type'];
                    $filme_id = $row['stream_id'];
                    $filme_img = $row['stream_icon'];
                    $filme_rat = isset($row['rating']) ? $row['rating'] : '';
                    $filme_ano = isset($row['year']) ? $row['year'] : '';
                ?>
                <div class="col-4 col-sm-4 col-lg-2">
                    <div class="card">
                        <div class="card__cover">
                            <img loading="lazy" src="<?php echo $filme_img; ?>" alt="">
                            <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>" class="card__play">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                        <div class="card__content">
                            <h3 class="card__title">
                                <a href="filme.php?stream=<?php echo $filme_id; ?>&streamtipo=<?php echo $filme_type; ?>">
                                    <?php echo $filme_nome; ?>
                                </a>
                            </h3>
                            <span class="card__rate">
                                <?php
                                if ($filme_ano) {
                                    echo $filme_ano;
                                }
                                if ($filme_rat !== '') {
                                    echo ' <i class="fa-solid fa-star"></i> ' . $filme_rat;
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php $i++; } ?>
                </div>
            </div>
        </div>
    </div>
</section>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="footer__copyright">
                    &copy; 2021 <img height="20px" style="padding-left: 10px; padding-right: 10px; margin-top: -2px;" class="whiteout" src="assets/logo/logo.png"> v1.1.8
                </div>
            </div>
        </div>
    </div>
</footer>
<script type="text/javascript" src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1"></script>
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
<script src="./scripts/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTrailer = document.getElementById('btnTrailer');
    const trailerModalElement = document.getElementById('trailerModal');
    const trailerIframe = document.getElementById('trailerIframe');
    const trailerCloseBtn = trailerModalElement ? trailerModalElement.querySelector('.btn-close') : null;
    const youtubeId = "<?php echo $youtube_id; ?>";

    if (!btnTrailer || !trailerModalElement || !trailerIframe || !youtubeId) {
        return;
    }

    const trailerModal = new bootstrap.Modal(trailerModalElement);

    btnTrailer.addEventListener('click', function() {
        trailerIframe.src = "https://www.youtube.com/embed/" + youtubeId + "?autoplay=1";
        trailerModal.show();
    });

    if (trailerCloseBtn) {
        trailerCloseBtn.addEventListener('click', function() {
            trailerModal.hide();
        });
    }

    trailerModalElement.addEventListener('hidden.bs.modal', function () {
        trailerIframe.src = "";
    });
});
</script>

<script>
// Fix para iOS - Manejo de pantalla completa
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.header');
    const navbarOverlay = document.querySelector('.navbar-overlay');
    
    // Detectar si es iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    
    if (isIOS) {
        // Función para ocultar/mostrar header
        function toggleHeaderVisibility(isFullscreen) {
            if (isFullscreen) {
                if (header) header.style.display = 'none';
                if (navbarOverlay) navbarOverlay.style.display = 'none';
            } else {
                if (header) header.style.display = 'block';
                if (navbarOverlay) navbarOverlay.style.display = 'block';
            }
        }
        
        // Escuchar eventos de pantalla completa para video nativo
        document.addEventListener('webkitfullscreenchange', function() {
            toggleHeaderVisibility(!!document.webkitFullscreenElement);
        });
        
        document.addEventListener('fullscreenchange', function() {
            toggleHeaderVisibility(!!document.fullscreenElement);
        });
        
        // Para Plyr player
        if (window.player && typeof window.player.on === "function") {
            window.player.on('enterfullscreen', function() {
                toggleHeaderVisibility(true);
            });
            
            window.player.on('exitfullscreen', function() {
                toggleHeaderVisibility(false);
            });
        }
        
        // Para video HTML5 nativo
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('webkitbeginfullscreen', function() {
                toggleHeaderVisibility(true);
            });
            
            video.addEventListener('webkitendfullscreen', function() {
                toggleHeaderVisibility(false);
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const movieKey = "movie_time_<?php echo $id; ?>";
    const movieTitle = "<?php echo addslashes($filme); ?>";

    function showResumeNotification(time, onAccept, onCancel) {
        if (document.getElementById('resumeNotifBg')) return;
        const bg = document.createElement('div');
        bg.id = 'resumeNotifBg';
        bg.style.position = 'fixed';
        bg.style.left = '0';
        bg.style.top = '0';
        bg.style.width = '100vw';
        bg.style.height = '100vh';
        bg.style.background = 'rgba(0,0,0,0.85)';
        bg.style.zIndex = '999999';
        bg.style.display = 'flex';
        bg.style.alignItems = 'center';
        bg.style.justifyContent = 'center';

        const notif = document.createElement('div');
        notif.id = 'resumeNotif';
        notif.style.background = '#232027';
        notif.style.color = '#fff';
        notif.style.padding = '32px 38px';
        notif.style.borderRadius = '16px';
        notif.style.boxShadow = '0 8px 32px #000a';
        notif.style.fontSize = '1.18rem';
        notif.style.display = 'flex';
        notif.style.flexDirection = 'column';
        notif.style.alignItems = 'center';
        notif.style.gap = '28px';
        notif.style.maxWidth = '90vw';
        notif.style.textAlign = 'center';

        const min = Math.floor(time/60);
        const sec = Math.floor(time%60).toString().padStart(2,'0');
        notif.innerHTML = `
            <span style="font-size:1.15rem;">¿Deseas continuar viendo <b>${movieTitle}</b> desde el minuto <b>${min}:${sec}</b>?</span>
            <div style="display:flex;gap:18px;">
                <button id="resumeAccept" style="background:#e50914;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;cursor:pointer;margin-right:10px;">Aceptar</button>
                <button id="resumeCancel" style="background:#444;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;cursor:pointer;">Cancelar</button>
            </div>
        `;
        bg.appendChild(notif);
        document.body.appendChild(bg);

        document.getElementById('resumeAccept').onclick = function() {
            bg.remove();
            onAccept();
        };
        document.getElementById('resumeCancel').onclick = function() {
            bg.remove();
            if (onCancel) onCancel();
        };
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === "Escape") {
                bg.remove();
                document.removeEventListener('keydown', escListener);
                if (onCancel) onCancel();
            }
        });
    }

    // Esperar a que Plyr esté listo
    if (document.getElementById('plyr-video') && window.Plyr) {
        // Espera a que window.player esté definido
        let plyrInterval = setInterval(function() {
            if (window.player && typeof window.player.on === "function") {
                clearInterval(plyrInterval);
                const plyrPlayer = window.player;
                plyrPlayer.on('timeupdate', function() {
                    localStorage.setItem(movieKey, Math.floor(plyrPlayer.currentTime));
                });
                plyrPlayer.on('ended', function() {
                    localStorage.removeItem(movieKey);
                });
                const lastTime = parseInt(localStorage.getItem(movieKey) || "0");
                if (lastTime > 10) {
                    plyrPlayer.pause();
                    showResumeNotification(lastTime, function() {
                        plyrPlayer.currentTime = lastTime;
                        plyrPlayer.play();
                    });
                }
            }
        }, 200);
    }
    // HTML5 player
    else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
        const video = document.querySelector('video');
        video.addEventListener('timeupdate', function() {
            localStorage.setItem(movieKey, Math.floor(video.currentTime));
        });
        video.addEventListener('ended', function() {
            localStorage.removeItem(movieKey);
        });
        const lastTime = parseInt(localStorage.getItem(movieKey) || "0");
        if (lastTime > 10) {
            video.pause();
            showResumeNotification(lastTime, function() {
                video.currentTime = lastTime;
                video.play();
            });
        }
    }
});
</script>

<script>
window['__onGCastApiAvailable'] = function(isAvailable) {
  if (isAvailable) {
    cast.framework.CastContext.getInstance().setOptions({
      receiverApplicationId: chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID,
      autoJoinPolicy: chrome.cast.AutoJoinPolicy.ORIGIN_SCOPED
    });
  }
};

document.addEventListener('DOMContentLoaded', function() {
    // Crear botón de Cast
    const castBtn = document.createElement('google-cast-button');
    castBtn.style.setProperty('--connected-color', '#e50914');
    castBtn.style.setProperty('--disconnected-color', '#fff');
    castBtn.style.width = '42px';
    castBtn.style.height = '42px';
    castBtn.style.marginLeft = '10px';
    castBtn.title = "Transmitir a Chromecast";

    // Insertar el botón en el contenedor de botones
    let insertTarget = document.querySelector('.card__content > div[style*="display: flex"]');
    if (insertTarget) {
        insertTarget.appendChild(castBtn);
    } else {
        // Si no existe, insértalo al inicio de .card__content
        let cardContent = document.querySelector('.card__content');
        if (cardContent) cardContent.insertBefore(castBtn, cardContent.firstChild);
    }

    // Lanzar video al Chromecast cuando se seleccione un dispositivo
    if (window.cast && cast.framework) {
        cast.framework.CastContext.getInstance().addEventListener(
            cast.framework.CastContextEventType.SESSION_STATE_CHANGED,
            function(event) {
                if (event.sessionState === cast.framework.SessionState.SESSION_STARTED) {
                    lanzarVideoACast();
                }
            }
        );
    }

    function lanzarVideoACast() {
        let video = document.getElementById('plyr-video') || document.querySelector('video');
        if (!video) return;
        let src = video.currentSrc || video.src;
        if (!src) return;
        let mediaInfo = new chrome.cast.media.MediaInfo(src, 'video/mp4');
        mediaInfo.metadata = new chrome.cast.media.GenericMediaMetadata();
        mediaInfo.metadata.title = document.title;
        let request = new chrome.cast.media.LoadRequest(mediaInfo);
        let session = cast.framework.CastContext.getInstance().getCurrentSession();
        if (session) session.loadMedia(request);
    }

    // Mostrar advertencia si no es HTTPS ni localhost
    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
        castBtn.style.display = 'none';
        console.warn('Chromecast solo funciona en HTTPS o localhost.');
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFav = document.getElementById('btnFavorito');
    const favText = document.getElementById('favText');
    let isFav = false;

    // Consultar estado inicial
    fetch('db/base.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=fav_check&id=<?php echo $id; ?>&tipo=pelicula`
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
            body: `action=${action}&id=<?php echo $id; ?>&nombre=<?php echo urlencode($filme); ?>&img=<?php echo urlencode($poster_tmdb ?: $poster_img); ?>&ano=<?php echo urlencode($ano); ?>&rate=<?php echo urlencode($nota); ?>&tipo=pelicula`
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
    let historialGuardado = false;
    function guardarHistorial() {
        if (historialGuardado) return;
        historialGuardado = true;
        fetch('db/base.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=hist_add&id=<?php echo $id; ?>&nombre=<?php echo urlencode($filme); ?>&img=<?php echo urlencode($poster_tmdb ?: $poster_img); ?>&ano=<?php echo urlencode($ano); ?>&rate=<?php echo urlencode($nota); ?>&tipo=pelicula`
        });
    }

    // Plyr
    if (window.player && typeof window.player.on === "function") {
        window.player.on('play', guardarHistorial);
    }
    // HTML5 player (cuando NO es Plyr)
    else if (document.querySelector('video#plyr-video') === null && document.querySelector('video')) {
        const video = document.querySelector('video');
        video.addEventListener('play', guardarHistorial);
    }
});
</script>

</body>
</html>
