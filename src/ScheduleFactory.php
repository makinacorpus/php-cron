<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron;

/**
 * Make schedule parser pluggable.
 */
interface ScheduleFactory
{
    /**
     * Create instance from string.
     *
     * Expected format is UNIX cron format, such as "0 0 13 * 5" where values are:
     *   - first is minute of hour (0-59),
     *   - second is hour of day (0-23)
     *   - third is day of month (1-31)
     *   - fourth is month of year (1-12)
     *   - last is day of week (0-7), 1 is mondy, 0 and 7 are sunday.
     *
     * Every value can be one of the following:
     *
     *  - "*" (wildcard) means "every of it",
     *  - "7" (a single digit): means at the given unit,
     *  - "1,2" (coma-separated unit list): each of these units,
     *  - "1-5" (hypen-separated unit range): until first to last,
     *
     * The default implementation does not support the full posix specification,
     * only the one specified above, for simplicity. Later implementations will
     * be more complete.
     *
     * Additionnally, it can be one of the following abbreviations:
     *
     *  - "@yearly" = "0 0 1 1 *"
     *  - "@annually" = "0 0 1 1 *"
     *  - "@monthly" = "0 0 1 * *"
     *  - "@weekly" = "0 0 * * 0"
     *  - "@daily" = "0 0 * * *"
     *  - "@midnight" = "0 0 * * *"
     *  - "@hourly" = "0 * * * *"
     *
     * @param string $spec
     *   The schedule specification.
     * @param ?string $minIntervalSpec
     *   An text compatible with \DateInterval constructor.
     */
    public function fromString(string $spec, ?string $minIntervalSpec = null): Schedule;
}
