<?php


namespace FahrradKrucken\YAAE\Http;


class Response implements ResponseInterface
{
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

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(int $status = self::STATUS_OK)
    {
        if (in_array($status, self::STATUSES_ALLOWED)) $this->status = $status;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $headerName): string
    {
        return isset($this->headers[$headerName]) ? $this->headers[$headerName] : null;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $headerName): bool
    {
        return !empty($this->headers[$headerName]);
    }

    /**
     * @inheritDoc
     */
    public function setHeader(string $headerName, $headerValue)
    {
        $this->headers[$headerName] = $headerValue;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function setData($value)
    {
        $this->data = $value;
    }
}