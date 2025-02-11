<?php 

namespace app\classes;

use PDO;

class Model {

    protected static  $table ;

    protected static string $id ;

    private static $db = null;

    protected static array $nullAbleArray = [];

    protected static array $notNullArray = [];

    protected static array $hiddenArray = [];

    // execute things start

    protected static $query ="";

    protected static array $executeWhereArray = [];

    protected static array $executeOrWhereArray = [];

    protected static array $executeOrderArray = [];

    protected static array $executeUpdateArray = [];

    protected static string $whereText = "";

    protected static string $orWhereText = "";

    protected static string $orderText = "";

     // execute things ends

    protected static array $unknowColumns = [];

    // get and set things start
    protected static array $attributes = [];
    // get and set things ends



    public function __set($name, $value) {
        static::$attributes[$name] = $value;
    }

    public function __get($name) {
        return static::$attributes[$name] ?? null;
    }

    public  function __construct() {

        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        if(count(static::$hiddenArray) > 0 && self::$query == "") {
                
            $hidden_columns = "";

            $schema = self::returnSchema() ;

            $lastKey = array_key_last($schema);
            
            foreach ( $schema as $colum_key => $column) {
                
                if (! in_array($colum_key, static::$hiddenArray))  {

                    if($colum_key == $lastKey) {
                        
                        $hidden_columns .= "`$colum_key` "; 
                        continue;

                    }

                    $hidden_columns .= "`$colum_key`, "; 

                }
            }

            $query = sprintf("SELECT $hidden_columns FROM %s", static::$table);
            
            self::$query = $query ;
            
        }elseif(self::$query == "") {

            self::$query = "SELECT * FROM " . static::$table;

        }

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
            ! isset(static::$attributes[$key] ) && 
            ! isset($schema[$key]["index"]) == "PRIMARY KEY" &&
            ! isset($schema[$key]["default"]);

            if( $rule ) {
                return ["message" => "Undefined column ". $key];
            }

        }

        $query = "INSERT INTO " ."`" .  static::$table .  "`"  ;
        $keys = " ( ";
        $values = " VALUES ( ";

        $last_key = array_keys(static::$attributes)[count(static::$attributes) - 1];

        foreach (static::$attributes as $key => $value) {

            if($key == $last_key) {

                $keys .= " `$key` ";

                $values .= " :$key ";

                break;

            }

            self::checkColumns($key);

            $keys .= " `$key` , ";

            static::$attributes[$key] = "$value";

            $values .= " :$key , ";

        }

        $keys .= ") ";

        $values .= ") ";

        $query .= $keys . $values;

        $stmt = self::$db->prepare($query);

        $stmt->execute(static::$attributes);

        self::closeConnection();
        
