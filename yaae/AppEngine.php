<?php


namespace YAAE;


use YAAE\Core\CallableHandler;
use YAAE\Http\HttpException;
use YAAE\Http\Request;
use YAAE\Http\Response;
use YAAE\Http\ResponseInterface;
use YAAE\Router\RouteDispatcher;
use YAAE\Router\RouteGroup;
use YAAE\Router\RouteInfo;
use YAAE\Http\RequestHandler;

require_once 'functions.php';

class AppEngine
{
    /**
     * @var null|self
     */
    private static $instance;

    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var RouteGroup
     */
    private $rootRoute;

    private function __construct()
    {
        $this->rootRoute = new RouteGroup();
        $this->config = [
            'baseUrl'              => (false !== $pos = strpos($_SERVER['REQUEST_URI'], '?')) ?
                substr($_SERVER['REQUEST_URI'], 0, $pos) :
                $_SERVER['REQUEST_URI'],
            'baseRequest'          => new Request(),
            'baseResponse'         => new Response(),
            'baseHttpErrorHandler' => ['YAAE\Http\RequestHandler', 'handleHttpError'],
            'baseDir' => $_SERVER['DOCUMENT_ROOT'],
            //            'baseFatalErrorHandler' => ['YAAE\Http\RequestHandler','handleFatalError'],
        ];
        $this->config['templatePath'] = $this->config['baseDir'] . '/tpl';
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    private static function getInstance(): self
    {
        if (!isset(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param string          $dependencyName
     * @param callable|object $dependency
     */
    public static function add(string $dependencyName, $dependency)
    {
        self::getInstance()->dependencies[$dependencyName] = is_callable($dependency) ?
            call_user_func($dependency) :
            $dependency;
    }

    /**
     * @param string $dependencyName
     *
     * @return mixed|null
     */
    public static function get(string $dependencyName)
    {
        return self::has($dependencyName) ? self::getInstance()->dependencies[$dependencyName] : null;
    }

    /**
     * @param string $dependencyName
     *
     * @return bool
     */
    public static function has(string $dependencyName): bool
    {
        return isset(self::getInstance()->dependencies[$dependencyName]);
    }

    /**
     * @param string $paramName
     * @param mixed  $paramValue
     */
    public static function setConfig(string $paramName, $paramValue)
    {
        self::getInstance()->config[$paramName] = $paramValue;
    }

    /**
     * @param string $paramName
     *
     * @return mixed|null
     */
    public static function getConfig(string $paramName)
    {
        return isset(self::getInstance()->config[$paramName]) ? self::getInstance()->config[$paramName] : null;
    }

    /**
     * @return RouteGroup
     */
    public static function route()
    {
        return self::getInstance()->rootRoute;
    }

    public static function start()
    {
//        $baseFatalErrorHandler = self::getConfig('baseFatalErrorHandler');
//        register_shutdown_function(function () use ($baseFatalErrorHandler) {
//            $lastError = error_get_last();
//            if (!empty($lastError)) {
//                $errorCode = $lastError['type'];
//                $errorMsg = $lastError['message'] . ' ; ' .  $lastError['file'] . ' ; ' . $lastError['line'];
//                CallableHandler::tryHandleCallableWithArguments(
//                    $baseFatalErrorHandler,
//                    [new HttpException($errorMsg, $errorCode)]
//                );
//            }
//        });

        try {

            $routeDispatcher = new RouteDispatcher(self::getInstance()->rootRoute, self::getConfig('baseUrl'));
            $currentRouteInfo = $routeDispatcher->dispatch();

            if ($currentRouteInfo instanceof RouteInfo) {
                $httpRequestHandler = new RequestHandler();
                $httpRequestHandler->handle(
                    $currentRouteInfo,
                    self::getConfig('baseRequest'),
                    self::getConfig('baseResponse')
                );
            }

        } catch (\Throwable $exception) {
            if ($exception instanceof HttpException) {
                CallableHandler::tryHandleCallableWithArguments(self::getConfig('baseHttpErrorHandler'), [$exception]);
            } else {
                CallableHandler::tryHandleCallableWithArguments(self::getConfig('baseFatalErrorHandler'), [$exception]);
            }
        }
    }

    /**
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
        exit();
    }

    /**
     * @param string $templateName
     * @param array  $variables
     *
     * @return string
     */
    public static function tpl(string $templateName, array $variables = [])
    {
        $templatePath = str_replace(
            ['//', '\\\\', '/', '\\',],
            DIRECTORY_SEPARATOR,
            self::getConfig('templatePath') . '/' . $templateName . '.php'
        );
        $templateContent = '';
        if (is_file($templatePath)) {
            ob_start();
            if (!empty($variables)) extract($variables, EXTR_OVERWRITE);
            include($templatePath);
            $templateContent = ob_get_clean();
        }
        return (string)$templateContent;
    }
}