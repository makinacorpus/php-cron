<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use Cron\CronExpression;
use MakinaCorpus\Cron\Schedule;
use MakinaCorpus\Cron\ScheduleFactory;

class CronExpressionScheduleFactory implements ScheduleFactory
{
    /**
     * {@inheritdoc}
     */
    public function fromString(string $spec, ?string $minIntervalSpec = null): Schedule
    {
        return new CronExpressionSchedule(
            new CronExpression($spec),
            $minIntervalSpec ? new \DateInterval($minIntervalSpec) : null
        );
    }
}
