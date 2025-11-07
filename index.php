<?php 
include("Xtream_api.php"); 

// Función para verificar si el usuario está bloqueado
function verificar_bloqueo($ip) {
    $archivo_bloqueo = 'bloqueos.json';
    if (!file_exists($archivo_bloqueo)) {
        return false;
    }
    
    $bloqueos = json_decode(file_get_contents($archivo_bloqueo), true);
    if (!isset($bloqueos[$ip])) {
        return false;
    }
    
    $bloqueo = $bloqueos[$ip];
    $tiempo_actual = time();
    
    // Si el bloqueo ha expirado, eliminarlo
    if ($tiempo_actual > $bloqueo['expira']) {
        unset($bloqueos[$ip]);
        file_put_contents($archivo_bloqueo, json_encode($bloqueos));
        return false;
    }
    
    return $bloqueo;
}

// Función para registrar intento fallido
function registrar_intento_fallido($ip) {
    $archivo_bloqueo = 'bloqueos.json';
    $bloqueos = [];
    
    if (file_exists($archivo_bloqueo)) {
        $bloqueos = json_decode(file_get_contents($archivo_bloqueo), true) ?: [];
    }
    
    $tiempo_actual = time();
    
    if (!isset($bloqueos[$ip])) {
        $bloqueos[$ip] = [
            'intentos' => 1,
            'primer_intento' => $tiempo_actual,
            'ultimo_intento' => $tiempo_actual,
            'expira' => $tiempo_actual + 300 // 5 minutos
        ];
    } else {
        $bloqueos[$ip]['intentos']++;
        $bloqueos[$ip]['ultimo_intento'] = $tiempo_actual;
        
        // Bloqueo progresivo: más intentos = más tiempo bloqueado
        if ($bloqueos[$ip]['intentos'] >= 5) {
            $bloqueos[$ip]['expira'] = $tiempo_actual + 1800; // 30 minutos
        } elseif ($bloqueos[$ip]['intentos'] >= 3) {
            $bloqueos[$ip]['expira'] = $tiempo_actual + 900; // 15 minutos
        } else {
            $bloqueos[$ip]['expira'] = $tiempo_actual + 300; // 5 minutos
        }
    }
    
    file_put_contents($archivo_bloqueo, json_encode($bloqueos));
}

// Función para limpiar intentos exitosos
function limpiar_intentos($ip) {
    $archivo_bloqueo = 'bloqueos.json';
    if (!file_exists($archivo_bloqueo)) {
        return;
    }
    
    $bloqueos = json_decode(file_get_contents($archivo_bloqueo), true) ?: [];
    if (isset($bloqueos[$ip])) {
        unset($bloqueos[$ip]);
        file_put_contents($archivo_bloqueo, json_encode($bloqueos));
    }
}

