<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle\Factory;

use Innmind\CommandBusBundle\{
    ContainerAwareCommandBus,
    Factory\ContainerAwareCommandBusFactory
};
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

class ContainerAwareCommandBusFactoryTest extends TestCase
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
