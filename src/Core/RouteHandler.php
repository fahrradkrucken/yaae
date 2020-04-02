<?php


namespace FahrradKrucken\YAAE\Core;


class RouteHandler implements RouteHandlerInterface
{
    /**
     * @var string
     * Current request's path/query, with '/' at the start and without '/' at the end.
     * Default = $_SERVER['QUERY_STRING']
     */
    protected $requestPath = '';

    /**
     * @var string
     * Current request's method, in uppercase.
     * Default = $_SERVER['REQUEST_METHOD']
     */
    protected $requestMethod = '';

    /**
     * @var array
     * Routes multidimensional array.
     */
    protected $routes = [];

    /**
     * @var array
     */
    protected $currentRoute = [];

    /**
     * Router constructor.
     *
     * @param string $requestPath
     * @param string $requestMethod
     */
    public function __construct(string $requestPath = '', string $requestMethod = '')
    {
        $this->setRequestPath($requestPath);
        $this->setRequestMethod($requestMethod);
    }

    /**
     * @inheritDoc
     */
    public function setRequestPath(string $requestPath = '')
    {
        $requestPath = !empty($requestPath) ? $requestPath : $_SERVER['REQUEST_URI'];
        $this->requestPath = '/' . trim(explode('?', $requestPath)[0], ' /');
    }

    /**
     * @inheritDoc
     */
    public function getRequestPath(): string
    {
        return $this->requestPath;
    }

    /**
     * @param string $requestMethod
     */
    public function setRequestMethod(string $requestMethod = '')
    {
        $this->requestMethod = !empty($requestMethod) ?
            strtoupper($requestMethod) :
            strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @inheritDoc
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * @inheritDoc
     */
    public function addRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @inheritDoc
     */
    public function dispatch()
    {
        $routesList = $this->routesArrayToRoutesList($this->routes); // Routes array to flat list
        $routesList = $this->routesListFormat($routesList); // Format Routes to compare them later

        // Current Route schema
        $currentRoute = [
            'status'                   => self::STATUS_NOT_FOUND,
            'request_path'             => $this->requestPath,
            'request_method'           => $this->requestMethod,
            'route_args'               => [],
            'route_callback'           => null,
            'route_request_callbacks'  => [],
            'route_response_callbacks' => [],
        ];

        // Check Routes
        foreach ($routesList as $route) {
            if ($route['direct_comparison']) { // Compare routes directly
                if ($route['path'] === $this->requestPath) {
                    $currentRoute['route_callback'] = $route['callback'];
                    $currentRoute['route_request_callbacks'] = $route['request_callbacks'];
                    $currentRoute['route_response_callbacks'] = $route['response_callbacks'];
                    if (in_array($this->requestMethod, $route['methods'])) {
                        $currentRoute['status'] = self::STATUS_FOUND;
                        break;
                    }
                    $currentRoute['status'] = self::STATUS_METHOD_NOT_ALLOWED;
                }
            } else { // Compare routes through RegEx
                if (preg_match($route['pattern'], $this->requestPath, $routeArgsMatches) !== false) {
                    if (!empty($routeArgsMatches) && is_array($routeArgsMatches)) {
                        $currentRoute['route_callback'] = $route['callback'];
                        $currentRoute['route_request_callbacks'] = $route['request_callbacks'];
                        $currentRoute['route_response_callbacks'] = $route['response_callbacks'];
                        foreach ($routeArgsMatches as $routeArgName => $routeArgVal) // Extract named 'route_args'
                            if (is_string($routeArgName))
                                $currentRoute['route_args'][$routeArgName] = $routeArgVal;
                        if (in_array($this->requestMethod, $route['methods'])) {
                            $currentRoute['status'] = self::STATUS_FOUND;
                            break;
                        }
                        $currentRoute['status'] = self::STATUS_METHOD_NOT_ALLOWED;
                    }
                }
            }
        }

        $this->currentRoute = $currentRoute;
    }

    /**
     * @param Route[] $routesArray
     * @param string  $routePath
     * @param array   $routeCallbacksBefore
     * @param array   $routeCallbacksAfter
     *
     * @return array - Flat array, created from the multi-dimensional routes array
     */
    protected function routesArrayToRoutesList(
        array $routesArray, string $routePath = '', array $routeCallbacksBefore = [], array $routeCallbacksAfter = []
    ): array
    {
        $routesList = [];
        foreach ($routesArray as $route) {
            if (!empty($route->routeInfo['routes'])) {
                $routesList = array_merge(
                    $routesList,
                    $this->routesArrayToRoutesList(
                        $route->routeInfo['routes'],
                        $routePath . $route->routeInfo['path'],
                        $route->routeInfo['request_callbacks'],
                        $route->routeInfo['response_callbacks']
                    )
                );
            } else {
                $routesListItem = $route->routeInfo;
                $routesListItem['path'] = $routePath . $route->routeInfo['path'];
                $routesListItem['request_callbacks'] = array_merge($route->routeInfo['request_callbacks'], $routeCallbacksBefore);
                $routesListItem['response_callbacks'] = array_merge($route->routeInfo['response_callbacks'], $routeCallbacksAfter);
                $routesList[] = $routesListItem;
            }
        }
        return $routesList;
    }

    /**
     * @param array $routesList
     *
     * @return array|mixed
     */
    protected function routesListFormat(array $routesList): array
    {
        return array_map(function ($route) {
            // Fix request_callbacks order (parent goes first)
            if (!empty($route['request_callbacks']))
                $route['request_callbacks'] = array_reverse($route['request_callbacks']);
            // Route should be checked directly (by default)
            $route['direct_comparison'] = true;
            // Route has named args?
            if (strpos($route['path'], '{') !== false) {
                // In this case route should be checked through regex
                $route['direct_comparison'] = false;
                // Create route regex 'pattern' (and extract named args)
                $route['pattern'] = preg_replace_callback('/({[a-z0-9_]+})/', function ($match) {
                    return "(?'" . trim($match[0], '{}') . "'[a-z0-9\-]+)";
                }, $route['path']);
                $route['pattern'] = '/' . str_replace('/', '\/', $route['pattern']) . '/';
            } else {
                // Fix for route groups, where we want to define main route of the group
                $route['path'] = rtrim($route['path'], '/');
            }
            return $route;
        }, $routesList);
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRouteStatus(): string
    {
        return $this->currentRoute['status'];
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRoutePath(): string
    {
        return $this->currentRoute['request_path'];
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRouteArguments(): array
    {
        return $this->currentRoute['route_args'];
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRouteCallback()
    {
        return $this->currentRoute['route_callback'];
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRouteRequestCallbacks(): array
    {
        return $this->currentRoute['route_request_callbacks'];
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRouteResponseCallbacks(): array
    {
        return $this->currentRoute['route_response_callbacks'];
    }
}