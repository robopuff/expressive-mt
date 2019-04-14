<?php

namespace App\Middleware;

use App\Action\AbstractRestfulAction as RestfulAction;
use App\ApiProblem;
use App\Response\ApiProblemResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PropagateRestfulActionMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());

        $restMethod = null;
        $hasId = !empty($request->getAttribute('id'));

        switch ($method) {
            case 'POST':
                $restMethod = RestfulAction::METHOD_CREATE;
                break;
            case 'GET':
                $restMethod = $hasId ? RestfulAction::METHOD_FETCH : RestfulAction::METHOD_FETCH_ALL;
                break;
            case 'PATCH':
                $restMethod = $hasId ? RestfulAction::METHOD_PATCH : RestfulAction::METHOD_PATCH_LIST;
                break;
            case 'DELETE':
                $restMethod = $hasId ? RestfulAction::METHOD_DELETE : RestfulAction::METHOD_DELETE_LIST;
                break;
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Unsupported HTTP method')
                );
        }

        return $handler->handle(
            $request->withAttribute(self::class, $restMethod)
        );
    }
}
