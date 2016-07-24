<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle\Factory;

use Innmind\CommandBusBundle\{
    ContainerAwareCommandBus,
    Factory\ContainerAwareCommandBusFactory
};
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareCommandBusFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $bus = ContainerAwareCommandBusFactory::make(
            $this->createMock(ContainerInterface::class),
            ['stdClass' => 'foo']
        );

        $this->assertInstanceOf(ContainerAwareCommandBus::class, $bus);
    }
}
