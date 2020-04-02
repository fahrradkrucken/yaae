<?php


namespace FahrradKrucken\YAAE\Http;


interface RequestInterface
{
    /**
     * @return array
     */
    public function getRouteInfo(): array;

    /**
     * @param array $routeInfo
     *
     * @return mixed
     */
    public function setRouteInfo(array $routeInfo);

    /**
     * @return array - HTTP Headers
     */
    public function getHeaders(): array;

    /**
     * @param string $headerName
     *
     * @return string - header value
     */
    public function getHeader(string $headerName): string;

    /**
     * @param string $headerName
     *
     * @return bool
     */
    public function hasHeader(string $headerName): bool;

    /**
     * @param string           $headerName
     * @param string|float|int $headerValue
     */
    public function setHeader(string $headerName, $headerValue);

    /**
     * @return null|array|string
     */
    public function getData();

    /**
     * @param mixed $value
     */
    public function setData($value);
}