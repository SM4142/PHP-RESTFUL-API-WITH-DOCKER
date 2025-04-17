<?php 

namespace app\classes;

class Response{

    public static function Json($response, $httpStatus = 200){
        $response = json_encode($response);
        http_response_code($httpStatus);
        header('Content-Type: application/json');
        echo $response;
    }

    public static function Html($response, $httpStatus = 200){
        http_response_code($httpStatus);
        header('Content-Type: text/html; charset=utf-8');
        echo $response;
    }


    Public static function Image($image){
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($image));
        readfile($image);

    }
}

?>
