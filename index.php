<?php

require __DIR__ . '/vendor/autoload.php'; 

spl_autoload_register(function ($class) {
    require str_replace('\\', '/', $class) . '.php';
});

use Dotenv\Dotenv;  

$dotenv = Dotenv::createImmutable(__DIR__);

$dotenv->load();

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type, Authorization'); 

header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'");

header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');

header('X-Frame-Options: DENY');

header('X-Content-Type-Options: nosniff');


date_default_timezone_set($_ENV['TIMEZONE']);



require_once __DIR__ . '/routes/Routes.php';



?>