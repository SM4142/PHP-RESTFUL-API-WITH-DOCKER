<?php

use app\classes\Response;
use app\classes\Route;

Route::GET('/api/{id}/{page}', ['app\controller\UserController', 'index']);
Route::GET('/login', ['app\controller\UserController', 'login']);
Route::GET('/admin/deneme22', ['app\controller\UserController', 'index']);
Route::GET('/admin/page/{id}', function ($get) {
    if(isset($get)){
        Response::sendResponse($get);
    }
   
});
Route::GET('/admin/deneme/deneme2', function ($get) {
    if(isset($get)){
        Response::sendResponse($get);
    }
   
});
?>
