<?php 

namespace app\classes;
class Schema {
    protected $columns = [];
    protected $column = "" ; 

    public function Id() {
        $this->columns["id"] =
            [
            "type" => "INT",
            "auto_increment" => true,
            "primary_key" => true,
            "nullable" => false
            ]
        ;
        $this->column = "id";
        return $this;
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


    public function Text(string $name ,int $length = 400) {
        $this->columns[$name] = 
            [
            "type" => "TEXT",
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
    public function References(string $name ) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['references'] = $name ;
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
    public function Primary (int $start = 1 , int $i = 1) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['index'] = "PRIMARY";
            $this->columns[$this->column]['start'] = $start;
            $this->columns[$this->column]['i'] = $i;
        }
        return $this;
    }

    
    public function Default ($default) {
        if (isset($this->columns[$this->column])) {
            $this->columns[$this->column]['default'] = $default;
        }
        return $this;
    }
    public function GetSchema() {
        return $this->columns;
    }
    
}

?>