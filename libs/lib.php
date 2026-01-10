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

function formatear_rating($rating) {
    if (empty($rating) || !is_numeric($rating)) {
        return $rating;
    }
    
    $rating_float = floatval($rating);
    $rating_truncado = floor($rating_float * 10) / 10;
    
    return number_format($rating_truncado, 1, '.', '');
} 

function ds($ds) {
	
	$dataent = explode(" ",$ds);
	$dsent1 = $dataent[0];
	$datas = explode("-",$dsent1);
	
	$datacerta = $datas[2] . '/'.$datas[1].'/'.$datas[0];
	
	return $datacerta . ' ' . $dataent[1];
	
}

function getTmdbEpisodeRuntime($tmdb_id, $season, $episode_num) {
    if (!$tmdb_id || !$season || !$episode_num) {
        return null;
    }
    
    $runtime_dir = __DIR__ . '/../assets/tmdb_runtime/';
    if (!is_dir($runtime_dir)) {
        @mkdir($runtime_dir, 0777, true);
    }
    
    $cache_filename = "{$tmdb_id}_{$season}_{$episode_num}.json";
    $cache_path = $runtime_dir . $cache_filename;
    
    if (file_exists($cache_path)) {
        $cached_data = @json_decode(file_get_contents($cache_path), true);
        if (isset($cached_data['runtime']) && is_numeric($cached_data['runtime']) && $cached_data['runtime'] > 0) {
            return intval($cached_data['runtime']);
        }
    }
    
    if (!defined('TMDB_API_KEY') || empty(TMDB_API_KEY)) {
        return null;
    }
    
    $language = defined('LANGUAGE') ? LANGUAGE : 'es-ES';
    $tmdb_ep_url = "https://api.themoviedb.org/3/tv/$tmdb_id/season/$season/episode/$episode_num?api_key=" . TMDB_API_KEY . "&language=" . $language;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tmdb_ep_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $tmdb_ep_json = @curl_exec($ch);
    curl_close($ch);
    
    $tmdb_ep_data = @json_decode($tmdb_ep_json, true);
    if (!empty($tmdb_ep_data['runtime']) && is_numeric($tmdb_ep_data['runtime']) && $tmdb_ep_data['runtime'] > 0) {
        $runtime_minutes = intval($tmdb_ep_data['runtime']);
        $cache_data = ['runtime' => $runtime_minutes, 'cached_at' => date('Y-m-d H:i:s')];
        @file_put_contents($cache_path, json_encode($cache_data, JSON_PRETTY_PRINT));
        return $runtime_minutes;
    }
    
    return null;
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