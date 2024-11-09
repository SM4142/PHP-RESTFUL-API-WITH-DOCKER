<?php
namespace app\controller;

use app\classes\Request;
use app\classes\Response;


class UserController {
   public function index() {
    $data = [["message" => "Hello World"]];
    $data[0]["message"] = "Hello World2";
    Response::sendResponse($data);
 
   }


   public function login(){
    $rule = [
    "name" =>["min-length" => 5 ,  "max-length" => 20] , 
    "password" =>["min-length" => 6 , "max-length" => 20],
    "email" =>["mail" => true]
    ];

    $data = Request::Validation($rule);

    Response::sendResponse($data );
   }
   
}   
?>
