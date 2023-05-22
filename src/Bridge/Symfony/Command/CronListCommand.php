<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\Command;

use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskRegistry;
use MakinaCorpus\Cron\TaskStateStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'cron:list', description: 'List cron tasks')]
final class CronListCommand extends Command
{
    public function __construct(private TaskRegistry $taskRegistry, private TaskStateStore $taskStateStore)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);

        if ($output->isVerbose()) {
            $table->setHeaders(['Id', 'Active', 'Schedule', 'Interval', 'Registered', 'Last run', 'Error message', 'Error trace']);
        } else {
            $table->setHeaders(['Id', 'Active', 'Schedule', 'Interval', 'Registered', 'Last run', 'Was error']);
        }

        foreach ($this->taskRegistry->all() as $task) {
            \assert($task instanceof Task);

            $state = $this->taskStateStore->register($task);

            $row = [
                $task->getId(),
                $state->isActive() ? 'Yes' : 'No',
                $state->getSchedule() ?? $task->getDefaultSchedule(),
                $state->getSchedule()->getMinIntervalString() ?? $task->getDefaultSchedule()->getMinIntervalString(),
                $state->getRegistedAt()->format('Y-m-d H:i:s'),
                $state->getLastRun()?->format('Y-m-d H:i:s') ?? "Never",
            ];

            if ($output->isVerbose()) {
                $row[] = $state->getErrorMessage();
                $row[] = $state->getErrorTrace();
            } else {
                $row[] = $state->getErrorMessage() ? "Yes" : "No";
            }

            $table->addRow($row);
        }

        $table->render();

        return self::SUCCESS;
    }
}
