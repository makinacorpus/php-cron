<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\TaskRegistry;

use MakinaCorpus\Cron\CronTask;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;
use MakinaCorpus\Cron\TaskRegistry\ContainerTaskRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ContainerTaskRegistryTest extends TestCase
{
    public function testAll(): void
    {
        $container = new Container();
        $container->set('foo_service', new InvokableServiceMethod());
        $container->set('bar_service', new InvokableService());

        $tested = new ContainerTaskRegistry(
            [
                'foo' => ContainerTaskRegistry::createReferenceInstanceMethod('foo_service', 'doIt'),
                'bar' => ContainerTaskRegistry::createReferenceInvokableClass('bar_service'),
                'baz' => ContainerTaskRegistry::createReferenceStaticMethod(InvokableStaticMethod::class, 'fireInTheHole'),
            ],
            $container
        );

        self::assertTrue($tested->has('foo'));
        self::assertTrue($tested->has('bar'));
        self::assertTrue($tested->has('baz'));
        self::assertFalse($tested->has('non_existing'));

        $all = \iterator_to_array($tested->all());

        self::assertCount(3, $all);
    }

    public function testNonExistingRaiseError(): void
    {
        $tested = new ContainerTaskRegistry([]);

        self::expectException(CronTaskDoesNotExistError::class);
        $tested->get('this_task_does_not_exist');
    }

    public function testServiceInstanceMethod(): void
    {
        $container = new Container();
        $container->set('foo_service', new InvokableServiceMethod());

        $tested = new ContainerTaskRegistry(
            [
                'foo' => ContainerTaskRegistry::createReferenceInstanceMethod('foo_service', 'doIt')
            ],
            $container
        );

        self::assertTrue($tested->has('foo'));

        $task = $tested->get('foo');
        $callback = $task->getCallback();
        self::assertIsCallable($callback);

        self::assertSame("bwaaa", $callback());
    }

    public function testServiceInvokable(): void
    {
        $container = new Container();
        $container->set('foo_service', new InvokableService());

        $tested = new ContainerTaskRegistry(
            [
                'foo' => ContainerTaskRegistry::createReferenceInvokableClass('foo_service')
            ],
            $container
        );

        self::assertTrue($tested->has('foo'));

        $task = $tested->get('foo');
        $callback = $task->getCallback();
        self::assertIsCallable($callback);

        self::assertSame("beeeh", $callback());
    }

    public function testWithStaticMethodNotInContainer(): void
    {
        $tested = new ContainerTaskRegistry(
            [
                'foo' => ContainerTaskRegistry::createReferenceStaticMethod(InvokableStaticMethod::class, 'fireInTheHole')
            ],
        );

        self::assertTrue($tested->has('foo'));

        $task = $tested->get('foo');
        $callback = $task->getCallback();
        self::assertIsCallable($callback);

        self::assertSame("aaah", $callback());
    }
}

#[CronTask(id: 'invokable_service')]
class InvokableService
{
    public function __invoke(): string
    {
        return "beeeh";
    }
}

class InvokableServiceMethod
{
    #[CronTask(id: 'invokable_service_method')]
    public function doIt(): string
    {
        return "bwaaa";
    }
}

class InvokableStaticMethod
{
    #[CronTask(id: 'invokable_service_method')]
    public static function fireInTheHole(): string
    {
        return "aaah";
    }
}
