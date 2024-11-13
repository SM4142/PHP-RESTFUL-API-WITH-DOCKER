<?php 

namespace app\classes;

use PDO;
use PDOException;
class Database {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            $db_connection = $_ENV['DB_CONNECTION'];
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $db = $_ENV['DB_DATABASE'];
            $user = $_ENV['DB_USERNAME'];
            $pass = $_ENV['DB_PASSWORD'];
            $dsn = "";

            if ($db_connection === 'mysql') {
                $dsn = "mysql:host=$host;port=$port;dbname=$db";
            }

            if ($db_connection === 'pgsql') {
                $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            }

            try {
                self::$pdo = new PDO($dsn, $user, $pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

?>
