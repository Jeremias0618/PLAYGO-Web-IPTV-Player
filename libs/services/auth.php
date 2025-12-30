<?php

function validateUser($username, $password) {
    $url = IP . "/player_api.php?username=$username&password=$password";
    $response = apixtream($url);
    $output = json_decode($response, true);
    
    if (isset($output['user_info']['auth']) && $output['user_info']['auth'] == 1) {
        return true;
    }
    
    return false;
}

function checkBlockStatus($ip) {
    $blockFile = 'bloqueos.json';
    if (!file_exists($blockFile)) {
        return false;
    }

    $blocks = json_decode(file_get_contents($blockFile), true);
    if (!isset($blocks[$ip])) {
        return false;
    }

    $block = $blocks[$ip];
    $currentTime = time();

    if ($currentTime > $block['expira']) {
        unset($blocks[$ip]);
        file_put_contents($blockFile, json_encode($blocks));
        return false;
    }

    return $block;
}

function registerFailedAttempt($ip) {
    $blockFile = 'bloqueos.json';
    $blocks = [];

    if (file_exists($blockFile)) {
        $blocks = json_decode(file_get_contents($blockFile), true) ?: [];
    }

    $currentTime = time();

    if (!isset($blocks[$ip])) {
        $blocks[$ip] = [
            'intentos' => 1,
            'primer_intento' => $currentTime,
            'ultimo_intento' => $currentTime,
            'expira' => $currentTime + 300
        ];
    } else {
        $blocks[$ip]['intentos']++;
        $blocks[$ip]['ultimo_intento'] = $currentTime;

        if ($blocks[$ip]['intentos'] >= 5) {
            $blocks[$ip]['expira'] = $currentTime + 1800;
        } elseif ($blocks[$ip]['intentos'] >= 3) {
            $blocks[$ip]['expira'] = $currentTime + 900;
        } else {
            $blocks[$ip]['expira'] = $currentTime + 300;
        }
    }

    file_put_contents($blockFile, json_encode($blocks));
}

function clearAttempts($ip) {
    $blockFile = 'bloqueos.json';
    if (!file_exists($blockFile)) {
        return;
    }

    $blocks = json_decode(file_get_contents($blockFile), true) ?: [];
    if (isset($blocks[$ip])) {
        unset($blocks[$ip]);
        file_put_contents($blockFile, json_encode($blocks));
    }
}

function validar_usuario($username, $password) {
    return validateUser($username, $password);
}

