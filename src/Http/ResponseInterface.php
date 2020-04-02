<?php


namespace FahrradKrucken\YAAE\Http;


interface ResponseInterface
{
    const STATUS_OK         = 200;
    const STATUS_NO_CONTENT = 204;

    const STATUS_BAD_REQUEST        = 400;
    const STATUS_UNAUTHORIZED       = 401;
    const STATUS_FORBIDDEN          = 403;
    const STATUS_NOT_FOUND          = 404;
    const STATUS_NOT_ALLOWED_METHOD = 405;
    const STATUS_NOT_ACCEPTABLE     = 406;
    const STATUS_CONFLICT           = 409;
    const STATUS_GONE               = 410;

    const STATUS_INTERNAL_SERVER_ERROR = 500;

    const STATUSES_ALLOWED = [
        100, 101, 102,
        200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
        300, 301, 302, 303, 304, 305, 306, 307, 308,
        400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 421, 422, 423, 424, 426, 428, 431, 449, 451, 499,
        500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 520, 521, 522, 523, 524, 525, 526,
    ];

    /**
     * @return int - Http Status
     */
    public function getStatus(): int;

    /**
     * @param int $status - Http Status
     */
    public function setStatus(int $status = self::STATUS_OK);

    /**
     * @return array - Http Headers
     */
    public function getHeaders(): array;

    /**
     * @param string $headerName
     *
     * @return string
     */
    public function getHeader(string $headerName): string;

    /**
     * @param string $headerName
     *
     * @return bool
     */
    public function hasHeader(string $headerName): bool;

    /**
     * @param string           $headerName
     * @param string|int|float $headerValue
     *
     * @return mixed
     */
    public function setHeader(string $headerName, $headerValue);

    /**
     * @return null|string|array
     */
    public function getData();

    /**
     * @param mixed $value
     */
    public function setData($value);
}