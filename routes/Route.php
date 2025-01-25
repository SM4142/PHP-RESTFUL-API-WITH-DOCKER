<?php

use app\classes\Response;
use app\classes\Route;
use app\controllers\UserController;


Route::GET('/api', [UserController::class, 'index']);
Route::POST('/api/login', [UserController::class, 'login']);
Route::POST('/api/register', [UserController::class, 'register']);
Route::GET('/admin/deneme22/{id?}', [UserController::class, 'index']);

Route::GET('/admin/deneme/deneme2', function ($get) {
    if(isset($get)){
        Response::sendResponse($get);
    }
   
});
?>
