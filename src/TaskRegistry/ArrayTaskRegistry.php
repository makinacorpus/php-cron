<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\TaskRegistry;

use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskRegistry;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;

class ArrayTaskRegistry implements TaskRegistry
{
    private array $info = [];
    private array $instances = [];

    public function __construct(array $instances)
    {
        foreach ($instances as $index => $instance) {
            if ($instance instanceof Task) {
                $this->instances[$instance->getId()] = $instance;
            } else if (\is_callable($instance)) {
                $task = new Task($instance);
                $this->instances[$task->getId()] = $task;
            } else {
                throw new \InvalidArgumentException(\sprintf(
                    "Object at key '%s' is expected to be a instance of %s or a callable, found %s",
                    $index, Task::class, \get_debug_type($instance)
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): Task
    {
        return $this->instances[$id] ?? throw new CronTaskDoesNotExistError(\sprintf("Cron task '%s' does not exist.", $id));
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->instances);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): iterable
    {
        return $this->instances;
    }
}
