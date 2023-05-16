<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Error;

class CronTaskDoesNotExistError extends \InvalidArgumentException implements CronError
{
}
