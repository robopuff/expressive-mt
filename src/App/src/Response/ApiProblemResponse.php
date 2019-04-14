<?php

namespace App\Response;

use App\ApiProblem;
use Zend\Diactoros\Response\JsonResponse;

class ApiProblemResponse extends JsonResponse
{
    /**
     * ApiProblemResponse constructor.
     * @param ApiProblem $problem
     * @param array $headers
     * @param int $encodingOptions
     */
    public function __construct(
        ApiProblem $problem,
        array $headers = [],
        int $encodingOptions = self::DEFAULT_JSON_FLAGS
    ) {
        parent::__construct(
            $problem->toArray(),
            $problem->getStatusCode(),
            $headers,
            $encodingOptions
        );
    }
}
