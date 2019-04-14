<?php

namespace App\Factory;

use MongoDB\Client;
use MongoDB\Database;
use Psr\Container\ContainerInterface;

class MongoDbFactory
{
    /**
     * @param ContainerInterface $container
     * @return Database
     * @throws Exception\InsufficientConfigException
     */
    public function __invoke(ContainerInterface $container): Database
    {
        $config = $container->get('config') ?? [];
        $config = $config['mongo'] ?? [];

        if (empty($config['uri'])) {
            throw new Exception\InsufficientConfigException('`mongo.uri` cannot be empty');
        }

        if (empty($config['database'])) {
            throw new Exception\InsufficientConfigException('`mongo.database` cannot be empty');
        }

        $mongo = new Client(
            $config['uri'],
            $config['uri_options'] ?? [],
            $config['driver_options'] ?? []
        );

        return $mongo->selectDatabase($config['database']);
    }
}
