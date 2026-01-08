<?php

if (!defined('IP')) {
    require_once(__DIR__ . '/../../libs/lib.php');
}

if (!function_exists('createTrialAccount')) {
    require_once(__DIR__ . '/../services/XtreamApi.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['op']) && $_REQUEST['op'] === 'criarteste') {
    $nome = trim($_REQUEST['nome'] ?? '');
    $email = trim($_REQUEST['email'] ?? '');
    $whatsapp = $_REQUEST['wa'] ?? '';
    
    if (empty($nome) || empty($email)) {
        header("Location: login.php?sess=erro");
        exit;
    }
    
    $result = createTrialAccount($nome, $email, $whatsapp);
    
    if (!$result) {
        header("Location: login.php?sess=erro");
        exit;
    }
}

