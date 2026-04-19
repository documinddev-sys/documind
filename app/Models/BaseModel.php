<?php

namespace App\Models;

use PDO;
use PDOStatement;

class BaseModel
{
    protected static $pdo;
    protected $table;

    public static function setPdo(PDO $pdo)
    {
        static::$pdo = $pdo;
    }

    protected static function getPdo(): PDO
    {
        if (static::$pdo === null) {
            throw new \Exception('Database connection not initialized');
        }
        return static::$pdo;
    }

    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = static::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1",
            [$id]
        );
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function insert(array $data): int
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $stmt = $this->query(
            "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)",
            array_values($data)
        );
        
        return (int) static::getPdo()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode('=?,', array_keys($data)) . '=?';
        $values = array_merge(array_values($data), [$id]);
        
        $stmt = $this->query(
            "UPDATE {$this->table} SET $set WHERE id = ?",
            $values
        );
        
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->query(
            "DELETE FROM {$this->table} WHERE id = ? LIMIT 1",
            [$id]
        );
        
        return $stmt->rowCount() > 0;
    }
}
