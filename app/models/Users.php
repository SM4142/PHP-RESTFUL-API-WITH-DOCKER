<?php

namespace app\models;

use app\classes\Model;

class Users extends Model  {

      protected static $table = 'Users';

      protected string $id = "id";

      protected array $nullAbleArray = [];

      protected array $notNullArray = ["name", "email", "password"];

      protected array $hiddenArray = [];
}
