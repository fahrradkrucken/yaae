# Yet Another AppEngine

This peace of "code" is just a wrapper around FastRoute and s a simple start 
 point for your codebase. Please, keep in mind 3 simple facts: it's not a 
 framework, it was written in about 16 hours, most of it made while author 
 was drunk. The good part is - it has no external dependencies.
 
## Application Lifecycle

#### Quick Start

Basically it's just a Request-Response handler:
  
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
SHOULD return `ResponseInterface $request`;
* Response Handler accepts 1 arg `ResponseInterface $request` and SHOULD return
 `(ResponseInterface $request)`. We have here the same rules as for request handlers.
 
 Request handlers will be executed from external (global or group) level to internal 
 (individual route) level. Response handlers will be executed in reverse order.
 
## Configuration
 
#### Main Config

#### Errors

## Templating

