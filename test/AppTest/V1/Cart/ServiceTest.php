<?php

namespace AppTest\V1\Cart;

use App\V1\Cart\Entity;
use App\V1\Cart\Exception\CartNotFoundException;
use App\V1\Cart\Exception\InvalidCartStatusException;
use App\V1\Cart\Service;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;

class ServiceTest extends TestCase
{
    /**
     * @var Database|ObjectProphecy
     */
    private $mongo;

    /**
     * @var Collection|ObjectProphecy
     */
    private $collection;

    public function setUp()
    {
        $this->collection = $this->prophesize(Collection::class);

        $this->mongo = $this->prophesize(Database::class);
        $this->mongo->selectCollection(Service::COLLECTION)->will([$this->collection, 'reveal']);
    }

    public function testUuidIsConvertedWithValidUuidInterface()
    {
        $uuid = Uuid::uuid4();
        $service = new Service($this->mongo->reveal());

        $this->assertSame($uuid, $service->convertToUuid($uuid));
    }

    public function testUuidIsConvertedWithValidString()
    {
        $uuid = Uuid::uuid4();
        $service = new Service($this->mongo->reveal());

        $this->assertEquals($uuid, $service->convertToUuid($uuid->toString()));
    }

    public function testUuidIsConvertedWithInvalidValidProperty()
    {
        $service = new Service($this->mongo->reveal());
        $this->assertNull($service->convertToUuid(\random_bytes(4)));
    }

    public function testPrepareCollection()
    {
        $uuid = Uuid::uuid4()->toString();
        $this->collection->find([
            'status' => [
                '$in' => [
                    'created', 'deleted'
                ]
            ]
        ])->shouldBeCalled()->willReturn([
            [
                'uuid' => $uuid,
            ]
        ]);
        $service = new Service($this->mongo->reveal());
        $collection = $service->prepareCollection(['status' => [
            'created', 'deleted'
        ]]);

        $this->assertArrayHasKey(0, $collection);
        $this->assertInstanceOf(Entity::class, $collection[0]);
        $this->assertEquals($uuid, $collection[0]->getArrayCopy()['uuid']);
    }

    public function testGetByUuid()
    {
        $uuid = Uuid::uuid4();
        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn([
            'uuid' => $uuid->toString()
        ]);

        $service = new Service($this->mongo->reveal());
        $result = $service->getByUuid($uuid);
        $this->assertEquals([
            'uuid' => $uuid->toString(),
        ], $result);
    }

    public function testCreateWithItems()
    {
        $this->collection->insertOne(Argument::that(function ($a) {
            return isset($a['uuid']) && is_string($a['uuid'])
                && isset($a['status']) && $a['status'] === Entity::STATUS_CREATED
                && isset($a['items']) && is_array($a['items'])
                && isset($a['created_at']) && is_int($a['created_at'])
                && isset($a['updated_at']) && is_int($a['updated_at']);
        }))->shouldBeCalled();

        $service = new Service($this->mongo->reveal());
        $create = $service->create([
            [
                'id' => 1,
                'qty' => 7,
                'unit_price' => 12300,
                'random' => 'no'
            ]
        ]);
    }

    public function testUpdateItemsWithValidDocument()
    {
        $id = new ObjectId();
        $uuid = Uuid::uuid4();

        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn([
            '_id' => $id,
            'uuid' => $uuid->toString(),
            'status' => Entity::STATUS_CREATED,
            'items' => [
                [
                    'id' => 1,
                    'qty' => 2,
                    'unit_price' => 1230
                ],
                [
                    'id' => 2,
                    'qty' => 1,
                    'unit_price' => 5050
                ]
            ]
        ]);

        $this->collection->updateOne([
            '_id' => $id
        ], Argument::that(function ($a) {
            $s = $a['$set'] ?? [];
            return isset($a['$set']) && is_array($a['$set'])
                && isset($s['updated_at']) && is_int($s['updated_at'])
                && isset($s['items']) && $s['items'] === [
                    ['id' => 2, 'unit_price' => 5050, 'qty' => 2],
                    ['id' => 3, 'unit_price' => 22100, 'qty' => 3]
                ];
        }))->shouldBeCalled();

        $service = new Service($this->mongo->reveal());
        $service->updateItems($uuid, [
            [
                'id' => 1,
                'qty' => 0
            ],
            [
                'id' => 2,
                'qty' => 2
            ],
            [
                'id' => 3,
                'qty' =>3,
                'unit_price' => 22100
            ]
        ]);
    }

    public function testUpdateItemsWithDocumentNotFound()
    {
        $uuid = Uuid::uuid4();

        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn(null);

        $service = new Service($this->mongo->reveal());

        $this->expectException(CartNotFoundException::class);
        $service->updateItems($uuid, []);
    }

    public function testUpdateItemsWithDocumentStatusDeleted()
    {
        $uuid = Uuid::uuid4();

        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn([
            'status' => Entity::STATUS_DELETED
        ]);

        $service = new Service($this->mongo->reveal());

        $this->expectException(InvalidCartStatusException::class);
        $service->updateItems($uuid, []);
    }

    public function testUpdateItemsWithDocumentStatusCompleted()
    {
        $uuid = Uuid::uuid4();

        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn([
            'status' => Entity::STATUS_COMPLETED
        ]);

        $service = new Service($this->mongo->reveal());

        $this->expectException(InvalidCartStatusException::class);
        $service->updateItems($uuid, []);
    }

    public function testDeleteWithValidDocument()
    {
        $id = new ObjectId();
        $uuid = Uuid::uuid4();

        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn([
            '_id' => $id
        ]);

        $this->collection->updateOne([
            '_id' => $id
        ], Argument::that(function ($a) {
            $s = $a['$set'] ?? [];
            return isset($a['$set']) && is_array($a['$set'])
                && isset($s['updated_at']) && is_int($s['updated_at'])
                && isset($s['status']) && $s['status'] === Entity::STATUS_DELETED;
        }))->shouldBeCalled();

        $service = new Service($this->mongo->reveal());
        $service->delete($uuid);
    }

    public function testDeleteWithInvalidDocument()
    {
        $uuid = Uuid::uuid4();

        $this->collection->findOne([
            'uuid' => $uuid->toString()
        ])->shouldBeCalled()->willReturn(null);

        $service = new Service($this->mongo->reveal());

        $this->expectException(CartNotFoundException::class);
        $service->delete($uuid);
    }
}
