<?php 

namespace app\classes;

class Route {

    public static function GET ( $path , $controller){
        $method = "GET";
        $path = $path;
        $controller = $controller;
        Route::RoutingFunction( $method , $path , $controller);
        
    }
    public static function POST ( $path , $controller){
        $method = "POST";
        $path = $path;
        $controller = $controller;
        Route::RoutingFunction( $method , $path , $controller);
    }
    public static function PUT ($path , $controller){
        $method = "PUT";
        $path = $path;
        $controller = $controller;
        Route::RoutingFunction( $method , $path , $controller);
    }
    public static function DELETE ( $path , $controller){
        $method= "DELETE";
        $path = $path;
        $controller = $controller;
    
        Route::RoutingFunction( $method , $path , $controller);
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

        for ($i = count($path_array) - 1; $i >= 0; $i--) {
            $pattern = "@^" . preg_replace('/{([\w]+)}/', '([\w-]*)', $path_array[$i]) . "$@";
    
            if (preg_match($pattern, $path_main, $matches)) {

                $parts = explode('/', $path_array[$i]);
                array_shift($parts);

                $hold_name_array = [];
                foreach ($parts as $part) {
                   if (strpos($part, '{') !== false) {
                        $part = str_replace(['{', '}'], '', $part);
                        $hold_name_array[] = $part;
                   }
                }
                array_shift($matches);
                $data = array_combine($hold_name_array, $matches);


                if (!is_array($controller)) {
                    echo "Invalid controller.";
                    return;
                }
    
                self::run($controller, $data);
                exit;
            }
        }
    }
    
    private static function run($controller ,  $params = []) {
        $class = $controller[0];
        $method = $controller[1];
    
        $controllerInstance = new $class();
        if (!method_exists($controllerInstance, $method)) {
            echo "Method not found.";
            return;
        }
    
        call_user_func_array([$controllerInstance, $method], [$params]);
        exit;
    }

}

?>