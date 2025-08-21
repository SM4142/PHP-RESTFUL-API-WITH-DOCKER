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

    public static function View ($route , $data = []){
        ob_start(null, 0);
        extract($data);
        include_once __DIR__ . '/../../view/'.  $route . '.php';
        $content = ob_get_clean();
        Response::Html($content);

    }


    Public static function Image($image){
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($image));
        readfile($image);

    }
}

?>
