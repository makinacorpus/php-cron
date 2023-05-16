# Applicative cron task runner

Simple applicative cron task implementation.

How it works:

 - You as the library user register callables as being cron tasks, using
   a descriptive attribute.
 - It literally can be any callable, a function anonymous or not, an instance
   method or a class static method.
 - You need to setup a tick for running the cron running, depending upon your
   framework, using Symfony a CLI command is provided. This tick must run very
   often, such as every minute or so.
 - When the cron runs, for every registered tasks, it checks if the current
   date matches the task schedule.
 - For schedule matching task, it checks first for the configured minimum delay
   between two run and excludes those which have been run too recently.
 - For each remaining non excluded task, it runs it, and store the run state.

And that's it. Simply put, it takes a task list, test each task schedule to
current date, and run it when it matches. Date can be passed as a parameter
which means it gives you the choice how and when to run it.

Schedule is a standard POSIX cron schedule, except that it only accepts single
digit units. It's more than enough for our use case at the time, but later it
may be extended to accept more.

State store knows nothing about schedule, and store it as a simple string,
which makes the schedule instance pluggable and replaceable, which means that
it's possible for users to plug `dragonmantank/cron-expression` instead for
example.

# Roadmap

 - [ ] `makinacorpus/goat-query` state store implementation,
 - [ ] `PDO` state store implementation,
 - [ ] unit test Symfony integration,
 - [ ] logging using `psr/log` everything everywhere,
 - [ ] add scheduler implementation using `dragonmantank/cron-expression`,
 - [ ] meaningful information display via console commands.

# How to use

First, install it:

```sh
composer require makinacorpus/cron
```

Then proceed with one of the following.

## Standalone

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

## Use makinacorpus/argument-resolver

## With Symfony

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

### Using commands

#### Run all cron tasks (that should run every minute)

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

#### Force run a single cron task

Simply call the same command, adding the cron task identifier as first
argument:

```sh
bin/console cron:run my_cron_task_id
```

# Task configuration

## Set a minimum interval

Write me.
