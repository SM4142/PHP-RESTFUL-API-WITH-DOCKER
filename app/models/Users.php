<?php

namespace app\models;

use app\classes\Model;

class Users extends Model  {

      protected static $table = 'Users';

      protected static string $id = "id";

      protected static array $nullAbleArray = [];

      protected static array $notNullArray = ["name", "email", "password"];

      protected static array $hiddenArray = ["password" , "name"];
      
}
