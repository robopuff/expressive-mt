<?php

namespace App\V1\Cart;

use App\Item\Entity as ItemEntity;
use MongoDB\Database;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Service
{
    public const COLLECTION = 'cart';

    /**
     * @var \MongoDB\Collection
     */
    private $mongo;

    /**
     * Service constructor.
     * @param Database $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo->selectCollection(self::COLLECTION);
    }

    /**
     * Convert a string into uuid
     * @param string|UuidInterface $uuid
     * @return UuidInterface|null
     */
    public function convertToUuid($uuid): ?UuidInterface
    {
        if ($uuid instanceof UuidInterface) {
            return $uuid;
        }

        try {
            return Uuid::fromString($uuid);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Prepare list for collection
     * @param array $query
     * @return array
     */
    public function prepareCollection($query = []): array
    {
        $filter = [
            'status' => 'created'
        ];

        if (!empty($query['status'])) {
            $filter['status'] = [
                '$in' => (array) $query['status']
            ];
        }

        $find = $this->mongo->find($filter);
        $process = [];
        foreach ($find as $document) {
            $entity = new Entity();
            $entity->exchangeArray((array) $document);
            $process[] = $entity;
        }

        return $process;
    }

    /**
     * Get cart by it's uuid
     * @param UuidInterface $uuid
     * @return array
     */
    public function getByUuid(UuidInterface $uuid): array
    {
        return (array) $this->mongo->findOne([
            'uuid' => $uuid->toString()
        ]);
    }

    /**
     * Create new cart
     * @param array $items
     * @return UuidInterface
     * @throws \Exception
     */
    public function create(array $items = []): UuidInterface
    {
        $uuid = Uuid::uuid4();

        if (!empty($items)) {
            $items = $this->processItems($items);
            array_walk($items, static function (ItemEntity &$item) {
                $item = $item->getArrayCopy();
            });
        }

        $entity = new Entity();
        $entity->exchangeArray([
            'uuid'   => $uuid,
            'status' => Entity::STATUS_CREATED,
        ]);

        $this->mongo->insertOne([
            'items'  => $items,
            'created_at' => time(),
            'updated_at' => time()
        ] + $entity->getArrayCopy());
        return $uuid;
    }

    /**
     * Update items in cart
     * @param UuidInterface $uuid
     * @param array $items
     * @throws Exception\CartNotFoundException
     * @throws Exception\InvalidCartStatusException
     */
    public function updateItems(UuidInterface $uuid, array $items): void
    {
        $find = $this->getByUuid($uuid);
        if (!$find) {
            throw new Exception\CartNotFoundException();
        }

        if ($find['status'] !== Entity::STATUS_CREATED) {
            throw new Exception\InvalidCartStatusException(
                sprintf('Cart status is `%s`, can modify only `%s`', $find['status'], Entity::STATUS_CREATED)
            );
        }

        $currentItems = [];
        foreach ($find['items'] as $item) {
            $currentItems[$item['id']] = $item;
        }

        $items = $this->processItems($items);
        foreach ($items as $item) {
            $item = $item->getArrayCopy();
            if (!isset($currentItems[$item['id']])) {
                $currentItems[$item['id']] = $item;
                continue;
            }

            if ($item['qty'] === 0) {
                unset($currentItems[$item['id']]);
                continue;
            }

            if ($item['unit_price'] === 0) {
                $item['unit_price'] = $currentItems[$item['id']]['unit_price'];
            }

            $currentItems[$item['id']] = $item;
        }

        $this->mongo->updateOne([
            '_id' => $find['_id']
        ], [
            '$set' => [
                'updated_at' => time(),
                'items' => array_values($currentItems)
            ]
        ]);
    }

    /**
     * Mark cart as deleted
     * @param UuidInterface $uuid
     * @throws Exception\CartNotFoundException
     */
    public function delete(UuidInterface $uuid): void
    {
        $find = $this->getByUuid($uuid);
        if (!$find) {
            throw new Exception\CartNotFoundException();
        }

        $this->mongo->updateOne([
            '_id' => $find['_id']
        ], [
            '$set' => [
                'status' => Entity::STATUS_DELETED,
                'updated_at' => time()
            ]
        ]);
    }

    /**
     * Process items from array to entity
     * @param array $items
     * @return array|ItemEntity[]
     */
    private function processItems(array $items): array
    {
        $processed = [];
        foreach ($items as $item) {
            $entity = new ItemEntity();
            $entity->exchangeArray($item);

            $processed[] = $entity;
        }
        return $processed;
    }
}
