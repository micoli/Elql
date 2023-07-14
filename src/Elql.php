<?php

declare(strict_types=1);

namespace Micoli\Elql;

use Micoli\Elql\ExpressionLanguage\ExpressionLanguageEvaluator;
use Micoli\Elql\ExpressionLanguage\ExpressionLanguageEvaluatorInterface;
use Micoli\Elql\Persister\PersisterInterface;

class Elql
{
    public function __construct(
        public readonly PersisterInterface $persister,
        private readonly ExpressionLanguageEvaluatorInterface $expressionLanguageEvaluator = new ExpressionLanguageEvaluator(),
    ) {
    }

    public function add(object ...$records): void
    {
        if (count($records) === 0) {
            return;
        }
        foreach ($records as $record) {
            $this->persister->addRecord($record);
        }
    }

    /**
     * @template T
     *
     * @param class-string<T> $model
     *
     * @return T[]
     */
    public function find(string $model, string $where = null): array
    {
        return array_values(array_filter(
            $this->persister->getRecords($model)->data,
            fn (mixed $record) => $this->match($record, $where),
        ));
    }

    /**
     * @param class-string $model
     */
    public function delete(string $model, string $where = null): void
    {
        $this->persister->updateRecords($model, array_values(array_filter(
            $this->persister->getRecords($model)->data,
            fn (mixed $record) => !$this->match($record, $where),
        )));
    }

    /**
     * @param class-string $model
     */
    public function count(string $model, string $where = null): int
    {
        return count($this->find($model, $where));
    }

    /**
     * @template T
     *
     * @param class-string<T> $model
     * @param callable(T):T $updater
     */
    public function update(string $model, callable $updater, string $where = null): void
    {
        $this->persister->updateRecords($model, array_map(
            /** @param T $record */
            fn (mixed $record): mixed => $this->match($record, $where)
                ? $updater($record)
                : $record,
            $this->persister->getRecords($model)->data,
        ));
    }

    /**
     * @param object $record
     *
     * @psalm-param T|object $record
     */
    private function match(mixed $record, ?string $where): bool
    {
        if ($where === null) {
            return true;
        }

        return (bool) $this->expressionLanguageEvaluator->evaluate($where, $record);
    }
}
