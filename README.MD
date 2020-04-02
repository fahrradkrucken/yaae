# Yet Another AppEngine

This peace of "code" is a simple start 
 point for your codebase. Please, keep in mind 3 simple facts: it's not a 
 framework, it was written in about 16 hours, most of it made while author 
 was drunk. The good part is - it has no external dependencies.
 
## Application Lifecycle

#### Quick Start

Basically it's just a Request-Response handler.
See simple example in `demo/demo.php`. 
  
#### Action Callbacks

You can use any `callable` parameter, or some kind of Laravel style `ControllerClass@actionMethod`.
  
#### Request & Response 

Well, Request & Response objects are not interesting at all, and they're not like
PSR best practices. But! But you can manipulate them as you want, so they could
become more pretty with your code. We have 3 rules:
* Request Handler accepts 1 arg `RequestInterface $request` and SHOULD return
`RequestInterface $request`. If you'll not return `RequestInterface`, everything will be fine 
anyway, so you can just do something inside your "Request Middleware" and return nothing;
* Action handler accepts 2 args `RequestInterface $request, ResponseInterface $response` and 
SHOULD return `ResponseInterface $response`;
* Response Handler accepts 1 arg `ResponseInterface $response` and SHOULD return
 `ResponseInterface $response`. We have here the same rules as for request handlers.
 
 Request handlers will be executed from external (global or group) level to internal 
 (individual route) level. Response handlers will be executed in reverse order.
 
## Configuration
 
#### Main Config

You can replace request method and path through:
* `$engine->setRequestPath(string $requestPath)`, default is `$_SERVER['REQUEST_URI']`, 
without `/` at the end and without params after `?`;
* `$engine->setRequestMethod(string $requestMethod)`, default is `$_SERVER['REQUEST_METHOD']`

You can replace default classes:
* `FahrradKrucken\YAAE\Http\Request` through `$engine->setRequest(RequestInterface $request)`;
* `FahrradKrucken\YAAE\Http\Response` through `$engine->setResponse(ResponseInterface $response)`; 
* `FahrradKrucken\YAAE\Core\RouteHandler` through `$engine->setRouteHandler(RouteHandlerInterface $routeHandler)`.

#### Errors

You can replace error handlers:
* `\Throwable` errors handler through `$engine->setErrorHandler(callable $errorHandler)`;
* `\FahrradKrucken\YAAE\Http\Error` errors handler through `$engine->setErrorHandlerHttp(callable $errorHandler)`.

Note that `\FahrradKrucken\YAAE\Http\Error` has `$request` and `$response` props, it could be useful. 
For example, when you'll get 404 (Not Found) or 405 (Method not allowed) error from `RouteHandler`, 
you can understand this by `$errorHttp->getCode()`.

Examples for API errors:
```php
<?php
use FahrradKrucken\YAAE\Engine;
use FahrradKrucken\YAAE\Http\ResponseInterface;

// ... Some Code ...

$engine->setErrorHandler(function (Throwable $error) {
    $response = new \FahrradKrucken\YAAE\Http\Response();
    $response->setStatus(ResponseInterface::STATUS_OK);
    $response->setHeader('Content-Type', 'application/json');
    $response->setData(
        json_encode(
            [
                'error_code'         => $error->getCode(),
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

// ... Some Code ...

```

## Templating

#### HTML

You can use any templating engine you want, just dont forget to put the result 
inside the `$response->setData($templateHtmlString)`. If you prefer simple 
php-html templating, you can use default `FahrradKrucken\YAAE\Core\TemplateHandler`.
Example:
```php
<?php
/**
 * Route Handler 
 */
use FahrradKrucken\YAAE\Http\RequestInterface;
use FahrradKrucken\YAAE\Http\ResponseInterface;
use FahrradKrucken\YAAE\Core\TemplateHandler;

TemplateHandler::setTemplatePath(__DIR__);

// ... Some Code ...
function actionTemplate(RequestInterface $request, ResponseInterface $response)
    {
        $response->setData(
            TemplateHandler::render('demo-template', [
                'foo' => 12345,
                'bar' => 'Lorem ipsum dolor sit amet',
                'baz' => false,
            ])
        );
        return $response;
    }
// ... Some Code ...
```

```php
<?php
/**
 * Route Template
 * 
 * @var int $foo
 * @var string $bar
 * @var bool $baz
 */
?>
<h3 style="text-align: center"><?= $foo . ' - ' . $bar ?></h3>
<p style="text-align: center"><?= $baz ? 'YES' : 'NO' ?></p>
```

#### JSON & other API

If you need to send responses in JSON format (for example), you can add response 
handler to single route or to the route group. Like this:

```php
<?php
use FahrradKrucken\YAAE\Core\Route;
// .. some code ...
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
    });
// .. some code ...
``` 