<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\TaskRegistry;

use MakinaCorpus\Cron\CronTask;
use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;
use MakinaCorpus\Cron\TaskRegistry\ArrayTaskRegistry;
use PHPUnit\Framework\TestCase;

class ArrayTaskRegistryTest extends TestCase
{
    public function testConstructorWithCallable(): void
    {
        $tested = new ArrayTaskRegistry([
            __NAMESPACE__ . '\\foo',
        ]);

        self::assertSame("Cassoulet", ($tested->get('foo')->getCallback())());
    }

    public function testConstructorWithTask(): void
    {
        $tested = new ArrayTaskRegistry([
            new Task(__NAMESPACE__ . '\\foo')
        ]);

        self::assertSame("Cassoulet", ($tested->get('foo')->getCallback())());
    }

    public function testConstructorOtherRaiseError(): void
    {
        self::expectException(\InvalidArgumentException::class);
        new ArrayTaskRegistry([new \DateTime()]);
    }

    public function testGetRaiseErrorWhenNotExist(): void
    {
        $tested = new ArrayTaskRegistry([
            __NAMESPACE__ . '\\foo',
        ]);

        self::expectException(CronTaskDoesNotExistError::class);
        $tested->get('bar');
    }

    public function testHas(): void
    {
        $tested = new ArrayTaskRegistry([
            __NAMESPACE__ . '\\foo',
        ]);

        self::assertTrue($tested->has('foo'));
        self::assertFalse($tested->has('bar'));
    }
}

#[CronTask(id: 'foo')]
function foo()
{
    return "Cassoulet";
}
