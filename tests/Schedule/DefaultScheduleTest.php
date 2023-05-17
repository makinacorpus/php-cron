<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\Schedule;

use MakinaCorpus\Cron\ScheduleFactory;
use MakinaCorpus\Cron\Schedule\DefaultScheduleFactory;

class DefaultScheduleTest extends AbstractScheduleTest
{
    protected function getFactory(): ScheduleFactory
    {
        return new DefaultScheduleFactory();
    }
}
