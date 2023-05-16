<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Object that plugs into your framework or dependency injection mecanism
 * and find programmatically defined tasks.
 */
interface TaskRegistry
{
    /**
     * Get task instance.
     */
    public function get(string $id): Task;

    /**
     * Is task know.
     */
    public function has(string $id): bool;

    /**
     * Get all tasks.
     *
     * @return Task[]
     */
    public function all(): iterable;
}
