<?php

namespace App\core;

use Exception;
use PDO;

/**
 * Singleton to manage a PDO database connection.
 */
class Database
{
    /**
     * Singleton instance
     * 
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * PDO connection object
     * 
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Private constructor to prevent direct instantiation.
     * Sets up the PDO connection with the given config.
     * 
     * @param array $config ['host', 'name', 'user', 'pass', 'charset']
     * @throws Exception If connection fails and debug mode is on.
     */
    private function __construct(array $config)
    {
        $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            if (app_config('APP_DEBUG')) {
                throw $e; // rethrow for debugging
            } else {
                // Show generic error page in production
                render('error', [
                    'errorTitle' => 'Database Error',
                    'errorMessage' => 'There was an error connecting to the database. Please try again later.'
                ], null);
                exit();
            }
        }
    }

    /**
     * Get the singleton instance, creating it if needed.
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            $config = app_config('db');
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Get the PDO connection instance.
     * 
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
