<?php

namespace FahrradKrucken\YAAE;

use FahrradKrucken\YAAE\Core\CallableHandler;
use FahrradKrucken\YAAE\Core\RouteHandler;
use FahrradKrucken\YAAE\Core\RouteHandlerInterface;
use FahrradKrucken\YAAE\Http\Request;
use FahrradKrucken\YAAE\Http\RequestInterface;
use FahrradKrucken\YAAE\Http\Response;
use FahrradKrucken\YAAE\Http\ResponseInterface;
use FahrradKrucken\YAAE\Http\Error;

class Engine
{
    /**
     * @var RouteHandlerInterface
     */
    private $routeHandler;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var callable
     */
    private $errorHandler;
    /**
     * @var callable
     */
    private $errorHandlerHttp;

    public function __construct()
    {
        $this->routeHandler = new RouteHandler();
        $this->request = new Request();
        $this->response = new Response();
        $this->errorHandler = [$this, 'errorHandlerDefault'];
        $this->errorHandlerHttp = [$this, 'errorHandlerDefault'];
    }

    public function setRouteHandler(RouteHandlerInterface $routeHandler)
    {
        $this->routeHandler = $routeHandler;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function setErrorHandler(callable $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function setErrorHandlerHttp(callable $errorHandler)
    {
        $this->errorHandlerHttp = $errorHandler;
    }

    public function setRequestPath(string $requestPath)
    {
        $this->routeHandler->setRequestPath($requestPath);
    }

    public function setRequestMethod(string $requestMethod)
    {
        $this->routeHandler->setRequestMethod($requestMethod);
    }

    public function addRoutes(array $routes)
    {
        $this->routeHandler->addRoutes($routes);
    }

    public function errorHandlerDefault(\Throwable $error)
    {
        if (($error instanceof Error) && ($error->response instanceof ResponseInterface)) {
            $this->sendResponse($error->response);
        } else {
            echo 'Error Code:' . $error->getCode() . '<br>';
            echo 'Error Message:' . $error->getMessage() . '<br>';
            echo 'Error Trace:' . $error->getTraceAsString() . '<br>';
            die();
        }
    }

    public function start()
    {
        $this->routeHandler->dispatch();

        try {
            $this->request->setRouteInfo([
                'request_path'   => $this->routeHandler->getRequestPath(),
                'request_method' => $this->routeHandler->getRequestMethod(),
                'arguments'      => $this->routeHandler->getCurrentRouteArguments(),
            ]);

            switch ($this->routeHandler->getCurrentRouteStatus()) {

                case RouteHandlerInterface::STATUS_NOT_FOUND:
                    $error = new Error();
                    $this->response->setStatus(ResponseInterface::STATUS_NOT_FOUND);
                    $this->response->setData('NOT FOUND');
                    $error->request = $this->request;
                    $error->response = $this->response;
                    throw $error;
                    break;

                case RouteHandlerInterface::STATUS_METHOD_NOT_ALLOWED:
                    $error = new Error();
                    $this->response->setStatus(ResponseInterface::STATUS_NOT_ALLOWED_METHOD);
                    $this->response->setData('METHOD NOT ALLOWED');
                    $error->request = $this->request;
                    $error->response = $this->response;
                    throw $error;
                    break;

                case RouteHandlerInterface::STATUS_FOUND:
                    $this->handleHttpRequest();
                    break;
            }
        } catch (\Throwable $httpError) {
            if ($httpError instanceof Error) call_user_func($this->errorHandlerHttp, $httpError);
            else call_user_func($this->errorHandler, $httpError);
        }
    }

    private function handleHttpRequest()
    {
        // request handlers
        $requestHandlers = $this->routeHandler->getCurrentRouteRequestCallbacks();
        if (!empty($requestHandlers)) {
            foreach ($requestHandlers as $handler) {
                if ($requestNew = CallableHandler::tryHandleCallableWithArguments($handler, [$this->request])) {
                    $this->request = $requestNew;
                }
            }
        }

        // route handler
        $httpHandler = $this->routeHandler->getCurrentRouteCallback();
        if ($responseNew = CallableHandler::tryHandleCallableWithArguments($httpHandler, [$this->request, $this->response])) {
            $this->response = $responseNew;
        }

        // response handlers
        $responseHandlers = $this->routeHandler->getCurrentRouteResponseCallbacks();
        if (!empty($responseHandlers)) {
            foreach ($responseHandlers as $handler) {
                if ($responseNew = CallableHandler::tryHandleCallableWithArguments($handler, [$this->response])) {
                    $this->response = $responseNew;
                }
            }
        }

        // send response

        $this->sendResponse($this->response);
    }

    private function sendResponse(ResponseInterface $response)
    {
        if (!empty(ob_get_contents())) ob_clean();

        http_response_code($response->getStatus());
        if ($responseHeaders = $response->getHeaders()) {
            foreach ($responseHeaders as $responseHeaderName => $responseHeaderValue) {
                header($responseHeaderName . ': ' . $responseHeaderValue, true);
            }
        }

        echo $response->getData();

        die();
    }
}
