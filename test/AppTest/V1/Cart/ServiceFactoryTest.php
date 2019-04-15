<?php

namespace AppTest\V1\Cart;

use App\V1\Cart\Service;
use App\V1\Cart\ServiceFactory;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ServiceFactoryTest extends TestCase
{
    public function testInvokeWithAllRequirements()
    {
        $database = $this->prophesize(Database::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Database::class)
            ->shouldBeCalled()
            ->will([$database, 'reveal']);

        $factory = new ServiceFactory();
        $this->assertInstanceOf(
            Service::class,
            $factory($container->reveal())
        );
    }
}
