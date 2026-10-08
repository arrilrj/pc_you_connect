<?php
date_default_timezone_set('Asia/Manila');

class Database {
    private const HOST = 'localhost';
    private const NAME = 'pc_you_connect';
    private const USER = 'root';
    private const PASS = '';
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                'mysql:host=' . self::HOST . ';dbname=' . self::NAME . ';charset=utf8mb4',
                self::USER, self::PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }
        return self::$pdo;
    }
}
function db(): PDO { return Database::conn(); }