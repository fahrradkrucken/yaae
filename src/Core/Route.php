<?php

namespace FahrradKrucken\YAAE\Core;

/**
 * Class Route
 * @package FahrradKrucken\YAAE\Core
 */
class Route
{
    const
        METHOD_GET = 'GET',
        METHOD_POST = 'POST',
        METHOD_PUT = 'PUT',
        METHOD_PATCH = 'PATCH',
        METHOD_DELETE = 'DELETE';

    /**
     * @var array Routes information, needed for Router handler
     */
    public $routeInfo = [
        'methods'            => [],
        'path'               => [],
        'callback'           => null,
        'request_callbacks'  => [],
        'response_callbacks' => [],
        'routes'             => [],
    ];

    /**
     * Route constructor.
     *
     * @param array $routeInfo
     */
    public function __construct(array $routeInfo = [])
    {
        if (!empty($routeInfo)) {
            $this->routeInfo = [
                'methods'            => !empty($routeInfo['methods']) && is_array($routeInfo['methods']) ?
                    array_map('strtoupper', $routeInfo['methods']) :
                    [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE],
                'path'               => '/' . trim($routeInfo['path'], ' /'),
                'callback'           => $routeInfo['callback'],
                'request_callbacks'  => is_array($routeInfo['callbacksBefore']) ?
                    $routeInfo['callbacksBefore'] :
                    [$routeInfo['callbacksBefore']],
                'response_callbacks' => is_array($routeInfo['callbacksAfter']) ?
                    $routeInfo['callbacksAfter'] :
                    [$routeInfo['callbacksAfter']],
                'routes'             => !empty($routeInfo['routes']) ?
                    $routeInfo['routes'] :
                    [],
            ];
        }
    }

    /**
     * @param array           $methods
     * @param string          $path
     * @param string|callable $callback - accepts FahrradKrucken\YAAE\Http\RequestInterface and FahrradKrucken\YAAE\Http\ResponseInterface
     *
     * @return self
     */
    public static function new(array $methods, string $path, $callback): self
    {
        return new self([
            'methods'  => $methods,
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param string  $path
     * @param Route[] $routes
     *
     * @return self
     */
    public static function group(string $path, array $routes): self
    {
        return new self([
            'path'   => $path,
            'routes' => $routes,
        ]);
    }

    /**
     * @param string          $path
     * @param string|callable $callback
     *
     * @return self
     */
    public static function get(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_GET],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param string          $path
     * @param string|callable $callback
     *
     * @return self
     */
    public static function post(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_POST],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param string          $path
     * @param string|callable $callback
     *
     * @return self
     */
    public static function put(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_PUT],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param string          $path
     * @param string|callable $callback
     *
     * @return self
     */
    public static function patch(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_PATCH],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param string          $path
     * @param string|callable $callback
     *
     * @return self
     */
    public static function delete(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_DELETE],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param callable|string $callback - accepts \FahrradKrucken\YAAE\Http\RequestInterface
     *
     * @return self
     */
    public function addRequestCallback($callback)
    {
        $this->routeInfo['request_callbacks'][] = $callback;
        return $this;
    }

    /**
     * @param callable|string $callback - accepts \FahrradKrucken\YAAE\Http\ResponseInterface
     *
     * @return self
     */
    public function addResponseCallback($callback)
    {
        $this->routeInfo['response_callbacks'][] = $callback;
        return $this;
    }
}