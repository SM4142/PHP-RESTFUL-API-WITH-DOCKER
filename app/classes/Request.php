<?php 

namespace app\classes;

class Request {

    public static function Validation($rule) {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true); 
        header('Content-Type: application/json');
        http_response_code(400);

        foreach ($rule as $key => $value) {
            $check = $data[$key] ?? null;
            if(!$check  ) {
                echo json_encode(["message" => "$key is required"]);
                return;
            }
            $minLength = $value["min-length"] ?? null ;
            if( $minLength  && strlen($check) < $value["min-length"]) {
                echo json_encode(["message" => "$key must be greater than " . $value["min-length"]]);
                return;
            }
            $maxLength = $value["max-length"] ?? null ;
            if( $maxLength  && strlen($check) > $value["max-length"]) {
                echo json_encode(["message" => "$key must be less than " . $value["max-length"]]);
                return;
            }
            $mail = $value["mail"] ?? null ;
            if( $mail && (strlen($check) < ( $value["min-length"] ?? 5) || strpos($check, '@') === false)) {
                echo json_encode(["message" => "it must be a valid email"]);
                return;
            }
            return $data ;
        }
    }
    
}

?>