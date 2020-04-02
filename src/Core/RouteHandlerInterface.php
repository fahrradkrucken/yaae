<?php


namespace FahrradKrucken\YAAE\Core;

/**
 * Interface RouteHandlerInterface
 * @package FahrradKrucken\YAAE\Core
 */
interface RouteHandlerInterface
{
    /**
     * Routing dispatch statuses
     */
    const
        STATUS_FOUND = 'STATUS_FOUND',
        STATUS_NOT_FOUND = 'STATUS_NOT_FOUND',
        STATUS_METHOD_NOT_ALLOWED = 'STATUS_METHOD_NOT_ALLOWED';

    /**
     * @param string $requestPath
     */
    public function setRequestPath(string $requestPath);

    /**
     * @return string
     */
    public function getRequestPath(): string;

    /**
     * @param string $requestMethod
     */
    public function setRequestMethod(string $requestMethod);

    /**
     * @return string
     */
    public function getRequestMethod(): string;

    /**
     * @param array $routes
     *
     * @return mixed
     */
    public function addRoutes(array $routes);

    /**
     * Start dispatching added routes
     */
    public function dispatch();

    /**
     * @return string
     */
    public function getCurrentRouteStatus(): string;

    /**
     * @return string
     */
    public function getCurrentRoutePath(): string;

    /**
     * @return array
     */
    public function getCurrentRouteArguments(): array;

    /**
     * @return string|callable
     */
    public function getCurrentRouteCallback();

    /**
     * @return string[]|callable[]
     */
    public function getCurrentRouteRequestCallbacks(): array;

    /**
     * @return string[]|callable[]
     */
    public function getCurrentRouteResponseCallbacks(): array;
}