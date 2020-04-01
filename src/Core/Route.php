<?php

namespace FahrradKrucken\YAAE\Core;


class Route
{
    const
        METHOD_GET = 'GET',
        METHOD_POST = 'POST',
        METHOD_PUT = 'PUT',
        METHOD_PATCH = 'PATCH',
        METHOD_DELETE = 'DELETE';

    public $routeInfo = [
        'methods'            => [],
        'path'               => [],
        'callback'           => null,
        'request_callbacks'  => [],
        'response_callbacks' => [],
        'routes'             => [],
    ];

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

    public static function new(array $methods, string $path, $callback): self
    {
        return new self([
            'methods'  => $methods,
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    public static function group(string $path, array $routes): self
    {
        return new self([
            'path'     => $path,
            'routes' => $routes,
        ]);
    }

    public static function get(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_GET],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    public static function post(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_POST],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    public static function put(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_PUT],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    public static function patch(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_PATCH],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    public static function delete(string $path, $callback): self
    {
        return new self([
            'methods'  => [self::METHOD_DELETE],
            'path'     => $path,
            'callback' => $callback,
        ]);
    }

    /**
     * @param callable|string $callback
     *
     * @return $this
     */
    public function addRequestCallback($callback)
    {
        $this->routeInfo['request_callbacks'][] = $callback;
        return $this;
    }

    /**
     * @param callable|string $callback
     *
     * @return $this
     */
    public function addResponseCallback($callback)
    {
        $this->routeInfo['response_callbacks'][] = $callback;
        return $this;
    }
}