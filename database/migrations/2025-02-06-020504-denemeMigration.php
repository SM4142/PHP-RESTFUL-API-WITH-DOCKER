<?php

namespace database\migrations;

use app\classes\Schema;
use app\classes\Migration;

class denemeMigration extends Migration 
{
    protected static $tableName = 'deneme';
    public static function up(Schema $colum) {
        $colum->Table(self::$tableName);
        $colum->Id();
        $colum->SetTimestamps(); 
        return $colum;
    }

    public static function down(Schema $colum) : Schema {
       $colum->Table(self::$tableName);
       return $colum;
    }

}
