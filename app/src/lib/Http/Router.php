<?php


namespace App\Lib\Http;


use App\Lib\Controllers\AbstractController;

class Router {

    private const string ROUTE_CONFIG_PATH = __DIR__ . '/../../../config/routes.json';
    private const string CONTROLLER_NAMESPACE = 'App\\Controllers\\';
    
    public function main(Request $request) {
        $routes = $this->getRoutes();
        $uriFoundFlag = false;
        $methodFoundFlag = false;

        $response = new Response();
        
        foreach($routes as $route) {
            $this->validateConfig($route);
            
            if($this->checkUri($request, $route['uri']) === false) {
                continue;
            }
            $uriFoundFlag = true;
            
            if($this->checkMethod($request, $route['method']) === false) {
                continue;
            }
            $methodFoundFlag = true;

            try {
                $controllerInstance = $this->getValidControllerInstance($route['controller']);

                return $controllerInstance->process($request);
            } catch(\Exception $e) {
                continue;
            }
        }

        if($uriFoundFlag === true && $methodFoundFlag === false) {
            $response->statusCode = 405;
            $response->headers['Content-Type'] = 'application/json';
            $response->content = json_encode(['error' => 'Invalid method'], JSON_THROW_ON_ERROR);

            return $response;
        }

        $response->statusCode = 404;
        $response->content = "route note found";

        return $response;
    }

    private function getRoutes(): array {
        if(file_exists(self::ROUTE_CONFIG_PATH) === false) {
            echo "Le fichier de config des routes n'a pas été trouvé";
            exit();
        }

        $routesConfigContent = file_get_contents(self::ROUTE_CONFIG_PATH);
            
        return json_decode($routesConfigContent, true, 512, JSON_THROW_ON_ERROR);
    }
    
    private function validateConfig(array $route): void {
        if(array_key_exists('uri', $route) === false || array_key_exists('method', $route) === false) {
            throw new \Exception(500, 'Le fichier de config est mal écrit');
        }
    }

    private function checkUri(Request $request, string $uri): bool {
        $requestUri = parse_url($request->getUri(), PHP_URL_PATH);
        if (!is_string($requestUri)) {
            return false;
        }

        $routeParts = explode('/', $uri);
        $requestParts = explode('/', $requestUri);
        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        foreach ($routeParts as $index => $routePart) {
            if (preg_match('/^\{[^}]+\}$/', $routePart) === 1) {
                if ($requestParts[$index] === '') {
                    return false;
                }

                continue;
            }

            if ($routePart !== $requestParts[$index]) {
                return false;
            }
        }

        return true;
    }
    
    private function checkMethod(Request $request, string $method): bool {
        return $request->getMethod() === $method;
    }
    
    private function getValidControllerInstance(string $routeController): AbstractController {

        $controllerName = self::CONTROLLER_NAMESPACE . $routeController;

        if(class_exists($controllerName) === false) {
            throw new \Exception('Controller ' . $controllerName . ' not found.');
        }

        $controllerInstance = new $controllerName();

        if(is_subclass_of($controllerInstance, AbstractController::class) === false) {
            throw new \Exception('Controller ' . $controllerName . ' must extends App\Controllers\AbstractController.');
        }

        return $controllerInstance;
    }

 
    
}
