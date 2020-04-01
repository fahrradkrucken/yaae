<?php


namespace FahrradKrucken\YAAE\Http;


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