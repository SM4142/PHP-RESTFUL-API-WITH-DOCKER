<?php 
namespace app\classes;
class Route {
    private $path_main;
    private $method_main;
    private $routes = [];
    public function __construct() {

        $this->path_main = $_SERVER['REQUEST_URI'];
        $this->method_main = $_SERVER['REQUEST_METHOD'];
        
    }
    public function RoutingFunction (){
        foreach ($this->routes as $route) {
            if($route->method == $this->method_main && $route->path ==$this->path_main) {
                if(!is_array($route->controller)){
                    echo "Invalid controller.";
                    return;
                }

                $controller = $route->controller;
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
    public function GET ( $path , $controller){
        $route = new \stdClass();
        $route->method = "GET";
        $route->path = $path;
        $route->controller = $controller;
        $this->routes[] = $route;
    }
    public function POST ( $path , $controller){
        $route = new \stdClass();
        $route->method = "POST";
        $route->path = $path;
        $route->controller = $controller;
        $this->routes[] = $route;
    }
    public function PUT ($path , $controller){
        $route = new \stdClass();
        $route->method = "PUT";
        $route->path = $path;
        $route->controller = $controller;
        $this->routes[] = $route;
    }
    public function DELETE ( $path , $controller){
        $route = new \stdClass();
        $route->method = "DELETE";
        $route->path = $path;
        $route->controller = $controller;
        $this->routes[] = $route;
    }

}

?>