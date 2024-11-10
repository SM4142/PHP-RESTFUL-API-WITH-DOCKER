<?php

use app\classes\Route;

Route::GET('/api', ['app\controller\UserController', 'index']);
Route::POST('/api/login', ['app\controller\UserController', 'login']);

?>
