<?php 

namespace database\migrations;

use app\classes\Schema;

class UserMigration 
{
    public static function up(Schema $colum) {
      $colum->Id();
      $colum->ForeignKey("role_id")->References("id")->On("roles"); ;
      $colum->Text("name", 50)->NullAble(true);
      $colum->Text("email");
      return $colum->GetSchema();
     
    }
    
}

?>