        return   static::$attributes ;
        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }

        return self::returnSchema();

    }


    public static function all() {

        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        if(count(static::$hiddenArray) > 0 && self::$query == "") {
                
            $hidden_columns = "";

            $schema = self::returnSchema() ;

            $lastKey = array_key_last($schema);
            
            foreach ( $schema as $colum_key => $column) {
                
                if (! in_array($colum_key, static::$hiddenArray))  {

                    if($colum_key == $lastKey) {
                        
                        $hidden_columns .= "`$colum_key` "; 
                        continue;

                    }

                    $hidden_columns .= "`$colum_key`, "; 

                }
            }

            $query = sprintf("SELECT $hidden_columns FROM %s", static::$table);
            
            self::$query = $query ;
            
        }elseif(self::$query == "") {

            self::$query = "SELECT * FROM " . static::$table;

        }
        
        $query =  self::$query ;

        $stmt = self::$db->prepare($query); 

        $stmt->execute();

        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        self::closeConnection();

        return  $fetched;
        
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
    public static function orWhere (string $column  , string $value ) {

        self::checkColumns($column);

        $placeholder = ":or$column" . count(self::$executeOrWhereArray);

        if(count(self::$executeOrWhereArray) == 0) {

            self::$executeOrWhereArray[$placeholder] = $value;

            self::$orWhereText .= " OR "." $column = $placeholder ";

        }else{

            self::$executeOrWhereArray[$placeholder] = $value;

            self::$orWhereText .= " AND $column = $placeholder ";

        }

        return new static();

    }

    public static function whereIn (string $column  , array $value ) {
       
        self::checkColumns($column);

        $placeholders = implode(", ", array_map(function($index) use ($column) {

            $text = ":in$column$index" ;

            return $text ;

        }, array_keys($value)));


        if(count(self::$executeWhereArray) == 0) {

            self::$whereText .= " WHERE "." $column IN ($placeholders)";

        }else{
        
            self::$whereText .= " AND $column IN ($placeholders)";

        }

        foreach ($value as $index => $val) {

            self::$executeWhereArray[":in$column$index"] = $val;

        }

        return  new static();
    }

    public static function orWhereIn (string $column  , array $value ) {
       
        self::checkColumns($column);

        $placeholders = implode(", ", array_map(function($index) use ($column) {

            $text = ":inOr$column$index" ;

            return $text ;

        }, array_keys($value)));


        if(count(self::$executeOrWhereArray) == 0) {

            self::$orWhereText .= " OR "." $column IN ($placeholders)";

        }else{
        
            self::$orWhereText .= " AND $column IN ($placeholders)";

        }

        foreach ($value as $index => $val) {

            self::$executeOrWhereArray[":inOr$column$index"] = $val;

        }

        return  new static();
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

        $where =  self::$whereText . " ";

        $orWhereText = self::$orWhereText . " " ;

        $order = self::$orderText  ?  self::$orderText ." " : " ORDER BY id DESC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = array_merge(self::$executeWhereArray , self::$executeOrWhereArray );

        $query = "SELECT * FROM " . static::$table . $where .   $orWhereText . $order ;

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
        
        $where =  self::$whereText . " ";

        $orWhereText = self::$orWhereText . " " ;

        $order = self::$orderText  ?  self::$orderText ." " : " ORDER BY id ASC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = array_merge(self::$executeWhereArray , self::$executeOrWhereArray);

        $query = "SELECT * FROM " . static::$table . $where .   $orWhereText. $order ;

        $stmt = self::$db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );

        self::closeConnection();

        return $fetched;
    }
    public  function update(array $values){

        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        $textChange = "";

        $keyCheck =array_keys($values)[count($values) - 1];

        foreach ($values as $key => $value) {

            self::checkColumns( $key );

            self::$executeUpdateArray [":update$key"] = $value;
            
            if($keyCheck  == $key) {

                $textChange .= " $key = :update$key  "; 

                continue ;
            }

            $textChange .= " $key = :update$key , "; 

        }

        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }

        $where =  self::$whereText . " ";

        $orWhereText = self::$orWhereText . " " ;

        $executeArray = array_merge(self::$executeWhereArray , self::$executeOrWhereArray , self::$executeUpdateArray);

        $query = "UPDATE " . static::$table ." SET ". $textChange  . $where .   $orWhereText;

        $stmt = self::$db->prepare($query);

        $stmt->execute($executeArray);

        self::closeConnection();

        return true;
    }
    public static function get(){
        
        if(count(self::$unknowColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknowColumns)];
        }

        $where =  self::$whereText . " ";

        $order =  self::$orderText ." ";

        $orWhereText = self::$orWhereText . " " ;

        $executeArray = array_merge(self::$executeWhereArray  , self::$executeOrWhereArray);

        $query = self::$query . $where . $orWhereText . $order ; 

        $stmt = self::$db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        self::closeConnection();

        return   $fetched   ;
    }

    public static function count() : int  {

        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        $table_name = static::$table ;

        $stmt = self::$db->query("SELECT COUNT(*) as total From $table_name "); 

        $item_number = $stmt->fetch(PDO::FETCH_ASSOC )["total"];

        return  $item_number;
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

        if(count(static::$hiddenArray) > 0 && self::$query == "") {
                
            $hidden_columns = "";

            $schema = self::returnSchema() ;

            $lastKey = array_key_last($schema);
            
            foreach ( $schema as $colum_key => $column) {
                
                if (! in_array($colum_key, static::$hiddenArray))  {

                    if($colum_key == $lastKey) {
                        
                        $hidden_columns .= "`$colum_key` "; 
                        continue;

                    }

                    $hidden_columns .= "`$colum_key`, "; 

                }
            }

            $query = sprintf("SELECT $hidden_columns FROM %s", static::$table);
            
            self::$query = $query ;
            
        }elseif(self::$query == "") {

            self::$query = "SELECT * FROM " . static::$table;

        }

        $page = $page - 1;

        $item_number = self::count();

        $total_page = ceil($item_number / $limit);

        $start_point = $limit * $page;

        $where =  self::$whereText . " ";

        $order =  self::$orderText ." ";

        $orWhereText = self::$orWhereText . " " ;

        $executeArray = array_merge(self::$executeWhereArray  , self::$executeOrWhereArray);

        $query = self::$query . $where . $orWhereText . $order . " LIMIT :limit OFFSET :start_point ";

        $stmt = self::$db->prepare($query);

        $stmt->bindValue(':start_point', (int) $start_point, PDO::PARAM_INT);

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        
        foreach ($executeArray as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        self::closeConnection();

        $pagination_data = [
            "total_page" => $total_page,
            "current_page" => $page + 1,
            "total_item" => $item_number,
            "data" =>  $fetched,
        ];
        
        return   $pagination_data ;
    } 

    public static function returnSchema() {
        
        $directory = 'database/migrations';

        $files = glob($directory . "/*.php");

        foreach ($files as $file) {

            $fileName = basename($file, '.php');

            if (strpos($fileName, '-') !== false) {

                $parts = explode('-', $fileName);

                $className = end($parts); 

            } 
            else {

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

    public static function find( int $value  ) {
        
        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        if(count(static::$hiddenArray) > 0 && self::$query == "") {
                
            $hidden_columns = "";

            $schema = self::returnSchema() ;

            $lastKey = array_key_last($schema);
            
            foreach ( $schema as $colum_key => $column) {
                
                if (! in_array($colum_key, static::$hiddenArray))  {

                    if($colum_key == $lastKey) {
                        
                        $hidden_columns .= "`$colum_key` "; 
                        continue;

                    }

                    $hidden_columns .= "`$colum_key`, "; 

                }
            }

            $query = sprintf("SELECT $hidden_columns FROM %s", static::$table);
            
            self::$query = $query ;
            
        }else{

            self::$query = "SELECT * FROM " . static::$table;

        }

        $column = static::$id ? static::$id : "id";

        $query = self::$query .  " WHERE $column = :id ";

        $stmt = self::$db->prepare( $query);

        $stmt->execute([":$column" => $value]); 

        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );

        self::closeConnection();

        return   $fetched ;
    }
  
    public static function delete() {

        $table_name = static::$table ;

        $where =  self::$whereText . " ";

        $orWhereText = self::$orWhereText . " " ;

        $executeArray = array_merge(self::$executeWhereArray , self::$executeOrWhereArray);

        $query = "DELETE FROM  $table_name $where  $orWhereText ";

        $stmt = self::$db->prepare( $query);

        $stmt->execute($executeArray);

        self::closeConnection();

        return  true;
    }

    public static function deleteById($value) {

        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        $column = static::$id ? static::$id : "id";

        $table_name = static::$table ;

        $query = "DELETE FROM  $table_name  WHERE $column = :id ";

        $stmt = self::$db->prepare( $query);

        $stmt->execute([":$column" => $value]); 

        self::closeConnection();

        return    true;
    }
    
}

?>