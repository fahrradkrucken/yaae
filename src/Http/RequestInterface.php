<?php


namespace FahrradKrucken\YAAE\Http;


interface RequestInterface
{
    public function getRouteInfo(): array ;

    public function setRouteInfo(array $routeInfo);

    public function getHeaders(): array;

    public function getHeader(string $headerName): string;

    public function hasHeader(string $headerName): bool;

    public function setHeader(string $headerName, $headerValue);

    public function getData();

    public function setData($value);
}