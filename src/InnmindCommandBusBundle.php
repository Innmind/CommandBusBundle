<?php
declare(strict_types = 1);

namespace Innmind\CommandBusBundle;

use Innmind\CommandBusBundle\DependencyInjection\Compiler\BuildCommandBusStackPass;
use Symfony\Component\{
    HttpKernel\Bundle\Bundle,
    DependencyInjection\ContainerBuilder
};

final class InnmindCommandBusBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BuildCommandBusStackPass);
    }
}
