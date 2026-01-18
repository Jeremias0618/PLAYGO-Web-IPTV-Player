<?php

/**
 * XtreamApi Endpoint
 * 
 * Este endpoint está reservado para futuras funcionalidades relacionadas con la API de Xtream UI.
 * La funcionalidad de creación de cuentas de prueba ha sido removida ya que no se utiliza.
 */

if (!defined('IP')) {
    require_once(__DIR__ . '/../../libs/lib.php');
}

// La funcionalidad de creación de cuentas de prueba ha sido deshabilitada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['op']) && $_REQUEST['op'] === 'criarteste') {
    header("Location: login.php?sess=erro");
    exit;
}

