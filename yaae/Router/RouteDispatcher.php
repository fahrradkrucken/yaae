<?php


namespace YAAE\Router;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use YAAE\Http\HttpException;
use YAAE\Http\ResponseInterface;

class RouteDispatcher
{
    const STATUS_FOUND = 'FOUND';
    const STATUS_NOT_FOUND = 'NOT_FOUND';
    const STATUS_METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';

    /**
     * @var RouteGroup
     */
    private $routeTree;

    /**
     * @var RouteInfo[]
     */
    private $routeMap = [];

    /**
     * @var string
     */
    private $baseUrl = '';

    /**
     * @var string
     */
    private $httpMethod = '';

    public function __construct($routeTree, $baseUrl = '', $httpMethod = '')
    {
        $this->routeTree = $routeTree;
        $this->baseUrl = $baseUrl;
        $this->httpMethod = !empty($httpMethod) ? $httpMethod : $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return RouteInfo
     * @throws HttpException
     */
    public function dispatch()
    {
        $this->routeMap = [];
        $this->convertRouteTreeToMap($this->routeTree);
        $fastRouteDispatcher = \FastRoute\simpleDispatcher([$this, 'convertRouteInfoToFastRoute']);
        $fastRouteInfo = $fastRouteDispatcher->dispatch($this->httpMethod, rawurldecode($this->baseUrl));

        if ($fastRouteInfo[0] === Dispatcher::NOT_FOUND) {
            throw new HttpException('Not Found', ResponseInterface::STATUS_NOT_FOUND);
        } elseif ($fastRouteInfo[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new HttpException('Method Not Allowed', ResponseInterface::STATUS_NOT_ALLOWED_METHOD);
        } elseif ($fastRouteInfo[0] === Dispatcher::FOUND) {
            $routeId = $fastRouteInfo[1];
            $vars = $fastRouteInfo[2];
            $this->routeMap[$routeId]->params = $vars;
            return $this->routeMap[$routeId];
        }
        throw new HttpException('RouterError', ResponseInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param RouteGroup $routeGroup
     * @param string $pathPrefix
     */
    private function convertRouteTreeToMap($routeGroup, $pathPrefix = '')
    {
        foreach ($routeGroup->children as $route) {
            if (!empty($routeGroup->routeInfo->requestHandlers))
                foreach ($routeGroup->routeInfo->requestHandlers as $groupHandler)
                    $route->addRequestHandler($groupHandler);

            if (!empty($routeGroup->routeInfo->responseHandlers))
                foreach ($routeGroup->routeInfo->responseHandlers as $groupHandler)
                    $route->addResponseHandler($groupHandler);

            if ($route instanceof RouteGroup) {
                $this->convertRouteTreeToMap($route, $pathPrefix . $route->routeInfo->path);
            } elseif ($route instanceof $route) {
                $routePath = $pathPrefix . $route->routeInfo->path;
                $this->routeMap[$routePath] = clone $route->routeInfo;
                $this->routeMap[$routePath]->path = $routePath;
            }
        }
    }

    public function convertRouteInfoToFastRoute(RouteCollector $r)
    {
        foreach ($this->routeMap as $routeInfo) {
            switch ($routeInfo->method) {
                case Route::METHOD_GET:
                    $r->get($routeInfo->path, $routeInfo->path);
                    break;
                case Route::METHOD_POST:
                    $r->post($routeInfo->path, $routeInfo->path);
                    break;
                case Route::METHOD_PUT:
                    $r->put($routeInfo->path, $routeInfo->path);
                    break;
                case Route::METHOD_PATCH:
                    $r->patch($routeInfo->path, $routeInfo->path);
                    break;
                case Route::METHOD_ANY:
                    $r->addRoute(['GET', 'POST', 'PUT', 'PATCH'], $routeInfo->path, $routeInfo->path);
                    break;
            }
        }
    }
}