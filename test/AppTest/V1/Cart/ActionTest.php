<?php

namespace AppTest\V1\Cart;

use App\ApiProblem;
use App\Response\ApiProblemResponse;
use App\V1\Cart\Action;
use App\V1\Cart\Collection;
use App\V1\Cart\DetailedEntity;
use App\V1\Cart\Entity;
use App\V1\Cart\Exception\CartNotFoundException;
use App\V1\Cart\Exception\InvalidCartStatusException;
use App\V1\Cart\Service;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;

class ActionTest extends TestCase
{
    /**
     * @var Service|ObjectProphecy
     */
    private $service;

    public function setUp()
    {
        $this->service = $this->prophesize(Service::class);
    }

    public function testFetchMethodWithInvalidUuid()
    {
        $this->service->convertToUuid('')->shouldBeCalled()->willReturn(null);
        $action = new Action($this->service->reveal());
        $fetch = $action->fetch('');
        $this->assertInstanceOf(ApiProblem::class, $fetch);
    }

    public function testDeleteWithInvalidUuid()
    {
        $this->service->convertToUuid('')->shouldBeCalled()->willReturn(null);
        $action = new Action($this->service->reveal());
        $fetch = $action->delete('');
        $this->assertInstanceOf(ApiProblem::class, $fetch);
    }

    public function testPatchWithInvalidUuid()
    {
        $this->service->convertToUuid('')->shouldBeCalled()->willReturn(null);
        $action = new Action($this->service->reveal());
        $fetch = $action->patch('', []);
        $this->assertInstanceOf(ApiProblem::class, $fetch);
    }

    public function testFetchMethodWithoutValidDocument()
    {
        $uuid = Uuid::uuid4();

        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->getByUuid($uuid)->shouldBeCalled()->willReturn([]);

        $action = new Action($this->service->reveal());
        $fetch = $action->fetch($uuid->toString());
        $this->assertInstanceOf(ApiProblemResponse::class, $fetch);
    }

    public function testFetchMethodWithValidDocument()
    {
        $uuid = Uuid::uuid4();

        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->getByUuid($uuid)->shouldBeCalled()->willReturn([
            'uuid' => $uuid->toString(),
            'status' => Entity::STATUS_CREATED,
            'updated_at' => 2,
            'created_at' => 1,
            'items' => [
                [
                    'id' => 1,
                    'unit_price' => 1,
                    'qty' => 2
                ]
            ]
        ]);

        $action = new Action($this->service->reveal());
        $fetch = $action->fetch($uuid->toString());
        $this->assertInstanceOf(DetailedEntity::class, $fetch);
    }

    public function testFetchAllWithValidData()
    {
        $entity = new Entity();
        $this->service->prepareCollection([])->shouldBeCalled()->willReturn([
            $entity
        ]);

        $action = new Action($this->service->reveal());
        $fetchAll = $action->fetchAll([]);
        $this->assertInstanceOf(Collection::class, $fetchAll);
    }

    public function testFetchAllWithEmptyData()
    {
        $entity = new Entity();
        $this->service->prepareCollection([])->shouldBeCalled()->willReturn([]);

        $action = new Action($this->service->reveal());
        $fetchAll = $action->fetchAll([]);
        $this->assertInstanceOf(Collection::class, $fetchAll);
    }

    public function testCreateWithValidData()
    {
        $uuid = Uuid::uuid4();
        $data = [
            'items' => [
                'id' => 1,
                'unit_price' => 4,
                'qty' => 2
            ]
        ];

        $this->service->create($data['items'])
            ->shouldBeCalled()
            ->willReturn($uuid);

        $this->service->convertToUuid($uuid)->shouldBeCalled()->willReturn($uuid);
        $this->service->getByUuid($uuid)->shouldBeCalled()->willReturn([
            'uuid' => $uuid
        ]);

        $action = new Action($this->service->reveal());
        $create = $action->create($data);
        $this->assertInstanceOf(DetailedEntity::class, $create);
    }

    public function testDeleteWithInvalidDocument()
    {
        $uuid = Uuid::uuid4();
        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->delete($uuid)->shouldBeCalled()->will(function () {
            throw new CartNotFoundException();
        });

        $action = new Action($this->service->reveal());
        $delete = $action->delete($uuid->toString());
        $this->assertInstanceOf(ApiProblemResponse::class, $delete);
    }

    public function testDeleteWithValidDocument()
    {
        $uuid = Uuid::uuid4();
        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->delete($uuid)->shouldBeCalled();
        $this->service->getByUuid($uuid)->shouldBeCalled()->willReturn([
            'uuid' => $uuid
        ]);

        $action = new Action($this->service->reveal());
        $delete = $action->delete($uuid->toString());
        $this->assertInstanceOf(DetailedEntity::class, $delete);
    }


    public function testPatchWithInvalidDocument()
    {
        $uuid = Uuid::uuid4();
        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->updateItems($uuid, [])->shouldBeCalled()->will(function () {
            throw new CartNotFoundException();
        });

        $action = new Action($this->service->reveal());
        $patch = $action->patch($uuid->toString(), []);
        $this->assertInstanceOf(ApiProblemResponse::class, $patch);
    }

    public function testPatchWithInvalidCartStatusDocument()
    {
        $uuid = Uuid::uuid4();
        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->updateItems($uuid, [])->shouldBeCalled()->will(function () {
            throw new InvalidCartStatusException('Invalid cart status');
        });

        $action = new Action($this->service->reveal());
        $patch = $action->patch($uuid->toString(), []);
        $this->assertInstanceOf(ApiProblem::class, $patch);
    }

    public function testPatchWithValidDocument()
    {
        $uuid = Uuid::uuid4();
        $this->service->convertToUuid($uuid->toString())->shouldBeCalled()->willReturn($uuid);
        $this->service->updateItems($uuid, [])->shouldBeCalled();
        $this->service->getByUuid($uuid)->shouldBeCalled()->willReturn([
            'uuid' => $uuid
        ]);

        $action = new Action($this->service->reveal());
        $patch = $action->patch($uuid->toString(), []);
        $this->assertInstanceOf(DetailedEntity::class, $patch);
    }
}
