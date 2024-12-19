<?php 

namespace app\classes;



class Route {

    public static function GET ( $path , $controller){
        $method = "GET";
        $path = $path;
        $controller = $controller;
        return new self( $method , $path , $controller);
        
    }
    public static function POST ( $path , $controller){
        $method = "POST";
        $path = $path;
        $controller = $controller;
        return new self( $method , $path , $controller);
    }
    public static function PUT ($path , $controller){
        $method = "PUT";
        $path = $path;
        $controller = $controller;
        return new self( $method , $path , $controller);
    }
    public static function DELETE ( $path , $controller){
        $method= "DELETE";
        $path = $path;
        $controller = $controller;
        return new self( $method , $path , $controller);
       
    }
    public function __construct($method , $path , $controller) {
        Route::RoutingFunction( $method , $path , $controller);
    }
    public function middleware($middleware) {
        if (is_array($middleware)) {
            foreach ($middleware as $func) {
                $middleware_item = new $func();
            }
        }
    }

    private static function RoutingFunction($method, $path, $controller) {
        $path_main = rtrim($_SERVER['REQUEST_URI'], '/'); 
        $method_main = $_SERVER['REQUEST_METHOD'];
    

        if ($method == $method_main && $path_main == $path) {
            self::run($controller);
            exit;
        }
    
        $parts = explode('/', $path);
        $path_holder = "";
        $path_array = [];
    
        foreach ($parts as $part) {
            $path_holder .= $part . "/";
            $path_array[] = rtrim($path_holder, '/'); 
        }

        $hold_required_name_array = [];
        $required_check = $path_array[count($path_array) - 1];

        if($required_check){
            $required_array = explode('/', $required_check);
            array_shift( $required_array);

            foreach ( $required_array as $part) {
                if (strpos($part, '{') !== false && strpos($part, '?') === false) {
                    $required_part = str_replace(['{', '}'], '', $part);
                    $hold_required_name_array[] = $required_part;
                }
            }
            
        }
        for ($i = count($path_array) - 1; $i >= 0; $i--) {

            $pattern = "@^" . preg_replace('/{([\w]+)\??}/', '([\w-]*)?', $path_array[$i]) . "$@";
            
            if (preg_match($pattern, $path_main, $matches)) {
                $parts = explode('/', $path_array[$i]);
                array_shift($parts);
                $hold_name_array = [];
                foreach ($parts as $part) {
                    if (strpos($part, '{') !== false) {
                        $cleaned_part = str_replace(['{', '}', '?'], '', $part);
                        $hold_name_array[] = $cleaned_part;
                    }
                }

                array_shift($matches);

               $data = array_combine($hold_name_array, $matches);
                foreach ($hold_required_name_array as $value){
                    if(! isset($data[$value])  ){
                        Response::sendResponse(["message" => "Missing required parameter: $value"], 400);
                        exit;
                    }
                }
                self::run($controller, $data);
                exit;
            }
        }
    }
    
    private static function run($controller ,  $params = []) {
        if (!is_array($controller)) {
            call_user_func($controller , $params);
            exit;
        }
        $class = $controller[0];
        $method = $controller[1];
    
        $controllerInstance = new $class();
        if (!method_exists($controllerInstance, $method)) {
            Response::sendResponse(["message" => "Method not found."], 400);
            exit;
        }
    
        call_user_func_array([$controllerInstance, $method], [$params]);
        exit;
    }

}

?>