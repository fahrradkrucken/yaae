<?php

namespace FahrradKrucken\YAAE\Http;

class Request implements RequestInterface
{
    /**
     * @var array
     */
    private $routeInfo = [];

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var null
     */
    private $data = null;

    public function __construct()
    {
        // Init Request Data
        $input = $_REQUEST;
        if (empty($input)) {
            $input = file_get_contents('php://input');
            $this->setData($input);
        } else {
            $params = [];
            foreach ($input as $paramName => $paramValue) {
                if (is_string($paramValue))
                    $params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_STRING);
                elseif (is_int($paramValue))
                    $params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_NUMBER_INT);
                elseif (is_float($paramValue))
                    $params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_NUMBER_FLOAT);
                elseif (is_numeric($paramValue))
                    $params[$paramName] = filter_var($paramValue, FILTER_SANITIZE_NUMBER_INT);
                elseif (is_array($paramValue))
                    $params[$paramName] = filter_var_array($paramValue);
                else
                    $params[$paramName] = filter_var($paramValue, FILTER_DEFAULT, ['options' => ['default' => '']]);
            }
            $this->setData($params);
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        $this->headers = $headers;
    }

    public function getRouteInfo(): array
    {
        return $this->routeInfo;
    }

    public function setRouteInfo(array $routeInfo)
    {
        $this->routeInfo = $routeInfo;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $headerName): string
    {
        return isset($this->headers[$headerName]) ? $this->headers[$headerName] : null;
    }

    public function hasHeader(string $headerName): bool
    {
        return !empty($this->headers[$headerName]);
    }

    public function setHeader(string $headerName, $headerValue)
    {
        $this->headers[$headerName] = $headerValue;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($value)
    {
        $this->data = $value;
    }
}