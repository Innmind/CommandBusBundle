<?php
declare(strict_types = 1);

namespace Tests\Innmind\CommandBusBundle;

use Innmind\CommandBusBundle\{
    InnmindCommandBusBundle,
    DependencyInjection\Compiler\BuildCommandBusStackPass,
    DependencyInjection\Compiler\RegisterHandlersPass
};
use Symfony\Component\{
    HttpKernel\Bundle\Bundle,
    DependencyInjection\ContainerBuilder
};

class InnmindCommandBusBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $bundle = new InnmindCommandBusBundle;
        $container = new ContainerBuilder;

        $this->assertInstanceOf(Bundle::class, $bundle);
        $this->assertNull($bundle->build($container));
        $passes = $container
            ->getCompilerPassConfig()
            ->getBeforeOptimizationPasses();
        $this->assertSame(2, count($passes));
        $this->assertInstanceOf(
            BuildCommandBusStackPass::class,
            $passes[0]
        );
        $this->assertInstanceOf(
            RegisterHandlersPass::class,
            $passes[1]
        );
    }
}
