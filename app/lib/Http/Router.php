<?php


namespace Lib\Http;

use Lib\Controllers\AbstractController;

class Router {

    private const string ROUTE_CONFIG_PATH = __DIR__ . '/../../config/routes.json';
    private const string CONTROLLER_NAMESPACE = 'App\\Controllers\\';

    public function main(Request $request): Response {
        $uriFoundFlag = false;

        try {
            $routes = $this->getRoutes();

            foreach($routes as $route) {
                $this->validateConfig($route);

                if($this->checkUri($request, $route['uri']) === false) {
                    continue;
                }
                $uriFoundFlag = true;

                if($this->checkMethod($request, $route['method']) === false) {
                    continue;
                }

                $controllerInstance = $this->getValidControllerInstance($route['controller']);

                return $controllerInstance->process($request);
            }
        } catch(\Throwable $e) {
            error_log($e->getMessage());
            return JsonResponse::error(500, 'internal server error');
        }

        if($uriFoundFlag === true) {
            return JsonResponse::error(405, 'invalid method');
        }

        return JsonResponse::error(404, 'route not found');
    }

    private function getRoutes(): array {
        if(file_exists(self::ROUTE_CONFIG_PATH) === false) {
            throw new \RuntimeException("Le fichier de config des routes n'a pas été trouvé");
        }

        $routesConfigContent = file_get_contents(self::ROUTE_CONFIG_PATH);

        return json_decode($routesConfigContent, true, 512, JSON_THROW_ON_ERROR);
    }

    private function validateConfig(mixed $route): void {
        if(
            is_array($route) === false
            || is_string($route['uri'] ?? null) === false
            || is_string($route['method'] ?? null) === false
            || is_string($route['controller'] ?? null) === false
        ) {
            throw new \RuntimeException('Le fichier de config est mal écrit', 500);
        }
    }

    private function checkUri(Request $request, string $uri): bool {
        if (str_contains($uri, '{') === false) {
            return $request->getUri() === $uri;
        }

        $matches = [];
        if (preg_match($this->getUriRegex($uri), $request->getUri(), $matches) !== 1) {
            return false;
        }

        $params = [];
        foreach ($matches as $name => $value) {
            if (is_string($name)) {
                $params[$name] = rawurldecode($value);
            }
        }
        $request->setRouteParams($params);

        return true;
    }

    private function getUriRegex(string $uri): string {
        $parts = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $uri, -1, PREG_SPLIT_DELIM_CAPTURE);
        $regex = '';

        foreach ($parts as $part) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $name) === 1) {
                $regex .= '(?P<' . $name[1] . '>[^/]+)';
                continue;
            }
            $regex .= preg_quote($part, '#');
        }

        return '#^' . $regex . '$#';
    }

    private function checkMethod(Request $request, string $method): bool {
        return $request->getMethod() === strtoupper($method);
    }

    private function getValidControllerInstance(string $routeController): AbstractController {

        $controllerName = self::CONTROLLER_NAMESPACE . $routeController;

        if(class_exists($controllerName) === false) {
            throw new \RuntimeException('Controller ' . $controllerName . ' not found.');
        }

        $controllerInstance = new $controllerName();

        if(is_subclass_of($controllerInstance, AbstractController::class) === false) {
            throw new \RuntimeException('Controller ' . $controllerName . ' must extends Lib\Controllers\AbstractController.');
        }

        return $controllerInstance;
    }
}
