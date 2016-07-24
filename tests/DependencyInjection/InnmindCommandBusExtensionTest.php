<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle\DependencyInjection;

use Innmind\CommandBusBundle\DependencyInjection\InnmindCommandBusExtension;
use Symfony\Component\{
    HttpKernel\DependencyInjection\Extension,
    DependencyInjection\ContainerBuilder
};

class InnmindCommandBusExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder;
        $extension = new InnmindCommandBusExtension;

        $this->assertInstanceOf(Extension::class, $extension);
        $this->assertNull($extension->load(
            [[
                'stack' => ['foo'],
            ]],
            $container
        ));
        $this->assertTrue($container->hasParameter('innmind_command_bus.stack'));
        $this->assertSame(
            ['foo'],
            $container->getParameter('innmind_command_bus.stack')
        );
    }
}
