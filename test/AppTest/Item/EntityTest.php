<?php

namespace AppTest\Item;

use App\Item\Entity;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function testEntityWithoutValues()
    {
        $entity = new Entity();
        $entity->exchangeArray([]);
        $this->assertEquals([
            'id' => 0,
            'unit_price' => 0,
            'qty' => 1
        ], $entity->getArrayCopy());
    }

    public function testEntityWithValues()
    {
        $data = [
            'id' => \random_int(1, 11111),
            'unit_price' => \random_int(1, 222222),
            'qty' => \random_int(1, 303)
        ];

        $entity = new Entity();
        $entity->exchangeArray($data);
        $this->assertEquals($data, $entity->getArrayCopy());
    }

    public function testEntityWithInvalidValues()
    {
        $data = [
            'id' => 1,
            'unit_price' => -1000,
            'qty' => -2
        ];

        $entity = new Entity();
        $entity->exchangeArray($data);
        $this->assertEquals([
            'id' => 1,
            'unit_price' => 1000,
            'qty' => 2
        ], $entity->getArrayCopy());
    }
}
