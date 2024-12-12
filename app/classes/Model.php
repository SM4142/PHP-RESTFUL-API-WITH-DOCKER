<?php 

namespace app\classes;

use PDO;

class Model {

    protected static  $table ;

    protected string $id ;

    private static $db = null;

    protected array $nullAbleArray = [];

    protected array $notNullArray = [];

    protected array $hiddenArray = [];

    // execute things start

    protected static $query ="";
    protected static array $executeWhereArray = [];
    protected static array $executeOrderArray = [];
    protected static string $whereText = "";
    protected static string $orderText = "";

     // execute things ends

    protected static array $unknowColumns = [];

    // get and set things start
    protected array $attributes = [];
    // get and set things ends



    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function __get($name) {
        return $this->attributes[$name] ? $this->attributes[$name] : null;
    }

    public  function __construct() {

        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        SELF::$query = "SELECT * FROM " . static::$table;

    }

    private static  function closeConnection() {

        if(self::$db == null) {
            self::$db  = Database::connect();
        }

    }
    public static function checkColumns(string $column) {
        $columns = self::returnSchema();

        if(!isset($columns[$column])) {
            self::$unknowColumns[] = $column;
         }

    }
    public function save() {

        $schema = self::returnSchema();

        foreach ($schema as $key => $value) {

            $rule = $schema[$key]["nullable"] == false && 
            ! isset($this->attributes[$key] ) && 
            ! isset($schema[$key]["index"]) == "PRIMARY KEY" &&
            ! isset($schema[$key]["default"]);

            if( $rule ) {
                return ["message" => "Undefined column ". $key];
            }

        }

        $query = "INSERT INTO " ."`" .  static::$table .  "`"  ;
        $keys = " ( ";
        $values = " VALUES ( ";

        $last_key = array_keys($this->attributes)[count($this->attributes) - 1];

        foreach ($this->attributes as $key => $value) {

            if($key == $last_key) {

                $keys .= " `$key` ";

                $values .= " :$key ";

                if(gettype($value) == "string") {

                    $this->attributes[$key] = "'$value'";
    
                }

                break;
            }

            self::checkColumns($key);

            $keys .= " `$key` , ";

            if(gettype($value) == "string") {

                $this->attributes[$key] = "'$value'";

            }

            $values .= " :$key , ";

        }

        $keys .= ") ";

        $values .= ") ";

        $query .= $keys . $values;

        $stmt = self::$db->prepare($query);

        $stmt->execute($this->attributes);

        self::closeConnection();
        
        return   $this->attributes ;
        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }

        return self::returnSchema();

    }
    public static function fetchAll() {
       
        $stmt = self::$db->prepare(self::$query); 
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );
        self::closeConnection();
        return $fetched;
    }
    public static function where (string $column  , string $value ) {

        self::checkColumns($column);

        $placeholder = ":$column" . count(self::$executeWhereArray);

        if(count(self::$executeWhereArray) == 0) {

            self::$executeWhereArray[$placeholder] = $value;
            self::$whereText .= " WHERE "." $column = $placeholder ";

        }else{

            self::$executeWhereArray[$placeholder] = $value;
            self::$whereText .= " AND $column = $placeholder ";

        }

        return new static();

    }
    public static function whereIn (string $column  , array $value ) {
       
        self::checkColumns($column);

        $placeholders = implode(", ", array_map(function($index) use ($column) {
            return ":$column$index";
        }, array_keys($value)));


        if(count(self::$executeWhereArray) == 0) {

            self::$whereText .= " WHERE "." $column IN ($placeholders)";

        }else{
        
            self::$whereText .= " AND $column IN ($placeholders)";

        }

        foreach ($value as $index => $val) {
            self::$executeWhereArray["$column$index"] = $val;
        }

        return    new static();
    }
    public function orderBy(string $column = "id" , string $order = "ASC") {

        self::checkColumns($column);

        $orderArray = ["ASC" => "ASC", "DESC" => "DESC" , "asc" => "ASC" , "desc" => "DESC"]; 

        if(!isset($orderArray[$order])) {
            error_log("order must be ASC or DESC"); 
        }

        if(count(self::$executeOrderArray) == 0) {
            self::$orderText .= " ORDER BY ". " $column $order";
        }else{
            self::$orderText .= " , $column $order";
        }
       
        self::$executeOrderArray[$order] = $order;

        return new static();
    }
    public static function latest(){

        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }

        $where = self::$whereText ?  self::$whereText . " " : " ";

        $order = self::$orderText  ?  self::$orderText ." " : " ORDER BY id DESC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = array_merge(self::$executeWhereArray );

        $query = "SELECT * FROM " . static::$table . $where . $order ;

        $stmt = self::$db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );

        self::closeConnection();

        return $fetched;
    }
    public static function first(){

        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }
        
        $where = self::$whereText ?  self::$whereText . " " : " ";

        $order = self::$orderText  ?  self::$orderText ." " : " ORDER BY id ASC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = array_merge(self::$executeWhereArray );

        $query = "SELECT * FROM " . static::$table . $where . $order ;

        $stmt = self::$db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );

        self::closeConnection();

        return $fetched;
    }
    public static function get(){
        
        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }

        $where =  self::$whereText . " ";

        $order =  self::$orderText ." ";

        $executeArray = array_merge(self::$executeWhereArray );

        $query = self::$query . $where . $order ; 

        $stmt = self::$db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        self::closeConnection();

        return   $fetched   ;
    }

    public static function count() {

        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        $table_name = static::$table ;

        $stmt = self::$db->query("SELECT COUNT(*) as total From $table_name "); 

        $item_number = $stmt->fetch(PDO::FETCH_ASSOC )["total"];

        return ["total" => $item_number];
    }


    public static function pagination($limit , $page = 1) {

        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        if( ! is_int($limit) || $limit <= 0){ {
            return ["message" => "Pagination  number must be greater than 0"];
        }
        if( ! is_int($page) || $page < 0) {
            return ["message" => "Page number must be greater than 0"];
        }

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

    public static function returnSchema() {
        $directory = 'database/migrations';
        $files = glob($directory . "/*.php");
        foreach ($files as $file) {
            $fileName = basename($file, '.php');
            if (strpos($fileName, '-') !== false) {
                $parts = explode('-', $fileName);
                $className = end($parts); 
            } else {
                $className = $fileName;
            }
        
            if($className == static::$table ."Migration") {

                $newClassName = 'database\\migrations\\' . $className;

                require_once $file;
                if (!class_exists( $newClassName ) && !method_exists( $newClassName , 'up')) {

                    echo "Class $newClassName  not found or does not have an up method in $file\n";

                    return true; 

                }

                $itemClass = $newClassName::up(new Schema());

                $itemClass->returnSchema() ;

                return $itemClass->returnSchema();
            }
          
        }


    }

    public static function findById( int $value , string $column = "id" ) {
        if(self::$db == null) {

            self::$db  = Database::connect();

        }
        $table_name = static::$table ;
        $query = "SELECT * FROM  $table_name  WHERE $column = :id ";
        $stmt = self::$db->prepare( $query);
        $stmt->execute(['id' => $value]); 
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );
        self::closeConnection();
        return $fetched;
    }
  
    public static function delete($id) {

        $table_name = static::$table ;
        $stmt = self::$db->prepare("DELETE FROM  $table_name WHERE id = :id ");
        $stmt->execute(['id' => $id]);
        self::closeConnection();
        return true;
    }
    
    



}

?>