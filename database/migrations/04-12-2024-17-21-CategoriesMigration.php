<?php

namespace database\migrations;

use app\classes\Schema;

class CategoriesMigration
{
    protected static $tableName = 'Categories';
    public static function up(Schema $colum) {
        $colum->Table(self::$tableName);
        $colum->Id();
        $colum->Create();
    }

    public static function down(Schema $colum) {
       $colum->Table(self::$tableName);
       $colum->Drop();
    }

}
