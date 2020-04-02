<?php

namespace FahrradKrucken\YAAE;

use FahrradKrucken\YAAE\Core\CallableHandler;
use FahrradKrucken\YAAE\Core\Route;
use FahrradKrucken\YAAE\Core\RouteHandler;
use FahrradKrucken\YAAE\Core\RouteHandlerInterface;
use FahrradKrucken\YAAE\Http\Request;
use FahrradKrucken\YAAE\Http\RequestInterface;
use FahrradKrucken\YAAE\Http\Response;
use FahrradKrucken\YAAE\Http\ResponseInterface;
use FahrradKrucken\YAAE\Http\Error;

/**
 * Class Engine
 * @package FahrradKrucken\YAAE
 */
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

    /**
     * Replace default route handler, if you need it
     *
     * @param RouteHandlerInterface $routeHandler
     */
    public function setRouteHandler(RouteHandlerInterface $routeHandler)
    {
        $this->routeHandler = $routeHandler;
    }

    /**
     * Replace default Request object
     *
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Replace default Response object
     *
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Set default error handler
     *
     * @param callable $errorHandler - should accept \Throwable
     */
    public function setErrorHandler(callable $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Set default Error handler
     *
     * @param callable $errorHandler - should accept \FahrradKrucken\YAAE\Http\Error
     */
    public function setErrorHandlerHttp(callable $errorHandler)
    {
        $this->errorHandlerHttp = $errorHandler;
    }

    /**
     * @param string $requestPath - request path for router
     */
    public function setRequestPath(string $requestPath)
    {
        $this->routeHandler->setRequestPath($requestPath);
    }

    /**
     * @param string $requestMethod - request method for router
     */
    public function setRequestMethod(string $requestMethod)
    {
        $this->routeHandler->setRequestMethod($requestMethod);
    }

    /**
     * @param Route[] $routes - array of \FahrradKrucken\YAAE\Core\Route objects
     */
    public function addRoutes(array $routes)
    {
        $this->routeHandler->addRoutes($routes);
    }

    /**
     * @param \Throwable $error - Default error handler
     */
    public function errorHandlerDefault(\Throwable $error)
    {
        if (($error instanceof Error) && ($error->response instanceof ResponseInterface)) {
            self::sendResponse($error->response);
        } else {
            echo 'Error Code:' . $error->getCode() . '<br>';
            echo 'Error Message:' . $error->getMessage() . '<br>';
            echo 'Error Trace:' . $error->getTraceAsString() . '<br>';
            die();
        }
    }

    /**
     * Start dispatching routes (start the whole application)
     */
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
                    $error = new Error('NOT FOUND', ResponseInterface::STATUS_NOT_FOUND);
                    $this->response->setStatus(ResponseInterface::STATUS_NOT_FOUND);
                    $this->response->setData('NOT FOUND');
                    $error->request = $this->request;
                    $error->response = $this->response;
                    throw $error;
                    break;

                case RouteHandlerInterface::STATUS_METHOD_NOT_ALLOWED:
                    $error = new Error('METHOD NOT ALLOWED', ResponseInterface::STATUS_NOT_ALLOWED_METHOD);
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
                $requestNew = CallableHandler::tryHandleCallableWithArguments($handler, [$this->request]);
                if ($requestNew && $requestNew instanceof RequestInterface) {
                    $this->request = $requestNew;
                }
            }
        }

        // route handler
        $httpHandler = $this->routeHandler->getCurrentRouteCallback();
        $responseNew = CallableHandler::tryHandleCallableWithArguments($httpHandler, [$this->request, $this->response]);
        if ($responseNew && $responseNew instanceof ResponseInterface) {
            $this->response = $responseNew;
        }

        // response handlers
        $responseHandlers = $this->routeHandler->getCurrentRouteResponseCallbacks();
        if (!empty($responseHandlers)) {
            foreach ($responseHandlers as $handler) {
                $responseNew = CallableHandler::tryHandleCallableWithArguments($handler, [$this->response]);
                if ($responseNew && $responseNew instanceof ResponseInterface) {
                    $this->response = $responseNew;
                }
            }
        }

        // send response
        self::sendResponse($this->response);
    }

    /**
     * Send response and stop the whole script
     *
     * @param ResponseInterface $response
     */
    public static function sendResponse(ResponseInterface $response)
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
