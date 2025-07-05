<?php

namespace app\models;

use app\classes\Model;

class Users extends Model  {

      protected static $table = 'Users';

      protected static string $id = "id";

      protected static array $nullableColumns = ["role"];

      protected static array $nonNullableColumns = ["name", "email", "password"];

      protected static array $hiddenArray = ["password" ];
      
}
