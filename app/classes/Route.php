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

    private static function RoutingFunction ($method , $path , $controller){
        $path_main   = $_SERVER['REQUEST_URI'];
        $method_main = $_SERVER['REQUEST_METHOD'];
        if($method == $method_main && $path == $path_main) {
            if(!is_array($controller)){
                echo "Invalid controller.";
                return;
            }

            $controller =  $controller;
            $class = $controller[0];
            $method = $controller[1];

            $controllerInstance = new $class();
            if(!method_exists($controllerInstance, $method)){
                echo "Method not found.";
                return;
            }

            $controllerInstance->$method();
        }
        
    }

}

?>