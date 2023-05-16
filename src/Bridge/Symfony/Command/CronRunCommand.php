<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\Symfony\Command;

use MakinaCorpus\Cron\CronRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'cron:run', description: 'Run cron tasks')]
final class CronRunCommand extends Command
{
    public function __construct(private CronRunner $cronRunner)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('task', InputArgument::OPTIONAL, "Single task to run, schedule will be ignored.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($id = $input->getArgument('task')) {
            $this->cronRunner->force($id);

            return self::SUCCESS;
        }

        $this->cronRunner->run();

        return self::SUCCESS;
    }
}
