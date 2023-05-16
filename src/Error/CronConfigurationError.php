<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Error;

class CronConfigurationError extends \InvalidArgumentException implements CronError
{
}
