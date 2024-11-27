<?php

use app\classes\Schema;

use Dotenv\Dotenv;  

use database\migrations\UserMigration;

require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    require_once str_replace('\\', '/', $class) . '.php';
});

$dotenv = Dotenv::createImmutable(__DIR__);

$dotenv->load();

$columns = UserMigration::up( new Schema() );
if(isset($columns) ){
    var_dump($columns);
}





?>