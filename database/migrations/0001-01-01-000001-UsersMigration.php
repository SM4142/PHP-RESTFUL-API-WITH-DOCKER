<?php

namespace database\migrations;

use app\classes\Schema;
use app\classes\Migration;

class UsersMigration extends Migration 
{
    protected static $tableName = 'Users';
    public static function up(Schema $column) : Schema{
        $column->Table(self::$tableName);
        $column->Id();
        $column->Text('name');
        $column->Text('email')->Unique();
        $column->Text('password');
        $column->Text('role')->Default('user');
        $column->SetTimestamps();
        return $column;
    }

    public static function down(Schema $column) : Schema {
       $column->Table(self::$tableName);
       return $column;
    }

}
