<?php 

namespace database\migrations;

use app\classes\Migration;
use app\classes\Schema;

class UserMigration 
{
    protected static $tableName = 'Users';
    public static function up(Schema $colum) {
      $colum->Table(self::$tableName);
      $colum->Id();
      $colum->Text("name", 50)->NullAble(true);
      $colum->Text("email");
      $colum->Create();
    }

    public static function down(Schema $colum) {
      $colum->Table(self::$tableName);
      $colum->Drop();
    }

    
}

?>