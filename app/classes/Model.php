<?php 

namespace app\classes;

use PDO;

class Model {

    protected static  $table ;
    // table name

    protected static string $id ;
    // table primary id

    private static $schema = [];

    private static $db = null;
    // db connection

    protected static array $nullableColumns = [];
    // don't need to create

    protected static array $nonNullableColumns = [];
    // need to create

    protected static array $hiddenArray = [];
    // hidden columns

    // execute things start

    protected static $query ="";    
    // final query

    protected static array $executeWhereArray = [];
    // holding where values and keys to bind

    protected static array $executeOrderArray = [];
    // holding order values and keys to bind

    protected static array $executeUpdateArray = [];

    protected static string $whereText = "";
    // query for where

    protected static string $orderText = "";
     // query for order

     // execute things ends

    protected static array $unknownColumns = [];

    // get and set things start

    protected static array $attributes = [];

    // get and set things ends

    protected static array $operatorsWhere = [
        '=',
        '!=',
        '<>',
        '>',
        '<',
        '>=',
        '<=',
        'LIKE',
        'NOT LIKE',
        'IN',
        'NOT IN',
        'BETWEEN',
        'NOT BETWEEN',
        'IS NULL',
        'IS NOT NULL',
    ];

    // contain operators keys

    private static $isChaining = false ;
    // method calling


    public function __set($name, $value) {
        static::$attributes[$name] = $value;
    }

    public function __get($name) {
        return static::$attributes[$name] ?? null;
    }

    public  function __construct() {

        if(self::$db == null) {

            self::$db  = Database::connect();
            // connect the db

        }
        // check if the db is connected

        static::checkHiddenColumns();
        // if there are hidden columns they are not shown
    }

