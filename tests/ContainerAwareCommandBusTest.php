<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle;

use Innmind\CommandBusBundle\{
    ContainerAwareCommandBus,
    Factory\ContainerAwareCommandBusFactory
};
use Innmind\CommandBus\CommandBusInterface;
use Innmind\Immutable\Map;
use Symfony\Component\DependencyInjection\{
    ContainerInterface,
    ContainerBuilder,
    Reference,
    Definition
};
use PHPUnit\Framework\TestCase;

class ContainerAwareCommandBusTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CommandBusInterface::class,
            new ContainerAwareCommandBus(
                $this->createMock(ContainerInterface::class),
                new Map('string', 'string')
            )
        );
    }

    /**
     * @expectedException Innmind\CommandBusBundle\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidHandlerMap()
    {
        new ContainerAwareCommandBus(
            $this->createMock(ContainerInterface::class),
            new Map('string', 'callable')
        );
    }

    /**
     * @expectedException Innmind\CommandBus\Exception\InvalidArgumentException
     */
    public function testThrowWhenCommandIsNotAnObject()
    {
        (new ContainerAwareCommandBus(
            $this->createMock(ContainerInterface::class),
            new Map('string', 'string')
        ))->handle([]);
    }

    public function testHandleWithCircularReference()
    {
        $container = new ContainerBuilder;
        $mock = $this->createMock(CommandBusInterface::class);
        $handler = new class($container, $mock) {
            private $container;

            public function __construct(
                ContainerInterface $container,
                CommandBusInterface $bus
            ) {
                $this->container = $container;
            }

            public function __invoke()
            {
                $this->container->setParameter('called', true);
            }
        };
        $container->setDefinition(
            'handler',
            new Definition(
                get_class($handler),
                [
                    new Reference('service_container'),
                    new Reference('command_bus')
                ]
            )
        );
        $container->setDefinition(
            'command_bus',
            (new Definition(
                ContainerAwareCommandBus::class,
                [
                    new Reference('service_container'),
                    ['stdClass' => 'handler']
                ]
            ))
                ->setFactory([ContainerAwareCommandBusFactory::class, 'make'])
        );

        $this->assertFalse($container->hasParameter('called'));
        $this->assertNull($container->get('command_bus')->handle(new \stdClass));
        $this->assertTrue($container->hasParameter('called'));
        $this->assertTrue($container->getParameter('called'));
    }
}
