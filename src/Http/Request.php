<?php

namespace FahrradKrucken\YAAE\Http;

use FahrradKrucken\YAAE\Engine;

/**
 * Class Request
 * @package FahrradKrucken\YAAE\Http
 */
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

    /**
     * Request constructor.
     */
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

    /**
     * @return array [
     * @var $reqiest_path string - Current request path
     * @var $reqiest_method string - Current request method
     * @var $arguments string - Current route's arguments (ex: "api/route/{foo}/{bar}" => ["foo" => "var", "bar" => ""])
     * ]
     *
     * @see Engine
     */
    public function getRouteInfo(): array
    {
        return $this->routeInfo;
    }

    /**
     * @param array $routeInfo
     */
    public function setRouteInfo(array $routeInfo)
    {
        $this->routeInfo = $routeInfo;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $headerName): string
    {
        return isset($this->headers[$headerName]) ? $this->headers[$headerName] : null;
    }


    /**
     * @inheritDoc
     */
    public function hasHeader(string $headerName): bool
    {
        return !empty($this->headers[$headerName]);
    }

    /**
     * @inheritDoc
     */
    public function setHeader(string $headerName, $headerValue)
    {
        $this->headers[$headerName] = $headerValue;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function setData($value)
    {
        $this->data = $value;
    }
}