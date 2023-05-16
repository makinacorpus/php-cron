<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\DependencyInjection;

/**
 * Abstract of command bus, allowing us to plug over various implementations.
 */
interface CronCommandBus
{
    public function dispatch($command): void;
}
