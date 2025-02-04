<?php
namespace routes\Routes;
use app\classes\Response;
use app\classes\Route;
use app\controllers\UserController;


Route::GET('/api', [UserController::class, 'index']);
Route::POST('/api/login', [UserController::class, 'login']);
Route::GET('/api/register', [UserController::class, 'register']);
Route::GET('/admin/deneme22/{id}', [UserController::class, 'index']);

Route::GET('/404', function () {
    Response::sendResponse( ["message" => "this page not found "] , 404);
});

Route::GET('/admin/deneme2/{id}', function ($get) {
    if(isset($get)){
        Response::sendResponse($get);
    }
   
});

?>
