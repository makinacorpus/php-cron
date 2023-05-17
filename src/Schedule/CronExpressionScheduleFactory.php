<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use Cron\CronExpression;
use MakinaCorpus\Cron\Schedule;
use MakinaCorpus\Cron\ScheduleFactory;

class CronExpressionScheduleFactory implements ScheduleFactory
{
    use ScheduleFactoryTrait;

    /**
     * {@inheritdoc}
     */
    public function fromString(string $spec, ?string $minIntervalSpec = null): Schedule
    {
        list ($spec, $minInterval) = $this->separateSpecAndInterval($spec, $minIntervalSpec);

        return new CronExpressionSchedule(new CronExpression($spec), $minInterval);
    }
}
