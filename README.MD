## Yet Another AppEngine

This peace of "code" is just a wrapper around FastRoute and s a simple start 
 point for your codebase. Please, keep in mind 3 simple facts: it's not a 
 framework, it was written in about 5 hours, most of it made while author 
 was drunk.
 
### Application Lifecycle

---

#### Quick Start

Basically it's just a RouterWrapper + some kind of ServiceLocator, so simple
 example is:
 
 ```php
 <?php
 
 use YAAE\AppEngine;
 use YAAE\Http\RequestInterface;
 use YAAE\Http\ResponseInterface;
 use YAAE\Router\RouteGroup;
 
 require 'vendor/autoload.php';
 
 $someCallback = function (RequestInterface $request, ResponseInterface $response) {
     $data = $request->getData();
     $name = $data['name'] ?? 'World';
     $response->setData("Hello, {$name}!");
     return $response;
 };
 
 AppEngine::route()->get('/', $someCallback);
 AppEngine::route()->group('/api', function (RouteGroup $routeApi) use ($someCallback) {
     $routeApi->get('/', $someCallback);
     $routeApi->get('/horses', $someCallback);
     $routeApi->post('/horse', $someCallback);
     $routeApi->get('/horse/{id}', $someCallback);
     $routeApi->patch('/horse/{id}', $someCallback);
     $routeApi->delete('/horse/{id}', $someCallback);
 });
 
 AppEngine::start();
 ```
 
 Routing works on top of FastRoute, so you can use any thing that FastRoute can do 
  for you, with all that nested route groups & named route params.
  
#### Request & Response 

Well, Request & Response objects are not interesting at all, and they're not like
PSR best practices. But! But you can manipulate them as you want, so they could
become more pretty with your code. We have 4 rules:
* Request Handler accepts 1 arg `(RequestInterface $request)` and SHOULD return
`(RequestInterface $request)`;
* Action handler accepts 2 args `(RequestInterface $request, ResponseInterface $response)` and 
SHOULD return `(ResponseInterface $request)`;
* Response Handler accepts 1 arg `(ResponseInterface $request)` and SHOULD return
 `(ResponseInterface $request)`.
 
 #### Request & Response Handlers
 
 You can add them globally:
 
 ```php
 AppEngine::route()->addRequestHandler($yourHandler1);
 AppEngine::route()->addResponseHandler($yourHandler2);
 ```
 
 or only to desired RouteGroup / Route:
 
 ```php
 AppEngine::route()->get('/foo', $actionHandler)->addRequestHandler($yourHandler);
 AppEngine::route()->group('/bar', $groupRouteCallback)->addRequestHandler($yourHandler);
 ```
 
 Request handlers will be executed from external (global or group) level to internal 
 (individual route) level. Response handlers will be executed in reverse order.
 
 Also, if you need to terminate app execution at some moment (for example, when your 
 theoretical `AuthRequestHandler` should return response with "401 Unauthorized" status),
 you can do this:
 
 ```php
 <?php
  use YAAE\AppEngine;
  
  // -- your beautiful code here
  
  $response = new \YAAE\Http\Response();
  $response->setData('Get out!');
  $response->setStatus(\YAAE\Http\Response::STATUS_UNAUTHORIZED);
    
  AppEngine::sendResponse($response);
  
  // -- your beautiful code here
 ```
 
#### AppEngine Container

AppEngine is actually a simple singleton. Yeah...not very SOLID, but it works. I think you want to see there an 
example of how we can set some global third-party DB tool for our application, but we have a John Wick here.

```php
<?php
use YAAE\AppEngine;
use YAAE\Http\RequestInterface;
use YAAE\Http\ResponseInterface;

class JohnWick {
    const STATUS_ACTIVE = 'badGuy';
    const STATUS_INACTIVE = 'goodGuy';

    public $first_name = 'John';
    public $last_name = 'Wick';
    public $status = self::STATUS_INACTIVE;

    public function someoneKillsMyDog(bool $status = false)
    {
        $this->status = $status ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
    }
}

AppEngine::add('JohnWick', function () {
   return new JohnWick();
});

AppEngine::route()->get('/john-wick/set-status/{status}', function (RequestInterface $request, ResponseInterface $response) {
    if (AppEngine::has('JohnWick')) {
        /**
         * @var $jw JohnWick
         */
        $jw = AppEngine::get('JohnWick');
        $jw->someoneKillsMyDog(boolval($request->getCurrentRoute()->params['status']));
        $response->setData("John Wick is a {$jw->status}");
    } else {
        $response->setData("John Wick doesn't exists");
    }
    return $response;
});

AppEngine::start();

```

Now if we'll go to `/john-wick/set-status/1`, our global JohnWick becomes a 'badGuy'. It's not the best example, but
who cares.
 
### Configuration
 
---

#### Main Config

In fact, application config is just a storage for different variables that you may need later. So you can put something
 in it with help of `AppEngine::setConfig('DB_connection','your_DB_connection_string')` and than take it when you need
 it `AppEngine::getConfig('DB_connection')`.

For now we have a few arguments to configure global application instance:

```php
<?php
use YAAE\AppEngine;
use YAAE\Http\Request;
use YAAE\Http\Response;
use YAAE\Http\HttpException;

// -- Source for route parsing
AppEngine::setConfig('baseUrl', $_GET['route']); // Default $_SERVER['REQUEST_URI'] without $_GET params
// -- Custom Request / Response
AppEngine::setConfig('baseRequest', new Request()); // should implement YAAE\Http\RequestInterface
AppEngine::setConfig('baseResponse', new Response()); // should implement YAAE\Http\ResponseInterface
// -- Default Error handler (example JSON formatting)
AppEngine::setConfig('baseHttpErrorHandler', function (HttpException $exception) {
    $response = new Response();
    $response->setStatus($exception->getCode());
    $response->setData(json_encode([
            'error' => [
                'code' => $exception->getCode(),
                'msg' => $exception->getMessage(),
            ],
        ]));
    $response->setHeader('Content-Type', 'application/json');
    AppEngine::sendResponse($response);
});
```

#### Errors

Inside your Action handlers (controllers) you can throw any exception that was inherited from `YAAE\Http\HttpException` 
and it'll be handled by your default error handler.
