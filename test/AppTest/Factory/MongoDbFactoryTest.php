<?php

namespace AppTest\Factory;

use App\Factory\Exception\InsufficientConfigException;
use App\Factory\MongoDbFactory;
use PHPUnit\Framework\TestCase;

class MongoDbFactoryTest extends TestCase
{
    public function testDatabaseWithValidConfig()
    {
        $container = new \Zend\ServiceManager\ServiceManager();
        $container->setService('config', [
            'mongo' => [
                'uri' => 'mongodb://localhost',
                'database' => 'mt_test'
            ]
        ]);

        $factory = new MongoDbFactory();
        $client = $factory($container);

        $this->assertInstanceOf(\MongoDB\Database::class, $client);
    }

    public function testDatabaseWithoutUriInConfig()
    {
        $container = new \Zend\ServiceManager\ServiceManager();
        $container->setService('config', [
            'mongo' => [
                'database' => 'mt_test'
            ]
        ]);

        $factory = new MongoDbFactory();

        $this->expectException(InsufficientConfigException::class);
        $client = $factory($container);
    }

    public function testDatabaseWithoutDatabasenConfig()
    {
        $container = new \Zend\ServiceManager\ServiceManager();
        $container->setService('config', [
            'mongo' => [
                'uri' => 'mongodb://localhost'
            ]
        ]);

        $factory = new MongoDbFactory();

        $this->expectException(InsufficientConfigException::class);
        $client = $factory($container);
    }
}
