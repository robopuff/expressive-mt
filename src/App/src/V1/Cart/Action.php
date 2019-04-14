<?php

namespace App\V1\Cart;

use App\Action\AbstractRestfulAction;
use App\ApiProblem;

class Action extends AbstractRestfulAction
{
    /**
     * @var Service
     */
    private $cartService;

    /**
     * Action constructor.
     * @param Service $cartService
     */
    public function __construct(Service $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Create a new simple cart instance without any items in it
     * Should be used to generate a new cart id
     * @param array $data
     * @return Entity|mixed
     * @throws \Exception
     */
    public function create(array $data)
    {
        $uuid = $this->cartService->create($data['items'] ?? []);

        // We want to be consistent, that's why I delegate a response from here into `fetch`,
        // same goes for delete and patch
        return $this->fetch($uuid);
    }

    /**
     * Clear whole cart (delete it)
     * @param mixed $id
     * @return ApiProblem|\App\Response\ApiProblemResponse|Entity|mixed|null
     */
    public function delete($id)
    {
        $uuid = $this->cartService->convertToUuid($id);
        if (null === $uuid) {
            return new ApiProblem(400, 'Provided uuid cannot be processed');
        }

        try {
            $this->cartService->delete($uuid);
        } catch (Exception\CartNotFoundException $_) {
            return $this->error404();
        }

        return $this->fetch($uuid);
    }

    /**
     * Update cart items
     * @param mixed $id
     * @param array $data
     * @return ApiProblem|\App\Response\ApiProblemResponse|Entity|mixed|null
     * @throws Exception\InvalidCartStatusException
     */
    public function patch($id, array $data)
    {
        $uuid = $this->cartService->convertToUuid($id);
        if (null === $uuid) {
            return new ApiProblem(400, 'Provided uuid cannot be processed');
        }

        try {
            $this->cartService->updateItems($uuid, $data['items'] ?? []);
        } catch (Exception\CartNotFoundException $_) {
            return $this->error404();
        } catch (Exception\InvalidCartStatusException $e) {
            return new ApiProblem(400, $e->getMessage());
        }

        return $this->fetch($uuid);
    }

    /**
     * Fetch all carts, this normally would be behind a login/session wall
     * but I don't want to overcomplicate this more than I already have
     * @param array $params
     * @return Collection|mixed
     */
    public function fetchAll(array $params)
    {
        return new Collection(
            $this->cartService->prepareCollection($params['filters'] ?? [])
        );
    }

    /**
     * Fetch a single cart, again that would be protected by some wall
     * so other people wouldn't be able to guess other clients cart id
     * @param mixed $id
     * @return ApiProblem|\App\Response\ApiProblemResponse|Entity|mixed|null
     */
    public function fetch($id)
    {
        $uuid = $this->cartService->convertToUuid($id);
        if (null === $uuid) {
            return new ApiProblem(400, 'Provided uuid cannot be processed');
        }

        $find = $this->cartService->getByUuid($uuid);
        if (empty($find)) {
            return $this->error404();
        }

        $entity = new DetailedEntity();
        $entity->exchangeArray($find);
        return $entity;
    }
}
