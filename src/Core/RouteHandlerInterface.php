<?php


namespace FahrradKrucken\YAAE\Core;


interface RouteHandlerInterface
{
    /**
     * Routing dispatch statuses
     */
    const
        STATUS_FOUND = 'STATUS_FOUND',
        STATUS_NOT_FOUND = 'STATUS_NOT_FOUND',
        STATUS_METHOD_NOT_ALLOWED = 'STATUS_METHOD_NOT_ALLOWED';

    public function setRequestPath(string $requestPath);

    public function getRequestPath(): string;

    public function setRequestMethod(string $requestMethod);

    public function getRequestMethod(): string;

    public function addRoutes(array $routes);

    public function dispatch();

    public function getCurrentRouteStatus(): string;

    public function getCurrentRoutePath(): string;

    public function getCurrentRouteArguments(): ?array;

    public function getCurrentRouteCallback();

    public function getCurrentRouteRequestCallbacks(): ?array;

    public function getCurrentRouteResponseCallbacks(): ?array;
}