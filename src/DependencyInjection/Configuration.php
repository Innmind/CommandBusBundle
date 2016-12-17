<?php
declare(strict_types = 1);

namespace Innmind\CommandBusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\{
    Builder\TreeBuilder,
    ConfigurationInterface
};

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder;
        $root = $treeBuilder->root('innmind_command_bus');

        $root
            ->children()
                ->arrayNode('stack')
                    ->requiresAtLeastOneElement()
                    ->defaultValue(['queue', 'logger', 'default'])
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
