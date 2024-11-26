<?php
spl_autoload_register(function ($class) {
    require str_replace('\\', '/', $class) . '.php';
});
require __DIR__ . '/vendor/autoload.php';

use app\classes\Schema;
use Dotenv\Dotenv;  

$dotenv = Dotenv::createImmutable(__DIR__);

$dotenv->load();


use database\migrations\UserMigration;

$columns = UserMigration::up( new Schema() );
if(isset($columns) ){
    var_dump($columns);
}





?>