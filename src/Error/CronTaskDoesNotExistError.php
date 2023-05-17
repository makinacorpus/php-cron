<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Error;

class CronTaskDoesNotExistError extends \InvalidArgumentException implements CronError
{
    public static function create(string $id, ?int $code = null, ?\Throwable $previous = null): self
    {
        $message = \sprintf("Task '%s' does not exist.", $id);
        if ($previous) {
            return new self($message, $code ?? 0, $previous);
        }
        return new self($message);
    }
}
