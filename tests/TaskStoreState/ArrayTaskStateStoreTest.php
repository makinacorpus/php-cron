<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\TaskStoreState;

use MakinaCorpus\Cron\TaskStateStore;
use MakinaCorpus\Cron\TaskStateStore\ArrayTaskStateStore;

class ArrayTaskStateStoreTest extends AbstractTaskStateStoreTest
{
    protected function createTaskStateStore(): TaskStateStore
    {
        return new ArrayTaskStateStore();
    }
}
