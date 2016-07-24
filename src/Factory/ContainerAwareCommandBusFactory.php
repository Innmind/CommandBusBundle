<?php
declare(strict_types = 1);

namespace Innmind\CommandBusBundle\Factory;

use Innmind\CommandBusBundle\ContainerAwareCommandBus;
use Innmind\Immutable\Map;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerAwareCommandBusFactory
{
    public static function make(
        ContainerInterface $container,
        array $services
    ): ContainerAwareCommandBus {
        $map = new Map('string', 'string');

        foreach ($services as $class => $service) {
            $map = $map->put($class, $service);
        }

        return new ContainerAwareCommandBus($container, $map);
    }
}
