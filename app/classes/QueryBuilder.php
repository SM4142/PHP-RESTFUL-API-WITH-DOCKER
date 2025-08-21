<?php

use app\classes\Database;
use app\classes\Response;
use app\classes\Schema;

class QueryBuilder {

    private string $table ;

    private string $id ;
    // table primary id

    private  $schema = [];

    private  $db = null;
    // db connection

    private array $nullableColumns = [];
    // don't need to create

    private array $nonNullableColumns = [];
    // need to create

    private array $hiddenArray = [];
    // hidden columns

    // execute things start

    private $query ="";    
    // final query

    private array $executeWhereArray = [];
    // holding where values and keys to bind

    private array $executeOrderArray = [];
    // holding order values and keys to bind

    private array $executeUpdateArray = [];

    private string $whereText = "";
    // query for where

    private string $orderText = "";
     // query for order

     // execute things ends

    private array $unknownColumns = [];

    // get and set things start

    private array $attributes = [];

    // get and set things ends

    private array $operatorsWhere = [
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

    private $isChaining = false ;
    // method calling


    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }

    public function __construct(string $table ,string $id) {

        $this->table = $table ;
        $this->id = $id ;

        if($this->db  == null) {

            $this->db  = Database::connect();
            // connect the db

        }

    }

    private function checkHiddenColumns (){

        if(count($this->hiddenArray) > 0 && $this->query == "") {
                
        $hidden_columns = "";

        if( count($this->schema) < 1){

            $this->schema= $this->returnSchema();
            // taking schema

        }

        $lastKey = array_key_last($this->schema);
            
        foreach ( $this->schema as $colum_key => $column) {
                
            if (! in_array($colum_key, $this->hiddenArray))  {

                if($colum_key == $lastKey) {
                        
                    $hidden_columns .= "`$colum_key` "; 
                    continue;

                }

                $hidden_columns .= "`$colum_key`, "; 

            }
        }

        $query = "SELECT {$hidden_columns} FROM  {$this->table}" ;
       
        $this->query = $query ;
    
            
        }elseif($this->query == "") {

            $this->query = "SELECT * FROM " . $this->table;

        }
    }

