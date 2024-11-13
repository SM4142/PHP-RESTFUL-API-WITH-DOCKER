<?php
namespace app\controller;

use app\classes\Request;
use app\classes\Response;
use app\model\User;

class UserController {
   public function index() {
    $users = User::fetchAll();
    if ($users) {
       
        Response::sendResponse($users);
    } else {
        echo "Veritabanı bağlantısı başarısız!";
    }
    
 
   }


   public function login(){
    $rule = [
    "name" =>["min-length" => 5 ,  "max-length" => 20] , 
    "password" =>["min-length" => 6 , "max-length" => 20],
    "email" =>["mail" => true]
    ];

    $data = Request::Validation($rule);
    Response::sendResponse($data);


   }
   
}   
?>
