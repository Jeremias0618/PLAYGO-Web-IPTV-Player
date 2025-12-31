<?php

define('IP','https://URL'); 

define('TMDB_API_KEY', 'api_here');

$customChannelLogos = [];

$template = 'blue'; 

define("WHATSAPP", ''); 

define("NOME_IPTV", 'PLAYGO'); 

define("LINK_PAGAR1", '');
define("LINK_PAGAR2", '');
define("LINK_PAGAR3", '');
define("LINK_PAGAR4", '');

$nome1 = "Mensal";
$valor1 = "29.90";
$nome2 = "Trimestral";
$valor2 = "79.90";
$nome3 = "Semestral";
$valor3 = "129.90";
$nome4 = "Anual";
$valor4 = "199.90";


define("ATIVAR_TESTE", '0');
define("HORAS", '2');
define("XTREAM_URL", 'https://cms-us.xtream-codes.com/');
define("XTREAM_USER", '');
define("XTREAM_PWD", '');
define("XTREAM_PLANO", '1');
define("ATIVA_BLOQUEIO_TESTE", '0');

define("SMTP_HOST", 'mail.playgo.pe');
define("SMTP_USER", '');
define("SMTP_SENHA", '');
define("SMTP_PORTA", '587');
define("SMTP_SEGURANCA", 'tls');
define("EMAIL_ASSUNTO", 'Bienvenido a PLAYGO - WebPlayer');
define("EMAIL_REVENDA", 'contato@playgo.pe');
define("NOME_REVENDA", 'PLAYGO');

define("AVISO_ADULTOS_CANAL", 'XXX: ADULTOS');
define("AVISO_ADULTOS_FILME", 'FILMES: ADULTOS');


define("CORPO_EMAIL", "Hola %NOME%, tu prueba fue creada con éxito.<br><br>
    Puedes iniciar sesión en nuestro webplayer con los siguientes datos:<br>
    <br>
    Usuario: <b>%USUARIO%</b> <br>
    Contraseña: <b>%SENHA%</b> <br><br><br>
    También puedes probar nuestra lista descargando desde el enlace:<br><br>
    %URL_LISTA%<br><br><br>
    Aprovecha, tu prueba es válida hasta: %VENCIMENTO%<br><br><br>
    
    Gracias<br>
    PLAYGO.
    
    ");
