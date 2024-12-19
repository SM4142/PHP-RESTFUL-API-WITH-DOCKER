<?php 

namespace app\classes;

class Request {

    public static array $attributes = [];

    public function __get($name)
    {
        return self::$attributes[$name] = self::$attributes[$name] ?? null;
    }

    public static function Validation($rule) {

        $input = file_get_contents("php://input");

        $data = json_decode($input, true); 

        header('Content-Type: application/json');

        http_response_code(400);

        foreach ($rule as $key => $value) {

            $check = $data[$key] ?? null;

            if(!$check ) {

                Response::sendResponse(["message" => "$key is required"]);

                exit; 

            }

            $minLength = $value["min-length"] ?? null ;

            if( $minLength  && strlen($check) < $value["min-length"]) {

                Response::sendResponse(["message" => "$key must be greater than " . $value["min-length"]]);

                exit; 

            }
            $maxLength = $value["max-length"] ?? null ;

            if( $maxLength  && strlen($check) > $value["max-length"]) {

                Response::sendResponse(["message" => "$key must be less than " . $value["max-length"]]);

                exit; 

            }
            $mail = $value["mail"] ?? null ;

            if( $mail && (strlen($check) < ( $value["min-length"] ?? 5) || strpos($check, '@') === false)) {

                Response::sendResponse(["message" => "it must be a valid email"]);

                exit; 
            }
           
        }
        
        self::$attributes = $data;

        return new static();

    }
    
}

?>