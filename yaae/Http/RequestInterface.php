<?php


namespace YAAE\Http;


use YAAE\Router\RouteInfo;

interface RequestInterface
{
    public function getCurrentRoute(): RouteInfo;

    public function setCurrentRoute(RouteInfo $currentRoute);

    public function getHeaders(): array;

    public function getHeader(string $headerName): string;

    public function hasHeader(string $headerName): bool;

    public function setHeader(string $headerName, $headerValue);

    public function getData();

    public function setData($value);
}