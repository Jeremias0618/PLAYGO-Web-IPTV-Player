<?php

function validateUser($username, $password) {
    $url = IP . "/player_api.php?username=$username&password=$password";
    $response = apixtream($url);
    $output = json_decode($response, true);
    
    if (empty($output) || !isset($output['user_info'])) {
        return ['success' => false, 'error' => 'invalid_credentials'];
    }
    
    $auth = $output['user_info']['auth'] ?? 0;
    $status = $output['user_info']['status'] ?? '';
    $exp_date = $output['user_info']['exp_date'] ?? 0;
    
    if ($auth == 1) {
        $now = time();
        if ($exp_date > 0 && $exp_date < $now) {
            return ['success' => false, 'error' => 'expired'];
        }
        
        if ($status == 'Banned' || $status == 'banned') {
            return ['success' => false, 'error' => 'banned'];
        }
        
        return ['success' => true];
    }
    
    if ($exp_date > 0 && $exp_date < time()) {
        return ['success' => false, 'error' => 'expired'];
    }
    
    if ($status == 'Banned' || $status == 'banned') {
        return ['success' => false, 'error' => 'banned'];
    }
    
    return ['success' => false, 'error' => 'invalid_credentials'];
}


function validar_usuario($username, $password) {
    return validateUser($username, $password);
}

