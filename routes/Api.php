<?php

use app\classes\Response;
use app\classes\Route;

Route::GET('/api/{id}/{page}/{order?}', ['app\controller\UserController', 'index']);
Route::POST('/login', ['app\controller\UserController', 'login']);
Route::GET('/admin/deneme22', ['app\controller\UserController', 'index']);
Route::GET('/admin/deneme/{id?}', function ($get) {
    if(isset($get)){
        Response::sendResponse($get);
    }
   
});
?>
