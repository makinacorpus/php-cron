<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\TaskStoreState;

use Goat\Driver\DriverFactory;
use Goat\Runner\Testing\DatabaseAwareQueryTestTrait;
use MakinaCorpus\Cron\TaskStateStore;
use MakinaCorpus\Cron\TaskStateStore\GoatQueryTaskStateStore;

class GoatQueryTaskStateStoreTest extends AbstractTaskStateStoreTest
{
    use DatabaseAwareQueryTestTrait;

    protected function createTaskStateStore(): TaskStateStore
    {
        if (!$databaseUri = \getenv('GOAT_QUERY_URI')) {
            self::markTestSkipped("Missing 'GOAT_QUERY_URI' environment variable, skipping test.");
        }

        return new GoatQueryTaskStateStore(DriverFactory::fromUri($databaseUri)->getRunner());
    }
}
