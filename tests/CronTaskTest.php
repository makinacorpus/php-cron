<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests;

use MakinaCorpus\Cron\CronTask;
use PHPUnit\Framework\TestCase;

class CronTaskTest extends TestCase
{
    public function testGetForCallableWithFunction(): void
    {
        $tested = CronTask::getForCallback('MakinaCorpus\\Cron\\Tests\\function_with_attribute');
        self::assertSame('foo', $tested->id);
        self::assertSame('This is foo.', $tested->name);
        self::assertSame('1 * 2 * 3', (string) $tested->schedule);

        $tested = CronTask::getForCallback('MakinaCorpus\\Cron\\Tests\\function_without_attribute');
        self::assertSame('MakinaCorpus\\Cron\\Tests\\function_without_attribute', $tested->id);
        self::assertNull($tested->name);
        self::assertNull($tested->schedule);
    }

    public function testGetForCallableWithInvokableClass(): void
    {
        $tested = CronTask::getForCallback(new InvokableClassWithAttribute());
        self::assertSame('buzz', $tested->id);
        self::assertSame('This is buzz.', $tested->name);
        self::assertSame('1 * 2 * 3', (string) $tested->schedule);

        $tested = CronTask::getForCallback(new InvokableClassWithoutAttribute());
        self::assertSame('MakinaCorpus\\Cron\\Tests\\InvokableClassWithoutAttribute', $tested->id);
        self::assertNull($tested->name);
        self::assertNull($tested->schedule);
    }

    public function testGetForCallableWithInstanceMethod(): void
    {
        $object = new TaskWithoutInterface();

        $tested = CronTask::getForCallback([$object, 'methodWithAttribute']);
        self::assertSame('bar', $tested->id);
        self::assertSame('This is bar.', $tested->name);
        self::assertSame('1 * 2 * 3', (string) $tested->schedule);

        $tested = CronTask::getForCallback([$object, 'methodWithoutAttribute']);
        self::assertSame('MakinaCorpus\\Cron\\Tests\\TaskWithoutInterface::methodWithoutAttribute', $tested->id);
        self::assertNull($tested->name);
        self::assertNull($tested->schedule);
    }

    public function testGetForCallableWithClassMethod(): void
    {
        $tested = CronTask::getForCallback([StaticTaskWithoutInterface::class, 'staticMethodWithAttribute']);
        self::assertSame('fizz', $tested->id);
        self::assertSame('This is fizz.', $tested->name);
        self::assertSame('1 * 2 * 3', (string) $tested->schedule);

        $tested = CronTask::getForCallback([StaticTaskWithoutInterface::class, 'staticMethodWithoutAttribute']);
        self::assertSame('MakinaCorpus\\Cron\\Tests\\StaticTaskWithoutInterface::staticMethodWithoutAttribute', $tested->id);
        self::assertNull($tested->name);
        self::assertNull($tested->schedule);
    }
}

#[CronTask(id: 'buzz', name: 'This is buzz.', schedule: '1 * 2 * 3')]
class InvokableClassWithAttribute
{
    public function __invoke()
    {
    }
}

class InvokableClassWithoutAttribute
{
    public function __invoke()
    {
    }
}

class StaticTaskWithoutInterface
{
    #[CronTask(id: 'fizz', name: 'This is fizz.', schedule: '1 * 2 * 3')]
    public static function staticMethodWithAttribute()
    {
    }

    public static function staticMethodWithoutAttribute()
    {
    }
}

class TaskWithoutInterface
{
    #[CronTask(id: 'bar', name: 'This is bar.', schedule: '1 * 2 * 3')]
    public function methodWithAttribute()
    {
    }

    public function methodWithoutAttribute()
    {
    }
}

#[CronTask(id: 'foo', name: 'This is foo.', schedule: '1 * 2 * 3')]
function function_with_attribute()
{
    
}

function function_without_attribute()
{
    
}
