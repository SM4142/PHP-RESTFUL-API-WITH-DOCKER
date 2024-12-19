<?php
spl_autoload_register(function ($class) {
    require str_replace('\\', '/', $class) . '.php';
});
require __DIR__ . '/vendor/autoload.php';


use Dotenv\Dotenv;  
use app\classes\AllCommands;
$dotenv = Dotenv::createImmutable(__DIR__);

$dotenv->load();

date_default_timezone_set($_ENV['TIMEZONE']);


AllCommands::Create($argv);


