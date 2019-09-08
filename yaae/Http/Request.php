<?php


namespace YAAE\Http;


use YAAE\Router\RouteInfo;

class Request implements RequestInterface
{

    /**
     * @var RouteInfo
     */
    private $currentRoute;

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
        $this->headers = \getallheaders();
    }

    public function getCurrentRoute(): RouteInfo
    {
        return $this->currentRoute;
    }

    public function setCurrentRoute(RouteInfo $currentRoute)
    {
        $this->currentRoute = $currentRoute;
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