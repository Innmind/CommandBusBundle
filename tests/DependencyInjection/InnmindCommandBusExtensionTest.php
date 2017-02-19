<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle\DependencyInjection;

use Innmind\CommandBusBundle\{
    DependencyInjection\InnmindCommandBusExtension,
    InnmindCommandBusBundle
};
use Symfony\Component\{
    HttpKernel\DependencyInjection\Extension,
    DependencyInjection\ContainerBuilder,
    DependencyInjection\Definition
};
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;

class InnmindCommandBusExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder;
        $extension = new InnmindCommandBusExtension;
        $container->setDefinition(
            'logger',
            new Definition(NullLogger::class)
        );

        $this->assertInstanceOf(Extension::class, $extension);
        $this->assertNull($extension->load(
            [],
            $container
        ));

        (new InnmindCommandBusBundle)->build($container);
        $container->compile();

        $this->assertTrue($container->hasParameter('innmind_command_bus.stack'));
        $this->assertSame(
            ['queue', 'logger', 'default'],
            $container->getParameter('innmind_command_bus.stack')
        );
        $this->assertSame(
            'innmind_command_bus.queue',
            (string) $container->getAlias('innmind_command_bus')
        );
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "innmind_command_bus.stack" should have at least 1 element(s) defined.
     */
    public function testThrowWhenEmptyStack()
    {
        (new InnmindCommandBusExtension)->load(
            [[
                'stack' => [],
            ]],
            new ContainerBuilder
        );
    }
}
