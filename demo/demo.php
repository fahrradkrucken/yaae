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
        Route::new([Route::METHOD_GET, Route::METHOD_POST],'user/{foo}/{bar}/info', ['DemoHandlerClass', 'actionStatic']),
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
    })
]);

// Required action - call "start()" after you're done
$engine->start();