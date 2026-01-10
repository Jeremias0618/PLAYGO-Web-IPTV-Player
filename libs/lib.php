<?php
date_default_timezone_set("America/Lima");
ini_set("allow_url_fopen", 1);
ini_set("display_errors", 0);
error_reporting(0);
ini_set("track_errors","0");

if (!defined('IP')) {
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        require_once($configPath);
    } else {
        $configPath = dirname(__DIR__) . '/libs/config.php';
        if (file_exists($configPath)) {
            require_once($configPath);
        }
    }
}


function apixtream($url_api){
$start_time = microtime(true);
$ch = curl_init();	
$timeout = 10;	
curl_setopt ($ch, CURLOPT_URL, $url_api);	
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);	
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);	
$retorno = curl_exec($ch);
$exec_time = (microtime(true) - $start_time) * 1000;
if ($exec_time > 500) {
    $action = '';
    if (strpos($url_api, 'get_vod_info') !== false) $action = 'get_vod_info';
    elseif (strpos($url_api, 'get_vod_streams') !== false) $action = 'get_vod_streams';
    elseif (strpos($url_api, 'get_series') !== false) $action = 'get_series';
    else $action = 'other';
    error_log("[apixtream] Llamada lenta ($action): " . number_format($exec_time, 2) . "ms - URL: " . substr($url_api, 0, 100));
}
curl_close($ch);	
return $retorno;
}

function gerar_hash($length) {
$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' . rand(0, 99999);
$randomString = '';
for ($i = 0; $i < $length; $i++) {
  $randomString .= $characters[rand(0, strlen($characters) - 1)];
}
return $randomString;
}

function limitar_texto($texto, $limite){
  $contador = strlen($texto);
  if ( $contador >= $limite ) {      
    $texto = substr($texto, 0, strrpos(substr($texto, 0, $limite), ' ')) . '';
    return $texto;
  }
  else{
    return $texto;
  }
}

function limpiar_titulo_episodio($titulo) {
    if (empty($titulo)) {
        return 'Episodio';
    }
    
    $titulo = trim($titulo);
    
    if (preg_match('/S\d+E\d+/i', $titulo, $matches, PREG_OFFSET_CAPTURE)) {
        $season_pos = $matches[0][1];
        $season_len = strlen($matches[0][0]);
        $after_season = trim(substr($titulo, $season_pos + $season_len));
        
        if (!empty($after_season)) {
            if (preg_match('/^-\s+(.+)$/s', $after_season, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/^\s+(.+)$/s', $after_season, $m)) {
                return trim($m[1]);
            }
            return $after_season;
        }
        
        if ($season_pos > 0) {
            $before_season = trim(substr($titulo, 0, $season_pos));
            if (preg_match('/-\s+(.+)$/s', $before_season, $m)) {
                return trim($m[1]);
            }
            $parts = array_filter(array_map('trim', explode('-', $before_season)));
            if (!empty($parts)) {
                $last = trim(end($parts));
                if (!preg_match('/^S\d+E\d+$/i', $last)) {
                    return $last;
                }
            }
        }
        
        return $titulo;
    }
    
    if (strpos($titulo, '-') !== false) {
        $parts = array_map('trim', explode('-', $titulo));
        $parts = array_filter($parts, function($p) {
            return !empty($p);
        });
        
        if (count($parts) > 1) {
            $last_part = trim(end($parts));
            if (!preg_match('/^S\d+E\d+$/i', $last_part)) {
                return $last_part;
            }
            array_pop($parts);
            if (!empty($parts)) {
                return trim(end($parts));
            }
        }
        
        return $titulo;
    }
    
    return $titulo;
} 

function ds($ds) {
	
	$dataent = explode(" ",$ds);
	$dsent1 = $dataent[0];
	$datas = explode("-",$dsent1);
	
	$datacerta = $datas[2] . '/'.$datas[1].'/'.$datas[0];
	
	return $datacerta . ' ' . $dataent[1];
	
}


if($_GET['acao'] == 'sair') {
  session_unset();
  session_destroy();
  setcookie('xuserm');
  setcookie('xpwdm');
  setcookie('xstatusm');
  setcookie('xconnm');
  setcookie('xtestem');
  setcookie('xdataexpm');
  header("Location: login.php");	
}

?>