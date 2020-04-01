<?php


namespace FahrradKrucken\YAAE\Http;


class Response implements ResponseInterface
{

    private static $blalb = [
        self::STATUS_NOT_FOUND => 'dsasda'
    ];

    /**
     * @var int
     */
    private $status = self::STATUS_OK;
    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var null|string|array
     */
    private $data = '';

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status = self::STATUS_OK)
    {
        if (in_array($status, self::STATUSES_ALLOWED)) $this->status = $status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $headerName): string
    {
        return isset($this->headers[$headerName]) ? $this->headers[$headerName] : null;
    }

    public function hasHeader(string $headerName): bool
    {
        return !empty($this->headers[$headerName]);
    }

    public function setHeader(string $headerName, $headerValue)
    {
        $this->headers[$headerName] = $headerValue;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($value)
    {
        $this->data = $value;
    }
}