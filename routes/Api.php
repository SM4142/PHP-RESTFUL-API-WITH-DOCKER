<?php

use app\classes\Route;

Route::GET('/api/{id}/{page}', ['app\controller\UserController', 'index']);
Route::POST('/login', ['app\controller\UserController', 'login']);

Route::GET('/admin/deneme22', ['app\controller\UserController', 'index']);
Route::GET('/admin/deneme/{id}', ['app\controller\UserController', 'index']);

?>
