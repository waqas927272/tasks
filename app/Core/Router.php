<?php

namespace App\Core;

class Router {
    private $routes = [];
    
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }
    
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }
    
    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
    }
    
    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }
    
    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    public function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath !== '/') {
            $path = str_replace($basePath, '', $path);
        }
        
        $path = '/' . trim($path, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->convertToRegex($route['path']);
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return $this->callCallback($route['callback'], $matches);
            }
        }
        
        http_response_code(404);
        require __DIR__ . '/../Views/errors/404.php';
    }
    
    private function convertToRegex($path) {
        $pattern = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    private function callCallback($callback, $params = []) {
        if (is_string($callback)) {
            list($controller, $method) = explode('@', $callback);
            $controller = "App\\Controllers\\{$controller}";
            
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $method)) {
                    return call_user_func_array([$controllerInstance, $method], $params);
                }
            }
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        
        throw new \Exception("Invalid callback");
    }
}