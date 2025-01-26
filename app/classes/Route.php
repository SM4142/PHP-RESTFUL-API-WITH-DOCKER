<?php 

namespace app\classes;


class Route {

    public static function GET ( $path , $controller){
        $method = "GET";
        
        return new self( $method , $path , $controller);
        
        // Creating new class
    }
    public static function POST ( $path , $controller){

        $method = "POST";

        return new self( $method , $path , $controller);

    }
    public static function PUT ($path , $controller){

        $method = "PUT";

        return new self( $method , $path , $controller);

    }
    public static function DELETE ( $path , $controller){
        $method= "DELETE";

        return new self( $method , $path , $controller);

       
    }
    public function __construct($method , $path , $controller) {

        // We call the routing function this method let us do the routing

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
        
        // First we get the main path and method

        if ($method == $method_main && $path_main == $path) {
            self::run($controller);
            exit;
            // If ther is no parameters we just run the controller
        }
        
        $parts = explode('/', $path);
        //we slice the path for the parameters
        $path_holder = "";
        // we'll hold the path
        $path_array = [];
        // we'll hold the path one by one this help us to check params exist
        foreach ($parts as $part) {
            $path_holder .= $part . "/";
            $path_array[] = rtrim($path_holder, '/'); 
           
        }

        $hold_required_name_array = [];
        // we'll hold the required parameters
        $required_check = $path_array[count($path_array) - 1];
        // we take the latest path if there is a required parameter it'll be here

        if($required_check){
            $required_array = explode('/', $required_check);
            // we slice the array to check if there is a required parameter
            array_shift( $required_array);
         
            foreach ( $required_array as $part) {
                // we check if there is a required parameter one by one 
                if (strpos($part, '{') !== false && strpos($part, '?') === false) {

                    $required_part = str_replace(['{', '}'], '', $part);
                    // if there is a required parameter we remove the {  } and add it to the required parameter array
                    $hold_required_name_array[] = $required_part;
                }
            }
            
        }
        
        for ($i = count($path_array) - 1; $i >= 0; $i--) {

            // we create a pattern to check the parameters
            $pattern = "@^" . preg_replace('/{([\w]+)\??}/', '([\w-]*)?', $path_array[$i]) . "$@";

            if (preg_match($pattern, $path_main, $matches)) {

                $parts = explode('/', $path_array[$i]);
                // We delte the main path
                array_shift($parts);
                
                $hold_name_array = [];

                foreach ($parts as $part) {
                    // We check here only it is a parameter 
                    if (strpos($part, '{') !== false) {
                        $cleaned_part = str_replace(['{', '}', '?'], '', $part);
                        $hold_name_array[] = $cleaned_part;
                    }
                }

                // We delete empty values
                array_shift($matches);

                // we combine the names and values
                $data = array_combine($hold_name_array, $matches);

                // We check required parameters is exist in data
                foreach ($hold_required_name_array as $value){
                    if(! isset($data[$value])  ){
                        Response::sendResponse(["message" => "Missing required parameter: $value"], 400);
                        exit;
                    }
                }
                // if exist we run the controller
                self::run($controller, $data);
                exit;
            }
        }
    }
    
    private static function run($controller ,  $params = []) {
        // We check if the controller is a array or not
        if (!is_array($controller)) {
            // if not we run the function with params
            call_user_func($controller , $params);
            exit;
        }
        // If it is an array first element is the class and second element is the method
        $class = $controller[0];
        $method = $controller[1];
        // We create a new instance of the class
        $controllerInstance = new $class();

        if (!method_exists($controllerInstance, $method)) {
            // If the method is not found
            Response::sendResponse(["message" => "Method not found."], 400);
            exit;
        }
        // If the method is found we run it
        call_user_func_array([$controllerInstance, $method], [$params]);
        
        exit;
    }

}

?>