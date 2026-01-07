<?php
require_once("libs/lib.php");

if (!isset($_COOKIE['xuserm']) || !isset($_COOKIE['xpwdm']) || empty($_COOKIE['xuserm']) || empty($_COOKIE['xpwdm'])) {
    header("Location: login.php");
    exit;
}

$user = $_COOKIE['xuserm'];
$pwd = $_COOKIE['xpwdm'];

$url_info = IP."/player_api.php?username=$user&password=$pwd&action=get_account_info";
$respuesta_info = apixtream($url_info);
$info = json_decode($respuesta_info, true);

$expira = 'Desconocida';
if (isset($info['user_info']['exp_date']) && is_numeric($info['user_info']['exp_date'])) {
    $expira = date('d/m/Y H:i', $info['user_info']['exp_date']);
}

$url = IP."/player_api.php?username=$user&password=$pwd&action=get_vod_streams";
$respuesta = apixtream($url);
$peliculas = json_decode($respuesta, true);

$wallpaper_url = '';
if ($peliculas && count($peliculas) > 0) {
    $random = $peliculas[array_rand($peliculas)];
    $wallpaper_url = isset($random['cover']) ? $random['cover'] : (isset($random['stream_icon']) ? $random['stream_icon'] : '');
}

$recomendados = [];
if ($peliculas && count($peliculas) > 0) {
    foreach ($peliculas as $p) {
        $recomendados[] = [
            'id' => $p['stream_id'],
            'nombre' => $p['name'],
            'img' => !empty($p['cover']) ? $p['cover'] : (!empty($p['stream_icon']) ? $p['stream_icon'] : 'assets/logo/logo.png'),
            'ano' => $p['year'] ?? '',
            'rate' => $p['rating'] ?? '0',
            'tipo' => 'movie'
        ];
    }
}

$url_series = IP."/player_api.php?username=$user&password=$pwd&action=get_series";
$respuesta_series = apixtream($url_series);
$series = json_decode($respuesta_series, true);

if ($series && count($series) > 0) {
    foreach ($series as $s) {
        $recomendados[] = [
            'id' => $s['series_id'],
            'nombre' => $s['name'],
            'img' => !empty($s['cover']) ? $s['cover'] : (!empty($s['stream_icon']) ? $s['stream_icon'] : 'assets/logo/logo.png'),
            'ano' => $s['year'] ?? '',
            'rate' => $s['rating'] ?? '0',
            'tipo' => 'serie'
        ];
    }
}

