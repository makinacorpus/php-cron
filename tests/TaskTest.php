<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests;

use MakinaCorpus\Cron\CronTask;
use MakinaCorpus\Cron\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testGetIdInitializes(): void
    {
        $task = new Task(__NAMESPACE__ . '\\cron_task');
        self::assertSame('foo', $task->getId());
    }

    public function testGetNameInitializes(): void
    {
        $task = new Task(__NAMESPACE__ . '\\cron_task');
        self::assertSame('The foo', $task->getName());
    }

    public function testGetDescriptionInitializes(): void
    {
        $task = new Task(__NAMESPACE__ . '\\cron_task');
        self::assertSame('This is the foo', $task->getDescription());
    }

    public function testGetCallback(): void
    {
        $task = new Task(__NAMESPACE__ . '\\cron_task');
        self::assertSame('Pouet', ($task->getCallback())());
    }
}

#[CronTask(id: 'foo', name: 'The foo', description: 'This is the foo', schedule: '0 0 1 * *')]
function cron_task()
{
    return "Pouet";
}
