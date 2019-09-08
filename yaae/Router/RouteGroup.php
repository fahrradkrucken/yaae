<?php


namespace YAAE\Router;


class RouteGroup extends Route
{
    /**
     * @var Route[]|RouteGroup[]
     */
    public $children = [];

    /**
     * @param string $method
     * @param string $path
     * @param string $handler
     *
     * @return Route
     */
    private function addRoute(string $method, string $path, $handler) : Route
    {
        $newRoute = new Route($method, $path, $handler);
        $newRouteId = md5(count($this->children) . $method . $path);
        $this->children[$newRouteId] = $newRoute;
        return $this->children[$newRouteId];
    }

    /**
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function any(string $path, $handler): Route
    {
        return $this->addRoute(self::METHOD_ANY, $path, $handler);
    }

    /**
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function get(string $path, $handler): Route
    {
        return $this->addRoute(self::METHOD_GET, $path, $handler);
    }

    /**
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function post(string $path, $handler): Route
    {
        return $this->addRoute(self::METHOD_POST, $path, $handler);
    }

    /**
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function put(string $path, $handler): Route
    {
        return $this->addRoute(self::METHOD_PUT, $path, $handler);
    }

    /**
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function patch(string $path, $handler): Route
    {
        return $this->addRoute(self::METHOD_PATCH, $path, $handler);
    }

    /**
     * @param string $path
     * @param        $handler
     *
     * @return Route
     */
    public function delete(string $path, $handler): Route
    {
        return $this->addRoute(self::METHOD_DELETE, $path, $handler);
    }

    /**
     * @param string $path
     * @param        $groupHandler
     *
     * @return RouteGroup
     */
    public function group(string $path, $groupHandler) : RouteGroup
    {
        $newGroup = new RouteGroup(self::METHOD_ANY, $path);
        $newGroupId = md5(count($this->children) . $path);
        if (is_callable($groupHandler)) {
            call_user_func($groupHandler, $newGroup);
        }
        $this->children[$newGroupId] = $newGroup;
        return $this->children[$newGroupId];
    }
}