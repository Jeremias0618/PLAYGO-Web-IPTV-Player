<?php

define('IP','URL_SERVER'); 
define('TMDB_API_KEY', 'API_KEY_TMDB');
define('LANGUAGE', 'LANGUAGE_DEFAULT'); // es-ES (España), es-MX (Latino), en-US, etc.

$customChannelLogos = [];

define("NOME_IPTV", 'PLAYGO');

define("XTREAM_URL", 'https://cms-us.xtream-codes.com/');
define("XTREAM_USER", '');
define("XTREAM_PWD", '');
define("XTREAM_PLANO", '1');
define("ATIVA_BLOQUEIO_TESTE", '0');

define("SMTP_HOST", 'mail.playgo.pe');
define("SMTP_USER", '');
define("SMTP_SENHA", '');
define("SMTP_SEGURANCA", 'tls');
define("EMAIL_ASSUNTO", 'Bienvenido a PLAYGO - WebPlayer');
define("EMAIL_REVENDA", 'contato@playgo.pe');
define("NOME_REVENDA", 'PLAYGO');

define("SAGAS_ADMIN_ENABLED", false);
define("SAGAS_ADMIN_USER", 'user');

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