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
        $table_name = static::$table ;
        $stmt = self::$db->query("SELECT * FROM  $table_name "); 
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );
        self::closeConnection();
        return $fetched;
    }
    public static function count() {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }
        $table_name = static::$table ;
        $stmt = self::$db->query("SELECT COUNT(*) as total From $table_name "); 
        $item_number = $stmt->fetch(PDO::FETCH_ASSOC )["total"];
        return $item_number;
    }

    public static function pagination($limit , $page = 1) {
        if( ! is_int($limit) || $limit <= 0){ {
            return ["message" => "Pagination  number must be greater than 0"];
        }
        if( ! is_int($page) || $page < 0) {
            return ["message" => "Page number must be greater than 0"];
        }

        }
        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        $page = $page - 1;
        
        $table_name = static::$table ;

        $item_number = self::count();

        $total_page = ceil($item_number / $limit);

        $start_point = $limit * $page;
        
        $stmt = self::$db->prepare("SELECT * FROM  $table_name LIMIT  :start_point ,  :limit ");
        $stmt->bindValue(':start_point', $start_point, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );
        self::closeConnection();

        $pagination_data = [
            "total_page" => $total_page,
            "current_page" => $page,
            "total_item" => $item_number,
            "data" =>  $fetched,
        ];
        
        return  $pagination_data ;
    } 

    public static function find($id) {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }
        $table_name = static::$table ;
        $stmt = self::$db->prepare("SELECT * FROM  $table_name  WHERE id = :id ");
        $stmt->execute(['id' => $id]); 
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );
        self::closeConnection();
        return $fetched;
    }

    public static function delete($id) {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }
        $table_name = static::$table ;
        $stmt = self::$db->prepare("DELETE FROM  $table_name WHERE id = :id ");
        $stmt->execute(['id' => $id]);
        self::closeConnection();
        return true;
    }
    


}

?>