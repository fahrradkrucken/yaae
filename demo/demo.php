<?php

use FahrradKrucken\YAAE\Engine;
use FahrradKrucken\YAAE\Core\Route;
use FahrradKrucken\YAAE\Http\RequestInterface;
use FahrradKrucken\YAAE\Http\ResponseInterface;
use FahrradKrucken\YAAE\Http\Error;

require_once '../vendor/autoload.php';
require_once 'demo-classes.php';


$engine = new Engine();
// You can set custom request path / method
$engine->setRequestPath($_GET['route'] ?? '/');

// Adding Routes
$engine->addRoutes([
    // Simple function as a handler
    Route::get('/', 'DemoHandlerFunction'),
    // Route Group
    Route::group('api', [
        Route::get('/', 'DemoHandlerClass@action'),
        // Route with params. Handler definition through "ClassName@methodName"
        Route::post('user/{foo}/{bar}/set', 'DemoHandlerClass@action'),
        // Static function as handler
        Route::new([Route::METHOD_GET, Route::METHOD_POST],'user/{foo}/{bar}/get', ['DemoHandlerClass', 'actionStatic']),
        // With Response Callback
        Route::get('auth', 'DemoHandlerFunction')
            ->addResponseCallback(new DemoResponseMiddleware()),
    // Request Callback (in this case - class with "__invoke()")
    ])->addRequestCallback(new DemoRequestMiddleware()),
    // Closure as handler
    Route::post('/john-wick/set-status/{jw_status}', function (RequestInterface $request, ResponseInterface $response) {
        $routeInfo = $request->getRouteInfo();
        $jwStatus = $routeInfo['arguments']['jw_status'] ? 'active' : 'inactive';
        $response->setData("John Wick is a {$jwStatus}");
        return $response;
    }),
]);

// Custom error handler
$engine->setErrorHandler(function (Throwable $error) {
    if ($error instanceof Error) {
        // do something with \FahrradKrucken\YAAE\Http\Error
    } else {
        // do something with \Throwable
    }
});


// Required action - call "start()" after you're done
$engine->start();