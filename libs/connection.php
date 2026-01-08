<?php

/**
 * Database Connection Handler
 * 
 * This file is part of a future database implementation that is currently not in use.
 * It provides a PDO connection handler for PostgreSQL database connections.
 * 
 * Future implementation:
 * - This will be used for storing user preferences, watch history, favorites, and playlists
 * - Currently, the application uses JSON file-based storage in the storage/users/ directory
 * - When the database implementation is activated, this connection will replace the file-based system
 * 
 * Configuration:
 * - Database credentials should be moved to libs/config.php when this is implemented
 * - Connection parameters are currently hardcoded for development purposes
 * 
 * @package libs
 * @version 1.0.0
 * @since Future implementation
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', '192.168.1.100');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'playgo');
}

if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', 'root');
}

if (!defined('DB_PORT')) {
    define('DB_PORT', '5432');
}

if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8');
}

/**
 * Get database connection instance
 * 
 * Creates and returns a PDO instance for PostgreSQL database connection.
 * Uses singleton pattern to ensure only one connection instance exists.
 * 
 * @return PDO|null Returns PDO instance on success, null on failure
 * @throws PDOException If connection fails
 */
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            
            if (defined('DEBUG') && DEBUG === true) {
                throw new PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode());
            }
            
            return null;
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * 
 * Verifies if the database connection is working properly.
 * 
 * @return bool Returns true if connection is successful, false otherwise
 */
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        if ($pdo === null) {
            return false;
        }
        
        $pdo->query('SELECT 1');
        return true;
        
    } catch (PDOException $e) {
        error_log('Database connection test failed: ' . $e->getMessage());
        return false;
    }
}

