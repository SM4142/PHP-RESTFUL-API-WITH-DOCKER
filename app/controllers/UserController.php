<?php
namespace app\controller;

use app\classes\Request;
use app\classes\Response;
use app\model\Users;

class UserController {
   public function index(  $get ) {

    $user = Users::where("name" , "semih");
    Response::sendResponse($user);
   }


   public function login(){
    $rule = [
    "password" =>["min-length" => 6 , "max-length" => 20],
    "email" =>["mail" => true]
    ];

    $data = Request::Validation($rule);
    Response::sendResponse($data);
   }

   public function register(){
    $rule = [
    "name" =>["min-length" => 5 ,  "max-length" => 20] , 
    "password" =>["min-length" => 6 , "max-length" => 20],
    "email" =>["mail" => true]
    ];

    $data = Request::Validation($rule);
    Response::sendResponse($data);
   }

   public function handle($id = null, $page = null) {
    if ($id === null && $page === null) {
        echo "API Root Reached!";
    } elseif ($page === null) {
        echo "ID: $id";
    } else {
        echo "ID: $id, Page: $page";
    }
}
   
}   
?>
