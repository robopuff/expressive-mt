<?php

namespace AppTest\V1\Cart;

use App\V1\Cart\Entity;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EntityTest extends TestCase
{
    public function testEntityWithoutValues()
    {
        $entity = new Entity();
        $entity->exchangeArray([]);
        $this->assertEquals([
            'uuid' => '',
            'status' => Entity::STATUS_CREATED,
            'created_at' => date(DATE_ATOM, 0),
            'updated_at' => date(DATE_ATOM, 0)
        ], $entity->getArrayCopy());
    }

    public function testEntityWithValidValues()
    {
        $uuid = Uuid::uuid4();
        $time = time();

        $entity = new Entity();
        $entity->exchangeArray([
            'uuid' => $uuid,
            'status' => Entity::STATUS_COMPLETED,
            'created_at' => $time - 10,
            'updated_at' => $time
        ]);
        $this->assertEquals([
            'uuid' => $uuid->toString(),
            'status' => Entity::STATUS_COMPLETED,
            'created_at' => date(DATE_ATOM, $time - 10),
            'updated_at' => date(DATE_ATOM, $time)
        ], $entity->getArrayCopy());
    }
}
