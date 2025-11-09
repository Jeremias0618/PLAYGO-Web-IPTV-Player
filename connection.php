<?php

// Conexión a la base de datos
// Prueba de conexión a la base de datos, demo para testear integracion de datos de servicio de IPTV.

$host = '10.80.80.145';
$db   = 'playgo';
$user = 'yeremi';
$pass = 'admin123';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>