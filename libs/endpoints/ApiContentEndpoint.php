<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    if (!defined('IP')) {
        require_once(__DIR__ . '/../lib.php');
    }
    
    if (!function_exists('getMovies')) {
        require_once(__DIR__ . '/../services/content.php');
    }
    
    if (!function_exists('handleRefreshRequest')) {
        require_once(__DIR__ . '/../controllers/ContentRefreshController.php');
    }
    
    if (!function_exists('handleRecentContentRequest')) {
        require_once(__DIR__ . '/../controllers/ContentRecentController.php');
    }
    
    if (!function_exists('handlePremieresRequest')) {
        require_once(__DIR__ . '/../controllers/ContentPremieresController.php');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'refresh') {
        handleRefreshRequest();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'recent') {
        handleRecentContentRequest();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'premieres') {
        handlePremieresRequest();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(), 
        'file' => basename($e->getFile()), 
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(), 
        'file' => basename($e->getFile()), 
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}

