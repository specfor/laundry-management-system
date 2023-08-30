<?php

namespace LogicLeap\PhpServerCore;

use LogicLeap\PhpServerCore\exceptions\NotFoundException;
use LogicLeap\StockManagement\models\API;

class Router
{
    public Request $request;
    protected static array $routes = [];
    protected static array $wildCardRoutes = [];

    /**
     * Create a new Router instance
     * @param Request $request instance of Request class
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Create a new "GET" route.
     * @param string $path path for the route.
     * @param array $callback array of the controller class and name of the relevant page.
     * ex :- [SiteController::class, 'home']
     */
    public function addGetRoute(string $path, array $callback): void
    {
        if (str_contains($path, '$')) {
            self::$wildCardRoutes['get'][$path] = $callback;
            $callback[1] = "optionsRequest";
            self::$wildCardRoutes['options'][$path] = $callback;
        } else {
            self::$routes['get'][$path] = $callback;
            $callback[1] = "optionsRequest";
            self::$routes['options'][$path] = $callback;
        }
    }

    /**
     * Create a new "POST" route.
     * @param string $path - url path for the route.
     * @param array $callback - array of the controller class and name of the relevant page.
     * ex :- [SiteController::class, 'contact']
     */
    public function addPostRoute(string $path, array $callback): void
    {
        if (str_contains($path, '$')) {
            self::$wildCardRoutes['post'][$path] = $callback;
            $callback[1] = "optionsRequest";
            self::$wildCardRoutes['options'][$path] = $callback;
        } else {
            self::$routes['post'][$path] = $callback;
            $callback[1] = "optionsRequest";
            self::$routes['options'][$path] = $callback;
        }
    }

    /**
     * When user made a request, request path is refined and call the relevant functions
     * with parameters.
     * @throws NotFoundException throw code 404 exception
     */
    public function resolveRoute(): void
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = self::$routes[$method][$path] ?? false;

        $arguments = [];

        if ($callback === false) {
            $found = false;
            foreach (self::$wildCardRoutes as $routeMethod => $wildCardRoute) {
                if ($method === $routeMethod) {
                    foreach ($wildCardRoute as $pathRegex => $callback_) {
                        $pathRegex = str_replace('/', '\/', $pathRegex);
                        $pathRegex = str_replace('$', '([\w\.-]{1,})', $pathRegex);
                        $pathRegex = "/^$pathRegex$/";
                        if (preg_match($pathRegex, $path, $matches)) {
                            unset($matches[0]);
                            $arguments = array_values($matches);
                            $callback = $callback_;
                            $found = true;
                            break;
                        }
                    }
                }
                if ($found)
                    break;
            }
        }
        if (is_array($callback)) {
            $controller = new $callback[0];
            if ($arguments)
                $controller->{$callback[1]}($arguments);
            else
                $controller->{$callback[1]}();
        } elseif ($callback === false) {
            $pathPieces = explode('/', $path);
            if ($pathPieces[0] === 'api') {
                self::endPointNotFound();
            } else {
                throw new NotFoundException();
            }
        }
    }

    public static function endPointNotFound(): void
    {
        $finalPayload = [
            'statusCode' => API::STATUS_CODE_NOTFOUND,
            'statusMessage' => API::STATUS_MSG_NOTFOUND,
            'body' => ['error' => 'Requested API endpoint was not found.']
        ];
        echo json_encode($finalPayload);
    }

}