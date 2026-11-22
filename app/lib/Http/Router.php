<?php


namespace App\Http;

use App\Controllers\AbstractController;

class Router
{

    private const string ROUTE_CONFIG_PATH = __DIR__ . '/../../config/routes.json';
    private const string CONTROLLER_NAMESPACE = 'App\\Controllers\\';

    public function main(Request $request)
    {
        $routes = $this->getRoutes();
        $uriFoundFlag = false;
        $methodFoundFlag = false;

        $response = new Response();

        foreach ($routes as $route) {
            $this->validateConfig($route);

            if ($this->checkUri($request, $route['uri']) === false) {
                continue;
            }
            $uriFoundFlag = true;

            if ($this->checkMethod($request, $route['method']) === false) {
                continue;
            }
            $methodFoundFlag = true;

            try {
                $controllerInstance = $this->getValidControllerInstance($route['controller']);

                return $controllerInstance->process($request);
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($uriFoundFlag === true && $methodFoundFlag === false) {
            $response->statusCode = 405;
            $response->content = "invalid method";

            return $response;
        }

        $response->statusCode = 404;
        $response->content = "route note found";

        return $response;
    }

    private function getRoutes(): array
    {
        if (file_exists(self::ROUTE_CONFIG_PATH) === false) {
            echo "Le fichier de config des routes n'a pas été trouvé";
            exit();
        }

        $routesConfigContent = file_get_contents(self::ROUTE_CONFIG_PATH);

        return json_decode($routesConfigContent, true, 512, JSON_THROW_ON_ERROR);
    }

    private function validateConfig(array $route): void
    {
        foreach (['uri', 'method', 'controller'] as $key) {
            if (array_key_exists($key, $route) === false || is_string($route[$key]) === false) {
                throw new \InvalidArgumentException(
                    'Le fichier de config des routes doit définir une chaîne pour la clé "' . $key . '".'
                );
            }
        }
    }

    private function checkUri(Request $request, string $uri): bool
    {
        if ($request->getUri() === $uri) {
            return true;
        }
        if (str_contains($uri, '<')) {

            $pattern = preg_replace('/<[a-zA-Z0-9_]+>/', '([^/]+)', $uri);
            $regex = '#^' . $pattern . '$#';
            return (bool) preg_match($regex, $request->getUri());
        }
        return false;
    }

    private function checkMethod(Request $request, string $method): bool
    {
        return $request->getMethod() === $method;
    }

    private function getValidControllerInstance(string $routeController): AbstractController
    {

        $controllerName = self::CONTROLLER_NAMESPACE . $routeController;

        if (class_exists($controllerName) === false) {
            throw new \Exception('Controller ' . $controllerName . ' not found.');
        }

        $controllerInstance = new $controllerName();

        if (is_subclass_of($controllerInstance, AbstractController::class) === false) {
            throw new \Exception('Controller ' . $controllerName . ' must extends App\Controllers\AbstractController.');
        }

        return $controllerInstance;
    }
}
