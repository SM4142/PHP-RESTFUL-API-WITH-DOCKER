<?php
namespace app\controllers;

use app\classes\Request;
use app\classes\Response;
use app\models\Users;

class UserController {
   public function  index( $get ) {

    $user = Users::where("role" ,"admin")->where(function ($query){
  
        $query->where("name" , "In", ["semih" , "semih3"]);

        $query->OrWhere("name" , "semih2");
     
    })->get();  

    Response::Json($user);

    // Response::Html("<div><h1 style ='color:red' >" . $user["email"] . "</h1></div>");

    }

   public function login(){
    
    $rule = [
    "password" =>["min-length" => 6 , "max-length" => 20],
    "email" =>["mail" => true]
    ];

    $data = Request::Validation($rule);

    $check = Users::where("email" ,"=", $data->email)->first();

    if($check){

        Response::Json(["message" => "Email already exist"]);

        exit;

    }

    Response::Json($data);
    
   }

   public function register(){

    $rule = [

        "name" =>["min-length" => 5 ,  "max-length" => 20] , 

        "password" =>["min-length" => 6 , "max-length" => 20],

        "email" =>["mail" => true , "exist" => true]

    ];

    $data = Request::Validation($rule);

    $check = Users::where("email" ,"=", $data->email)->first();

    if($check){

        Response::Json(["message" => "Email already exist"]);

        exit;

    }

    $user = new Users();

    $user->name = $data->name;

    $user->email = $data->email;

    $password = hash("sha256" , $data->password);

    $user->password = $password ;

    $user->save();

    Response::Json(["message" => $data->name]);
   }

   
}   
?>
