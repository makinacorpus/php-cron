<?php

declare(strict_types=1);

namespace MakinaCorpus\Cron\Bridge\GoatQuery;

use Goat\Driver\Error\TableDoesNotExistError;
use Goat\Query\Expression\TableExpression;
use Goat\Runner\Runner;
use MakinaCorpus\Cron\Schedule;
use MakinaCorpus\Cron\ScheduleFactoryRegistry;
use MakinaCorpus\Cron\Task;
use MakinaCorpus\Cron\TaskState;
use MakinaCorpus\Cron\TaskStateStore;
use MakinaCorpus\Cron\Error\CronTaskDoesNotExistError;

class GoatQueryTaskStateStore implements TaskStateStore
{
    private bool $tableChecked = false;

    public function __construct(
        private Runner $runner,
        private string $table = 'public.cron'
    ) {}

    /**
     * {@inheritdoc}
     */
    public function get(string $id): TaskState
    {
        $this->checkTable();

        return $this
            ->runner
            ->execute(
                "SELECT * FROM ? WHERE id = ?",
                [
                    $this->tableExpression(),
                    $id,
                ]
            )
            ->setHydrator($this->hydrator())
            ->fetch() ?? throw CronTaskDoesNotExistError::create($id)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Task $task): TaskState
    {
        $this->checkTable();

        return $this
            ->runner
            ->getQueryBuilder()
            ->merge($this->tableExpression())
            ->setKey(['id'])
            ->values([
                'id' => $task->getId(),
                'schedule' => $task->getDefaultSchedule()->toString(true),
            ])
            ->returning('*')
            ->execute()
            ->setHydrator($this->hydrator())
            ->fetch()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): void
    {
        $this->get($id);

        $this->runner->perform("DELETE FROM ? WHERE id = ?", [$this->tableExpression(), $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(string $id, ?Schedule $schedule): void
    {
        $this->get($id);

        $this->runner->execute("UPDATE ? SET schedule = ? WHERE id = ?", [$this->tableExpression(), $schedule->toString(), $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(string $id): void
    {
        $this->get($id);

        $this->runner->execute("UPDATE ? SET active = ? WHERE id = ?", [$this->tableExpression(), true, $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(string $id): void
    {
        $this->get($id);

        $this->runner->execute("UPDATE ? SET active = ? WHERE id = ?", [$this->tableExpression(), false, $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function markAsRun(string $id, \DateTimeInterface $lastRun, ?string $errorMessage, ?string $errorTrace): void
    {
        $this->get($id);

        $this
            ->runner
            ->execute(
                <<<SQL
                UPDATE ?
                SET
                    last_run = ?,
                    error_message = ?,
                    error_trace = ?
                WHERE
                    id = ?
                SQL,
                [
                    $this->tableExpression(),
                    $lastRun,
                    $errorMessage,
                    $errorTrace,
                    $id,
                ]
            )
        ;
    }

    /**
     * Line hydrator.
     */
    protected function hydrator(): callable
    {
        return fn (array $row) => new TaskState(
            $row['id'],
            (bool) $row['active'],
            $this->toDate($row['registered']),
            $this->toDate($row['last_run']),
            ScheduleFactoryRegistry::get()->fromString($row['schedule']),
            $row['error_message'],
            $row['error_trace'],
        );
    }

    /**
     * Convert SQL expression to date.
     */
    protected function toDate(mixed $date): ?\DateTimeInterface
    {
        if (!$date || $date instanceof \DateTimeInterface) {
            return $date;
        }
        return new \DateTimeImmutable($date);
    }

    /**
     * Create table expression.
     */
    protected function tableExpression(): TableExpression
    {
        return new TableExpression($this->table, 'cron');
    }

    /**
     * Ensure table exists.
     */
    protected function checkTable(): void
    {
        if ($this->tableChecked) {
            return;
        }

        try {
            $this->runner->execute("SELECT 1 FROM ?", [$this->tableExpression()]);
            $this->tableChecked = true;
        } catch (TableDoesNotExistError $e) {
            $this->runner->execute(
                <<<SQL
                CREATE TABLE ? (
                    "id" text NOT NULL,
                    "active" bool NOT NULL DEFAULT true,
                    "registered" timestamp NOT NULL DEFAULT current_timestamp,
                    "last_run" timestamp DEFAULT NULL,
                    "schedule" text DEFAULT NULL,
                    "error_message" text DEFAULT NULL,
                    "error_trace" text DEFAULT NULL,
                    PRIMARY KEY ("id")
                );
                SQL,
                [$this->tableExpression()]
            );
        }
    }
}
