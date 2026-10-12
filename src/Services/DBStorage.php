<?php
namespace App\Services;

use PDO;
use App\Config\Config;

class DBStorage 
{
    protected $connection;

    public function __construct() {
        // устанавливаем соединение
        try {
            $this->connection = new PDO(
                Config::MYSQL_DNS,
                Config::MYSQL_USER,
                Config::MYSQL_PASSWORD
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            // Если база не существует, попробуем создать её автоматически (XAMPP MySQL)
            if (strpos($e->getMessage(), 'Unknown database') !== false || strpos($e->getCode(), '1049') !== false) {
                // Подключаемся к серверу без указания БД
                $rootDsn = 'mysql:host=localhost';
                $rootConn = new PDO($rootDsn, Config::MYSQL_USER, Config::MYSQL_PASSWORD);
                $rootConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $rootConn->exec('CREATE DATABASE IF NOT EXISTS is231 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                // Повторно подключаемся к нужной базе
                $this->connection = new PDO(
                    Config::MYSQL_DNS,
                    Config::MYSQL_USER,
                    Config::MYSQL_PASSWORD
                );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                // Иная ошибка подключения
                throw $e;
            }
        }
    }
}