    private static function checkHiddenColumns (){

        if(count(static::$hiddenArray) > 0 && self::$query == "") {
                
        $hidden_columns = "";

        if( count(static::$schema) < 1){

            static::$schema= self::returnSchema();
            // taking schema

        }

        $lastKey = array_key_last(static::$schema);
            
        foreach ( static::$schema as $colum_key => $column) {
                
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

        if(self::$db != null) {

            self::$db  = null;
            // making the db null
        }   

    }
    //close the connection
    

    public static function checkColumns(string $column) {

        if( count(static::$schema) < 1){

            static::$schema= self::returnSchema();
            // taking schema

        }
        //check if the schema exist


        if(!isset(static::$schema[$column])) {

            self::$unknownColumns[] = $column;
            // add columns to unknownColumns if columns is not exist in schema 

        }
        //check if the columns is exist in schema

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

        if(count(self::$unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknownColumns)];
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

    private static function generatePlaceHolders( $values , $column  ) {

        $placeholders = [];

        foreach ($values as $index => $val) {

            $placeholder = ":$column" . count(self::$executeWhereArray)  ;
            
            self::$executeWhereArray[$placeholder] = $val;

            $placeholders[] = $placeholder;
        }

        return $placeholders;
        
    }

    private static function checkWhere( $numArgs , $column, $operator, $value ,  $whereTypeOr = false) {

        if ($numArgs === 1 && is_callable($column)) {

            static::$whereText =  static::$whereText . ( count(self::$executeWhereArray) > 0 ? " AND ( "   : " ( ");

            static::$isChaining = true;
      
            $column(new static());

            static::$whereText =  static::$whereText . " ) ";

            static::$isChaining = false;
          
            return new static();
            
        }
        
        if ($numArgs === 2) {

            $value = $operator;

            $operator = "="; 
        }

        if($numArgs === 3 &&  in_array(strtoupper($operator), static::$operatorsWhere) === false   ){
            
            Response::Json(["message" => "Undefined operator ". $operator]);

            exit;
        }

        $placeholder = ":$column" . count(self::$executeWhereArray);

        self::checkColumns($column);

        $countExecuteWhereArray = count(self::$executeWhereArray) > 0 ? ($whereTypeOr == false ? (static::$isChaining == false ?  "AND" : " ") : "OR") : "";

    

        if( strtoupper($operator) == "BETWEEN" || strtoupper($operator) == "NOT BETWEEN") {
            
            if(! is_array($value) || count($value) != 2) {

                Response::Json(["message" => "".$operator." must be an array with 2 values!"]);
                exit;
            }

            $generated = self::generatePlaceHolders($value, $column);
        
            self::$whereText .= " $countExecuteWhereArray " . $column  . " ".$operator ." " . $generated[0]   . " AND " . $generated[1]  ;

            return new static();

        }

        if( strtoupper($operator) == "IN" || strtoupper($operator) == "NOT IN") {
            
            if(! is_array($value) ) {

                Response::Json(["message" => "$operator must be an array with values!"]);
                exit;
            }
        

            self::whereInHandle($column, $value , $whereTypeOr , strtoupper($operator) );

            return new static();

        }

        self::$executeWhereArray[$placeholder] = $value;
     
        self::$whereText .= " $countExecuteWhereArray " . $column  . " $operator ". $placeholder ;

        static::$isChaining = false;

        return new static();
        
    }

    public static function where ( $column  ,  $operator =null, $value = null ) {

        $numArgs = func_num_args();

        self::checkWhere( $numArgs , $column, $operator, $value );

      return new static();

    }

    public static function orWhere ( $column  ,  $operator =null, $value = null ) {

        $numArgs = func_num_args();

        self::checkWhere( $numArgs , $column, $operator, $value , true);

        return new static();

    }

    private static function whereInHandle ( $column  , array $value  ,  $whereTypeOr = false , $operator = "IN") {

        self::checkColumns($column);

        $generated = self::generatePlaceHolders($value, $column);

        $placeholders = "(" . implode(", ", $generated) . ")";

        $executeWhereArray =  count(self::$executeWhereArray) > 0 ? ($whereTypeOr == false ? (static::$isChaining == false ?  "AND" : " ") : "OR") : "";

        static::$isChaining = false;


        self::$whereText .= " $executeWhereArray $column $operator $placeholders";
        
    }

    public static function whereIn (string $column  , array $value ) {
       
        self::whereInHandle($column, $value);

        return  new static();

    }


    public static function orWhereIn (string $column  , array $value ) {

        self::whereInHandle($column, $value , true);

        return  new static();

    }

    public static function whereNotIn (string $column  , array $value ) {

        self::whereInHandle($column, $value, false, "NOT IN");

        return  new static();
    }
    
    public static function orWhereNotIn (string $column  , array $value ) {

        self::whereInHandle($column, $value , true, "NOT IN");

        return  new static();

    }

    public static function orderBy(string $column = "id" , string $order = "ASC") {

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

        if(count(self::$unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknownColumns)];
        }

        static::checkHiddenColumns();
        // if there are hidden columns they are not shown

        $where =  self::$whereText . " ";

        $order = self::$orderText  ?  self::$orderText ." " : " ORDER BY id DESC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = self::$executeWhereArray ;

        $isWhere = count(self::$executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = self::$query . $isWhere . $where .$order . " LIMIT 1 ";

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

        if(count(self::$unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknownColumns)];
        }

        static::checkHiddenColumns();
        // if there are hidden columns they are not shown
        
        $where =  self::$whereText . " ";" " ;

        $order = self::$orderText  ?  self::$orderText ." " : " ORDER BY id ASC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = self::$executeWhereArray ;

        $isWhere = count(self::$executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = self::$query . $isWhere . $where .$order . " LIMIT 1 ";

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

        if(count(self::$unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknownColumns)];
        }

        $where =  self::$whereText . " ";

        $executeArray = self::$executeWhereArray ;

        $query = "UPDATE " . static::$table ." SET ". $textChange  . $where ;

        $stmt = self::$db->prepare($query);

        $stmt->execute($executeArray);

        self::closeConnection();

        return true;
    }
    public static function get(){
        
        if(count(self::$unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", self::$unknownColumns)];
        }

        $where =  self::$whereText . " ";

        $order =  self::$orderText ." ";
     
        $executeArray = self::$executeWhereArray ;
       
        $isWhere = count(self::$executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = self::$query . $isWhere . $where .$order ;

        $stmt = self::$db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        self::closeConnection();

        return  $fetched; 
        
     
        return  ["query" => $query , "executeArray" => $executeArray];
     

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

        $executeArray = self::$executeWhereArray ;

        $isWhere = count(self::$executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = self::$query . $isWhere . $where .$order . " LIMIT :limit OFFSET :start_point ";

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

        $executeArray = self::$executeWhereArray ;

        $query = "DELETE FROM  $table_name $where  ";

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