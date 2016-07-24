<?php
declare(strict_types = 1);

namespace Innmind\CommandBusBundle\DependencyInjection\Compiler;

use Innmind\CommandBusBundle\Exception\LogicException;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Compiler\CompilerPassInterface
};

final class RegisterHandlersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('innmind_command_bus.handler');
        $services = [];

        foreach ($ids as $id => $tags) {
            foreach ($tags as $tag => $attributes) {
                if (!isset($attributes['handles'])) {
                    throw new LogicException;
                }

                $services[$attributes['handles']] = $id;
            }
        }

        $container
            ->getDefinition('innmind_command_bus.default')
            ->replaceArgument(1, $services);
    }
}
