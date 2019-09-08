<?php


namespace YAAE\Router;


class Route
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_ANY = 'ANY';

    /**
     * @var null | RouteInfo
     */
    public $routeInfo = null;

    /**
     * Route constructor.
     *
     * @param string   $method
     * @param string   $path
     * @param callable $handler
     */
    public function __construct(string $method = self::METHOD_ANY, string $path = '', $handler = null)
    {
        $this->routeInfo = new RouteInfo();
        $this->routeInfo->method = $method;
        $this->routeInfo->path = $path;
        $this->routeInfo->handler = $handler;
    }

    /**
     * @param $handler
     *
     * @return $this
     */
    public function addRequestHandler($handler)
    {
        $this->routeInfo->requestHandlers[] = $handler;
        return $this;
    }

    /**
     * @param $handler
     *
     * @return $this
     */
    public function addResponseHandler($handler)
    {
        $this->routeInfo->responseHandlers[] = $handler;
        return $this;
    }
}