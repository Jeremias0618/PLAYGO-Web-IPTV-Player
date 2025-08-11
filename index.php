<?php 
include("Xtream_api.php"); 

if (isset($_COOKIE['xuserm']) && isset($_COOKIE['xpwdm'])) {
    header("Location: painel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['op'] === 'login') {
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    if (validar_usuario($usuario, $senha)) {
        setcookie('xuserm', $usuario, time() + (7 * 24 * 60 * 60), "/");
        setcookie('xpwdm', $senha, time() + (7 * 24 * 60 * 60), "/");
        header("Location: painel.php");
        exit;
    } else {
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        body {
            background: url('img/wallpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            position: relative;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(20, 20, 20, 0.45);
            backdrop-filter: blur(4px);
            z-index: 1;
        }
        .login-container {
            min-height: 100vh;
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
@media (max-width: 600px) {
    .login-container {
        align-items: center !important;
        justify-content: center !important;
        padding-top: 0 !important;
        min-height: 100vh !important;
        display: flex !important;
    }
    .login-box {
        max-width: 80vw !important;
        width: 80vw !important;
        padding: 6vw 3vw 6vw 3vw !important;
        border-radius: 14px !important;
        font-size: 1rem !important;
        margin: 0 auto !important;
        box-shadow: 0 4px 18px 0 rgba(0,0,0,0.18) !important;
    }
    .login-logo img {
        height: 14vw !important;
        max-height: 60px !important;
        min-height: 36px !important;
    }
    .login-title {
        font-size: 1.3rem !important;
        margin-bottom: 10px !important;
    }
    .login-desc {
        font-size: 0.95rem !important;
        margin-bottom: 14px !important;
    }
    .form-label,
    .form-control,
    .login-btn {
        font-size: 0.95rem !important;
    }
    .form-control {
        padding: 8px !important;
        margin-bottom: 10px !important;
    }
    .login-btn {
        padding: 10px !important;
        font-size: 1rem !important;
        margin-top: 6px !important;
    }
    .alert {
        font-size: 0.95rem !important;
        padding: 8px 10px !important;
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
            <?php if(isset($_GET['sess']) && $_GET['sess'] == 'erro') { ?>
                <div class="alert"><b>DATOS INVÁLIDOS</b> No fue posible iniciar sesión, datos no encontrados en el sistema.</div>
            <?php } ?>
            <form method="POST" action="">
                <input type="hidden" name="op" value="login"/>
                <div class="form-group">
                    <label class="form-label" for="usuario">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="senha">Contraseña</label>
                    <input type="password" class="form-control" id="senha" name="senha" required autocomplete="current-password">
                </div>
                <button class="login-btn" type="submit">Entrar</button>
            </form>
        </div>
    </div>
    <?php include("inc/scripts.php"); ?>
</body>
</html>