<?php 

namespace database\migrations;

use app\classes\enums\OnAction;
use app\classes\Schema;

class UserMigration 
{
    protected static $tableName = 'users';
    public static function up(Schema $colum) {
      $colum->Table(self::$tableName);
      $colum->Id();
      $colum->ForeignKey("role_id")->References("id")->On("roles")->OnDelete(OnAction::SET_NULL)->OnUpdate(onAction::CASCADE); 
      $colum->Text("name", 50)->NullAble(true);
      $colum->Text("email");
      return $colum->Create();
     
    }
    
}

?>