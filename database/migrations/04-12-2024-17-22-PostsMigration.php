<?php

namespace database\migrations;

use app\classes\enums\OnAction;
use app\classes\Schema;

class PostsMigration
{
    protected static $tableName = 'Posts';
    public static function up(Schema $colum) {
        $colum->Table(self::$tableName);
        $colum->Id();
        $colum->ForeignKey('users_id')->References('id')->On('Users') ;
        $colum->ForeignKey('categories_id')->References('id')->On('Categories') ;
        $colum->Text('title');
        $colum->Text('body');
        $colum->Create();
    }

    public static function down(Schema $colum) {
       $colum->Table(self::$tableName);
       $colum->Drop();
    }

}
