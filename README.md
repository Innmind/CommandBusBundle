# CommandBusBundle

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/CommandBusBundle/build-status/develop) |

Symfony integration of `innmind/command-bus` that ease stacking command buses.

## Installation

```sh
composer require innmind/command-bus-bundle
```

In your `AppKernel.php` add the following line:
```php
//app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Innmind\CommandBusBundle\InnmindCommandBusBundle,
        );
        // ...
    }
    // ...
}
```

## Usage

```php
$container->get('innmind_command_bus');
```

In order to handle your commands you need to define the handlers as services with the tag `innmind_command_bus.handler` with the attribte `handles` that will contain the command FQCN.

## Advanced configuration

You may want to add extra capabilities to the command bus to do some specific stuff before or after each command is executed (like flushing the doctrine manager or logging each command).
To do so you need to create a class that implements [`CommandBusInterface`](https://github.com/Innmind/CommandBus/blob/master/src/CommandBusInterface.php). The class must at least have one argument in its contructor type hinted with this interface.

Then you declare this command bus as a service with a tag `innmind_command_bus` and an attribute `alias`. Then the alias must be placed in the `innminc_command_bus.stack` configuration array.

Example:
```php
use Innmind\CommandBus\CommandBusInterface;
use Psr\Log\LoggerInterface;

final class LoggingCommandBus implements CommandBusInterface
{
    private $bus;
    private $logger;

    public function __construct(LoggerInterface $logger, CommandBusInterface $bus)
    {
        $this->logger = $logger;
        $this->bus = $bus;
    }

    public function handle($command)
    {
        $this->bus->handle($command);
        $this->logger->debug(
            'A command has been executed',
            ['class' => get_class($command)]
        );
    }
}
```

```yml
#app/config/services.yml
services:
    logging_command_bus:
        class: LoggingCommandBus
        arguments:
            - '@logger'
            - ~ #this is important to declare this argument as null
        tags:
            - { name: innmind_command_bus, alias: logging }
```

```yml
#app/config/config.yml
innmind_command_bus:
    stack:
        - queue
        - logging
        - default
```

With all this each time you handle a command it will look if there's a handle being handled (in such case it will queue it; this happens if you handle a command inside a handler), then if it can be executed it will log the command (this is your class) and finally it will call the handler associated to the command.
