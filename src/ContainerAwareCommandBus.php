<?php
declare(strict_types = 1);

namespace Innmind\CommandBusBundle;

use Innmind\CommandBusBundle\Exception\InvalidArgumentException;
use Innmind\CommandBus\{
    CommandBusInterface,
    CommandBus,
    Exception\InvalidArgumentException as InvalidCommandException
};
use Innmind\Immutable\{
    Map,
    MapInterface
};
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContainerAwareCommandBus implements CommandBusInterface
{
    private $container;
    private $handlers;
    private $bus;

    public function __construct(
        ContainerInterface $container,
        MapInterface $handlers
    ) {
        if (
            (string) $handlers->keyType() !== 'string' ||
            (string) $handlers->valueType() !== 'string'
        ) {
            throw new InvalidArgumentException;
        }

        $this->container = $container;
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($command)
    {
        if (!is_object($command)) {
            throw new InvalidCommandException;
        }

        if (!$this->bus instanceof CommandBusInterface) {
            $this->initialize();
        }

        $this->bus->handle($command);
    }

    private function initialize()
    {
        $handlers = $this
            ->handlers
            ->reduce(
                new Map('string', 'callable'),
                function(Map $carry, string $class, string $service): Map {
                    return $carry->put(
                        $class,
                        $this->container->get($service)
                    );
                }
            );
        $this->bus = new CommandBus($handlers);
    }
}
