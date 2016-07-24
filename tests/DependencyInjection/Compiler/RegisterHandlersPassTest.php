<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle\DependencyInjection\Compiler;

use Innmind\CommandBusBundle\DependencyInjection\{
    Compiler\RegisterHandlersPass,
    InnmindCommandBusExtension
};
use Symfony\Component\DependencyInjection\{
    Compiler\CompilerPassInterface,
    ContainerBuilder,
    Definition
};

class RegisterHandlersPassTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            CompilerPassInterface::class,
            new RegisterHandlersPass
        );
    }

    public function testProcess()
    {
        $container = new ContainerBuilder;
        (new InnmindCommandBusExtension)->load([], $container);
        $container->setDefinition(
            'foo',
            (new Definition('stdClass'))->addTag(
                'innmind_command_bus.handler',
                ['handles' => 'bar']
            )
        );

        $this->assertNull((new RegisterHandlersPass)->process($container));
        $handlers = $container
            ->getDefinition('innmind_command_bus.default')
            ->getArgument(1);
        $this->assertCount(1, $handlers);
        $this->assertSame(['bar' => 'foo'], $handlers);
    }

    /**
     * @expectedException Innmind\CommandBusBundle\Exception\LogicException
     */
    public function testThrowWhenMissingHandlesAttribute()
    {
        $container = new ContainerBuilder;
        (new InnmindCommandBusExtension)->load([], $container);
        $container->setDefinition(
            'foo',
            (new Definition('stdClass'))->addTag(
                'innmind_command_bus.handler'
            )
        );

        (new RegisterHandlersPass)->process($container);
    }
}
