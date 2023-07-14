<?php

declare(strict_types=1);

namespace Micoli\Elql\Persister;

/**
 * @template T
 */
class InMemoryTable
{
    /**
     * @var T[]
     */
    public array $data;

    /**
     * @param class-string<T> $model
     */
    public function __construct(public readonly string $model, array $values)
    {
        $this->data = $values;
    }
}
