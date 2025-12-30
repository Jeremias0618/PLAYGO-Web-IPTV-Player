<?php

function handleLoginRequest() {
    $userIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if (isset($_COOKIE['xuserm']) && isset($_COOKIE['xpwdm'])) {
        header("Location: painel.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['op'] === 'login') {
        $block = checkBlockStatus($userIp);
        if ($block) {
            $timeRemaining = $block['expira'] - time();
            $minutesRemaining = ceil($timeRemaining / 60);
            header("Location: login.php?sess=blocked&time=" . $minutesRemaining);
            exit;
        }

        $username = trim($_POST['usuario']);
        $password = trim($_POST['senha']);

        if (validar_usuario($username, $password)) {
            clearAttempts($userIp);
            setcookie('xuserm', $username, time() + (7 * 24 * 60 * 60), "/");
            setcookie('xpwdm', $password, time() + (7 * 24 * 60 * 60), "/");
            header("Location: painel.php");
            exit;
        } else {
            registerFailedAttempt($userIp);
            header("Location: login.php?sess=erro");
            exit;
        }
    }
}

