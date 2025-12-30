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


function validar_usuario($username, $password) {
    return validateUser($username, $password);
}

