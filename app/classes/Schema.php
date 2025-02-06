<?php 

namespace app\classes;

use app\classes\enums\OnAction;
use PDO;

class Schema {
    private $columns = [];
    private $column = "" ; 
    private $table = "";
    private static $db = null;
    private $foreignArrayKeys = [];
    private $holdingReferences = [];

    public function Id(string $name = "id") {
        $this->columns[$name] =
            [
            "type" => "INT",
            "auto_increment" => true,
            "index" => "PRIMARY KEY",
            "nullable" => false
            ]
        ;
        $this->column = "id";
        return $this;
    }
    public function Table(string $name) {
        $this->table = $name;
    }
    public function returnTableName() {
        return $this->table;
    }
    public function Boolean (string $name ) {
        $this->columns[$name] = 
            [
            "type" => "BOOLEAN",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function Date(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "DATE",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function DateTime(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "DATETIME",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function CreatedAt(string $name ) {
        $this->columns[$name] = 
        [
            "type" => "DATETIME",
            "nullable" => false,
            "default" => "CURRENT_TIMESTAMP",
        ];
    }
    public function UpdatedAt(string $name ) {
        $this->columns[$name] = 
        [
            "type" => "DATETIME",
            "nullable" => false,
            "default" => "CURRENT_TIMESTAMP",
        ];
    }
    public function SetTimestamps( ) {
        $this->columns["created_at"] = 
        [
            "type" => "DATETIME",
            "nullable" => false,
            "default" => "CURRENT_TIMESTAMP",
        ];

        $this->columns["updated_at"] = 
        [
            "type" => "DATETIME",
            "nullable" => false,
            "default" => "CURRENT_TIMESTAMP",
        ];

        return $this;
    }
    public function Number(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "INT",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function UnsignedNumber(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "INT",
            "nullable" => false,
            "unsigned" => true
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function BigNumber(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "BIGINT",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function UnsignedBigNumber(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "BIGINT",
            "nullable" => false,
            "unsigned" => true
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function Float(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "FLOAT",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }


    public function Text(string $name ,int $length = 255) {
        $this->columns[$name] = 
            [
            "type" => "VARCHAR",
            "length" => $length,
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function MediumText(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "MEDIUMTEXT",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function LongText(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "LONGTEXT",
            "nullable" => false,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function ForeignKey(string $name ) {
        //saving foreign key name to use later
        $this->foreignArrayKeys[] = $name;
        $this->columns[$name] = 
            [
            "type" => "INT",
            "foreign_key" => $name,
            "nullable" => false,
            "on_delete" => "CASCADE",
            "on_update" => "CASCADE",
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function References(string $name ) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['references'] = $name ;
        }
        return $this;
    }

    public function OnDelete($name ) {
        if (isset($this->columns[$this->column])) {
                     
            if($name == "SET NULL" || $name == "set null"){
                $this->columns[$this->column]['on_delete'] =  "SET NULL";
                $this->columns[$this->column]['nullable'] =  "true";
                return $this;
            } 
            if($name == "CASCADE" || $name == "cascade"){
                $this->columns[$this->column]['on_delete'] =  "CASCADE";
                return $this;
            }
            if($name == "NO ACTION" || $name == "no action"){
                $this->columns[$this->column]['on_delete'] =  "NO ACTION";
                return $this;
            }
            if($name == "RESTRICT" || $name == "restrict"){
                $this->columns[$this->column]['on_delete'] =  "RESTRICT";
                return $this;
            }
            else{
               error_log("on delete must be SET NULL,CASCADE,NO ACTION,RESTRICT");
            }

        }
    }
    public function OnUpdate($name ) {
        if (isset($this->columns[$this->column])) {
            if($name == "SET NULL" || $name == "set null"){
                $this->columns[$this->column]['on_delete'] =  "SET NULL";
                $this->columns[$this->column]['nullable'] =  "true";
                return $this;
            } 
            if($name == "CASCADE" || $name == "cascade"){
                $this->columns[$this->column]['on_delete'] =  "CASCADE";
                return $this;
            }
            if($name == "NO ACTION" || $name == "no action"){
                $this->columns[$this->column]['on_delete'] =  "NO ACTION";
                return $this;
            }
            if($name == "RESTRICT" || $name == "restrict"){
                $this->columns[$this->column]['on_delete'] =  "RESTRICT";
                return $this;
            }
            else{
               error_log("on delete must be SET NULL,CASCADE,NO ACTION,RESTRICT");
            }
        }
    }
    public function On(string $name ) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['on'] = $name ;
        }
        return $this;
    }
    public function NullAble (bool $nullable ) {
    if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['nullable'] = $nullable;
        }
        return $this;
    }
    public function Unique ( ) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['index'] = "UNIQUE";
        }
        return $this;
    }
    public function Primary () {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['index'] = "PRIMARY KEY";
        }
        return $this;
    }

    public function Default ($default) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['default'] = $default;
        }
        return $this;
    }

    private function CloseConnection(){
        self::$db = null;
    }
    public function returnSchema () {

        return $this->columns ;
        
    }
    public function Create () : bool | array{

        // check if database is connected
        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        // check if table exists
        $stmt = self::$db->query("SHOW TABLES LIKE '$this->table'");

        $check = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //if table exists return false
        if(count($check) > 0) {
            return false;
        }

        // we use foreach to create the table

        $create_table_sql ="CREATE TABLE IF NOT EXISTS $this->table (\n";

        $columns_last_key = array_keys($this->columns)[count($this->columns) - 1];

        foreach ($this->columns as $key => $value) {

            //checking values is exists. If it is not exist we are making it ""
            $length = isset($value["length"]) ? '(' . $value["length"] .')' :"" ;

            $nullable = isset($value["nullable"]) && $value["nullable"] ? ' NULL ' : ' NOT NULL ';

            $index = isset($value["index"]) ? $value["index"] : '' ;

            $default = "";

            //special default values
            $defaultValues = array(
                "CURRENT_TIMESTAMP",
                "NULL",
                "CURRENT_TIME",
                "CURRENT_DATE",
                "UTC_TIMESTAMP"
            );
            
            $rule = isset($value["default"]) && in_array($value["default"], $defaultValues);
            
            if($rule) {
                $default = isset($value["default"]) ? ' DEFAULT ' . $value["default"] : '' ;
            } else {
                $default = isset($value["default"]) ? ' DEFAULT ' . var_export($value["default"], true) : '' ;
            }

            $unsigned = isset($value["unsigned"]) ? ' UNSIGNED' : '' ;

            $auto_increment = isset($value["auto_increment"]) ? ' AUTO_INCREMENT' : '' ;

            $foreign_key = isset($value["foreign_key"]) ? 'FOREIGN KEY ' .'(' . $value["foreign_key"] . ')' : '' ;

            $references = isset($value["references"]) ? ' REFERENCES ' . $value["on"] . '(' . $value["references"] . ')' . 
                        " ON DELETE " . $value["on_delete"] . " ON UPDATE " . $value["on_update"] : '' ;
            
            
            // Creating an array to save the foreign keys and their references to use it later
            if(in_array($key, $this->foreignArrayKeys)) {
                $this->holdingReferences[] = "fk_". explode("_" , $key)[0];
                $foreign_array[] = $foreign_key . " ". $references  ;

            }
            
            //if it is the last key we don't add `,` 
            if($columns_last_key == $key) {
                //checking foreign_array if it is exist we add ` , `
                $create_table_sql .= "$key " . $value["type"] . $length . " " . $nullable . " " . $unsigned . " " . $auto_increment . " " . $default . " " . $index ."\n";
                continue;
              
            }

            $create_table_sql .= "$key " . $value["type"] . $length ." " . $nullable . " ". $unsigned . " ". $auto_increment ." " . $default . " ". $index . " , \n";

        }

        $create_table_sql .= ')';
        //we execute the query

        self::$db->exec($create_table_sql);

        $this->CloseConnection();

        if(! isset($foreign_array)) {
            return true;
        }

        return ["table_name" => $this->table , "foreign_array" => $foreign_array , "fk" => $this->holdingReferences];    
        
    }
    public function Drop () : bool {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }

        $stmt = self::$db->query("SHOW TABLES LIKE '$this->table'");
        $check = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if( count($check) == 0) {
            return false;
        }
    
        self::$db->exec("DROP TABLE IF EXISTS $this->table");
    
        $this->CloseConnection();
    
        return true;

    }
    public function GetSchema() {

        return $this->columns;
        
    }
    
}

?>