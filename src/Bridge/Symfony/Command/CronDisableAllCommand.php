<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\Command;

use MakinaCorpus\Cron\TaskRegistry;
use MakinaCorpus\Cron\TaskStateStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'cron:disable-all', description: 'Disables all cron tasks')]
final class CronDisableAllCommand extends Command
{
    public function __construct(
        private TaskRegistry $taskRegistry,
        private TaskStateStore $taskStateStore
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->caution(
            <<<TXT
            You are going to disable all cron tasks. Some tasks can be necessary for your application to work.
            You may should to enable some of them at short term.
            TXT
        );

        if (!$io->confirm('Disable all cron tasks?', false)) {
            return self::FAILURE;
        }

        $count = 0;
        foreach ($this->taskRegistry->all() as $task) {
            $count++;
            $state = $this->taskStateStore->register($task);
            $this->taskStateStore->deactivate($state->getId());
        }
        $io->success(\sprintf('%s cron tasks disabled.', $count));

        return 0;
    }
}
