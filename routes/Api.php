<?php
use app\classes\Route;

$Route = new Route();


$Route->GET('/api', ['app\controller\UserController', 'index']);
$Route->GET('/api/login', ['app\controller\UserController', 'login']);

$Route->RoutingFunction();
?>
