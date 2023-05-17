<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Tests\Schedule;

use MakinaCorpus\Cron\ScheduleFactory;
use MakinaCorpus\Cron\Schedule\CronExpressionScheduleFactory;

class CronExpressionScheduleTest extends AbstractScheduleTest
{
    protected function getFactory(): ScheduleFactory
    {
        return new CronExpressionScheduleFactory();
    }

    protected function allowFuzzy(): bool
    {
        return false;
    }
}
