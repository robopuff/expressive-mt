<?php

namespace AppTest\V1\Cart;

use App\V1\Cart\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testCollectionConstructor()
    {
        $documents = [
            ['document' => 1],
            ['document' => 2]
        ];

        // In this case collection doesn't really require a test case but there are always
        // more complicated classes that would need some validation
        $collection = new Collection($documents);
        $this->assertEquals(
            $documents,
            (array) $collection->getCurrentItems()
        );
    }
}
