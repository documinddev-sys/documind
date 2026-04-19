<?php
// Version 2 - Fixed getInstance optional parameter
namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static $instance;
    private $pdo;

    private function __construct(array $config)
    {
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
            
            $this->pdo = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(?array $config = null): PDO
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new \Exception("Database config required for first getInstance() call");
            }
            self::$instance = new self($config);
        }
        return self::$instance->pdo;
    }
}
