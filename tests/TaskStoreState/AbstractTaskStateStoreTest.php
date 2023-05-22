<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\TaskStoreState;

use MakinaCorpus\Cron\ScheduleFactoryRegistry;
use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskStateStore;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;
use PHPUnit\Framework\TestCase;

abstract class AbstractTaskStateStoreTest extends TestCase
{
    protected abstract function createTaskStateStore(): TaskStateStore;

    public function testGetRaiseErrorWhenNotExists(): void
    {
        $tested = $this->createTaskStateStore();

        self::expectException(CronTaskDoesNotExistError::class);
        $tested->get('this_task_does_not_exist');
    }

    public function testGet(): void
    {
        $id = \uniqid('some_id_');
        $tested = $this->createTaskStateStore();
        $task = new Task(fn () => null, $id, 'Some Name', null, ScheduleFactoryRegistry::get()->fromString('0 1 2 3 4'));

        $instance = $tested->register($task);

        $instance = $tested->get($id);
        self::assertSame($id, $instance->getId());
        self::assertTrue($instance->isActive());
        self::assertNull($instance->getLastRun());
        self::assertNull($instance->getErrorMessage());
        self::assertNull($instance->getErrorTrace());
        self::assertSame('0 1 2 3 4', (string) $instance->getSchedule());
    }

    public function testRegister(): void
    {
        $id = \uniqid('some_id_');
        $tested = $this->createTaskStateStore();
        $task = new Task(fn () => null, $id, 'Some Name', null, ScheduleFactoryRegistry::get()->fromString('0 1 2 3 4'));

        $instance = $tested->register($task);
        self::assertSame($id, $instance->getId());
        self::assertTrue($instance->isActive());
        self::assertNull($instance->getLastRun());
        self::assertNull($instance->getErrorMessage());
        self::assertNull($instance->getErrorTrace());
        self::assertSame('0 1 2 3 4', (string) $instance->getSchedule());

        $instance = $tested->get($id);
        self::assertSame($id, $instance->getId());
        self::assertTrue($instance->isActive());
        self::assertNull($instance->getLastRun());
        self::assertNull($instance->getErrorMessage());
        self::assertNull($instance->getErrorTrace());
        self::assertSame('0 1 2 3 4', (string) $instance->getSchedule());
    }

    public function testDelete(): void
    {
        $id = \uniqid('some_id_');
        $tested = $this->createTaskStateStore();
        $task = new Task(fn () => null, $id, 'Some Name', null, ScheduleFactoryRegistry::get()->fromString('0 1 2 3 4'));

        $tested->register($task);
        self::assertNotNull($tested->get($id));

        $tested->delete($id);
        self::expectException(CronTaskDoesNotExistError::class);
        $tested->get($id);
    }

    public function testActivate(): void
    {
        $id = \uniqid('some_id_');
        $tested = $this->createTaskStateStore();
        $task = new Task(fn () => null, $id, 'Some Name', null, ScheduleFactoryRegistry::get()->fromString('0 1 2 3 4'));

        $instance = $tested->register($task);
        self::assertTrue($instance->isActive());
        self::assertTrue($tested->get($id)->isActive());

        $tested->deactivate($id);
        self::assertFalse($tested->get($id)->isActive());

        $tested->activate($task->getId());
        self::assertTrue($tested->get($id)->isActive());
    }

    public function testMarkAsRun(): void
    {
        $id = \uniqid('some_id_');
        $tested = $this->createTaskStateStore();
        $task = new Task(fn () => null, $id, 'Some Name', null, ScheduleFactoryRegistry::get()->fromString('0 1 2 3 4'));

        $instance = $tested->register($task);
        self::assertNull($instance->getLastRun());
        $instance = $tested->get($id);
        self::assertNull($instance->getLastRun());
        self::assertNull($instance->getErrorMessage());
        self::assertNull($instance->getErrorTrace());

        $tested->markAsRun($id, new \DateTimeImmutable('2023-05-22 14:00:00'), 'foo!', 'foo\nbar');
        $instance = $tested->get($id);
        self::assertSame('2023-05-22 14:00:00', $instance->getLastRun()->format('Y-m-d H:i:s'));
        self::assertSame('foo!', $instance->getErrorMessage());
        self::assertSame('foo\nbar', $instance->getErrorTrace());

        $tested->markAsRun($id, new \DateTimeImmutable('2023-05-22 14:01:00'), null, null);
        $instance = $tested->get($id);
        self::assertSame('2023-05-22 14:01:00', $instance->getLastRun()->format('Y-m-d H:i:s'));
        self::assertNull($instance->getErrorMessage());
        self::assertNull($instance->getErrorTrace());
    }
}
