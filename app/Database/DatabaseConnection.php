<?php

declare(strict_types=1);

namespace App\Database;

class DatabaseConnection
{
    public static function tryConnect(): ?\PDO
    {
        $host = getenv('MYSQL_HOST');
        $database = getenv('MYSQL_DB');
        $user = getenv('MYSQL_USER');
        $password = getenv('MYSQL_PASS');

        $connectionString = "mysql:host=$host;dbname=$database;charset=UTF8";

        $pdo = new \PDO(
            $connectionString,
            $user,
            $password,
            [
                \PDO::MYSQL_ATTR_LOCAL_INFILE => true
            ]
        );

        return $pdo;
    }
}
