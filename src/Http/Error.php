<?php


namespace FahrradKrucken\YAAE\Http;

/**
 * Class Error - helper \Throwable class for the HTTP error handling
 * @package FahrradKrucken\YAAE\Http
 */
class Error extends \Exception
{
    /**
     * @var ResponseInterface
     */
    public $response;
    /**
     * @var RequestInterface
     */
    public $request;
}