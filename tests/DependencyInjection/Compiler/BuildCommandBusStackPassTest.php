<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle\DependencyInjection\Compiler;

use Innmind\CommandBusBundle\{
    DependencyInjection\Compiler\BuildCommandBusStackPass,
    ContainerAwareCommandBus,
    Factory\ContainerAwareCommandBusFactory
};
use Innmind\CommandBus\{
    CommandBusInterface,
    QueueableCommandBus
};
use Symfony\Component\DependencyInjection\{
    Compiler\CompilerPassInterface,
    ContainerBuilder,
    Definition,
    Reference
};
use PHPUnit\Framework\TestCase;

class BuildCommandBusStackPassTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CompilerPassInterface::class,
            new BuildCommandBusStackPass
        );
    }

    public function testProcess()
    {
        $container = new ContainerBuilder;
        $mock = $this->createMock(CommandBusInterface::class);
        $bus1 = new class($mock, 'foo') implements CommandBusInterface {
            public static $called = false;
            private $bus;

            public function __construct(CommandBusInterface $bus, string $whatever)
            {
                $this->bus = $bus;
            }

            public function handle($command)
            {
                $this->bus->handle($command);
                self::$called = true;
            }
        };
        $bus2 = new class('bar', $mock) implements CommandBusInterface {
            public static $called = false;
            private $bus;

            public function __construct(string $whatever, CommandBusInterface $bus)
            {
                $this->bus = $bus;
            }

            public function handle($command)
            {
                $this->bus->handle($command);
                self::$called = true;
            }
        };
        $container->setDefinition(
            'command_bus.queue',
            (new Definition(QueueableCommandBus::class, [null]))->addTag(
                'innmind_command_bus',
                ['alias' => 'queue']
            )
        );
        $container->setDefinition(
            'command_bus.first',
            (new Definition(get_class($bus1), [null, 'foo']))->addTag(
                'innmind_command_bus',
                ['alias' => 'first']
            )
        );
        $container->setDefinition(
            'command_bus.second',
            (new Definition(get_class($bus2), ['bar', null]))->addTag(
                'innmind_command_bus',
                ['alias' => 'second']
            )
        );
        $container->setDefinition(
            'command_bus.default',
            (new Definition(
                ContainerAwareCommandBus::class,
                [
                    new Reference('service_container'),
                    ['stdClass' => 'handler']
                ]
            ))
                ->setFactory([ContainerAwareCommandBusFactory::class, 'make'])
                ->addTag('innmind_command_bus', ['alias' => 'default'])
        );
        $container->set(
            'handler',
            function($command) {}
        );
        $container->setParameter(
            'innmind_command_bus.stack',
            ['queue', 'first', 'second', 'default']
        );

        $this->assertNull((new BuildCommandBusStackPass)->process($container));
        $this->assertSame(
            'command_bus.queue',
            (string) $container->getAlias('innmind_command_bus')
        );
        $this->assertInstanceOf(
            Reference::class,
            $container->getDefinition('command_bus.queue')->getArgument(0)
        );
        $this->assertInstanceOf(
            Reference::class,
            $container->getDefinition('command_bus.first')->getArgument(0)
        );
        $this->assertInstanceOf(
            Reference::class,
            $container->getDefinition('command_bus.second')->getArgument(1)
        );
        $this->assertSame(
            'command_bus.first',
            (string) $container->getDefinition('command_bus.queue')->getArgument(0)
        );
        $this->assertSame(
            'command_bus.second',
            (string) $container->getDefinition('command_bus.first')->getArgument(0)
        );
        $this->assertSame(
            'command_bus.default',
            (string) $container->getDefinition('command_bus.second')->getArgument(1)
        );
        $this->assertNull($container->get('innmind_command_bus')->handle(new \stdClass));
        $this->assertTrue($bus1::$called);
        $this->assertTrue($bus2::$called);
    }

    /**
     * @expectedException Innmind\CommandBusBundle\Exception\LogicException
     */
    public function testThrowWhenStackIsNotDefined()
    {
        (new BuildCommandBusStackPass)->process(new ContainerBuilder);
    }

    public function testProcessWithOneElementInTheStack()
    {
        $container = new ContainerBuilder;
        $container->setParameter('innmind_command_bus.stack', ['default']);
        $container->setDefinition(
            'command_bus.default',
            (new Definition(
                ContainerAwareCommandBus::class,
                [
                    new Reference('service_container'),
                    ['stdClass' => 'handler']
                ]
            ))
                ->setFactory([ContainerAwareCommandBusFactory::class, 'make'])
                ->addTag('innmind_command_bus', ['alias' => 'default'])
        );

        $this->assertNull((new BuildCommandBusStackPass)->process($container));
        $this->assertSame(
            'command_bus.default',
            (string) $container->getAlias('innmind_command_bus')
        );
    }

    /**
     * @expectedException Innmind\CommandBusBundle\Exception\LogicException
     */
    public function testThrowWhenMissingAlias()
    {
        $container = new ContainerBuilder;
        $container->setParameter('innmind_command_bus.stack', ['default']);
        $container->setDefinition(
            'command_bus.default',
            (new Definition(
                ContainerAwareCommandBus::class,
                [
                    new Reference('service_container'),
                    ['stdClass' => 'handler']
                ]
            ))
                ->addTag('innmind_command_bus')
        );

        (new BuildCommandBusStackPass)->process($container);
    }

    /**
     * @expectedException Innmind\CommandBusBundle\Exception\LogicException
     * @expectedExceptionMessageRegExp /^Missing argument type hinted with CommandBusInterface for "class@anonymous.+"$/
     */
    public function testThrowWhenNoCommandBusTypeHint()
    {
        $container = new ContainerBuilder;
        $bus1 = new class implements CommandBusInterface {
            public function __construct()
            {
            }

            public function handle($command)
            {
                $this->bus->handle($command);
                self::$called = true;
            }
        };
        $container->setDefinition(
            'command_bus.queue',
            (new Definition(QueueableCommandBus::class, [null]))->addTag(
                'innmind_command_bus',
                ['alias' => 'queue']
            )
        );
        $container->setDefinition(
            'command_bus.first',
            (new Definition(get_class($bus1), []))->addTag(
                'innmind_command_bus',
                ['alias' => 'first']
            )
        );
        $container->setDefinition(
            'command_bus.default',
            (new Definition(
                ContainerAwareCommandBus::class,
                [
                    new Reference('service_container'),
                    []
                ]
            ))
                ->setFactory([ContainerAwareCommandBusFactory::class, 'make'])
                ->addTag('innmind_command_bus', ['alias' => 'default'])
        );
        $container->setParameter(
            'innmind_command_bus.stack',
            ['queue', 'first', 'default']
        );

        (new BuildCommandBusStackPass)->process($container);
    }
}
