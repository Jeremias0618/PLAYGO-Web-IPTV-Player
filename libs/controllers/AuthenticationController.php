<?php

function handleLoginRequest() {
    if (isset($_COOKIE['xuserm']) && isset($_COOKIE['xpwdm'])) {
        header("Location: home.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['op'] === 'login') {
        $username = trim($_POST['usuario']);
        $password = trim($_POST['senha']);

        if (validar_usuario($username, $password)) {
            setcookie('xuserm', $username, time() + (7 * 24 * 60 * 60), "/");
            setcookie('xpwdm', $password, time() + (7 * 24 * 60 * 60), "/");
            header("Location: home.php");
            exit;
        } else {
            header("Location: login.php?sess=erro");
            exit;
        }
    }
}