// Obtener IP del usuario
$ip_usuario = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (isset($_COOKIE['xuserm']) && isset($_COOKIE['xpwdm'])) {
    header("Location: painel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['op'] === 'login') {
    // Verificar si está bloqueado
    $bloqueo = verificar_bloqueo($ip_usuario);
    if ($bloqueo) {
        $tiempo_restante = $bloqueo['expira'] - time();
        $minutos_restantes = ceil($tiempo_restante / 60);
        header("Location: index.php?sess=blocked&time=" . $minutos_restantes);
        exit;
    }
    
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    if (validar_usuario($usuario, $senha)) {
        // Login exitoso - limpiar intentos fallidos
        limpiar_intentos($ip_usuario);
        setcookie('xuserm', $usuario, time() + (7 * 24 * 60 * 60), "/");
        setcookie('xpwdm', $senha, time() + (7 * 24 * 60 * 60), "/");
        header("Location: painel.php");
        exit;
    } else {
        // Login fallido - registrar intento
        registrar_intento_fallido($ip_usuario);
        header("Location: index.php?sess=erro");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PLAYGO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#e50914">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        body {
            background: url('img/wallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            height: 100dvh; /* Dynamic viewport height for mobile */
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            position: relative;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* Prevent zoom on input focus on iOS */
            -webkit-text-size-adjust: 100%;
            /* Improve touch scrolling on Android */
            -webkit-overflow-scrolling: touch;
            /* Prevent scroll when content fits */
            overflow: hidden;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(20, 20, 20, 0.45);
            backdrop-filter: blur(4px);
            z-index: 1;
        }
        .login-container {
            height: 100vh;
            height: 100dvh; /* Dynamic viewport height for mobile */
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }
        .login-box {
            background: rgba(30,30,30,0.38);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.37);
            padding: 40px 32px 32px 32px;
            max-width: 370px;
            width: 100%;
            border: 1.5px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(12px);
            animation: fadeIn 1.2s cubic-bezier(.39,.575,.565,1) both;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(40px);}
            100% { opacity: 1; transform: translateY(0);}
        }
        .login-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 0;
        }
        .login-logo img {
            height: 80px;
            filter: drop-shadow(0 2px 16px #e50914cc);
            transition: transform 0.3s;
        }
        .login-logo img:hover {
            transform: scale(1.07) rotate(-2deg);
        }
        .login-title {
            color: #ff2e63;
            font-size: 3rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 24px;
            letter-spacing: 2px;
            text-shadow: 0 2px 12px #000a;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .login-desc {
            color: #e0e0e0;
            text-align: center;
            margin-bottom: 24px;
            font-size: 1.08rem;
            text-shadow: 0 1px 6px #0007;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-bottom: 18px;
        }
        .form-label {
            color: #fff;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: 1px;
            font-size: 1rem;
        }
        .form-control {
            background: rgba(30,30,30,0.85);
            border: 2px solid #e50914;
            border-radius: 8px;
            color: #fff;
            padding: 13px;
            margin-bottom: 0;
            font-size: 1.08rem;
            transition: border 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px 0 rgba(229,9,20,0.07);
            /* Improve touch targets for Android */
            min-height: 48px;
            /* Prevent zoom on focus */
            font-size: 16px;
            /* Better touch interaction */
            -webkit-appearance: none;
            -webkit-tap-highlight-color: transparent;
        }
        .form-control:focus {
            background: rgba(40,40,40,0.95);
            color: #fff;
            outline: none !important;
            border: 2px solid #ff2e63;
            box-shadow: 0 0 0 2px #ff2e6340;
        }
        .login-btn {
            width: 100%;
            background: linear-gradient(90deg, #e50914 0%, #ff2e63 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 1.18rem;
            letter-spacing: 1px;
            box-shadow: 0 2px 12px 0 #e5091440;
            transition: background 0.2s, transform 0.2s;
            margin-top: 10px;
            /* Improve touch targets for Android */
            min-height: 48px;
            /* Better touch interaction */
            -webkit-tap-highlight-color: transparent;
            cursor: pointer;
            /* Prevent text selection on touch */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #ff2e63 0%, #e50914 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .alert {
            margin-bottom: 18px;
            border-radius: 6px;
            padding: 12px 18px;
            font-size: 1.05rem;
            background: rgba(229,9,20,0.85);
            color: #fff;
            border: none;
            box-shadow: 0 2px 8px #e5091440;
            text-align: center;
        }
        
        .alert-blocked {
            background: rgba(255, 87, 34, 0.9) !important;
            border: 2px solid #ff5722;
            animation: pulse 2s infinite;
        }
        
        .alert-blocked b {
            display: block;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: rgba(255,255,255,0.3);
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #fff 0%, #ffeb3b 100%);
            border-radius: 2px;
            transition: width 1s linear;
            animation: progress-animation 1s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        @keyframes progress-animation {
            0% { width: 100%; }
            100% { width: 0%; }
        }
/* Android and Mobile Optimizations */
@media (max-width: 768px) {
    body {
        /* Prevent horizontal scroll on Android */
        overflow-x: hidden;
        /* Better viewport handling */
        height: 100vh;
        height: 100dvh;
        overflow: hidden;
    }
    
    .login-container {
        align-items: center !important;
        justify-content: center !important;
        padding: 20px 16px !important;
        height: 100vh !important;
        height: 100dvh !important;
        display: flex !important;
    }
    
    .login-box {
        max-width: 90vw !important;
        width: 90vw !important;
        padding: 24px 20px !important;
        border-radius: 16px !important;
        margin: 0 auto !important;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.3) !important;
        /* Better spacing for touch */
        margin-bottom: 20px !important;
    }
    
    .login-logo img {
        height: 60px !important;
        max-height: 60px !important;
        min-height: 50px !important;
    }
    
    .login-title {
        font-size: 2rem !important;
        margin-bottom: 16px !important;
        line-height: 1.2 !important;
    }
    
    .login-desc {
        font-size: 1rem !important;
        margin-bottom: 20px !important;
        line-height: 1.4 !important;
    }
    
    .form-group {
        margin-bottom: 20px !important;
    }
    
    .form-label {
        font-size: 1rem !important;
        margin-bottom: 8px !important;
    }
    
    .form-control {
        padding: 14px 16px !important;
        font-size: 16px !important; /* Prevent zoom on Android */
        min-height: 52px !important;
        border-radius: 10px !important;
        margin-bottom: 0 !important;
    }
    
    .login-btn {
        padding: 16px !important;
        font-size: 1.1rem !important;
        margin-top: 8px !important;
        min-height: 52px !important;
        border-radius: 10px !important;
        /* Better touch feedback */
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.1);
    }
    
    .alert {
        font-size: 0.95rem !important;
        padding: 12px 16px !important;
        border-radius: 8px !important;
        margin-bottom: 16px !important;
        line-height: 1.4 !important;
    }
}

/* Small Android phones (320px - 480px) */
@media (max-width: 480px) {
    .login-container {
        padding: 16px 12px !important;
    }
    
    .login-box {
        max-width: 95vw !important;
        width: 95vw !important;
        padding: 20px 16px !important;
    }
    
    .login-title {
        font-size: 1.8rem !important;
        margin-bottom: 12px !important;
    }
    
    .login-desc {
        font-size: 0.95rem !important;
        margin-bottom: 16px !important;
    }
    
    .form-control {
        padding: 12px 14px !important;
        min-height: 48px !important;
    }
    
    .login-btn {
        padding: 14px !important;
        min-height: 48px !important;
    }
}

/* Landscape orientation on mobile */
@media (max-width: 768px) and (orientation: landscape) {
    .login-container {
        padding: 10px 16px !important;
        align-items: center !important;
    }
    
    .login-box {
        max-width: 80vw !important;
        width: 80vw !important;
        padding: 20px !important;
    }
    
    .login-logo img {
        height: 50px !important;
    }
    
    .login-title {
        font-size: 1.6rem !important;
        margin-bottom: 12px !important;
    }
    
    .login-desc {
        margin-bottom: 16px !important;
    }
    
    .form-group {
        margin-bottom: 16px !important;
    }
}

/* High DPI Android devices */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .login-box {
        border-width: 0.5px !important;
    }
    
    .form-control {
        border-width: 1px !important;
    }
}

