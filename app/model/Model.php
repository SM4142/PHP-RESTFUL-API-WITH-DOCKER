<?php 

namespace app\model;

use app\classes\Database;
use PDO;

class Model {
    protected  static $table ;
    private static $db = null;

    private static  function closeConnection() {
        self::$db = null;
    }
    public static function fetchAll() {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }
        $tabla_name = static::$table ;
        $stmt = self::$db->query("SELECT * FROM  $tabla_name "); 
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );
        self::closeConnection();
        return $fetched;
    }


}

?>