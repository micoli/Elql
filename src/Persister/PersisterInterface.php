<?php

declare(strict_types=1);

namespace Micoli\Elql\Persister;

interface PersisterInterface
{
    /**
     * @template T
     *
     * @param class-string<T> $model
     *
     * @return InMemoryTable<T>
     */
    public function getRecords(string $model): InMemoryTable;

    /**
     * @return array<class-string, InMemoryTable>
     */
    public function getDatabase(): array;

    public function addRecord(object $record): void;

    /**
     * @template T
     *
     * @param class-string<T> $model
     * @param array<array-key, T> $values
     */
    public function updateRecords(string $model, array $values): void;

    public function flush(): void;
}
