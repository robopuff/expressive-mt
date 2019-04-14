<?php

namespace App\V1\Cart;

use Psr\Container\ContainerInterface;

class ActionFactory
{
    /**
     * Get instance of Action
     * @param ContainerInterface $container
     * @return Action
     */
    public function __invoke(ContainerInterface $container): Action
    {
        return new Action(
            $container->get(Service::class)
        );
    }
}
