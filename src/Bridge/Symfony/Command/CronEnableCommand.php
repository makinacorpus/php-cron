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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'cron:enable', description: 'Enables one or more cron tasks')]
final class CronEnableCommand extends Command
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
    protected function configure()
    {
        $this->addArgument('tasks', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, "Task(s) to enable.");
        $this->addOption('all', 'a', InputOption::VALUE_NONE, "Enable all cron tasks.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $all = (bool) $input->getOption('all');
        $tasks = (array) $input->getArgument('tasks');

        if ($all && $tasks) {
            $io->error("You cannot specify task names and use --all at the same time.");

            return self::FAILURE;
        }

        if ($all) {
            $io->caution(
                <<<TXT
                You are going to enable all cron tasks.
                TXT
            );

            if (!$io->confirm('Enable all cron tasks?', false)) {
               return self::FAILURE;
            }

            $tasks = [];
            foreach ($this->taskRegistry->all() as $task) {
                $tasks[] = $task->getId();
            }
        }

        $count = 0;
        foreach ($tasks as $id) {
            $count++;
            $task = $this->taskRegistry->get($id);
            $this->taskStateStore->register($task);
            $this->taskStateStore->activate($task->getId());
        }
        $io->success(\sprintf('%s cron tasks enabled.', $count));

        return self::SUCCESS;
    }
}
