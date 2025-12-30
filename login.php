<?php
require_once("libs/lib.php");
require_once("libs/services/auth.php");
require_once("libs/controllers/login-controller.php");

handleLoginRequest();
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
    <link rel="icon" type="image/x-icon" href="assets/icon/favicon.ico">
    <link rel="stylesheet" href="styles/login/login.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="assets/logo/logo.png" alt="PLAYGO" title="PLAYGO">
            </div>
            <div class="login-desc">Ingresa tus datos para acceder</div>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'teste') { ?>
                <div class="alert"><b>OCURRIÓ UN ERROR</b> Lo sentimos, ya solicitaste una prueba en nuestro sistema.</div>
            <?php } ?>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'block') { ?>
                <div class="alert"><b>USUARIO BLOQUEADO</b> Lo sentimos, tu usuario está bloqueado o vencido. Contacta al soporte.</div>
            <?php } ?>
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'blocked') {
                $time = isset($_GET['time']) ? (int)$_GET['time'] : 5;
            ?>
                <div class="alert alert-blocked">
                    <b>🔒 ACCESO TEMPORALMENTE BLOQUEADO</b><br>
                    Demasiados intentos fallidos. Intenta nuevamente en <span id="countdown"><?php echo $time; ?></span> minutos.
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
    <script src="scripts/login/blocked-handler.js"></script>
    <script src="scripts/login/form-handler.js"></script>
</body>
</html>
