<?php
namespace app\controllers;

use app\classes\Request;
use app\classes\Response;
use app\models\Users;

class UserController {
   public function index(   ) {

    $oldUser = Users::findById(4);
    
    Response::sendResponse(["message" => $oldUser->name ]);
    
   }


   public function login(){
    $rule = [
    "password" =>["min-length" => 6 , "max-length" => 20],
    "email" =>["mail" => true]
    ];

    $data = Request::Validation($rule);

    $check = Users::where("email" , $data->email)->first();

    if($check){

        Response::sendResponse(["message" => "Email already exist"]);

        exit;

    }

    Response::sendResponse($data);
    
   }

   public function register(){

    $rule = [

        "name" =>["min-length" => 5 ,  "max-length" => 20] , 

        "password" =>["min-length" => 6 , "max-length" => 20],

        "email" =>["mail" => true , "exist" => true]

    ];

    $data = Request::Validation($rule);

    $check = Users::where("email" , $data->email)->first();

    if($check){

        Response::sendResponse(["message" => "Email already exist"]);

        exit;

    }

    $user = new Users();

    $user->name = $data->name;

    $user->email = $data->email;

    $password = hash("sha256" , $data->password);

    $user->password = $password ;

    $user->save();

    Response::sendResponse(["message" => $data->name]);
   }

   
}   
?>
