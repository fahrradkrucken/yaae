<?php


namespace YAAE\Http;


interface ResponseInterface
{
    const STATUS_OK = 200;
    const STATUS_NO_CONTENT = 204;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_NOT_ALLOWED_METHOD = 405;
    const STATUS_NOT_ACCEPTABLE = 406;
    const STATUS_CONFLICT = 409;
    const STATUS_GONE = 410;

    const STATUS_INTERNAL_SERVER_ERROR = 500;

    const STATUSES_ALLOWED = [
        100, 101, 102,
        200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
        300, 301, 302, 303, 304, 305, 306, 307, 308,
        400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 421, 422, 423, 424, 426, 428, 431, 449, 451, 499,
        500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 520, 521, 522, 523, 524, 525, 526,
    ];

    public function getStatus(): int;

    public function setStatus(int $status = self::STATUS_OK);

    public function getHeaders(): array;

    public function getHeader(string $headerName): string;

    public function hasHeader(string $headerName): bool;

    public function setHeader(string $headerName, $headerValue);

    public function getData();

    public function setData($value);
}