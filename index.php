<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); 

spl_autoload_register(function ($class) {
    require str_replace('\\', '/', $class) . '.php';
});




require_once __DIR__ . '/routes/Api.php';



?>