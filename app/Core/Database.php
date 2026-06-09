<?php

declare(strict_types=1);

namespace App\Core;

use mysqli;
use mysqli_stmt;

class Database
{
    private static ?Database $instance = null;
    private mysqli $connection;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );
        $this->connection->set_charset($config['charset']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function prepare(string $sql): mysqli_stmt
    {
        return $this->connection->prepare($sql);
    }

    public function query(string $sql): \mysqli_result|bool
    {
        return $this->connection->query($sql);
    }

    public function lastInsertId(): int
    {
        return (int) $this->connection->insert_id;
    }

    public function escape(string $value): string
    {
        return $this->connection->real_escape_string($value);
    }

    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollback();
    }
}
