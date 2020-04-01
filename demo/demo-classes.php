<?php

use FahrradKrucken\YAAE\Http\RequestInterface;
use FahrradKrucken\YAAE\Http\ResponseInterface;
use FahrradKrucken\YAAE\Core\TemplateHandler;

function DemoHandlerFunction(RequestInterface $request, ResponseInterface $response)
{
    $data = '';
    $data .= '<h4>Handler - ' . __FUNCTION__ . '</h4>';
    $data .= 'Request Data : <br>';
    $data .= '<pre>';
    $data .= var_export($request->getData(), true);
    $data .= '</pre>';
    $data .= 'Route Data: <br>';
    $data .= '<pre>';
    $data .= var_export($request->getRouteInfo(), true);
    $data .= '</pre>';
    $response->setData($data);

    return $response;
}

class DemoHandlerClass
{
    public function action(RequestInterface $request, ResponseInterface $response)
    {
        $data = '';
        $data .= '<h4>Handler - ' . __METHOD__ . '</h4>';
        $data .= 'Request Data : <br>';
        $data .= '<pre>';
        $data .= var_export($request->getData(), true);
        $data .= '</pre>';
        $data .= 'Route Data: <br>';
        $data .= '<pre>';
        $data .= var_export($request->getRouteInfo(), true);
        $data .= '</pre>';
        $response->setData($data);

        return $response;
    }

    public function actionTemplate(RequestInterface $request, ResponseInterface $response)
    {
        $routeParams = $request->getRouteInfo()['arguments'];
        $response->setData(
            TemplateHandler::render('demo-template', [
                'foo' => $routeParams['foo'],
                'bar' => $routeParams['bar'],
            ])
        );
        return $response;
    }

    public static function actionStatic(RequestInterface $request, ResponseInterface $response)
    {

        $data = '';
        $data .= '<h4>Handler - ' . __METHOD__ . '</h4>';
        $data .= 'Request Data : <br>';
        $data .= '<pre>';
        $data .= var_export($request->getData(), true);
        $data .= '</pre>';
        $data .= 'Route Data: <br>';
        $data .= '<pre>';
        $data .= var_export($request->getRouteInfo(), true);
        $data .= '</pre>';
        $response->setData($data);

        return $response;
    }
}

class DemoRequestMiddleware
{
    public function __invoke(RequestInterface $request)
    {
        $request->setData(
            array_merge(
                $request->getData(),
                [
                    'request_middleware' => 'Request Middleware: ' . __METHOD__,
                ]
            ));
        return $request;
    }
}

class DemoResponseMiddleware
{
    public function __invoke(ResponseInterface $response)
    {
        $response->setData($response->getData() . '<br> Response Middleware: ' . __METHOD__ . '<br>');
        return $response;
    }
}