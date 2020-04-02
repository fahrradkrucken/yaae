<?php

use FahrradKrucken\YAAE\Engine;
use FahrradKrucken\YAAE\Core\Route;
use FahrradKrucken\YAAE\Http\RequestInterface;
use FahrradKrucken\YAAE\Http\ResponseInterface;
use FahrradKrucken\YAAE\Core\TemplateHandler;

require_once '../vendor/autoload.php';
require_once 'demo-classes.php';


$engine = new Engine();
// You can set custom request path / method
$engine->setRequestPath($_GET['route'] ?? '/');
TemplateHandler::setTemplatePath(__DIR__);

// Adding Routes
$engine->addRoutes([
    // Simple function as a handler
    Route::get('/', 'DemoHandlerFunction'),
    // Route Group
    Route::group('api', [
        Route::get('/', 'DemoHandlerClass@action'),
        // Route with params. Handler definition through "ClassName@methodName"
        Route::get('user/{foo}/{bar}/get', 'DemoHandlerClass@actionTemplate'),
        Route::post('user/{foo}/{bar}/set', 'DemoHandlerClass@action'),
        // Static function as handler
        Route::new([Route::METHOD_GET, Route::METHOD_POST], 'user/{foo}/{bar}/info', ['DemoHandlerClass', 'actionStatic']),
        // With Response Callback
        Route::get('auth', 'DemoHandlerFunction')
            ->addResponseCallback(new DemoResponseMiddleware()),
        // Some additional Examples
        Route::get('post/{id}', 'DemoHandlerClass@action'),
        Route::post('post/{id}', 'DemoHandlerClass@action'),
        Route::put('post/{id}', 'DemoHandlerClass@action'),
        Route::patch('post/{id}', 'DemoHandlerClass@action'),
        Route::delete('post/{id}', 'DemoHandlerClass@action'),
        // Request Callback (in this case - class with "__invoke()")
    ])->addRequestCallback(new DemoRequestMiddleware()),
    // Closure as handler
    Route::post('/john-wick/set-status/{jw_status}', function (RequestInterface $request, ResponseInterface $response) {
        $routeInfo = $request->getRouteInfo();
        $jwStatus = $routeInfo['arguments']['jw_status'] ? 'active' : 'inactive';
        $response->setData("John Wick is a {$jwStatus}");
        return $response;
    }),
    // JSON Response example
    Route::get('/some-json', function (RequestInterface $request, ResponseInterface $response) {
        $response->setData([
            'foo' => 12345,
            'bar' => 'Lorem ipsum dolor sit amet',
            'baz' => false,
        ]);
        return $response;
    })->addResponseCallback(function (ResponseInterface $response) {
        $response->setHeader('Content-Type', 'application/json');
        $response->setData(json_encode($response->getData(), JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE));
        return $response;
    }),
    Route::get('/some-json-err', function (RequestInterface $request, ResponseInterface $response) {
        throw new Exception('Custom Exception', 12345);
    })
]);

$engine->setErrorHandler(function (Throwable $error) {
    $response = new \FahrradKrucken\YAAE\Http\Response();
    $response->setStatus(ResponseInterface::STATUS_OK);
    $response->setHeader('Content-Type', 'application/json');
    $response->setData(
        json_encode(
            [
                'error_code' => $error->getCode(),
                'error_msg' => $error->getMessage(),
            ],
            JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
        )
    );
    Engine::sendResponse($response);
});

$engine->setErrorHandlerHttp(function (\FahrradKrucken\YAAE\Http\Error $error) {
    if (
        $error->getCode() === ResponseInterface::STATUS_NOT_FOUND ||
        $error->getCode() === ResponseInterface::STATUS_NOT_ALLOWED_METHOD
    ) { // Default Router errors
        $error->response->setHeader('Content-Type', 'application/json');
        $error->response->setData(
            json_encode(
                [
                    'error_code' => $error->response->getStatus(),
                    'error_msg'  => $error->response->getData(),
                ],
                JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
            )
        );
        Engine::sendResponse($error->response);
    } else { // Custom errors
        if (!$error->response || !($error->response instanceof ResponseInterface)) {
            $newResponse = new \FahrradKrucken\YAAE\Http\Response();
            $newResponse->setStatus(ResponseInterface::STATUS_BAD_REQUEST);
            $newResponse->setData(json_encode(
                    [
                        'error_code' => $error->getCode(),
                        'error_msg'  => $error->getMessage(),
                    ],
                    JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
                )
            );
        } else {
            $newResponse = $error->response;
        }
        $newResponse->setHeader('Content-Type', 'application/json');
        Engine::sendResponse($newResponse);
    }
});

// Required action - call "start()" after you're done
$engine->start();