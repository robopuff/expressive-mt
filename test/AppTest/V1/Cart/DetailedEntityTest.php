<?php

namespace AppTest\V1\Cart;

use App\V1\Cart\DetailedEntity;

class DetailedEntityTest extends EntityTest
{
    public function testItemsPropagation()
    {
        $items = [
            [
                'id' => 1,
                'unit_price' => 1,
                'qty' => 7
            ],
            [
                'id' => 2,
                'unit_price' => 2,
                'qty' => 3
            ]
        ];

        $entity = new DetailedEntity();
        $entity->exchangeArray([
            'items' => $items
        ]);

        $array = $entity->getArrayCopy();
        $this->assertArrayHasKey('items', $array);
        $this->assertSame($items, $array['items']);
    }

    public function testItemsValueCalculation()
    {
        $items = [
            [
                'id' => 1,
                'unit_price' => 100,
                'qty' => 7
            ],
            [
                'id' => 2,
                'unit_price' => 233,
                'qty' => 3
            ]
        ];

        $entity = new DetailedEntity();
        $entity->exchangeArray([
            'items' => $items
        ]);

        $array = $entity->getArrayCopy();
        $this->assertArrayHasKey('total_items', $array);
        $this->assertSame(10, $array['total_items']);
        $this->assertSame(1399, $array['total_value']);
        $this->assertSame('13.99', $array['total_value_in_eur']);
    }
}
