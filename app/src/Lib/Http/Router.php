<?php

namespace App\Lib\Http;

use App\Lib\Controllers\AbstractController;

class Router {

    private const ROUTE_CONFIG_PATH = __DIR__ . '/../../../config/routes.json';
    private const CONTROLLER_NAMESPACE = 'App\\Controllers\\';

    // Traite la requête entrante et appelle le contrôleur correspondant
    public function main(Request $request): Response {
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
            $response->content = json_encode(['error' => 'invalid method']);

            return $response;
        }

        $response->statusCode = 404;
        $response->content = json_encode(['error' => 'route not found']);

        return $response;
    }

    private function getRoutes(): array {
        if (file_exists(self::ROUTE_CONFIG_PATH) === false) {
            echo "Le fichier de config des routes n'a pas été trouvé";
            exit();
        }

        $routesConfigContent = file_get_contents(self::ROUTE_CONFIG_PATH);

        return json_decode($routesConfigContent, true, 512, JSON_THROW_ON_ERROR);
    }

    private function validateConfig(array $route): void {
        if (
            array_key_exists('uri', $route) === false ||
            array_key_exists('method', $route) === false ||
            array_key_exists('controller', $route) === false
        ) {
            throw new \Exception('Le fichier de config est mal écrit', 500);
        }
    }

    // Vérifie si l'URI correspond à la route et extrait les paramètres dynamiques
    private function checkUri(Request $request, string $uri): bool {
        $requestPath = parse_url($request->getUri(), PHP_URL_PATH) ?: '/';
        if ($requestPath !== '/') {
            $requestPath = rtrim($requestPath, '/');
        }

        $routeUri = $uri !== '/' ? rtrim($uri, '/') : '/';

        if (
            strpos($routeUri, '{') === false &&
            strpos($routeUri, '<') === false &&
            strpos($routeUri, ':') === false
        ) {
            if ($requestPath === $routeUri) {
                $request->setPathParams([]);
                return true;
            }
            return false;
        }

        $pattern = preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)\}|<([a-zA-Z0-9_]+)>|:([a-zA-Z0-9_]+)/',
            static function (array $matches): string {
                $paramName = $matches[1] ?: ($matches[2] ?: $matches[3]);
                return '(?P<' . $paramName . '>[^/]+)';
            },
            $routeUri
        );
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = rawurldecode($value);
                }
            }
            $request->setPathParams($params);
            return true;
        }

        return false;
    }

    private function checkMethod(Request $request, string $method): bool {
        return $request->getMethod() === $method;
    }

    private function getValidControllerInstance(string $routeController): AbstractController {
        $controllerName = self::CONTROLLER_NAMESPACE . $routeController;

        if (class_exists($controllerName) === false) {
            throw new \Exception('Controller ' . $controllerName . ' not found.');
        }

        $controllerInstance = new $controllerName();

        if (is_subclass_of($controllerInstance, AbstractController::class) === false) {
            throw new \Exception('Controller ' . $controllerName . ' must extends App\Lib\Controllers\AbstractController.');
        }

        return $controllerInstance;
    }
}