shuffle($recomendados);
$recomendados = array_slice($recomendados, 0, 8);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>PLAYGO - MI PERFIL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="assets/icon/favicon.ico">
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
    <style>
        .mobile-menu {
        display: none;
        }
        #mobileMenuOverlay {
        display: none;
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
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&display=swap');
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            background: #181a20 url('<?php echo $wallpaper_url; ?>') center/cover no-repeat;
            background-blend-mode: multiply;
            color: #fff;
            overflow-x: hidden;
        }
        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px 20px;
        }
        .main-content {
            padding-top: 450px;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 20px;
            padding-right: 20px;
        }
        .profile-section {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .profile-card-modern {
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), 0 0 0 1px rgba(229,9,20,0.2);
            max-width: 1200px;
            margin: 100px auto 40px auto;
            padding: 0;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #e50914 0%, #c8008f 50%, #ff6a00 100%);
            padding: 30px 40px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 30px;
            position: relative;
            z-index: 2;
        }
        
        .profile-avatar-modern {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #e50914;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 4px rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.3);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .profile-avatar-modern::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .profile-welcome {
            flex: 1;
        }
        
        .profile-welcome h2 {
            color: #fff;
            font-size: 1.8rem;
            font-weight: 900;
            margin: 0 0 8px 0;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .profile-welcome p {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            margin: 0;
            font-weight: 500;
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
            .profile-card-modern {
                margin: 90px auto 20px auto !important;
                max-width: 360px;
                border-radius: 20px;
                background: linear-gradient(160deg, #1c1c20 0%, #23232a 100%);
                box-shadow: 0 10px 30px rgba(0,0,0,0.55), 0 0 0 1px rgba(255,255,255,0.05);
                overflow: hidden;
            }
            .profile-header {
                padding: 20px 18px;
                border-radius: 20px 20px 0 0;
                text-align: center;
            }
            .profile-header-content {
                flex-direction: column !important;
                align-items: center !important;
                gap: 12px !important;
                text-align: center !important;
            }
            .profile-avatar-modern {
                width: 72px !important;
                height: 72px !important;
                font-size: 2.2rem !important;
            }
            .profile-welcome h2 {
                font-size: 1.35rem !important;
                margin-bottom: 4px !important;
            }
            .profile-welcome p {
                display: block !important;
                font-size: 0.85rem !important;
                color: rgba(255,255,255,0.75);
            }
            .profile-compact-info {
                display: flex !important;
                flex-direction: column;
                gap: 10px;
                background: rgba(0,0,0,0.35);
                padding: 18px 16px;
            }
            .compact-row {
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 12px;
                padding: 10px 12px;
                background: rgba(0,0,0,0.22);
            }
            .compact-label {
                font-size: 0.82rem;
                color: rgba(255,255,255,0.75);
            }
            .compact-value {
                font-size: 0.95rem;
                color: #fff;
            }
            .profile-actions-modern {
                flex-direction: column !important;
                padding: 18px !important;
                gap: 12px !important;
                background: rgba(0,0,0,0.25) !important;
            }
            .profile-btn-modern {
                width: 100%;
                justify-content: center;
                padding: 14px 18px !important;
                font-size: 1rem !important;
                border-radius: 12px !important;
            }
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 30px 40px;
            background: rgba(0,0,0,0.2);
        }
        
        .stat-item {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-item:hover {
            background: rgba(255,255,255,0.08);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229,9,20,0.2);
        }
        
        .stat-item::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, #e50914, #c8008f, #ff6a00);
        }
        
        .stat-label {
            color: #e50914;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stat-value {
            color: #fff;
            font-size: 1.8rem;
            font-weight: 900;
            margin: 0;
            font-family: 'Montserrat', Arial, sans-serif;
        }
        
        .profile-actions-modern {
            display: flex;
            gap: 20px;
            padding: 30px 40px;
            justify-content: center;
            background: rgba(0,0,0,0.1);
        }
        
        .profile-btn-modern {
            background: linear-gradient(135deg, #e50914 0%, #c8008f 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 16px 32px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(229,9,20,0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-btn-modern::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .profile-btn-modern:hover::before {
            left: 100%;
        }
        
        .profile-btn-modern:hover {
            background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
            color: #e50914;
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(229,9,20,0.6);
        }
        
        .profile-btn-modern i {
            font-size: 1.2rem;
        }
        
        .profile-compact-info {
            display: none;
            background: rgba(0,0,0,0.1);
            padding: 15px;
            border-radius: 0 0 24px 24px;
        }
        
        @media (min-width: 769px) {
            .container {
                max-width: 1200px !important;
                margin: 0 auto !important;
                padding: 0 20px 40px 20px !important;
            }
            
            .profile-stats {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 20px !important;
                padding: 30px 40px !important;
                background: rgba(0,0,0,0.2) !important;
            }
            
            .profile-compact-info {
                display: none !important;
            }
            
            .profile-header-content {
                flex-direction: row !important;
                text-align: left !important;
                gap: 30px !important;
                align-items: center !important;
            }
            
            .profile-avatar-modern {
                width: 100px !important;
                height: 100px !important;
                font-size: 3rem !important;
            }
            
            .profile-welcome h2 {
                font-size: 1.8rem !important;
                margin-bottom: 8px !important;
            }
            
            .profile-welcome p {
                display: block !important;
                font-size: 1rem !important;
            }
            
            .stat-item {
                padding: 20px !important;
                border-radius: 16px !important;
            }
            
            .stat-label {
                font-size: 0.9rem !important;
                margin-bottom: 8px !important;
            }
            
            .stat-value {
                font-size: 1.8rem !important;
                line-height: 1.2 !important;
            }
            
            .profile-actions-modern {
                flex-direction: row !important;
                gap: 20px !important;
                padding: 30px 40px !important;
            }
            
            .profile-btn-modern {
                padding: 16px 32px !important;
                font-size: 1.1rem !important;
            }
            
            .section-title-row {
                max-width: 1200px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding: 0 20px !important;
            }
            
            .carousel-wrapper {
                max-width: 1200px !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding: 0 20px !important;
            }
        }
        
        .compact-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .compact-row:last-child {
            border-bottom: none;
        }
        
        .compact-label {
            color: #e50914;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .compact-label i {
            font-size: 0.7rem;
        }
        
        .compact-value {
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: right;
            max-width: 60%;
            word-break: break-all;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 10px 40px 10px !important;
            }
            
            .main-content {
                padding-top: 280px !important;
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            
            .profile-card-modern {
                margin: 0 0 20px 0;
                border-radius: 16px;
            }
            
            .profile-header {
                padding: 15px 20px;
            }
            
            .profile-header-content {
                flex-direction: row;
                text-align: left;
                gap: 15px;
                align-items: center;
            }
            
            .profile-avatar-modern {
                width: 60px;
                height: 60px;
                font-size: 2rem;
                flex-shrink: 0;
            }
            
            .profile-welcome h2 {
                font-size: 1.2rem;
                margin-bottom: 4px;
            }
            
            .profile-welcome p {
                font-size: 0.8rem;
                display: none; /* Ocultar descripción en móviles */
            }
            
            /* Ocultar tarjetas grandes en móviles */
            .profile-stats {
                display: none !important;
            }
            
            /* Mostrar información compacta en móviles */
            .profile-compact-info {
                display: block !important;
            }
            
            .profile-actions-modern {
                flex-direction: row;
                gap: 10px;
                padding: 15px;
            }
            
            .profile-btn-modern {
                flex: 1;
                justify-content: center;
                padding: 10px 16px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 5px 40px 5px !important;
            }
            
            .main-content {
                padding-top: 250px !important;
                padding-left: 5px !important;
                padding-right: 5px !important;
            }
            
            .profile-card-modern {
                margin: 0 0 15px 0;
                border-radius: 12px;
            }
            
            .profile-header {
                padding: 12px 15px;
            }
            
            .profile-header-content {
                gap: 12px;
            }
            
            .profile-avatar-modern {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
            }
            
            .profile-welcome h2 {
                font-size: 1rem;
                margin-bottom: 2px;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
                padding: 12px;
                gap: 6px;
            }
            
            .stat-item {
                padding: 8px;
                border-radius: 8px;
            }
            
            .stat-label {
                font-size: 0.65rem;
                margin-bottom: 2px;
            }
            
            .stat-value {
                font-size: 0.85rem;
                line-height: 1.1;
            }
            
            .profile-actions-modern {
                flex-direction: row;
                gap: 8px;
                padding: 12px;
            }
            
            .profile-btn-modern {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            
            .profile-btn-modern i {
                font-size: 0.9rem;
            }
        }
        
        /* Diseño para pantallas muy pequeñas (Android landscape) */
        @media (max-width: 600px) and (orientation: landscape) {
            .profile-card-modern {
                margin: 0 5px 10px 5px;
            }
            
            .profile-header {
                padding: 10px 15px;
            }
            
            .profile-avatar-modern {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
            
            .profile-welcome h2 {
                font-size: 0.9rem;
            }
            
            .profile-stats {
                grid-template-columns: repeat(3, 1fr);
                padding: 10px;
                gap: 5px;
            }
            
            .stat-item {
                padding: 6px;
            }
            
            .stat-label {
                font-size: 0.6rem;
            }
            
            .stat-value {
                font-size: 0.75rem;
            }
            
            .profile-actions-modern {
                padding: 10px;
            }
            
            .profile-btn-modern {
                padding: 6px 10px;
                font-size: 0.75rem;
            }
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

        @media (max-width: 900px) {
            .profile-card-horizontal {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 6px 12px 6px;
                max-width: 98vw;
                gap: 18px;
            }
            .profile-avatar-horizontal {
                margin: 0 auto 8px auto;
                width: 80px;
                height: 80px;
                font-size: 2.2rem;
                min-width: 80px;
                min-height: 80px;
            }
            .profile-info-row {
                grid-template-columns: 90px 1fr 90px 1fr;
                gap: 6px 8px;
            }
            .profile-value-h {
                font-size: 1rem;
                padding: 7px 8px;
            }
            .profile-label-h {
                font-size: 0.98rem;
            }
            .profile-actions-horizontal {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
        }
        /* Carrusel estilo poster vertical tipo streaming */
        .carousel-wrapper {
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            padding: 0;
            margin-bottom: 38px;
            position: relative;
            overflow: visible;
        }
        .owl-carousel.profile-carousel .item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .movie-card {
            width: 210px;
            background: transparent;
            border: none;
            box-shadow: none;
            margin: 0 12px;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.18s;
            position: relative;
            overflow: hidden;
        }
        .movie-card:hover {
            transform: scale(1.04) translateY(-6px);
        }
        .movie-poster-wrap {
            position: relative;
            width: 210px;
            height: 315px;
            display: block;
        }
        .movie-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 0;
            background: #232027;
            box-shadow: 0 4px 24px #0008;
            transition: filter 0.2s;
        }
        .movie-poster-wrap:hover .movie-overlay {
            opacity: 1;
        }
        .movie-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(229,9,80,0.70) 0%, rgba(255,46,122,0.60) 100%);
            opacity: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.25s;
            z-index: 2;
        }
        .movie-play {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            width: 64px;
            height: 64px;
            color: #fff;
            font-size: 2.3rem;
            box-shadow: 0 2px 12px #e5091440;
            transition: background 0.2s, color 0.2s, transform 0.2s;
            text-decoration: none;
        }
        .movie-play:hover {
            background: #fff;
            color: #e50914;
            transform: scale(1.12);
        }
        .movie-info {
            width: 100%;
            padding: 0;
            text-align: left;
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .movie-title {
            color: #fff;
            font-size: 1.13rem;
            font-weight: 700;
            margin-bottom: 6px;
            margin-top: 0;
            min-height: 1.5em;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .movie-title a {
            color: #fff;
            text-decoration: none;
            transition: color 0.2s;
        }
        .movie-title a:hover {
            color: #e50914;
        }
        .movie-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 0;
        }
        .movie-year {
            color: #fff;
            font-size: 1.02rem;
            font-weight: 700;
        }
        .movie-rating {
            color: #ff2e7a;
            font-size: 1.02rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .movie-rating i {
            color: #ff2e7a;
            font-size: 1.1rem;
        }
        @media (max-width: 900px) {
            .movie-card, .movie-poster-wrap { width: 120px; }
            .movie-poster-wrap, .movie-cover { height: 180px; }
            .movie-title { font-size: 0.98rem; }
        }
        @media (max-width: 600px) {
            .movie-card, .movie-poster-wrap { width: 90px; }
            .movie-poster-wrap, .movie-cover { height: 135px; }
            .movie-title { font-size: 0.88rem; }
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
        /* Menú lateral móvil mejorado */
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
        .main-content {
            padding-top: 80px !important;
        }
        .profile-section {
            gap: 12px !important;
            margin-bottom: 18px !important;
            padding: 0 4px;
        }
        .profile-card-horizontal {
            flex-direction: column !important;
            align-items: center !important;
            padding: 16px 6px 10px 6px !important;
            max-width: 98vw !important;
            gap: 10px !important;
            border-radius: 18px !important;
            box-shadow: 0 4px 18px #e5091440, 0 1px 10px #000a !important;
        }
        .profile-avatar-horizontal {
            margin: 0 auto 8px auto !important;
            width: 64px !important;
            height: 64px !important;
            font-size: 2.1rem !important;
            min-width: 64px !important;
            min-height: 64px !important;
            border-width: 4px !important;
        }
        .profile-info-horizontal {
            gap: 6px !important;
            width: 100%;
        }
        .profile-info-row {
            grid-template-columns: 1fr 1fr !important;
            gap: 4px 4px !important;
            margin-bottom: 0 !important;
        }
        .profile-label-h {
            font-size: 0.92rem !important;
            padding-right: 2px;
            text-align: right !important;
            justify-content: flex-end !important;
        }
        .profile-value-h {
            font-size: 0.98rem !important;
            padding: 6px 6px !important;
            border-radius: 7px !important;
            text-align: left !important;
            min-width: 0 !important;
            word-break: break-all;
        }
        .profile-actions-horizontal {
            flex-direction: column !important;
            gap: 8px !important;
            align-items: stretch !important;
            margin-top: 10px !important;
        }
        .profile-btn-h {
            font-size: 1rem !important;
            padding: 10px 0 !important;
            border-radius: 8px !important;
        }
        .section-title-row {
            margin-top: 18px !important;
            margin-bottom: 8px !important;
            text-align: center !important;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }
        .section-title {
            font-size: 1.1rem !important;
            letter-spacing: 1px;
        }
        .carousel-wrapper {
            margin-bottom: 18px !important;
            padding: 0 !important;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }
        .movie-card, .movie-poster-wrap {
            width: 110px !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 165px !important;
            border-radius: 8px !important;
        }
        .movie-title {
            font-size: 0.92rem !important;
            margin-bottom: 2px !important;
            margin-top: 0 !important;
            text-align: center !important;
            max-width: 100%;
            white-space: normal !important;
        }
        .movie-meta {
            gap: 6px !important;
            font-size: 0.88rem !important;
            justify-content: center !important;
        }
        .carousel-nav {
            width: 32px !important;
            height: 32px !important;
            font-size: 1.2rem !important;
            top: 40% !important;
        }
        }

        @media (max-width: 600px) {
        .profile-actions-horizontal {
            flex-direction: row !important;
            gap: 8px !important;
            align-items: center !important;
            justify-content: center !important;
            margin-top: 10px !important;
        }
        .profile-btn-h {
            font-size: 0.92rem !important;
            padding: 7px 12px !important;
            border-radius: 7px !important;
            min-width: 0 !important;
            flex: 1 1 auto;
            max-width: 120px;
        }
        }
        @media (max-width: 600px) {
        .carousel-wrapper {
            margin-bottom: 14px !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .owl-carousel.profile-carousel .item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 2px !important;
        }
        .movie-card, .movie-poster-wrap {
            width: 120px !important;
            min-width: 100px !important;
            margin: 0 4px !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 180px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px #0008 !important;
        }
        .movie-title {
            font-size: 1rem !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            max-width: 100%;
            white-space: normal !important;
            font-weight: 700 !important;
            color: #fff !important;
        }
        .movie-meta {
            gap: 4px !important;
            font-size: 0.92rem !important;
            justify-content: center !important;
            margin-top: 0 !important;
            color: #ff2e7a !important;
        }
        .movie-year, .movie-rating {
            font-size: 0.92rem !important;
            color: #ff2e7a !important;
        }
        .carousel-nav {
            width: 36px !important;
            height: 36px !important;
            font-size: 1.3rem !important;
            top: 38% !important;
            background: #232027 !important;
            color: #fff !important;
            border-radius: 50% !important;
            border: none !important;
            box-shadow: 0 2px 8px #0005 !important;
            opacity: 0.85 !important;
            transition: background 0.2s, color 0.2s;
            z-index: 10;
        }
        .carousel-nav:active,
        .carousel-nav:focus {
            background: #e50914 !important;
            color: #fff !important;
            outline: none !important;
        }
        .section-title-row {
            margin-top: 12px !important;
            margin-bottom: 6px !important;
            text-align: center !important;
        }
        .section-title {
            font-size: 1.05rem !important;
            letter-spacing: 1px;
            color: #fff !important;
        }
        }

        @media (max-width: 600px) {
        /* Oculta las flechas de navegación de los carruseles */
        .carousel-nav {
            display: none !important;
        }

        @media (max-width: 600px) {
        .carousel-wrapper {
            margin-bottom: 10px !important;
            padding: 0 !important;
        }
        .owl-carousel.profile-carousel .item {
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .movie-card, .movie-poster-wrap {
            width: 46vw !important;
            min-width: 0 !important;
            max-width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 68vw !important;
            max-height: 220px !important;
            border-radius: 8px !important;
        }
        .movie-title {
            font-size: 0.98rem !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            max-width: 100%;
            white-space: normal !important;
            font-weight: 700 !important;
            color: #fff !important;
        }
        .movie-meta {
            gap: 6px !important;
            font-size: 0.92rem !important;
            justify-content: center !important;
            margin-top: 0 !important;
            color: #ff2e7a !important;
        }
        .carousel-nav {
            display: none !important;
        }
        
        }

        @media (max-width: 600px) {
        .movie-card, .movie-poster-wrap {
            width: 40vw !important;      /* Más delgado */
            min-width: 0 !important;
            max-width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .movie-poster-wrap, .movie-cover {
            height: 56vw !important;     /* Más alto */
            max-height: 320px !important;
            border-radius: 10px !important;
        }
        }
        @media (max-width: 600px) {
        .movie-title {
            font-size: 0.85rem !important;
            max-width: 100%;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            font-weight: 700 !important;
            color: #fff !important;
            line-height: 1.1 !important;
            min-height: unset !important;
        }
        }
        @media (max-width: 600px) {
        .movie-title {
            font-size: 0.85rem !important;
            max-width: 100%;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
            margin-top: 4px !important;
            text-align: center !important;
            font-weight: 700 !important;
            color: #fff !important;
            line-height: 1.1 !important;
            min-height: unset !important;
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
        
        /* Estilos adicionales para el logo como en painel.php */
        .header__logo {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        .header__content {
            padding-left: 4px !important;
        }
        
        /* Estilos del logo exactamente como en painel.php */
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
        @media (max-width: 600px) {
            .header__logo {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            .header__content {
                padding-left: 4px !important;
            }
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
    </style>
</head>
<body>
<!-- HEADER (copiado exactamente de painel.php) -->
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

<!-- Menú lateral móvil -->
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

<!-- CONTENIDO PRINCIPAL -->
<div class="container main-content">
    <div class="profile-section" style="justify-content:center;">
        <div class="profile-card-modern">
            <!-- Header del perfil -->
            <div class="profile-header">
                <div class="profile-header-content">
                    <div class="profile-avatar-modern">
                <i class="fas fa-user"></i>
            </div>
                    <div class="profile-welcome">
                        <h2>¡Bienvenido, <?php echo htmlspecialchars($user); ?>!</h2>
                        <p>Gestiona tu cuenta y disfruta del mejor contenido</p>
                </div>
                </div>
                </div>
            
            <!-- Estadísticas del usuario -->
            <div class="profile-stats">
                <!-- Primera fila -->
                <div class="stat-item">
                    <div class="stat-label">
                        <i class="fas fa-user"></i>
                        Usuario
                </div>
                    <div class="stat-value"><?php echo htmlspecialchars($user); ?></div>
            </div>
                
                <div class="stat-item">
                    <div class="stat-label">
                        <i class="fas fa-key"></i>
                        Contraseña
                    </div>
                    <div class="stat-value"><?php echo str_repeat('*', strlen($pwd)); ?></div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-label">
                        <i class="fas fa-calendar-alt"></i>
                        Vencimiento
                    </div>
                    <div class="stat-value"><?php echo isset($expira) ? htmlspecialchars($expira) : 'Desconocida'; ?></div>
                </div>
                
                <!-- Segunda fila -->
                <div class="stat-item">
                    <div class="stat-label">
                        <i class="fas fa-history"></i>
                        Historial
                    </div>
                    <div class="stat-value" id="historial-count">0</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-label">
                        <i class="fas fa-heart"></i>
                        Favoritos
                    </div>
                    <div class="stat-value" id="favoritos-count">0</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-label">
                        <i class="fas fa-clock"></i>
                        Fecha Actual
                    </div>
                    <div class="stat-value" id="fecha-actual"></div>
                </div>
            </div>
            
            <!-- Información compacta para móviles -->
            <div class="profile-compact-info">
                <div class="compact-row">
                    <span class="compact-label"><i class="fas fa-user"></i> Usuario:</span>
                    <span class="compact-value"><?php echo htmlspecialchars($user); ?></span>
                </div>
                <div class="compact-row">
                    <span class="compact-label"><i class="fas fa-key"></i> Clave:</span>
                    <span class="compact-value"><?php echo str_repeat('*', strlen($pwd)); ?></span>
                </div>
                <div class="compact-row">
                    <span class="compact-label"><i class="fas fa-calendar-alt"></i> Vence:</span>
                    <span class="compact-value"><?php echo isset($expira) ? htmlspecialchars($expira) : 'Desconocida'; ?></span>
                </div>
                <div class="compact-row">
                    <span class="compact-label"><i class="fas fa-history"></i> Historial:</span>
                    <span class="compact-value" id="historial-count-compact">0</span>
                </div>
                <div class="compact-row">
                    <span class="compact-label"><i class="fas fa-heart"></i> Favoritos:</span>
                    <span class="compact-value" id="favoritos-count-compact">0</span>
                </div>
                <div class="compact-row">
                    <span class="compact-label"><i class="fas fa-clock"></i> Fecha:</span>
                    <span class="compact-value" id="fecha-actual-compact"></div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="profile-actions-modern">
                <button class="profile-btn-modern" onclick="location.href='home.php'">
                    <i class="fas fa-home"></i>
                    Ir al Inicio
                </button>
                <button class="profile-btn-modern" onclick="location.href='home.php?acao=sair'">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </button>
            </div>
        </div>
    </div>

    <!-- HISTORIAL -->
    <div class="section-title-row">
        <span class="section-title"><i class="fas fa-history"></i> HISTORIAL</span>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-nav historial-nav-prev" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="owl-carousel profile-carousel" id="historial-carousel"></div>
        <button class="carousel-nav historial-nav-next" type="button">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <!-- FAVORITOS -->
    <div class="section-title-row">
        <span class="section-title"><i class="fas fa-heart"></i> FAVORITOS</span>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-nav favoritos-nav-prev" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="owl-carousel profile-carousel" id="favoritos-carousel"></div>
        <button class="carousel-nav favoritos-nav-next" type="button">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>

    <!-- RECOMENDADOS -->
    <div class="section-title-row">
        <span class="section-title"><i class="fas fa-magic"></i> RECOMENDADOS</span>
    </div>
    <div class="carousel-wrapper">
        <button class="carousel-nav recomendados-nav-prev" type="button">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="owl-carousel profile-carousel" id="recomendados-carousel">
            <?php foreach($recomendados as $item):
                $url = ($item['tipo'] === "serie")
                    ? "serie.php?stream={$item['id']}&streamtipo=serie"
                    : "movie.php?stream={$item['id']}&streamtipo=movie";
            ?>
            <div class="item">
                <div class="movie-card">
                    <div class="movie-poster-wrap">
                        <img class="movie-cover" src="<?php echo htmlspecialchars($item['img']); ?>" alt="">
                        <div class="movie-overlay">
                            <a href="<?php echo $url; ?>" class="movie-play"><i class="fas fa-play"></i></a>
                        </div>
                    </div>
                    <div class="movie-info">
                        <div class="movie-title">
                            <a href="<?php echo $url; ?>"><?php echo htmlspecialchars($item['nombre']); ?></a>
                        </div>
                        <div class="movie-meta">
                            <span class="movie-year"><?php echo htmlspecialchars($item['ano']); ?></span>
                            <span class="movie-rating"><i class="fas fa-star"></i> <?php echo htmlspecialchars($item['rate']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-nav recomendados-nav-next" type="button">
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<script src="./scripts/vendors/jquery-3.5.1.min.js"></script>
<script src="./scripts/vendors/owl.carousel.min.js"></script>
<script>
// HISTORIAL
$(document).ready(function(){
    fetch('libs/endpoints/UserData.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_historial'
    })
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.success && data.historial.length > 0) {
            data.historial.forEach(function(item){
                let url = (item.tipo === "serie")
                    ? `serie.php?stream=${item.id}&streamtipo=serie`
                    : `movie.php?stream=${item.id}&streamtipo=movie`;
                html += `<div class="item">
                    <div class="movie-card">
                        <div class="movie-poster-wrap">
                            <img class="movie-cover" src="${item.img}" alt="">
                            <div class="movie-overlay">
                                <a href="${url}" class="movie-play"><i class="fas fa-play"></i></a>
                            </div>
                        </div>
                        <div class="movie-info">
                            <div class="movie-title">
                                <a href="${url}">${item.nombre}</a>
                            </div>
                            <div class="movie-meta">
                                <span class="movie-year">${item.ano ? item.ano.toString().substring(0,4) : ''}</span>
                                <span class="movie-rating"><i class="fas fa-star"></i> ${item.rate}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html = '<div style="color:#fff;padding:20px;">Sin historial</div>';
        }
        $('#historial-carousel').html(html);

        let $carousel = $('#historial-carousel');
        $carousel.owlCarousel({
            loop: true,
            margin: 20,
            nav: false,
            dots: false,
            autoplay: false,
            smartSpeed: 1800,
            responsive:{
            0:{ items:2, margin:30 },      // <-- margen 0 en móviles
            600:{ items:3, margin:20 },
            1000:{ items:4, margin:20 }
        }
    });
        $('.historial-nav-prev').off('click').on('click', function(){
            $carousel.trigger('prev.owl.carousel');
        });
        $('.historial-nav-next').off('click').on('click', function(){
            $carousel.trigger('next.owl.carousel');
        });
        setHistorialCount(data.success ? data.historial.length : 0);
    });

    // FAVORITOS
    fetch('libs/endpoints/UserData.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_favoritos'
    })
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.success && data.favoritos.length > 0) {
            data.favoritos.forEach(function(item){
                let url = (item.tipo === "serie")
                    ? `serie.php?stream=${item.id}&streamtipo=serie`
                    : `movie.php?stream=${item.id}&streamtipo=movie`;
                html += `<div class="item">
                    <div class="movie-card">
                        <div class="movie-poster-wrap">
                            <img class="movie-cover" src="${item.img}" alt="">
                            <div class="movie-overlay">
                                <a href="${url}" class="movie-play"><i class="fas fa-play"></i></a>
                            </div>
                        </div>
                        <div class="movie-info">
                            <div class="movie-title">
                                <a href="${url}">${item.nombre}</a>
                            </div>
                            <div class="movie-meta">
                                <span class="movie-year">${item.ano ? item.ano.toString().substring(0,4) : ''}</span>
                                <span class="movie-rating"><i class="fas fa-star"></i> ${item.rate}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html = '<div style="color:#fff;padding:20px;">Sin favoritos</div>';
        }
        $('#favoritos-carousel').html(html);

        let $carousel = $('#favoritos-carousel');
        $carousel.owlCarousel({
            loop: true,
            margin: 20,
            nav: false,
            dots: false,
            autoplay: false,
            smartSpeed: 1800,
            responsive:{
            0:{ items:2, margin:30 },      // <-- margen 0 en móviles
            600:{ items:3, margin:20 },
            1000:{ items:4, margin:20 }
        }
    });
        $('.favoritos-nav-prev').off('click').on('click', function(){
            $carousel.trigger('prev.owl.carousel');
        });
        $('.favoritos-nav-next').off('click').on('click', function(){
            $carousel.trigger('next.owl.carousel');
        });
        setFavoritosCount(data.success ? data.favoritos.length : 0);
    });

    // RECOMENDADOS (solo inicializa el carrusel, NO fetch)
    $('#recomendados-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: false,
        dots: false,
        autoplay: false,
        smartSpeed: 1800,
        responsive:{
        0:{ items:2, margin:30 },      // <-- margen 0 en móviles
        600:{ items:3, margin:20 },
        1000:{ items:4, margin:20 }
    }
});
    $('.recomendados-nav-prev').off('click').on('click', function(){
        $('#recomendados-carousel').trigger('prev.owl.carousel');
    });
    $('.recomendados-nav-next').off('click').on('click', function(){
        $('#recomendados-carousel').trigger('next.owl.carousel');
    });
});

// Fecha y hora en tiempo real
function actualizarFecha() {
    const fecha = new Date();
    const opciones = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute:'2-digit', second:'2-digit' };
    const fechaFormateada = fecha.toLocaleString('es-ES', opciones);
    document.getElementById('fecha-actual').textContent = fechaFormateada;
    document.getElementById('fecha-actual-compact').textContent = fechaFormateada;
}
setInterval(actualizarFecha, 1000);
actualizarFecha();

function setHistorialCount(n) {
    document.getElementById('historial-count').textContent = n;
    document.getElementById('historial-count-compact').textContent = n;
}
function setFavoritosCount(n) {
    document.getElementById('favoritos-count').textContent = n;
    document.getElementById('favoritos-count-compact').textContent = n;
}
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
