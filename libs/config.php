<?php

// URL DNS
define('IP','https://URL'); 

$template = 'blue'; 

define("WHATSAPP", ''); 

// Website 
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


define("ATIVAR_TESTE", '0');  // 1 = YES / 0 = NO
define("HORAS", '2');  // Trial Duration in hours
define("XTREAM_URL", 'https://cms-eu.xtream-codes.com/'); // URL del CMS de Xtream-Codes
define("XTREAM_USER", ''); // Usuario de Xtream-Codes
define("XTREAM_PWD", ''); // Contraseña de Xtream-Codes
define("XTREAM_PLANO", '1'); // Número del plan de Xtream-Codes; en caso de dudas contacta al soporte
define("ATIVA_BLOQUEIO_TESTE", '0'); // Si está activado (1) bloquea nuevos registros desde el computador del usuario por 30 días, evitando pruebas constantes

// Configuración del correo SMTP para envío de pruebas:
define("SMTP_HOST", 'mail.revenda.com');
define("SMTP_USER", '');
define("SMTP_SENHA", '');
define("SMTP_PORTA", '587');
define("SMTP_SEGURANCA", 'tls');
define("EMAIL_ASSUNTO", 'Bem Vindo ao ITV - WebPlayer');
define("EMAIL_REVENDA", 'contato@revenda.com');
define("NOME_REVENDA", 'WebPlayer');

// Adults categories
define("AVISO_ADULTOS_CANAL", 'XXX: ADULTOS'); // Should be the same as XC
define("AVISO_ADULTOS_FILME", 'FILMES: ADULTOS'); // Should be the same as XC
// Puedes ver este nombre cuando el sistema genere las categorías


// Atención al editar el cuerpo del correo
// No cambies las variables
//
// %VENCIMENTO%, %NOME%, %USUARIO%, %SENHA%
//
// Pide ayuda a un desarrollador si es necesario.


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
