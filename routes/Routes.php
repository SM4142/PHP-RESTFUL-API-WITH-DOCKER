<?php
namespace routes\Routes;
use app\classes\Response;
use app\classes\Route;
use app\controllers\UserController;


Route::GET('/api/{page}', [UserController::class, 'index']);
Route::GET('/api/users/{id}', [UserController::class, 'users']);
Route::POST('/api/auth/login', [UserController::class, 'login']);
Route::POST('/api/auth/register', [UserController::class, 'register']);
Route::GET('/admin/deneme22/{id}', [UserController::class, 'index']);

Route::GET('/404', function () {
    Response::Json( ["message" => "this page not found "] , 404);
});

Route::GET('/admin/deneme2/{id}/{id2?}', function ($get) {
    if(isset($get)){
        Response::Json($get);
    }
   
});

?>
    