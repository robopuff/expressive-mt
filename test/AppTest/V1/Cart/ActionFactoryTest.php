<?php

namespace AppTest\V1\Cart;

use App\V1\Cart\Action;
use App\V1\Cart\ActionFactory;
use App\V1\Cart\Service;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ActionFactoryTest extends TestCase
{
    public function testInvokeWithAllRequirements()
    {
        $service = $this->prophesize(Service::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Service::class)
            ->shouldBeCalled()
            ->will([$service, 'reveal']);

        $factory = new ActionFactory();
        $this->assertInstanceOf(
            Action::class,
            $factory($container->reveal())
        );
    }
}
