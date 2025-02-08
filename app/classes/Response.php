<?php 

namespace app\classes;

class Response{

    public static function Json($response, $httpStatus = 200){
        $response = json_encode($response);
        http_response_code($httpStatus);
        header('Content-Type: application/json');
        echo $response;
    }


    Public static function Image($image){
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($image));
        readfile($image);

    }
}

?>
