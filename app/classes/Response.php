<?php 

namespace app\classes;

class Response{

    public static function sendResponse($response, $httpStatus = 200){
        $response = json_encode($response);
        http_response_code($httpStatus);
        header('Content-Type: application/json');
        echo $response;
    }


    Public static function sendImage($image){
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($image));
        readfile($image);

    }
}

?>
