<?php 

namespace app\classes;

use app\classes\enums\OnAction;

class Schema {
    protected $columns = [];
    protected $column = "" ; 
    protected $table = "";
    protected static $db = null;
    protected $foreignArrayKeys = [];

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
    public function Boolean (string $name ) {
        $this->columns[$name] = 
            [
            "type" => "BOOLEAN",
            "nullable" => false,
            "default" => null,
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
            "default" => null,
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
            "default" => null,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function Number(string $name ) {
        $this->columns[$name] = 
            [
            "type" => "INT",
            "nullable" => false,
            "default" => null,
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
            "default" => null,
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
            "default" => null,
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
            "default" => null,
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
            "default" => null,
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
            "default" => null,
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
            "default" => null,
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
            "default" => null,
            ]
        ;
        $this->column = $name;
        return $this;
    }
    public function ForeignKey(string $name ) {
        $this->foreignArrayKeys[] = $name;
        $this->columns[$name] = 
            [
            "type" => "INT",
            "foreign_key" => $name,
            "nullable" => true,
            "default" => null,
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

    public function OnDelete(onAction $name ) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['on_delete'] =$name->value;
        }
        return $this;
    }
    public function OnUpdate(onAction $name ) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['on_update'] =$name->value;
        }
        return $this;
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
    public function Create () {

        if(self::$db == null) {

            self::$db  = Database::connect();

        }

        $create_table_sql ="CREATE TABLE IF NOT EXISTS $this->table (\n";

        $columns_last_key = array_keys($this->columns)[count($this->columns) - 1];

        foreach ($this->columns as $key => $value) {

            $length = isset($value["length"]) ? '(' . $value["length"] .')' :"" ;

            $nullable = isset($value["nullable"]) && $value["nullable"] ? ' NULL ' : ' NOT NULL ';

            $index = isset($value["index"]) ? $value["index"] : '' ;

            $default = isset($value["default"]) ? ' DEFAULT ' . $value["default"] : '' ;

            $unsigned = isset($value["unsigned"]) ? ' UNSIGNED' : '' ;

            $auto_increment = isset($value["auto_increment"]) ? ' AUTO_INCREMENT' : '' ;

            $foreign_key = isset($value["foreign_key"]) ? 'FOREIGN KEY ' .'(' . $value["foreign_key"] . ')' : '' ;

            $references = isset($value["references"]) ? ' REFERENCES ' . $value["on"] . '(' . $value["references"] . ')' . 
                        " ON DELETE " . $value["on_delete"] . " ON UPDATE " . $value["on_update"] : '' ;
            
            if(in_array($key, $this->foreignArrayKeys)) {

                $foreign_array[] = $foreign_key . " ". $references  ;

            }
       

            if($columns_last_key == $key) {

                $create_table_sql .= "$key " . $value["type"] . $length . " " . $nullable . " " . $unsigned . " " . $auto_increment . " " . $default . " " . $index . (count($this->foreignArrayKeys) > 0 ? " , \n" : "\n");
                continue;
              
            }

            $create_table_sql .= "$key " . $value["type"] . $length ." " . $nullable . " ". $unsigned . " ". $auto_increment ." " . $default . " ". $index . " , \n";


        }
        if(isset($foreign_array)) {
            foreach ($foreign_array as $key => $value) {
             
                $create_table_sql .= $value . (count($foreign_array) > $key + 1 ? " , \n" : "\n");

            }
        }
        $create_table_sql .= ')';
        
        self::$db->exec($create_table_sql);

        $this->CloseConnection();

        return $create_table_sql;    
        
    }
    public function Drop () {
        if(self::$db == null) {
            self::$db  = Database::connect();
        }
        self::$db->exec("DROP TABLE IF EXISTS $this->table");
        $this->CloseConnection();
    }
    public function GetSchema() {

        return $this->columns;
    }
    
}

?>