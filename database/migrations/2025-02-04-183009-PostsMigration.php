<?php

namespace database\migrations;

use app\classes\Schema;
use app\classes\Migration;

class PostsMigration extends Migration 
{
    protected static $tableName = 'Posts';
    public static function up(Schema $colum) {
        $colum->Table(self::$tableName);
        $colum->Id();
        $colum->ForeignKey("user_id")->On("Users")->References("id");
        $colum->Text("title");
        $colum->Text("body")->NullAble(true);
        $colum->SetTimestamps(); 
        return $colum;
    }

    public static function down(Schema $colum) : Schema {
       $colum->Table(self::$tableName);
       return $colum;
    }

}
