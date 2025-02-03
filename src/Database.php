<?php

class Database
{
    private static $pdo = null;

    public static function get()
    {
        if (self::$pdo === null) {
            if (DB_DRIVER === 'sqlite') {
                $dsn = 'sqlite:' . DB_NAME;
                self::$pdo = new PDO($dsn);
            } else {
                $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS);
            }
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

    public static function fetchAll($sql, $params = [])
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetchOne($sql, $params = [])
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function execute($sql, $params = [])
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function insert($sql, $params = [])
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return (int)self::get()->lastInsertId();
    }
}
