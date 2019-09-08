<?php


namespace YAAE\Router;


class RouteInfo
{
    /**
     * @var string
     */
    public $method = Route::METHOD_ANY;

    /**
     * @var string
     */
    public $path = '';

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var array
     */
    public $requestHandlers = [];

    /**
     * @var array
     */
    public $responseHandlers = [];

    /**
     * @var callable
     */
    public $handler = null;
}