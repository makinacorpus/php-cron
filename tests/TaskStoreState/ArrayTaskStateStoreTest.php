<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\TaskStoreState;

use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskStateStore\ArrayTaskStateStore;
use PHPUnit\Framework\TestCase;

class ArrayTaskStateStoreTest extends TestCase
{
    public function testActivationStatus(): void
    {
        $task = new Task(fn () => true);
        $tested = new ArrayTaskStateStore();

        $state = $tested->register($task);
        self::assertTrue($state->isActive());

        $state = $tested->register($task);
        $tested->deactivate($task->getId());
        self::assertFalse($state->isActive());
    }
}
