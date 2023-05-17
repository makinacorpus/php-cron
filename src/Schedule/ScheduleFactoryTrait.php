<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Schedule;

use MakinaCorpus\Cron\Schedule;

trait ScheduleFactoryTrait
{
    private function separateSpecAndInterval(string $spec, ?string $minIntervalSpec = null)
    {
        if (\strpos($spec, Schedule::INTERVAL_SEPARATOR)) {
            if ($minIntervalSpec) {
                // Ignore if min interval was explicitely passed here.
                list ($spec, ) = \array_map('trim', \explode(Schedule::INTERVAL_SEPARATOR, $spec));
            } else {
                list ($spec, $minIntervalSpec) = \array_map('trim', \explode(Schedule::INTERVAL_SEPARATOR, $spec));
            }
        }

        return [$spec, $minIntervalSpec ? new \DateInterval($minIntervalSpec) : null];
    }
}
