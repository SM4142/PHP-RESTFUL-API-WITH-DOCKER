<?php 

namespace app\classes;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth{
    private  function jwtEncode(array $payload) : string{
        $secretKey = $_ENV['JWT_SECRET_KEY'];
        return JWT::encode($payload, $secretKey, 'HS256');
    }
    private function jwtDecode(string $token) : array | null{
        $secretKey = $_ENV['JWT_SECRET_KEY'];
        try{
            $decoded = JWT::decode($token , new Key($secretKey, 'HS256'));
            return (array) $decoded;
        }catch(\Exception $e){
            return null;
        }
    }
    public static function generateAccesToken(int $userId){
       $payload = [
           'UserId' => $userId,
           'exp'=> time() + (60*15)
       ];
       return Auth::jwtEncode($payload);
    }
    public static function generateRefreshToken(int $userId) : string {
        $payload = [
            'UserId' => $userId,
            'exp'=> time() + (60*60*24)
        ];
        return Auth::jwtEncode($payload);
    }
    public static function validateToken(string $token) : int | false{
        $payload = Auth::jwtDecode($token);
        if(! $payload || $payload['exp'] < time()){
            return false;
        }
        return $payload['UserId'];
    }
    public static function logout(){
        setcookie('refreshToken', '', time() - 3600 , '/');
        echo json_encode(['status' => 'logout success']); 
    } 
    public static function refreshToken(string $token){
        setcookie('refreshToken' , $token , time() + (60*60*24) , '/' , '',true , true);
    }   

}

?>