<?php

namespace App;

class ApiProblem
{
    /**
     * Content type for api problem response
     */
    public const CONTENT_TYPE = 'application/problem+json';

    /**
     * Status titles for common problems.
     *
     * @var array
     */
    public const PROBLEM_STATUS_TITLES = [
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $detail;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string
     */
    private $type = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';

    /**
     * @var array
     */
    private $additionalDetails;

    /**
     * ApiProblem constructor.
     *
     * @param int $status
     * @param string $detail
     * @param string|null $type
     * @param string|null $title
     * @param array $additional
     */
    public function __construct($status, $detail, $type = null, $title = null, array $additional = [])
    {
        // Ensure a valid HTTP status
        if (! is_numeric($status)
            || ($status < 100)
            || ($status > 599)
        ) {
            $status = 500;
        }

        $this->status = $status;
        $this->detail = $detail;
        $this->title = $title;

        if (null !== $type) {
            $this->type = $type;
        }

        $this->additionalDetails = $additional;
    }

    /**
     * Get status code
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Cast to an array.
     * @return array
     */
    public function toArray(): array
    {
        $detail = $this->detail;
        $problem = [
            'type' => $this->type,
            'title' => $this->getTitle(),
            'status' => $this->status,
            'detail' => $detail,
        ];
        // Required fields should always overwrite additional fields
        return array_merge($this->additionalDetails, $problem);
    }

    /**
     * Retrieve the title.
     * @return string
     */
    protected function getTitle(): string
    {
        if ($this->title !== null) {
            return $this->title;
        }

        if ($this->title === null
            && $this->type === 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html'
            && array_key_exists($this->status, self::PROBLEM_STATUS_TITLES)
        ) {
            return self::PROBLEM_STATUS_TITLES[$this->status];
        }

        return 'Unknown';
    }
}
