<?php 
namespace app\classes;

use app\classes\Schema;

class Migration{
    
    protected static $items = [];
    protected static $db = null;
    private static  function closeConnection() {
        self::$db = null;
    }
    public function up(Schema $item) {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }
        self::$items[] = $item;
        $createTableSql ="CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name  VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        static::$db->exec($createTableSql);
        self::closeConnection();
    }
}
?>