    private function returnSchema() {
        
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
        
            if($className == $this->table ."Migration") {

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

    private function closeConnection() {

        if($this->db != null) {

            $this->db  = null;
            // making the db null
        }   

    }
    //close the connection

    private function checkColumns(string $column) {

        if( count($this->schema) < 1){

            $this->schema= $this->returnSchema();
            // taking schema

        }
        //check if the schema exist


        if(!isset($this->schema[$column])) {

            $this->unknownColumns[] = $column;
            // add columns to unknownColumns if columns is not exist in schema 

        }
        //check if the columns is exist in schema

    }

    private function generatePlaceHolders( $values , $column  ) {

        $placeholders = [];

        foreach ($values as $index => $val) {

            $placeholder = ":$column" . count($this->executeWhereArray)  ;
            
            $this->executeWhereArray[$placeholder] = $val;

            $placeholders[] = $placeholder;
        }

        return $placeholders;
        
    }

    private function checkWhere( $numArgs , $column, $operator, $value ,  $whereTypeOr = false) {

        if ($numArgs === 1 && is_callable($column)) {

            $this->whereText =  $this->whereText . ( count($this->executeWhereArray) > 0 ? " AND ( "   : " ( ");

            $this->isChaining = true;
      
            $column($this);

            $this->whereText =  $this->whereText . " ) ";

            $this->isChaining = false;
          
            return $this;
            
        }
        
        if ($numArgs === 2) {

            $value = $operator;

            $operator = "="; 
        }

        if($numArgs === 3 &&  in_array(strtoupper($operator), $this->operatorsWhere) === false   ){
            
            Response::Json(["message" => "Undefined operator ". $operator]);

            exit;
        }

        $placeholder = ":$column" . count($this->executeWhereArray);

        $this->checkColumns($column);

        $countExecuteWhereArray = count($this->executeWhereArray) > 0 ? ($whereTypeOr == false ? ($this->isChaining == false ?  "AND" : " ") : "OR") : "";

    

        if( strtoupper($operator) == "BETWEEN" || strtoupper($operator) == "NOT BETWEEN") {
            
            if(! is_array($value) || count($value) != 2) {

                Response::Json(["message" => "".$operator." must be an array with 2 values!"]);
                exit;
            }

            $generated = $this->generatePlaceHolders($value, $column);
        
            $this->whereText .= " $countExecuteWhereArray " . $column  . " ".$operator ." " . $generated[0]   . " AND " . $generated[1]  ;

            return $this;

        }

        if( strtoupper($operator) == "IN" || strtoupper($operator) == "NOT IN") {
            
            if(! is_array($value) ) {

                Response::Json(["message" => "$operator must be an array with values!"]);
                exit;
            }
        

            $this->whereInHandle($column, $value , $whereTypeOr , strtoupper($operator) );

            return $this;

        }

        $this->executeWhereArray[$placeholder] = $value;
     
        $this->whereText .= " $countExecuteWhereArray " . $column  . " $operator ". $placeholder ;

        $this->isChaining = false;

        return $this;
        
    }

    public function where ( $column  ,  $operator =null, $value = null ) {

        $numArgs = func_num_args();

        $this->checkWhere($numArgs , $column , $operator , $value );

        return $this ;

    }

    public function orWhere ($column , $operator =null , $value = null ) {

        $numArgs = func_num_args();

        $this->checkWhere( $numArgs , $column, $operator, $value , true);

        return $this;

    }

    private  function whereInHandle ( $column  , array $value  ,  $whereTypeOr = false , $operator = "IN") {

        $this->checkColumns($column);

        $generated = $this->generatePlaceHolders($value, $column);

        $placeholders = "(" . implode(", ", $generated) . ")";

        $executeWhereArray =  count($this->executeWhereArray) > 0 ? ($whereTypeOr == false ? ($this->isChaining == false ?  "AND" : " ") : "OR") : "";

        $this->isChaining = false;


        $this->whereText .= " $executeWhereArray $column $operator $placeholders";
        
    }

    public function whereIn (string $column  , array $value ) {
       
        $this->whereInHandle($column, $value);

        return  $this;

    }

    public function orWhereIn (string $column  , array $value ) {

        $this->whereInHandle($column, $value , true);

        return  $this;

    }

    public function whereNotIn (string $column  , array $value ) {

        $this->whereInHandle($column, $value, false, "NOT IN");

        return  $this;
    }
    
    public function orWhereNotIn (string $column  , array $value ) {

        $this->whereInHandle($column, $value , true, "NOT IN");

        return  $this;

    }

    public function orderBy(string $column = "id" , string $order = "ASC") {

        $this->checkColumns($column);

        $orderArray = ["ASC" => "ASC", "DESC" => "DESC" , "asc" => "ASC" , "desc" => "DESC"]; 

        if(!isset($orderArray[$order])) {
            error_log("order must be ASC or DESC"); 
        }

        if(count($this->executeOrderArray) == 0) {
            $this->orderText .= " ORDER BY ". " $column $order";
        }else{
            $this->orderText .= " , $column $order";
        }
       
        $this->executeOrderArray[$order] = $order;

        return $this;
    }
    
    public function latest(){

        if($this->db == null) {
            $this->db  = Database::connect();
        }

        if(count($this->unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", $this->unknownColumns)];
        }

        $this->checkHiddenColumns();
        // if there are hidden columns they are not shown

        $where =  $this->whereText . " ";

        $order = $this->orderText  ?  $this->orderText ." " : " ORDER BY id DESC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = $this->executeWhereArray ;

        $isWhere = count($this->executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = $this->query . $isWhere . $where .$order . " LIMIT 1 ";

        $stmt = $this->db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );

        $this->closeConnection();

        return $fetched;

    }

    public function first(){

        if($this->db == null) {
            $this->db  = Database::connect();
        }

        if(count($this->unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", $this->unknownColumns)];
        }

        $this->checkHiddenColumns();
        // if there are hidden columns they are not shown
        
        $where =  $this->whereText . " ";" " ;

        $order = $this->orderText  ?  $this->orderText ." " : " ORDER BY id ASC ";

        str_replace("ASC" , "DESC" , $order);

        $executeArray = $this->executeWhereArray ;

        $isWhere = count($this->executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = $this->query . $isWhere . $where .$order . " LIMIT 1 ";

        $stmt = $this->db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC );

        $this->closeConnection();

        return $fetched;
    }

    public function update(array $values){

        if($this->db == null) {
            $this->db  = Database::connect();
        }

        $textChange = "";

        $keyCheck = array_keys($values)[count($values) - 1];

        foreach ($values as $key => $value) {

            $this->checkColumns( $key );

            $this->executeUpdateArray [":update$key"] = $value;
            
            if($keyCheck == $key) {

                $textChange .= " $key = :update$key  "; 

                continue ;
            }

            $textChange .= " $key = :update$key , "; 

        }

        if(count($this->unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", $this->unknownColumns)];
        }

        $where =  $this->whereText . " ";

        $executeArray = $this->executeWhereArray ;

        $query = "UPDATE " . $this->table ." SET ". $textChange  . $where ;

        $stmt = $this->db->prepare($query);

        $stmt->execute($executeArray);

        $this->closeConnection();

        return true;
    }

    public function get(){
        
        if(count($this->unknownColumns) > 0) {
            return ["message" => "unknow columns ". implode(",", $this->unknownColumns)];
        }

        $where =  $this->whereText . " ";

        $order =  $this->orderText ." ";
     
        $executeArray = $this->executeWhereArray ;
       
        $isWhere = count($this->executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = $this->query . $isWhere . $where .$order ;

        $stmt = $this->db->prepare($query); 

        $stmt->execute($executeArray);
        
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        $this->closeConnection();

        return  $fetched; 
        
        return  ["query" => $query , "executeArray" => $executeArray];
     
    }

    public function all() {

        if($this->db == null) {

            $this->db  = Database::connect();

        }

        if(count($this->hiddenArray) > 0 && $this->query == "") {
                
            $hidden_columns = "";

            $schema = $this->returnSchema() ;

            $lastKey = array_key_last($schema);
            
            foreach ( $schema as $colum_key => $column) {
                
                if (! in_array($colum_key, $this->hiddenArray))  {

                    if($colum_key == $lastKey) {
                        
                        $hidden_columns .= "`$colum_key` "; 
                        continue;

                    }

                    $hidden_columns .= "`$colum_key`, "; 

                }
            }

            $this->query = "SELECT {$hidden_columns } FROM {$this->table}" ;
                      
        }elseif($this->query == "") {

            $this->query = "SELECT * FROM " . $this->table;

        }
        
        $query =  $this->query ;

        $stmt = $this->db->prepare($query); 

        $stmt->execute();

        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        $this->closeConnection();

        return  $fetched;
        
    }

    public function count() : int  {

        if($this->db == null) {

            $this->db  = Database::connect();

        }

        $table_name = $this->table ;

        $stmt = $this->db->query("SELECT COUNT(*) as total From $table_name "); 

        $item_number = $stmt->fetch(PDO::FETCH_ASSOC )["total"];

        return  $item_number;
    }

    public function pagination($limit , $page = 1) {

        if($this->db == null) {

            $this->db  = Database::connect();

        }

        if( ! is_int($limit) || $limit <= 0){ {
            return ["message" => "Pagination  number must be greater than 0"];
        }
        if( ! is_int($page) || $page < 0) {
            return ["message" => "Page number must be greater than 0"];
        }

        }

        if(count($this->hiddenArray) > 0 && $this->query == "") {
                
            $hidden_columns = "";

            $schema = $this->returnSchema() ;

            $lastKey = array_key_last($schema);
            
            foreach ( $schema as $colum_key => $column) {
                
                if (! in_array($colum_key, $this->hiddenArray))  {

                    if($colum_key == $lastKey) {
                        
                        $hidden_columns .= "`$colum_key` "; 
                        continue;

                    }

                    $hidden_columns .= "`$colum_key`, "; 

                }
            }

            $this->query = " SELECT {$hidden_columns} FROM {$this->table} ";
            
        }elseif($this->query == "") {

            $this->query = "SELECT * FROM " . $this->table;

        }

        $page = $page - 1;

        $item_number = $this->count();

        $total_page = ceil($item_number / $limit);

        $start_point = $limit * $page;

        $where =  $this->whereText . " ";

        $order =  $this->orderText ." ";

        $executeArray = $this->executeWhereArray ;

        $isWhere = count($this->executeWhereArray) > 0 ? " WHERE " : "" ;

        $query = $this->query . $isWhere . $where .$order . " LIMIT :limit OFFSET :start_point ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':start_point', (int) $start_point, PDO::PARAM_INT);

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        
        foreach ($executeArray as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();

        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC );

        $this->closeConnection();

        $pagination_data = [
            "total_page" => $total_page,
            "current_page" => $page + 1,
            "total_item" => $item_number,
            "data" =>  $fetched,
        ];
        
        return  $pagination_data ;
    } 

    public function delete() {

        $table_name = $this->table ;

        $where =  $this->whereText . " ";

        $executeArray = $this->executeWhereArray ;

        $query = "DELETE FROM  $table_name $where  ";

        $stmt = $this->db->prepare($query);

        $stmt->execute($executeArray);

        $this->closeConnection();

        return  true;
    }
}


?>