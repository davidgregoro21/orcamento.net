<?php
namespace App\Core;
use PDO;
use PDOException;

final class Database {
    private static ?PDO $pdo = null;
    public static function driver(): string { return (string)((cfg('db') ?? [])['driver'] ?? 'mysql'); }
    public static function connection(): PDO {
        if (self::$pdo) return self::$pdo;
        $db = cfg('db');
        $op = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        try {
            if (self::driver() === 'sqlite') {
                self::$pdo = new PDO('sqlite:'.$db['sqlite_path'], null, null, $op);
                self::$pdo->exec('PRAGMA foreign_keys = ON');
            } else {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
                self::$pdo = new PDO($dsn, $db['user'], $db['pass'], $op);
                // Mantém NOW() do MySQL no mesmo fuso do PHP (America/Sao_Paulo por padrão).
                self::$pdo->exec("SET time_zone = '".date('P')."'");
            }
            return self::$pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Falha na conexão com o banco de dados. Verifique se o MySQL está ligado e os dados em config/config.php.');
        }
    }
}
