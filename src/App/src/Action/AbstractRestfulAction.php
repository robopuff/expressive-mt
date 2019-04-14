<?php

namespace App\Action;

use App\ApiProblem;
use App\Middleware\PropagateRestfulActionMiddleware;
use App\Response\ApiProblemResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ArraySerializable;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\Paginator;

abstract class AbstractRestfulAction implements MiddlewareInterface
{
    public const METHOD_FETCH       = 'fetch';
    public const METHOD_FETCH_ALL   = 'fetchAll';
    public const METHOD_CREATE      = 'create';
    public const METHOD_DELETE      = 'delete';
    public const METHOD_DELETE_LIST = 'deleteList';
    public const METHOD_PATCH       = 'patch';
    public const METHOD_PATCH_LIST  = 'patchList';

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
        $dispatch = $this->dispatch($request);

        if ($dispatch instanceof ResponseInterface) {
            return $dispatch;
        }

        if ($dispatch instanceof ApiProblem) {
            return new ApiProblemResponse($dispatch);
        }

        if ($dispatch instanceof Paginator) {
            return $this->processPaginator($request, $dispatch);
        }

        if (\is_object($dispatch)) {
            $dispatch = $this->getHydrator()->extract($dispatch);
        }

        return new JsonResponse($dispatch);
    }

    /**
     * Dispatch a request
     * @param ServerRequestInterface $request
     * @return ApiProblem|mixed
     */
    private function dispatch(ServerRequestInterface $request)
    {
        $restMethod = $request->getAttribute(PropagateRestfulActionMiddleware::class);
        if (null === $restMethod) {
            new ApiProblem(500, 'Restful action not propagated');
        }

        $id = $request->getAttribute('id', false);
        $data = (array) $request->getParsedBody();

        switch ($restMethod) {
            case self::METHOD_FETCH:
                return $this->fetch($id);
            case self::METHOD_FETCH_ALL:
                return $this->fetchAll($request->getQueryParams());
            case self::METHOD_CREATE:
                return $this->create($data);
            case self::METHOD_PATCH:
                return $this->patch($id, $data);
            case self::METHOD_PATCH_LIST:
                return $this->patchList($data);
            case self::METHOD_DELETE:
                return $this->delete($id);
            case self::METHOD_DELETE_LIST:
                return $this->deleteList($data);
            default:
                return $this->error404();
        }
    }

    /**
     * Process traversable dispatch response
     * @param ServerRequestInterface $request
     * @param Paginator $dispatch
     * @return ResponseInterface
     */
    private function processPaginator(ServerRequestInterface $request, Paginator $dispatch): ResponseInterface
    {
        $payload = [
            '_embedded' => []
        ];

        $page  = $request->getQueryParams()['page'] ?? 1;
        $limit = $request->getQueryParams()['limit'] ?? 15;

        $dispatch->setItemCountPerPage($limit);
        $dispatch->setCurrentPageNumber($page);

        $hydrator = $this->getHydrator();
        foreach ($dispatch->getIterator() as $item) {
            if (\is_object($item)) {
                $item = $hydrator->extract($item);
            }

            $payload['_embedded'][] = $item;
        }

        $payload['page']        = $dispatch->getCurrentPageNumber();
        $payload['page_count']  = $dispatch->count();
        $payload['page_size']   = $dispatch->getItemCountPerPage();
        $payload['total_items'] = $dispatch->getTotalItemCount();

        return new JsonResponse($payload);
    }

    /**
     * Return an error 404
     * @return ApiProblemResponse
     */
    protected function error404(): ApiProblemResponse
    {
        return new ApiProblemResponse(
            new ApiProblem(404, 'Entity not found')
        );
    }

    /*
     * Methods below are just a base and are meant to be overridden by action classes
     */

    /**
     * Get a hydrator for an entity
     * @return HydratorInterface
     */
    public function getHydrator(): HydratorInterface
    {
        return new ArraySerializable();
    }

    /**
     * Fetch a single record (GET method)
     * @param mixed $id
     * @return mixed
     */
    public function fetch($id)
    {
        return new ApiProblem(501, 'Method not implemented');
    }

    /**
     * Fetch a collection of records (GET method)
     * @param array $params
     * @return mixed
     */
    public function fetchAll(array $params)
    {
        return new ApiProblem(501, 'Method not implemented');
    }

    /**
     * Create a new record
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return new ApiProblem(501, 'Method not implemented');
    }

    /**
     * Patch single record (PATCH method)
     * @param mixed $id
     * @param array $data
     * @return mixed
     */
    public function patch($id, array $data)
    {
        return new ApiProblem(501, 'Method not implemented');
    }

    /**
     * Patch a collection of records (PATCH method)
     * @param array $data
     * @return mixed
     */
    public function patchList(array $data)
    {
        return new ApiProblem(501, 'Method not implemented');
    }

    /**
     * Delete a single record (DELETE method)
     * @param mixed $id
     * @return mixed
     */
    public function delete($id)
    {
        return new ApiProblem(501, 'Method not implemented');
    }

    /**
     * Delete a collection of records (DELETE method)
     * @param array $data
     * @return mixed
     */
    public function deleteList(array $data)
    {
        return new ApiProblem(501, 'Method not implemented');
    }
}
