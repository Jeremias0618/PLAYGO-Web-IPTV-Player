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
$ch = curl_init();	
$timeout = 10;	
curl_setopt ($ch, CURLOPT_URL, $url_api);	
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);	
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);	
$retorno = curl_exec($ch);	
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