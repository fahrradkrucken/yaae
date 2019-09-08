<?php


namespace YAAE\Http;

use YAAE\AppEngine;
use YAAE\Http\RequestInterface;
use YAAE\Http\ResponseInterface;
use YAAE\Router\RouteInfo;
use YAAE\Core\CallableHandler;
use Throwable;

class RequestHandler
{
    /**
     * @param RouteInfo         $currentRouteInfo
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function handle(RouteInfo $currentRouteInfo, RequestInterface $request, ResponseInterface $response)
    {
        // -- Init Request
        $request = $this->initRequest($currentRouteInfo, $request);
        // -- Process Request
        $request = $this->processRequestHandlers($request, $request->getCurrentRoute()->requestHandlers);
        // -- Process Action
        $response = $this->processActionHandler($request, $response, $request->getCurrentRoute()->handler);
        // -- Process Response
        $response = $this->processResponseHandlers($response, $request->getCurrentRoute()->responseHandlers);
        // -- Return Response
        AppEngine::sendResponse($response);
    }

    /**
     * @param HttpException $exception
     */
    public static function handleHttpError(HttpException $exception)
    {
        $response = new Response();
        $response->setStatus($exception->getCode());
        ob_start();
        echo '<pre>';
        echo (string)$exception;
        echo '</pre>';
        $response->setData(ob_get_clean());
        AppEngine::sendResponse($response);
    }

    /**
     * @param Throwable $exception
     */
    public static function handleFatalError(Throwable $exception)
    {
        $response = new Response();
        $response->setStatus(ResponseInterface::STATUS_INTERNAL_SERVER_ERROR);
        ob_start();
        echo '<pre>';
        echo (string)$exception;
        echo '</pre>';
        $response->setData(ob_get_clean());
        AppEngine::sendResponse($response);
    }

    /**
     * @param RouteInfo        $currentRouteInfo
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    private function initRequest(RouteInfo $currentRouteInfo, RequestInterface $request): RequestInterface
    {
        // Set reverse order of Request (and it's correct)
        if (!empty($currentRouteInfo->requestHandlers))
            $currentRouteInfo->requestHandlers = array_reverse($currentRouteInfo->requestHandlers);
        $request->setCurrentRoute($currentRouteInfo);
        return $request;
    }

    /**
     * @param RequestInterface $request
     * @param callable[]       $handlers
     *
     * @return RequestInterface
     */
    private function processRequestHandlers(RequestInterface $request, array $handlers): RequestInterface
    {
        if (!empty($handlers)) {
            foreach ($handlers as $handler) {
                if ($requestNew = CallableHandler::tryHandleCallableWithArguments($handler, [$request])) {
                    $request = $requestNew;
                }
            }
        }
        return $request;
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $handler
     *
     * @return ResponseInterface
     */
    private function processActionHandler(RequestInterface $request, ResponseInterface $response, $handler): ResponseInterface
    {
        if (!empty($handler)) {
            if ($responseNew = CallableHandler::tryHandleCallableWithArguments($handler, [$request, $response])) {
                $response = $responseNew;
            }
        }
        return $response;

    }

    /**
     * @param ResponseInterface $response
     * @param callable[]        $handlers
     *
     * @return ResponseInterface
     */
    private function processResponseHandlers(ResponseInterface $response, array $handlers): ResponseInterface
    {
        if (!empty($handlers)) {
            foreach ($handlers as $handler) {
                if ($responseNew = CallableHandler::tryHandleCallableWithArguments($handler, [$response])) {
                    $response = $responseNew;
                }
            }
        }
        return $response;
    }
}