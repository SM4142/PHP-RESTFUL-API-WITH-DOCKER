<?php

namespace database\migrations;

use app\classes\Schema;
use app\classes\Migration;

class UsersMigration extends Migration 
{
    protected static $tableName = 'Users';
    public static function up(Schema $colum) : Schema{
        $colum->Table(self::$tableName);
        $colum->Id();
        $colum->Text('name');
        $colum->Text('email')->Unique();
        $colum->Text('password');
        $colum->SetTimestamps();
        return $colum;
    }

    public static function down(Schema $colum) : Schema {
       $colum->Table(self::$tableName);
       return $colum;
    }

}