/* When virtual keyboard is open (shorter viewport) */
@media (max-height: 500px) and (max-width: 768px) {
    body {
        overflow: auto !important;
    }
    
    .login-container {
        align-items: flex-start !important;
        padding-top: 5vh !important;
        height: 100vh !important;
        height: 100dvh !important;
    }
    
    .login-box {
        margin-top: 0 !important;
    }
    
    .login-logo img {
        height: 40px !important;
    }
    
    .login-title {
        font-size: 1.5rem !important;
        margin-bottom: 10px !important;
    }
    
    .login-desc {
        margin-bottom: 12px !important;
    }
    
    .form-group {
        margin-bottom: 12px !important;
    }
}
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="img/logo.png" alt="MAXGO" title="MAXGO">
            </div>
            <div class="login-desc">Ingresa tus datos para acceder</div>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'teste') { ?>
                <div class="alert"><b>OCURRIÓ UN ERROR</b> Lo sentimos, ya solicitaste una prueba en nuestro sistema.</div>
            <?php } ?>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'block') { ?>
                <div class="alert"><b>USUARIO BLOQUEADO</b> Lo sentimos, tu usuario está bloqueado o vencido. Contacta al soporte.</div>
            <?php } ?>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'blocked') { 
                $tiempo = isset($_GET['time']) ? (int)$_GET['time'] : 5;
            ?>
                <div class="alert alert-blocked">
                    <b>🔒 ACCESO TEMPORALMENTE BLOQUEADO</b><br>
                    Demasiados intentos fallidos. Intenta nuevamente en <span id="countdown"><?php echo $tiempo; ?></span> minutos.
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 100%;"></div>
                    </div>
                </div>
            <?php } ?>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'erro') { ?>
                <div class="alert"><b>⚠️ DATOS INVÁLIDOS</b> No fue posible iniciar sesión, datos no encontrados en el sistema.</div>
            <?php } ?>
            <form method="POST" action="">
                <input type="hidden" name="op" value="login"/>
                <div class="form-group">
                    <label class="form-label" for="usuario">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required autocomplete="username" autocapitalize="off" autocorrect="off" spellcheck="false">
                </div>
                <div class="form-group">
                    <label class="form-label" for="senha">Contraseña</label>
                    <input type="password" class="form-control" id="senha" name="senha" required autocomplete="current-password" autocapitalize="off" autocorrect="off" spellcheck="false">
                </div>
                <button class="login-btn" type="submit">Entrar</button>
            </form>
        </div>
    </div>
    <?php include("inc/scripts.php"); ?>
    
    <script>
    // Sistema de bloqueo y countdown
    document.addEventListener('DOMContentLoaded', function() {
        const blockedAlert = document.querySelector('.alert-blocked');
        const form = document.querySelector('form');
        const inputs = document.querySelectorAll('.form-control');
        const submitBtn = document.querySelector('.login-btn');
        
        if (blockedAlert) {
            // Deshabilitar formulario cuando está bloqueado
            if (form) {
                form.style.pointerEvents = 'none';
                form.style.opacity = '0.5';
            }
            
            inputs.forEach(input => {
                input.disabled = true;
                input.style.cursor = 'not-allowed';
            });
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.textContent = 'BLOQUEADO';
            }
            
            // Countdown timer
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                let timeLeft = parseInt(countdownElement.textContent);
                const countdownInterval = setInterval(() => {
                    timeLeft--;
                    countdownElement.textContent = timeLeft;
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        // Recargar página cuando expire el bloqueo
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1000);
                    }
                }, 60000); // Actualizar cada minuto
            }
        }
        
        // Prevenir múltiples envíos del formulario
        if (form && !blockedAlert) {
            let isSubmitting = false;
            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                submitBtn.textContent = 'VERIFICANDO...';
                submitBtn.disabled = true;
            });
        }
    });
    </script>
</body>
</html>