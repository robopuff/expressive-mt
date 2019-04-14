<?php

namespace App\V1\Cart;

use Ramsey\Uuid\UuidInterface;
use Zend\Stdlib\ArraySerializableInterface;

class Entity implements ArraySerializableInterface
{
    public const STATUS_CREATED = 'created';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_COMPLETED = 'completed';

    /**
     * @var UuidInterface|string
     */
    private $uuid;

    /**
     * @var string
     */
    private $status = self::STATUS_CREATED;

    /**
     * @var int
     */
    private $createdAt = 0;

    /**
     * @var int
     */
    private $updatedAt = 0;

    /**
     * Exchange internal values from provided array
     *
     * @param array $array
     * @return void
     */
    public function exchangeArray(array $array): void
    {
        $this->uuid = $array['uuid'] ?? '';
        $this->status = $array['status'] ?? self::STATUS_CREATED;
        $this->createdAt = $array['created_at'] ?? 0;
        $this->updatedAt = $array['updated_at'] ?? 0;
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'   => $this->uuid instanceof UuidInterface ? $this->uuid->toString() : $this->uuid,
            'status' => $this->status,
            'created_at' => date(DATE_ATOM, $this->createdAt),
            'updated_at' => date(DATE_ATOM, $this->updatedAt)
        ];
    }
}
