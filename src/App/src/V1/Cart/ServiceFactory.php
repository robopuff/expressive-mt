<?php

namespace App\V1\Cart;

use MongoDB\Database;
use Psr\Container\ContainerInterface;

class ServiceFactory
{
    /**
     * Get instance of Service
     * @param ContainerInterface $container
     * @return Service
     */
    public function __invoke(ContainerInterface $container): Service
    {
        return new Service(
            $container->get(Database::class)
        );
    }
}
