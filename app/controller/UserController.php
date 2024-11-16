<?php
namespace app\controller;

use app\classes\Request;
use app\classes\Response;
use app\model\User;

class UserController {
   public function index(  $get ) {
    var_dump($get);
    $users = User::pagination(10 );
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
