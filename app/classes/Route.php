<?php 

namespace app\classes;

use Closure;
use Exception;

class Route {
    private static array $routesArray = [];
    private static int $numberOfRoutes = 0 ;
    private static  bool $checkNumberOfRoutes = false;

    private static $undefinedPage = [] ;

    public static function GET ( $path , $controller){
    
        self::$routesArray[] = [ "path" => $path , "controller" => $controller ,"method"=> "GET" ];
        return new self( );
        
        // Creating new class
    }
    public static function POST ( $path , $controller){
       
        self::$routesArray[] = [ "path" => $path , "controller" => $controller ,"method"=> "POST" ];
        return new self( );

    }
    public static function PUT ($path , $controller){
       
        self::$routesArray[] = [ "path" => $path , "controller" => $controller ,"method"=> "PUT" ];
        return new self( );

    }
    public static function DELETE ( $path , $controller){

        self::$routesArray[] = [ "path" => $path , "controller" => $controller ,"method"=> "DELETE" ];
        return new self( );
       
    }
    public function __construct() {

        if(self::$checkNumberOfRoutes == false) {
            self::getNumberOfRoutes();
            // We get the number of routes
        }

        if(self::$numberOfRoutes == count(self::$routesArray)) {

            $newRoutesArray = [];

            $newRoutesParamsArray = [];
    
            foreach (self::$routesArray as $key => $route) {
                
                if($route["path"] == "/404"){
              
                    self::$undefinedPage[] = $route["controller"];
                };

                if((strpos($route["path"] , '{') !== false )){
                   
                    $newRoutesParamsArray [] = $route;
                }
                
                else{

                    $newRoutesArray [] = $route;
                }
        
                  // We call the routing function this method let us do the routing
            }   

            $newRoutesArray = array_merge($newRoutesArray, $newRoutesParamsArray);

            foreach ($newRoutesArray as $key => $route) {

                self::RoutingFunction($route["method"], $route["path"], $route["controller"] , $key , count($newRoutesArray));

            }
         
          
        }
        
      
    }
    private static function getNumberOfRoutes () {
        
        $route = 'routes/Routes.php'; 

        $content = file_get_contents($route); 

        preg_match_all('/Route::(GET|POST|PUT|DELETE)\s*\((.*?)\);/s', $content, $matches);
        
        self::$numberOfRoutes = count($matches[0]);

        self::$checkNumberOfRoutes = true ;

    }

    public function middleware($middleware) {
        if (is_array($middleware)) {
            foreach ($middleware as $func) {
                $middleware_item = new $func();
            }
        }
    }

    private static function RoutingFunction($method, $path, $controller , $key ,$total) {
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
                if(strpos($part, '{') !== false ){
                    $params_Array[] = $part;
                }
            }
            
        }

        for ($i = count($path_array) - 1; $i >= 0; $i--) {

            // we create a pattern to check the parameters
            $pattern = "@^" . preg_replace('/{([\w]+)\??}/', '([\w-]*)?', $path_array[$i]) . "$@";

            if( !strpos($path_array[count($path_array) - 1] , '{') !== false && strpos($part, '?') === false){
               continue;
            }

            // we'll slice the path to compare with parts
            $sliced_path = explode('/', $path);

            array_shift($sliced_path);

            // we slice path one by one "/" "/api" "api/login
            $parts = explode('/', $path_array[$i]);

            array_shift($parts);

            // we use a counter here to count the number of parameters
            $check_path_longh = 0;
            
            foreach ($sliced_path as $part) {

                if(strpos($part, '{') !== false ){

                    $check_path_longh++;

                }
            }

            // if the sliced path is less than sliced_path - check_path_longh or greater than sliced_path we continue
            if (count($parts) < count($sliced_path) - $check_path_longh || count($parts) > count($sliced_path)) {
                continue;
            }
           
            if (preg_match($pattern, $path_main, $matches)) {
                
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
                        Response::Json(["message" => "Missing required parameter: $value"], 400);
                        exit;
                    }
                }
                // if exist we run the controller
                self::run($controller, $data);
                exit;
            }
            
        }

        // If the path is not found

        if($total == $key +1 ){
            if(count(self::$undefinedPage) > 0){
                //checking 404 is defined
                self::run(self::$undefinedPage[0]);
                exit;
            }
            Response::Json(["message"=> "Path not found"],404);
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
            Response::Json(["message" => "Method not found."], 400);
            exit;
        }
        // If the method is found we run it
        call_user_func_array([$controllerInstance, $method], [$params]);
        
        exit;
    }

}

?>