# Applicative cron task runner

Simple applicative cron task implementation.

How it works:

 - You as the library user register callables as being cron tasks, using
   a descriptive attribute.

 - It can be any callable, a function, an anonymous function, an instance
   method, a class static method or an invokable class instance.

 - You need to setup a system cron entry for running the applicative cron,
   depending upon your framework, using Symfony a CLI command is provided.
   This tick must run very often, such as every minute.

 - When the cron runs, for each registered task, it checks its schedule
   againsts the current sytem date.

 - For each matching schedule, it checks first for the configured minimum
   delay between two run and excludes tasks which have been run too recently.

 - For each remaining non excluded task, it runs it, and store the latest
   run data long error message and error trace if any error occured.

Simply put, it takes a task list, test each task schedule against current date,
and run it when it matches.

State is per default kept in memory during runtime, then discarded. Future
implementations will allow you to store it within PDO and maybe other
backends.

State when persisted allows the user to change task schedule without changing
the code. Schedule is always stored as a raw string which allows alternative
implementations to exist.

Default schedule implementation accepts incomplete POSIX cron expression but
only with single digit values. An alternative implementation can use
`dragonmantank/cron-expression` for a more complete POSIX cron expression.

# Roadmap

 - [ ] `makinacorpus/goat-query` state store implementation,
 - [ ] `PDO` state store implementation,
 - [x] unit test Symfony integration,
 - [ ] logging using `psr/log` everything everywhere,
 - [x] add scheduler implementation using `dragonmantank/cron-expression`,
 - [ ] cron task list and detailed information restitution command,
 - [ ] meaningful information display via console commands.

# How to use

First, install it:

```sh
composer require makinacorpus/cron
```

Then proceed with one of the following.

## Standalone

### Configuring cron tasks

First, create some cron methods:

```php
namespace MyVendor\MyApp\Cron;

use MakinaCorpus\Cron\CronTask;

// Using a function.
#[CronTask(id: 'foo', schedule: '1 2 3 4 5')]
function foo(): void
{
    // Do something.
}

// Using an invokable class.
#[CronTask(id: 'bar', schedule: '@daily')]
class Bar
{
    public function __invoke(): mixed
    {
        // Do something.
    }
}

// Using an instance method.
class Buzz
{
    #[CronTask(id: 'buzz', schedule: '@monthly')]
    public function someMethod(): void
    {
    }
}

// Using a static class method.
class Fizz
{
    #[CronTask(id: 'fizz', schedule: '@weekly')]
    public function someMethod(): void
    {
    }
}
```

Then create a task registry:

```php
namespace MyVendor\MyApp\Command;

use MakinaCorpus\Cron\TaskRegistry\ArrayTaskRegistry;
use MyVendor\MyApp\Cron\Bar;
use MyVendor\MyApp\Cron\Buzz;
use MyVendor\MyApp\Cron\Fizz;

$taskRegistry = new ArrayTaskRegistry([
    'MyVendor\\MyApp\\Cron\\foo',
    new Bar(),
    [new Buzz(), 'someMethod']
    [Fizz::class, 'someMethod'],
]);
```

### Running it

Then, create a runner and execute it, this is basically the piece of code
you need to have in your CLI script that executes the cron:

```php
namespace MyVendor\MyApp\Command;

use MakinaCorpus\Cron\CronRunner;

// $taskRegistry is the instance you created upper.

$runner = new CronRunner($taskRegistry);
$runner->run();
```

And that's it.

Per default, schedule is forgiving, you may run this script only every
2 or 3 minutes, cron rules will match in a 5 minutes time span after their
due date to avoid missing running them.

## Symfony

### Installing

Start by adding the bundle to the `config/bundles.php` file:

```php
return [
    // Other bundles.
    MakinaCorpus\Cron\Bridge\Symfony\CronBundle::class => ['all' => true],
];
```

### Configuring cron tasks

Create some services that have cron task methods, it can litteraly be any class
or service, the only requirement is to set the `CronTask` attribute over the
targeted methods:

```php
namespace MyVendor\MyApp\Cron;

use MakinaCorpus\Cron\CronTask;

// Using an instance method.
class SomeClassWithCronTaskMethods
{
    #[CronTask(id: 'buzz', schedule: '@monthly')]
    public function someInstanceMethod(): void
    {
    }

    #[CronTask(id: 'buzz', schedule: '@monthly')]
    public static function someStaticMethod(): void
    {
    }
}
```

Using the `makinacorpus/argument-resolver` dependency, considering you
installed and configured the provided bundle, your methods can have other
services as parameters, they will be injected at runtimme.

Make sure they are services in `config/services.yaml` or via any other
service registration method:

```yaml
services:
    MyVendor\MyApp\Cron\SomeClassWithCronTaskMethods:
        autoconfigure: true
```

And that's it.

# Usage

## Configuring schedule implementation

### Default implementation

Default implementation if configuration is left untouched supports incomplete
POSIX cron expressions, where parts can only be single digits.

For a lot of applications, this is more than enough.

You don't need to configure anything since this is the default.

### dragonmantank/cron-expression

First install it:

```sh
composer require dragonmantank/cron-expression
```

Then, during your application bootstrap, call:

```php
use MakinaCorpus\Cron\ScheduleFactoryRegistry;
use MakinaCorpus\Cron\Schedule\CronExpressionScheduleFactory;

ScheduleFactoryRegistry::set(new CronExpressionScheduleFactory());
```

And use this API as you would normally do.

## Commands

Commands are available when using it as a Symfony bundle, but nothing prevents
you from setting up and using those outside of the Symfony full stack framework
usage.

### Run all cron tasks (that should run every minute)

Set this in your system cron, or supervisord, or any other orchestrator
application:

```sh
crontab -e

# Run every minute
/symfony/project/path/bin/console cron:run
```

You can run it manually as well:

```sh
bin/console cron:run
```

### Force run a single cron task

Simply call the same command, adding the cron task identifier as first
argument:

```sh
bin/console cron:run my_cron_task_id
```

# Task configuration

## Set a minimum interval

Write me.
